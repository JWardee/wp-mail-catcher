<?php

namespace MailCatcher;

use Underscore\Types\Arrays;
use Underscore\Types\Strings;

class GeneralHelper
{
	public static $pluginPath;
	public static $pluginUrl;
	public static $languageDomain = 'mail-catcher-text';
	public static $tableName = 'mail_catcher_logs';

    public static function arrayToString($pieces, $glue = ', ')
    {
		$result = Arrays::flatten($pieces);

		if (is_array($result)) {
			$result = implode($glue, $pieces);
		}

		return $result;
	}

    public static function slugToLabel($slug)
    {
		$illegalChars = array(
			'-', '_'
		);

		foreach ($illegalChars as $illegalChar) {
			$slug = str_replace($illegalChar, ' ', $slug);
		}

		return Strings::title($slug);
    }

	public static function labelToSlug($label)
	{
		return Strings::slugify($label);
	}

	public static function sanitiseForQuery($string)
	{
		return sanitize_title_for_query($string);
	}
}


