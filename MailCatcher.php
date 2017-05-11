<?php
/**
 * @package Mail_Catcher
 * @version 1
 */
/*
Plugin Name: Mail Catcher
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: derp
Author: James Ward
Version: 1
Author URI: http://jamesward.io
*/

include_once __DIR__ . '/MailCatcherLog.php';

class MailCatcher
{
    public static $table_name = 'mail_catcher_logs';

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
                  success bool DEFAULT 1 NOT NULL,
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
}

$mail_catcher = new MailCatcher();
register_activation_hook( __FILE__, array($mail_catcher, 'install'));
register_deactivation_hook( __FILE__, array($mail_catcher, 'uninstall'));
//register_uninstall_hook( __FILE__, array($mail_catcher, 'uninstall'));

add_filter('wp_mail', function($args) {
    $mail_catcher = new MailCatcherLog();

    add_action('phpmailer_init', array($mail_catcher, 'phpMailerInit'));
    add_action('wp_mail_failed', array($mail_catcher, 'phpMailerFailed'));

    return $args;
});


