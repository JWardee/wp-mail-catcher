<?php
class MailCatcher
{
    public static $table_name = 'mail_catcher_logs';

    public function __construct()
    {
        register_activation_hook( __FILE__, array($this, 'install'));
        register_deactivation_hook( __FILE__, array($this, 'uninstall'));
        //register_uninstall_hook( __FILE__, array($this, 'uninstall'));

        add_filter('wp_mail', array($this, 'logWpMail'));
        add_action('admin_menu', array($this, 'route'));
    }

    public function route()
    {
        add_menu_page('Mail Catcher', 'Mail Catcher', 'manage_options', 'mail-catcher', function() {
            require __DIR__ . '/../views/logs.php';
        });
    }

    public function install()
    {
        global $wpdb;

        $sql = "CREATE TABLE " . $wpdb->prefix . MailCatcher::$table_name . " (
                  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                  emailto text DEFAULT NULL,
                  subject text DEFAULT NULL,
                  message text DEFAULT NULL,
                  backtrace_segment text NOT NULL,
                  status bool DEFAULT 1 NOT NULL,
                  error text DEFAULT NULL,
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