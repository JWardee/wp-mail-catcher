<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Settings;

class ExpiredLogManager
{
    public static function deletionIntervals()
    {
        return apply_filters(GeneralHelper::$actionNameSpace . '_deletion_intervals', Settings::$defaultDeletionIntervals);
    }

    public static function removeExpiredLogs($timeInterval = null)
    {
        Logs::deleteOlderThan($timeInterval);
    }
}
