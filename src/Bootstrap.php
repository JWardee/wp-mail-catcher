<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Mail;
use WpMailCatcher\Models\Settings;

class Bootstrap
{
    private $screenOptions;

    public function __construct()
    {
        GeneralHelper::setSettings();
        LoggerFactory::set();
        $this->registerCronTasks();
        $this->screenOptions = ScreenOptions::getInstance();

        // ensure that is_plugin_active_for_network() is defined.
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (is_plugin_active_for_network(GeneralHelper::$pluginMainPhpFile)) {
            $hook = 'wp_initialize_site';

            if (version_compare(get_bloginfo('version'), '5.1', '<')) {
                $hook = 'wpmu_new_blog';
            }

            add_action($hook, [$this, 'install']);
        }

        add_filter('wpmu_drop_tables', function ($tables) {
            $tables[] = $GLOBALS['wpdb']->prefix . GeneralHelper::$tableName;
            return $tables;
        });

        add_filter('plugin_action_links_wp-mail-catcher/WpMailCatcher.php', [$this, 'extraPluginLinks']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('plugins_loaded', function () {
            // Fix for db_version falling out of sync in previous versions
            if (in_array(Settings::get('db_version'), ['2.0.1', '2.0.2', '2.0.3', '2.0.4'])) {
                DatabaseUpgradeManager::getInstance()->doUpgrade(true);
            } else {
                // Silently run database upgrades - if there are any
                DatabaseUpgradeManager::getInstance()->doUpgrade();
            }
        });
        add_action('admin_menu', function () {
            $this->registerPages();
            $this->route();
        });
    }

    public function extraPluginLinks($links)
    {
        $href = 'admin.php?page=' . GeneralHelper::$adminPageSlug . '-settings';

        array_unshift(
            $links,
            '<a href="' . $href . '">' . __('Settings', 'wp-mail-catcher') . '</a>'
        );

        return $links;
    }

    public function registerCronTasks()
    {
        if (Settings::get('auto_delete')) {
            $cronManager = CronManager::getInstance();
            $cronManager->addTask(
                'WpMailCatcher\ExpiredLogManager::removeExpiredLogs',
                Settings::$howOftenCheckForExpiredMessages
            );
        }
    }

    public function enqueue()
    {
        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'admin_css',
            GeneralHelper::$pluginAssetsUrl . '/global.min.css',
            [],
            GeneralHelper::$pluginVersion
        );
        wp_enqueue_script(
            'admin_js',
            GeneralHelper::$pluginAssetsUrl . '/global.min.js',
            ['jquery'],
            GeneralHelper::$pluginVersion,
            true
        );
        wp_localize_script('admin_js', GeneralHelper::$tableName, [
            'plugin_url' => GeneralHelper::$pluginUrl,
        ]);
    }

    public function registerPages()
    {
        $mainPageHook = add_menu_page(
            'WP Mail Catcher',
            'WP Mail Catcher',
            Settings::get('default_view_role'),
            GeneralHelper::$adminPageSlug,
            function () {
                require GeneralHelper::$pluginViewDirectory . '/Log.php';
            },
            'dashicons-email-alt'
        );

        add_submenu_page(
            GeneralHelper::$adminPageSlug,
            __('Settings', 'wp-mail-catcher'),
            __('Settings', 'wp-mail-catcher'),
            Settings::get('default_settings_role'),
            GeneralHelper::$settingsPageSlug,
            function () {
                require GeneralHelper::$pluginViewDirectory . '/Settings.php';
            }
        );

        $this->screenOptions->newOption($mainPageHook, 'per_page', [
            'default' => GeneralHelper::$logsPerPage
        ]);
//        $this->screenOptions->newHelpTab($mainPageHook, 'General', '<strong>blah</strong> blah');
    }

