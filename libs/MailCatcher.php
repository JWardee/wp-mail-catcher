<?php
class MailCatcher
{
    public static $table_name = 'mail_catcher_logs';

    public function __construct()
    {
        global $plugin_path;

        register_activation_hook($plugin_path . '/MailCatcher.php', array($this, 'install'));
        register_deactivation_hook($plugin_path . '/MailCatcher.php', array($this, 'uninstall'));
        //register_uninstall_hook( $plugin_path, array($this, 'uninstall'));

        add_filter('wp_mail', array($this, 'logWpMail'));
        add_action('admin_menu', array($this, 'route'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));

        add_action('admin_init', function() {
            // TODO: Refactor export, export2 $_REQUEST
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'export' || isset($_REQUEST['action2']) && $_REQUEST['action2'] == 'export') {
                Mail::export($_REQUEST['id']);
            }
        });
    }

    public function enqueue()
    {
        wp_enqueue_style('admin_css', plugins_url('/assets/admin.css', __DIR__));
        wp_enqueue_script('admin_js', plugins_url('/assets/admin.js', __DIR__), array('jquery'));
    }

    public function route()
    {
        add_menu_page('Mail Catcher', 'Mail Catcher', 'manage_options', 'mail-catcher', function() {
//            $args = array(
//                'label' => __('Members per page', 'pippin'),
//                'default' => 10,
//                'option' => 'pippin_per_page'
//            );
//            add_screen_option( 'per_page', $args );

            require __DIR__ . '/../views/logs.php';
        }, 'dashicons-email-alt');
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