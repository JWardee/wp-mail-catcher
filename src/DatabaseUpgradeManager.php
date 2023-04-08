<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

class DatabaseUpgradeManager
{
    private static $instance = false;

    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        // When adding a new migration use the current/next plugin version as the
        // key rather than just incrementing the previous key
        self::$instance = new DatabaseUpgradeService([
            '2.0.0' => function () {
                global $wpdb;

                // dbDelta creates a diff between the table schemas, and executes
                // the necessary SQL so they match. In this case we add the column
                // is_html and default it to false
                $sql = "CREATE TABLE " . $wpdb->prefix . GeneralHelper::$tableName . " (
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
                        is_html bool DEFAULT 0,
                        PRIMARY KEY  (id)
                        ) " . $wpdb->get_charset_collate() . ";";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
        ], Settings::get('db_version'));

        return self::$instance;
    }
}
