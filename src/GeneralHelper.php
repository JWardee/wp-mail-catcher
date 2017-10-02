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
	static public $uploadsFolderInfo;
	static public $pluginAssetsUrl;
	static public $pluginViewDirectory;
	static public $attachmentNotInMediaLib;
	public static $attachmentNotImageThumbnail;

	static public function setSettings()
	{
		self::$csvExportFileName = 'MailCatcherExport_' . date('d-m-Y_H-i-s') . '.csv';
		self::$csvExportLegalColumns = array('time', 'subject', 'email_to', 'message', 'attachments', 'additional_headers', 'status', 'error');
		self::$tableName = 'mail_catcher_logs';
		self::$languageDomain = 'mail-catcher-text';
		self::$adminUrl = admin_url();
		self::$pluginPath = __DIR__ . '/..';
		self::$pluginUrl = plugins_url('..', self::$pluginPath);
		self::$adminPageSlug = 'mail-catcher';
		self::$uploadsFolderInfo = wp_upload_dir();
		self::$pluginAssetsUrl = self::$pluginUrl . '/assets';
		self::$pluginViewDirectory = __DIR__ . '/views';
		self::$attachmentNotInMediaLib = 'An attachment was sent but it was not in the media library';
		self::$attachmentNotImageThumbnail = self::$pluginAssetsUrl . '/file-icon.png';
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

	static public function sanitiseForQuery($value)
	{
		// TODO: sanitize_title_for_query breaks deletion
		switch (gettype($value))
		{
			case ('array') :
				array_walk_recursive($value, function(&$value) {
//					$value = sanitize_title_for_query($value);
				});
				break;
			default :
//				$value = sanitize_title_for_query($value);
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

		$sql = "SELECT post_id
                FROM " . $wpdb->prefix . "postmeta
				WHERE meta_value LIKE '%" . $urls[0] . "%'";

		if (is_array($urls) && count($urls) > 1) {
			foreach (Arrays::removeFirst($urls) as $url) {
				$sql .= " OR meta_value LIKE '%" . $url . "%'";
			}
		}

		$sql .= " AND meta_key = '_wp_attachment_metadata'";

		$results = $wpdb->get_results($sql, ARRAY_N);

		if (isset($results[0])) {
			return $results[0];
		}

		return [];
	}

	public static function redirectToThisHomeScreen()
	{
		//TODO: add wp nonce
		header('Location: ' . GeneralHelper::$adminUrl . '?page=' . GeneralHelper::$adminPageSlug);
		exit;
	}
}


