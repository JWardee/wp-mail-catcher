<?php

namespace MailCatcher;

class Bootstrap
{
    public function __construct()
    {
		GeneralHelper::$pluginPath = __DIR__ . '/..';
		GeneralHelper::$pluginUrl = plugins_url('..', GeneralHelper::$pluginPath);

        add_filter('wp_mail', array($this, 'logWpMail'), 999999);
        add_action('admin_menu', array($this, 'route'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
		add_action('plugins_loaded', function() {
			load_plugin_textdomain(GeneralHelper::$languageDomain, false, GeneralHelper::$pluginPath . '/languages');
		});

        add_action('admin_init', function() {
			// TODO: Refactor export, export2 $_REQUEST
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'export' || isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'export') {
				Mail::export($_REQUEST['id']);
			}

			if (isset($_GET['action']) && $_GET['action'] == 'new_mail') {
				Mail::add($_POST['header_keys'],
					$_POST['header_values'],
					$_POST['attachment_ids'],
					$_POST['subject'],
					$_POST['message']
				);
			}
		});
    }

    public function enqueue()
    {
        wp_enqueue_style('admin_css', GeneralHelper::$pluginUrl . '/assets/admin.css');
        wp_enqueue_script('admin_js', GeneralHelper::$pluginUrl . '/assets/admin.js', array('jquery'), '?');

        wp_localize_script('admin_js', GeneralHelper::$tableName, array(
            'plugin_url' => GeneralHelper::$pluginPath,
        ));
    }

    public function route()
    {
        add_menu_page('Mail Catcher', 'Mail Catcher', 'manage_options', 'mail-catcher', function() {
			require GeneralHelper::$pluginPath . '/views/logs.php';
		}, 'dashicons-email-alt');
    }

    public function install()
    {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . GeneralHelper::$tableName . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                  emailto text DEFAULT NULL,
                  subject text DEFAULT NULL,
                  message text DEFAULT NULL,
                  backtrace_segment text NOT NULL,
                  status bool DEFAULT 1 NOT NULL,
                  error text DEFAULT NULL,
                  attachments text DEFAULT NULL,
                  additional_headers text DEFAULT NULL,
                  PRIMARY KEY  (id)
                ) " . $wpdb->get_charset_collate() . ";";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    static public function uninstall()
    {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . GeneralHelper::$tableName . ";";
        $wpdb->query($sql);
    }

    public function logWpMail($args)
    {
        $mailCatcher = new MailCatcherLog();
		$mailCatcher->phpMailerInit($args);
//		add_filter('wp_mail', array($mail_catcher, 'phpMailerInit'));
        add_action('wp_mail_failed', array($mailCatcher, 'phpMailerFailed'), 999999);

		return $args;
    }
}
