<?php

namespace MailCatcher;

use Underscore\Types\Arrays;
use Underscore\Types\Strings;

class GeneralHelper
{
	static public $pluginPath;
	static public $pluginUrl;
	static public $languageDomain;
	static public $tableName;
	static public $csvExportLegalColumns;
	static public $csvExportFileName;
	static public $adminUrl;
	static public $adminPageSlug;

	static public function setSettings()
	{
		self::$csvExportFileName = 'MailCatcherExport_' . date('d-m-Y_H-i-s') . '.csv';
		self::$csvExportLegalColumns = array('time', 'subject', 'emailto', 'message', 'status', 'error');
		self::$tableName = 'mail_catcher_logs';
		self::$languageDomain = 'mail-catcher-text';
		self::$adminUrl = admin_url();
		self::$pluginPath = __DIR__ . '/..';
		self::$pluginUrl = plugins_url('..', self::$pluginPath);
		self::$adminPageSlug = 'mail-catcher';
	}

    static public function arrayToString($pieces, $glue = ', ')
    {
		$result = Arrays::flatten($pieces);

		if (is_array($result)) {
			$result = implode($glue, $pieces);
		}

		return $result;
	}

    static public function slugToLabel($slug)
    {
		$illegalChars = array(
			'-', '_'
		);

		foreach ($illegalChars as $illegalChar) {
			$slug = str_replace($illegalChar, ' ', $slug);
		}

		return Strings::title($slug);
    }

	static public function labelToSlug($label)
	{
		return Strings::slugify($label);
	}

	static public function sanitiseForQuery($string)
	{
		return sanitize_title_for_query($string);
	}
}


