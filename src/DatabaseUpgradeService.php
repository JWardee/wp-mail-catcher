<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

class DatabaseUpgradeService
{
    private $dbVersion;
    private $upgradePaths = [];

    public function __construct($upgradePaths, $currentDbVersion)
    {
        $this->upgradePaths = $upgradePaths;
        $this->dbVersion = $currentDbVersion;
    }

    public function isUpgradeRequired()
    {
        foreach ($this->upgradePaths as $version => $function) {
            if ($this->dbVersion < $version) {
                return true;
            }
        }

        return false;
    }

    public function doUpgrade($forceUpgrade = false)
    {
        if (!$forceUpgrade && !$this->isUpgradeRequired()) {
            return;
        }

        foreach ($this->upgradePaths as $version => $function) {
            if ($forceUpgrade || $this->dbVersion < $version) {
                $function();
            }
        }

        Settings::update(['db_version' => array_key_last($this->upgradePaths)]);
    }
}
