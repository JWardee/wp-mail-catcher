<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;
use WP_Error;

// TODO: Add doc blocks
// TODO: Refactor Logs.php and Mail.php dbTransform and export to their own fractals

abstract class Logger
{
	protected $id = null;

	public function __construct()
	{
		add_action('wp_mail', [$this, 'recordMail'], 999999);
		add_action('wp_mail_failed', [$this, 'recordError'], 999999);
	}

	public function recordMail($args)
	{
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . GeneralHelper::$tableName,
			$this->getMailArgs($args)
		);

		$this->id = $wpdb->insert_id;

		if (!isset($args['to']) || $args['to'] == null) {
			$args['to'] = [];
		}

		return $args;
	}

	public function recordError(WP_Error $error)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . GeneralHelper::$tableName,
			[
				'status' => 0,
				'error' => $error->errors['wp_mail_failed'][0],
			],
			['id' => $this->id]
		);
	}

	protected function getMailArgs($args)
	{
		return [
			'time' => time(),
			'email_to' => GeneralHelper::arrayToString($args['to']),
			'subject' => $args['subject'],
			'message' => $args['message'],
			'backtrace_segment' => json_encode($this->getBacktrace()),
			'status' => 1,
			'attachments' => json_encode($this->getAttachmentLocations($args['attachments'])),
			'additional_headers' => json_encode($args['headers'])
		];
	}

	protected function getAttachmentLocations($attachments)
	{
		if (empty($attachments)) {
			return [];
		}

		$result = [];

		array_walk($attachments, function(&$value) {
			$value = str_replace(GeneralHelper::$uploadsFolderInfo['basedir'] . '/', '', $value);
		});

		if (isset($_POST['attachment_ids'])) {
			$attachmentIds = array_values(array_filter($_POST['attachment_ids']));
		} else {
			$attachmentIds = GeneralHelper::getAttachmentIdsFromUrl($attachments);

			if (empty($attachmentIds)) {
				return [
					[
						'id' => -1,
					]
				];
			}
		}

		if (empty($attachmentIds)) {
			return [];
		}

		for ($i = 0; $i < count($attachments); $i++) {
			$result[] = [
				'id' => $attachmentIds[$i],
				'url' => GeneralHelper::$uploadsFolderInfo['url'] . $attachments[$i]
			];
		}

		return $result;
	}

	private function getBacktrace()
	{
		$backtraceSegment = null;
		$backtrace = debug_backtrace();

		foreach ($backtrace as $segment) {
			if ($segment['function'] == 'wp_mail') {
				$backtraceSegment = $segment;
			}
		}

		return $backtraceSegment;
	}
}
