<?php
class MailCatcher
{
	public static $language_domain = 'mail-catcher-text';
    public static $table_name = 'mail_catcher_logs';

    public function __construct()
    {
		$this->install();

        global $plugin_path;

        register_activation_hook($plugin_path . '/MailCatcher.php', array($this, 'install'));
//        register_deactivation_hook($plugin_path . '/MailCatcher.php', array($this, 'uninstall'));
        //register_uninstall_hook( $plugin_path, array($this, 'uninstall'));

        add_filter('wp_mail', array($this, 'logWpMail'));
        add_action('admin_menu', array($this, 'route'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
		add_action('plugins_loaded', array($this, 'plugins_loaded'));
        add_action('admin_init', array($this, 'admin_init'));
    }

	public function admin_init()
	{
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
	}

	public function plugins_loaded()
	{
		load_plugin_textdomain(MailCatcher::$language_domain, false, $GLOBALS['plugin_path'] . '/languages');
	}

    public function enqueue()
    {
        wp_enqueue_style('admin_css', plugins_url('/assets/admin.css', __DIR__));
        wp_enqueue_script('admin_js', plugins_url('/assets/admin.js', __DIR__), array('jquery'), '?');

        wp_localize_script('admin_js', MailCatcher::$table_name, array(
            'plugin_url' => $GLOBALS['plugin_path'],
        ));
    }

    public function route()
    {
        add_menu_page('Mail Catcher', 'Mail Catcher', 'manage_options', 'mail-catcher', array($this, 'require_view'), 'dashicons-email-alt');
    }

	public function require_view()
	{
		require __DIR__ . '/../views/logs.php';
	}

    public function install()
    {
        global $wpdb;

//		$this->uninstall();

        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . MailCatcher::$table_name . " (
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

    public function uninstall()
    {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . MailCatcher::$table_name . ";";
        $wpdb->query($sql);
    }

    public function logWpMail()
    {
        $mail_catcher = new MailCatcherLog();
        add_action('phpmailer_init', array($mail_catcher, 'phpMailerInit'));
        add_action('wp_mail_failed', array($mail_catcher, 'phpMailerFailed'));
    }
}