    /**
     * Nonces are verified per action in route() before any value read here is acted upon
     */
    private function getRequestString(string $key): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_REQUEST[$key]) || !is_scalar($_REQUEST[$key])) {
            return '';
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return sanitize_text_field(wp_unslash($_REQUEST[$key]));
    }

    private function getRequestIds(): array
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_REQUEST['id'])) {
            return [];
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return array_values(array_filter(array_map('absint', (array)wp_unslash($_REQUEST['id']))));
    }

    /**
     * Returns the unslashed value, callers must sanitize it (e.g. Mail::add())
     */
    private function getPostValue(string $key)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        return isset($_POST[$key]) ? wp_unslash($_POST[$key]) : null;
    }

    private function isAction(string $action): bool
    {
        return $this->getRequestString('action') === $action || $this->getRequestString('action2') === $action;
    }

    private function verifyNonce(string ...$actions)
    {
        $nonce = $this->getRequestString('_wpnonce');

        foreach ($actions as $action) {
            if (wp_verify_nonce($nonce, $action)) {
                return;
            }
        }

        wp_die(esc_html(GeneralHelper::$failedNonceMessage));
    }

    public function route()
    {
        if ($this->getRequestString('page') !== GeneralHelper::$adminPageSlug) {
            return;
        }

        $action = $this->getRequestString('action');

        if (current_user_can(Settings::get('default_view_role'))) {
            /** Perform database upgrade */
            if ($action === 'upgrade-database') {
                $this->verifyNonce('upgrade-database');

                DatabaseUpgradeManager::getInstance()->doUpgrade();
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Export all messages */
            if ($action === 'export-all') {
                $this->verifyNonce('bulk-logs');

                $args = Logs::getTotalAmount() > GeneralHelper::$logLimitBeforeWarning ? [
                    'posts_per_page' => absint($this->getRequestString('posts_per_page')),
                    'paged' => max(1, absint($this->getRequestString('paged'))),
                ] : [
                    'posts_per_page' => -1
                ];

                Mail::export(wp_list_pluck(
                    Logs::get($args),
                    'id'
                ));
            }

            /** Export message(s) */
            if ($this->isAction('export')) {
                $this->verifyNonce('bulk-logs');

                $ids = $this->getRequestIds();

                if (empty($ids)) {
                    GeneralHelper::redirectToThisHomeScreen();
                }

                Mail::export($ids);
            }

            /** Resend message(s) */
            if ($this->isAction('resend') && !empty($this->getRequestIds())) {
                $this->verifyNonce('bulk-logs', 'modal-resend');

                Mail::resend($this->getRequestIds());
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Delete message(s) */
            if ($this->isAction('delete') && !empty($this->getRequestIds())) {
                $this->verifyNonce('bulk-logs');

                Logs::delete($this->getRequestIds());
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Send mail */
            if ($action === 'new_mail') {
                check_admin_referer('new_mail');

                Mail::add(
                    $this->getPostValue('header_keys'),
                    $this->getPostValue('header_values'),
                    $this->getPostValue('attachment_ids'),
                    $this->getPostValue('subject'),
                    $this->getPostValue('message'),
                    !empty($this->getPostValue('is_html'))
                );
                GeneralHelper::redirectToThisHomeScreen();
            }

            if ($action === 'single_mail' && !empty($this->getRequestIds())) {
                $log = Logs::get(['post__in' => [$this->getRequestIds()[0]]])[0];
                $view = GeneralHelper::$pluginViewDirectory;
                $view .= $log['is_html'] ? '/HtmlMessage.php' : '/TextMessage.php';

                require $view;
                exit;
            }
        }

        if (current_user_can(Settings::get('default_settings_role'))) {
            if ($action === 'rerun-migrations') {
                $this->verifyNonce('rerun_migrations');

                DatabaseUpgradeManager::getInstance()->doUpgrade(true);
                GeneralHelper::redirectToThisHomeScreen([
                    'trigger-rerun-migration-success' => true,
                    'page' => GeneralHelper::$adminPageSlug . '-settings'
                ]);
            }

            if ($action === 'trigger-auto-delete') {
                $this->verifyNonce('trigger_auto_delete');

                ExpiredLogManager::removeExpiredLogs();
                GeneralHelper::redirectToThisHomeScreen([
                    'trigger-auto-delete-success' => true,
                    'page' => GeneralHelper::$adminPageSlug . '-settings'
                ]);
            }

            if ($action !== 'update_settings') {
                return;
            }

            $this->verifyNonce('update_settings');

            $autoDelete = $this->getRequestString('auto_delete') === 'true';

            CronManager::getInstance()->clearTasks();

            $updateSuccess = Settings::update([
                'default_view_role' => sanitize_key($this->getRequestString('default_view_role')),
                'default_settings_role' => sanitize_key($this->getRequestString('default_settings_role')),
                'auto_delete' => $autoDelete,
                'timescale' => $autoDelete ? absint($this->getRequestString('timescale')) : null,
            ]);

            GeneralHelper::redirectToThisHomeScreen([
                'update_success' => $updateSuccess,
                'page' => GeneralHelper::$adminPageSlug . '-settings'
            ]);
        }
    }

    public function install($newSite = null)
    {
        global $wpdb;

        if ($newSite != null) {
            // $new_site will only be passed when we're called via the wp_insert_site (WP >=5.1)
            // or wpmu_new_blog (WP < 5.1) actions being fired.  When wp_insert_site is fired,
            // it passes a WP_Site object; whereas, when wpmu_new_blog fires, it passes the
            // blog_id.
            if ('wp_initialize_site' === current_action()) {
                $newSite = $newSite->blog_id;
            }

            switch_to_blog($newSite);
        }

        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . GeneralHelper::$tableName . " (
                id int NOT NULL AUTO_INCREMENT,
                time int NOT NULL,
                email_to text DEFAULT NULL,
                subject text DEFAULT NULL,
                message text DEFAULT NULL,
                backtrace_segment text NOT NULL,
                status bool DEFAULT 1 NOT NULL,
                error text DEFAULT NULL,
                attachments text DEFAULT NULL,
                additional_headers text DEFAULT NULL,
                PRIMARY KEY  (id)
                ) " . $wpdb->get_charset_collate() . ";";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        Settings::installOptions();

        DatabaseUpgradeManager::getInstance()->doUpgrade();

        if ($newSite != null) {
            restore_current_blog();
        }
    }

    public static function deactivate()
    {
        CronManager::getInstance()->clearTasks();
    }

    public static function uninstall()
    {
        self::deactivate();

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Dropping the plugin's own table, the name is not user input
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . GeneralHelper::$tableName);

        Settings::uninstallOptions();
    }
}
