<?php

namespace MailCatcher;

use MailCatcher\Models\Logs;
use MailCatcher\Models\Mail;
use MailCatcher\Models\Settings;

class Bootstrap
{
    public function __construct()
    {
		GeneralHelper::setSettings();
		LoggerFactory::Set();
		$this->registerCronTasks();

        add_action('admin_menu', array($this, 'route'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
		add_action('plugins_loaded', function() {
			load_plugin_textdomain('MailCatcher', false, GeneralHelper::$pluginPath . '/languages');
		});
    }

	public function registerCronTasks()
	{
		if (Settings::get('auto_delete') == true) {
			$cronManager = CronManager::getInstance();
			$cronManager->addTask('MailCatcher\Models\Logs::truncate', Settings::get('timescale'), 'Truncate');
		}
	}

    public function enqueue()
    {
        wp_enqueue_style('admin_css', GeneralHelper::$pluginAssetsUrl . '/admin.css');
        wp_enqueue_script('admin_js', GeneralHelper::$pluginAssetsUrl . '/admin.js', array('jquery'), '?');
        wp_localize_script('admin_js', GeneralHelper::$tableName, array(
            'plugin_url' => GeneralHelper::$pluginUrl,
        ));
    }

    public function route()
    {
		add_menu_page('Mail Catcher', 'Mail Catcher', Settings::get('default_view_role'), GeneralHelper::$adminPageSlug, function() {
			require GeneralHelper::$pluginViewDirectory . '/Log.php';
		}, 'dashicons-email-alt');

		add_submenu_page(GeneralHelper::$adminPageSlug, 'Settings', 'Settings', Settings::get('default_settings_role'), 'settings', function() {
			require GeneralHelper::$pluginViewDirectory . '/Settings.php';
		});

		// TODO: Refactor export, export2 $_REQUEST
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'export' || isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'export') {
			Mail::export($_REQUEST['id']);
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'resend' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
			Mail::resend($_REQUEST['id']);
			GeneralHelper::redirectToThisHomeScreen();
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
			Logs::delete($_REQUEST['id']);
			GeneralHelper::redirectToThisHomeScreen();
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'new_mail') {
			Mail::add($_POST['header_keys'], $_POST['header_values'], $_POST['attachment_ids'], $_POST['subject'], $_POST['message']);
			GeneralHelper::redirectToThisHomeScreen();
		}

		// TODO: Sanitise
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'update_settings') {
			$_POST['auto_delete'] = $_POST['auto_delete'] === 'true';

			$cronManager = CronManager::getInstance();
			$cronManager->clearTasks();

			$updateSuccess = Settings::update([
				'default_view_role' => $_POST['default_view_role'],
				'default_settings_role' => $_POST['default_settings_role'],
				'auto_delete' => $_POST['auto_delete'],
				'timescale' => $_POST['auto_delete'] == true ? $_POST['timescale'] : null,
			]);

			GeneralHelper::redirectToThisHomeScreen([
				'update_success' => $updateSuccess,
				'page' => 'settings'
			]);
		}
    }

    public function install()
    {
        global $wpdb;

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
    }

	static public function deactivate()
	{
		$cronManager = CronManager::getInstance();
		$cronManager->clearTasks();
		self::uninstall();
	}

    static public function uninstall()
    {
//		self::deactivate();

        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . GeneralHelper::$tableName . ";";
        $wpdb->query($sql);

		Settings::uninstallOptions();
    }
}
