<?php

namespace WpMailCatcher\Models;

class Settings
{
    static public $optionsName = 'mailcatcher_settings';
    static public $howOftenCheckForExpiredMessages = 'daily';
    static private $settings = null;
    static public $defaultSettings = [
        'default_view_role' => 'manage_options',
        'default_settings_role' => 'manage_options',
        'auto_delete' => true,
        'timescale' => 2419200
    ];
    static public $defaultDeletionIntervals = [
        604800 => '1 week',
        1209600 => '2 weeks',
        1814400 => '3 weeks',
        2419200 => '4 weeks',
        15780000 => '6 months'
    ];

    static public function get($key = null)
    {
        if (self::$settings == null) {
            self::$settings = unserialize(get_option(self::$optionsName, null));
        }

        if (self::$settings == null) {
            self::installOptions();
        }

        if ($key != null) {
            return isset(self::$settings[$key]) ? self::$settings[$key] : self::$defaultSettings[$key];
        }

        return self::$settings;
    }

    static public function update($newValues)
    {
        $settings = self::get();

        foreach ($newValues as $key => $newValue) {
            $settings[$key] = $newValue;
        }

        self::$settings = $settings;
        return update_option(self::$optionsName, serialize($settings));
    }

    static public function installOptions($force = false)
    {
        if ($force == true || get_option(self::$optionsName, false) == false) {
            add_option(self::$optionsName, serialize(self::$defaultSettings), '', 'no');
            self::$settings = self::$defaultSettings;
        }
    }

    public static function uninstallOptions()
    {
        delete_option(self::$optionsName);
    }
}
