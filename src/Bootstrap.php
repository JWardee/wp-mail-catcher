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
        add_action('plugins_loaded', function() {
            load_plugin_textdomain('WpMailCatcher', false, GeneralHelper::$adminPageSlug . '/languages/');

            // Fix for db_version falling out of sync in previous versions
            if (in_array(Settings::get('db_version'), ['2.0.1', '2.0.2', '2.0.3', '2.0.4'])) {
                DatabaseUpgradeManager::getInstance()->doUpgrade(true);
            } else {
                // Silently run database upgrades - if there are any
                DatabaseUpgradeManager::getInstance()->doUpgrade();
            }
        });
        add_action('admin_menu', function() {
            $this->registerPages();
            $this->route();
        });
    }

    public function extraPluginLinks($links)
    {
        array_unshift($links, '<a href="admin.php?page=' . GeneralHelper::$adminPageSlug . '-settings">' . __('Settings', 'WpMailCatcher') . '</a>');
        return $links;
    }

    public function registerCronTasks()
    {
        if (Settings::get('auto_delete') == true) {
            $cronManager = CronManager::getInstance();
            $cronManager->addTask('WpMailCatcher\ExpiredLogManager::removeExpiredLogs', Settings::$howOftenCheckForExpiredMessages);
        }
    }

    public function enqueue()
    {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('admin_css',
            GeneralHelper::$pluginAssetsUrl . '/global.min.css?v=' . GeneralHelper::$pluginVersion);
        wp_enqueue_script('admin_js',
            GeneralHelper::$pluginAssetsUrl . '/global.min.js?v=' . GeneralHelper::$pluginVersion,
            ['jquery']);
        wp_localize_script('admin_js', GeneralHelper::$tableName, [
            'plugin_url' => GeneralHelper::$pluginUrl,
        ]);
    }

    public function registerPages()
    {
        $mainPageHook = add_menu_page('WP Mail Catcher', 'WP Mail Catcher', Settings::get('default_view_role'),
            GeneralHelper::$adminPageSlug, function() {
                require GeneralHelper::$pluginViewDirectory . '/Log.php';
            }, 'dashicons-email-alt'
        );

        add_submenu_page(GeneralHelper::$adminPageSlug, __('Settings', 'WpMailCatcher'), __('Settings', 'WpMailCatcher'), Settings::get('default_settings_role'),
            GeneralHelper::$settingsPageSlug, function() {
                require GeneralHelper::$pluginViewDirectory . '/Settings.php';
            }
        );

        $this->screenOptions->newOption($mainPageHook, 'per_page', [
            'default' => GeneralHelper::$logsPerPage
        ]);
//        $this->screenOptions->newHelpTab($mainPageHook, 'General', '<strong>blah</strong> blah');
    }

    public function route()
    {
        if (!isset($_GET['page']) || $_GET['page'] !== GeneralHelper::$adminPageSlug) {
            return;
        }

        if (current_user_can(Settings::get('default_view_role'))) {
            /** Perform database upgrade */
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'upgrade-database') {
                DatabaseUpgradeManager::getInstance()->doUpgrade();
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Export all messages */
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'export-all') {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-logs')) {
                    wp_die(GeneralHelper::$failedNonceMessage);
                }

                $args = Logs::getTotalAmount() > GeneralHelper::$logLimitBeforeWarning ? [
                    'posts_per_page' => $_REQUEST['posts_per_page'],
                    'paged' => $_REQUEST['paged'],
                ] : [
                    'posts_per_page' => -1
                ];

                Mail::export(wp_list_pluck(
                    Logs::get($args),
                    'id'
                ));
            }

            /** Export message(s) */
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'export' ||
                isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'export') {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-logs')) {
                    wp_die(GeneralHelper::$failedNonceMessage);
                }

                Mail::export($_REQUEST['id']);
            }

            /** Resend message(s) */
            if (((isset($_REQUEST['action']) && $_REQUEST['action'] == 'resend') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'resend')) &&
                isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-logs')) {
                    wp_die(GeneralHelper::$failedNonceMessage);
                }

                Mail::resend($_REQUEST['id']);
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Delete message(s) */
            if (((isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') || (isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'delete')) &&
                isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-logs')) {
                    wp_die(GeneralHelper::$failedNonceMessage);
                }

                Logs::delete($_REQUEST['id']);
                GeneralHelper::redirectToThisHomeScreen();
            }

            /** Send mail */
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new_mail') {
                if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'new_mail')) {
                    wp_die(GeneralHelper::$failedNonceMessage);
                }

                Mail::add(
                    $_POST['header_keys'],
                    $_POST['header_values'],
                    $_POST['attachment_ids'],
                    $_POST['subject'],
                    $_POST['message'],
                    $_POST['is_html']
                );
                GeneralHelper::redirectToThisHomeScreen();
            }

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'single_mail' &&
                isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                $log = Logs::get(['post__in' => [$_REQUEST['id']]])[0];
                $view = GeneralHelper::$pluginViewDirectory;
                $view .= $log['is_html'] ? '/HtmlMessage.php' : '/TextMessage.php';

                require $view;
                exit;
            }
        }

        if (current_user_can(Settings::get('default_settings_role'))) {
            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'rerun-migrations') {
                DatabaseUpgradeManager::getInstance()->doUpgrade(true);
                GeneralHelper::redirectToThisHomeScreen([
                    'trigger-rerun-migration-success' => true,
                    'page' => GeneralHelper::$adminPageSlug . '-settings'
                ]);
            }

            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'trigger-auto-delete') {
                ExpiredLogManager::removeExpiredLogs();
                GeneralHelper::redirectToThisHomeScreen([
                    'trigger-auto-delete-success' => true,
                    'page' => GeneralHelper::$adminPageSlug . '-settings'
                ]);
            }

            if (!isset($_REQUEST['action']) || $_REQUEST['action'] !== 'update_settings') {
                return;
            }

            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'update_settings')) {
                wp_die(GeneralHelper::$failedNonceMessage);
            }

            $_POST['auto_delete'] = $_POST['auto_delete'] === 'true';

            CronManager::getInstance()->clearTasks();

            $updateSuccess = Settings::update([
                'default_view_role' => $_POST['default_view_role'],
                'default_settings_role' => $_POST['default_settings_role'],
                'auto_delete' => $_POST['auto_delete'],
                'timescale' => $_POST['auto_delete'] == true ? $_POST['timescale'] : null,
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

    static public function deactivate()
    {
        CronManager::getInstance()->clearTasks();
    }

    static public function uninstall()
    {
        self::deactivate();

        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . GeneralHelper::$tableName . ";";
        $wpdb->query($sql);

        Settings::uninstallOptions();
    }
}
