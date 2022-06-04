<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

class DatabaseUpgradeManager
{
    static private $instance = false;

    private $dbVersion;
    private $upgradePaths = [
        '2.0.0' => 'doV2Upgrade',
    ];

    private function __construct()
    {
        $this->dbVersion = Settings::get('db_version');
    }

    public static function getInstance()
    {
        if (self::$instance == false) {
            self::$instance = new DatabaseUpgradeManager();
        }

        return self::$instance;
    }


    public function isUpgradeRequired()
    {
        foreach ($this->upgradePaths as $version => $method) {
            if ($this->dbVersion < $version) {
                return true;
            }
        }

        return false;
    }

    public function doUpgrade()
    {
        if (!$this->isUpgradeRequired()) {
            return;
        }

        foreach ($this->upgradePaths as $version => $method) {
            if ($this->dbVersion < $version) {
                $this->{$method}();
            }
        }

        Settings::update(['db_version' => GeneralHelper::$pluginVersion]);
    }

    private function doV2Upgrade()
    {
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
}
