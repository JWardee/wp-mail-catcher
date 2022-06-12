<?php

namespace WpMailCatcher;

class GeneralHelper
{
    static public $csvItemDelimiter = ' | ';
    static public $logsPerPage = 5;
    static public $pluginPath;
    static public $pluginUrl;
    static public $pluginVersion;
    static public $tableName;
    static public $csvExportLegalColumns;
    static public $csvExportFileName;
    static public $adminUrl;
    static public $adminPageSlug;
    static public $uploadsFolderInfo;
    static public $pluginAssetsUrl;
    static public $pluginViewDirectory;
    static public $attachmentNotInMediaLib;
    static public $attachmentNotImageThumbnail;
    static public $failedNonceMessage;
    static public $pluginMainPhpFile;
    static public $settingsPageSlug;
    static public $logLimitBeforeWarning;
    static public $humanReadableDateFormat;
    static public $namespacePrefix;
    static public $reviewLink;
    static public $actionNameSpace;
    static public $htmlEmailHeader = 'content-type: text/html;';

    static public function setSettings()
    {
        self::$csvExportFileName = 'WpMailCatcherExport_' . date('d-m-Y_H-i-s') . '.csv';
        self::$csvExportLegalColumns = [
            'time',
            'subject',
            'email_to',
            'message',
            'attachments',
            'additional_headers',
            'status',
            'error'
        ];
        self::$tableName = 'mail_catcher_logs';
        self::$adminUrl = admin_url();
        self::$pluginPath = __DIR__ . '/..';
        self::$pluginMainPhpFile = self::$pluginPath . '/WpMailCatcher.php';
        self::$pluginUrl = plugins_url('..', self::$pluginPath);
        self::$adminPageSlug = 'wp-mail-catcher';
        self::$uploadsFolderInfo = wp_upload_dir();
        self::$pluginAssetsUrl = self::$pluginUrl . '/assets';
        self::$pluginViewDirectory = __DIR__ . '/Views';
        self::$attachmentNotInMediaLib = 'An attachment was sent but it was not in the media library';
        self::$attachmentNotImageThumbnail = self::$pluginAssetsUrl . '/file-icon.png';
        self::$failedNonceMessage = 'Failed security check';
        self::$pluginVersion = get_file_data(self::$pluginMainPhpFile, ['Version'], 'plugin')[0];
        self::$settingsPageSlug = self::$adminPageSlug . '-settings';
        self::$logLimitBeforeWarning = 100;
        self::$humanReadableDateFormat = get_option('date_format') . ' H:i:s';
        self::$namespacePrefix = GeneralHelper::$adminPageSlug . '_';
        self::$reviewLink = 'https://wordpress.org/support/plugin/wp-mail-catcher/reviews/#new-post';
        self::$actionNameSpace = 'wp_mail_catcher';
    }

    /**
     * Flattens an array to dot notation.
     *
     * @param array $array An array
     * @param string $separator The character to flatten with
     * @param string $parent The parent passed to the child (private)
     *
     * @return array Flattened array to one level
     */
    static public function flatten($array, $separator = '.', $parent = null)
    {
        if (!is_array($array)) {
            return $array;
        }

        $_flattened = [];

        // Rewrite keys
        foreach ($array as $key => $value) {
            if ($parent) {
                $key = $parent . $separator . $key;
            }
            $_flattened[$key] = self::flatten($value, $separator, $key);
        }

        // Flatten
        $flattened = [];
        foreach ($_flattened as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, $value);
            } else {
                $flattened[$key] = $value;
            }
        }

        return $flattened;
    }

    static public function arrayToString($pieces, $glue = ', ')
    {
        $result = self::flatten($pieces);

        if (is_array($result)) {
            $result = implode($glue, $pieces);
        }

        return $result;
    }

    static public function slugToLabel($slug)
    {
        $illegalChars = [
            '-',
            '_'
        ];

        foreach ($illegalChars as $illegalChar) {
            $slug = str_replace($illegalChar, ' ', $slug);
        }

        return mb_convert_case($slug, MB_CASE_TITLE, 'UTF-8');
    }

    static public function labelToSlug($label)
    {
        $label = str_replace(' ', '-', $label);
        return strtolower($label);
    }

    static public function sanitiseForQuery($value)
    {
        switch (gettype($value)) {
            case ('array') :
                array_walk_recursive($value, function (&$value) {
                    $value = sanitize_text_field($value);
                });
                break;
            default :
                $value = sanitize_text_field($value);
                break;

        }

        return $value;
    }

    static public function getAttachmentIdsFromUrl($urls)
    {
        if (empty($urls)) {
            return [];
        }

        global $wpdb;

        $urls = self::sanitiseForQuery($urls);

        $sql = "SELECT DISTINCT post_id
                FROM " . $wpdb->prefix . "postmeta
				WHERE meta_value LIKE '%" . $urls[0] . "%'";

        if (is_array($urls) && count($urls) > 1) {
            array_shift($urls);
            foreach ($urls as $url) {
                $sql .= " OR meta_value LIKE '%" . $url . "%'";
            }
        }

        $sql .= " AND meta_key = '_wp_attached_file'";

        $results = $wpdb->get_results($sql, ARRAY_N);

        if (isset($results[0])) {
            return array_column($results, 0);
        }

        return [];
    }

    static public function redirectToThisHomeScreen($params = [])
    {
        if (!isset($params['page'])) {
            $params['page'] = GeneralHelper::$adminPageSlug;
        }

        header('Location: ' . GeneralHelper::$adminUrl . 'admin.php?' . http_build_query($params));
        exit;
    }

    static public function doesArrayContainSubString($array, $subString)
    {
        foreach ($array as $element) {
            if (stripos($element, $subString) !== false) {
                return true;
            }
        }

        return false;
    }

    static public function searchForSubStringInArray($array, $subString)
    {
        foreach ($array as $element) {
            if (stripos($element, $subString) !== false) {
                return $element;
            }
        }

        return false;
    }

    static public function getHumanReadableTime($from, $to, $suffix = ' ago')
    {
        return sprintf(
            _x('%s' . $suffix, '%s = human-readable time difference', 'WpMailCatcher'),
            human_time_diff($from, $to)
        );
    }

    /**
     * Retrieves current timestamp using WPs native functions and translation
     * @param $from @type timestamp
     * @param string $suffix
     * @return string
     */
    static public function getHumanReadableTimeFromNow($from, $suffix = ' ago')
    {
        return self::getHumanReadableTime($from, time(), $suffix);
    }

    /**
     * Generates a near unique, replicable key based on a string value
     * @param $slugOrLabel
     * @return string
     */
    static public function getPrefixedSlug($slugOrLabel)
    {
        return self::$namespacePrefix . self::labelToSlug($slugOrLabel);
    }

    static public function dd($value)
    {
        echo '<pre>';
        print_r($value);
        exit;
    }
}


