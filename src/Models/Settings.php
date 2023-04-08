<?php

namespace WpMailCatcher\Models;

class Settings
{
    public static $optionsName = 'mailcatcher_settings';
    public static $howOftenCheckForExpiredMessages = 'daily';
    private static $settings = null;
    public static $defaultSettings = [
        'default_view_role' => 'manage_options',
        'default_settings_role' => 'manage_options',
        'auto_delete' => true,
        'timescale' => 2419200, // 28 days
        'db_version' => '0',
    ];
    public static $defaultDeletionIntervals = [
        604800 => '1 week',
        1209600 => '2 weeks',
        1814400 => '3 weeks',
        2419200 => '4 weeks',
        15780000 => '6 months'
    ];

    public static function get($key = null, $bypassCache = false)
    {
        if (self::$settings == null || $bypassCache) {
            $options = unserialize(get_option(self::$optionsName, null));

            if (!is_array($options)) {
                self::installOptions();
            } else {
                self::$settings = array_merge(self::$defaultSettings, $options);
            }
        }

        if ($key != null) {
            return self::$settings[$key] ?? self::$defaultSettings[$key];
        }

        return self::$settings;
    }

    public static function update($newValues): bool
    {
        $settings = self::get();

        foreach ($newValues as $key => $newValue) {
            $settings[$key] = $newValue;
        }

        self::$settings = $settings;
        return update_option(self::$optionsName, serialize($settings));
    }

    public static function installOptions($force = false)
    {
        if ($force || ! get_option(self::$optionsName, false)) {
            add_option(self::$optionsName, serialize(self::$defaultSettings), '', 'no');
            self::$settings = self::$defaultSettings;
        }
    }

    public static function uninstallOptions()
    {
        delete_option(self::$optionsName);
    }
}
