<?php

namespace MailCatcher\Loggers;

use MailCatcher\GeneralHelper;
use WP_Error;

// TODO: Add grunt support
// TODO: Change 'time' to be timestamp and change human diff functions
// TODO: Add additional headers column and ensure htmlspecialchars
// TODO: Test "to" addresses accepts and processes all to formats in WP docs
// TODO: Test plugin works with Mailgun, Sparkpost etc
// TODO: Check all errors are logged by phpMailerFailed
// TODO: Redo db schema to just seralize a modified version of the $mailer object like getAdditionalHeaders()
// TODO: Add doc blocks

class Logger
{
	protected $id = null;

	public function __construct($args)
	{
		/**
		 * Add code here to choose which logger to instansiate
		 */
		new WpMail($args);
	}

	public function recordMail($args)
	{
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . GeneralHelper::$tableName,
			$this->getMailArgs($args)
		);

		$this->id = $wpdb->insert_id;
	}

	public function recordError(WP_Error $error)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . GeneralHelper::$tableName,
			array(
				'status' => 0,
				'error' => $error->errors['wp_mail_failed'][0],
			),
			array('id' => $this->id)
		);
	}

	protected function getMailArgs($args)
	{
		return array(
			'time' => current_time('mysql'),
			'email_to' => GeneralHelper::arrayToString($args['to']),
			'subject' => $args['subject'],
			'message' => $args['message'],
			'backtrace_segment' => json_encode($this->getBacktrace()),
			'status' => 1,
			'attachments' => json_encode($this->getAttachmentLocations($args['attachments'])),
			'additional_headers' => json_encode($args['headers'])
		);
	}

	protected function getAttachmentLocations($attachments)
	{
		if (empty($attachments)) {
			return array();
		}

		$result = array();

		array_walk($attachments, function(&$value) {
			$value = str_replace(GeneralHelper::$uploadsFolderInfo['path'], '', $value);
		});

		if (isset($_POST['attachment_ids'])) {
			$attachmentIds = array_values(array_filter($_POST['attachment_ids']));
		} else {
			$attachmentIds = GeneralHelper::getAttachmentIdsFromUrl($attachments);
		}

		for ($i = 0; $i < count($attachments); $i++) {
			$result[] = array(
				'id' => $attachmentIds[$i],
				'url' => GeneralHelper::$uploadsFolderInfo['url'] . $attachments[$i]
			);
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
