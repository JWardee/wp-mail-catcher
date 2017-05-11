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

add_action( 'admin_menu', function() {
    add_menu_page('Mail Catcher', 'Mail Catcher', 'manage_options', 'mail-catcher', function() {
            include __DIR__ . '/MailAdminTable.php';
            //Create an instance of our package class...
            $testListTable = new TT_Example_List_Table();
            //Fetch, prepare, sort, and filter our data...
            $testListTable->prepare_items();

            ?>
            <div class="wrap">

                <div id="icon-users" class="icon32"><br/></div>
                <h2>List Table Test</h2>

                <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                    <p>This page demonstrates the use of the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt> class in plugins.</p>
                    <p>For a detailed explanation of using the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt>
                        class in your own plugins, you can view this file <a href="<?php echo admin_url( 'plugin-editor.php?plugin='.plugin_basename(__FILE__) ); ?>" style="text-decoration:none;">in the Plugin Editor</a> or simply open <tt style="color:gray;"><?php echo __FILE__ ?></tt> in the PHP editor of your choice.</p>
                    <p>Additional class details are available on the <a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WordPress Codex</a>.</p>
                </div>

                <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
                <form id="movies-filter" method="get">
                    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <!-- Now we can render the completed list table -->
                    <?php $testListTable->display() ?>
                </form>

            </div>
        <?php
    });
});



