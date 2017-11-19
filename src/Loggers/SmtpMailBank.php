<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;

class SmtpMailBank extends Logger
{
	public function __construct()
	{
		add_filter('retrieve_logs', [$this, 'transformer']);
	}

	public function transformer($logs)
	{
		global $wpdb;
		$email_logs_data = $wpdb->get_results
		(
			$wpdb->prepare
			(
				"SELECT * FROM " . $wpdb->prefix . "mail_bank_meta
				 WHERE meta_key = %s ORDER BY id DESC", "email_logs"
			)
		);

//		$unserialized_email_logs_data = \get_mail_bank_log_data_maybe_unserialize($email_logs_data, $start_date, $end_date);


		foreach ($email_logs_data as $value) {
			$tmp = unserialize($value->meta_value);

			$logs[] = [
				'id' => '999',
				'email_to' => $tmp['email_to'],
				'subject' => $tmp['subject'],
				'message' => $tmp['content'],
				'status' => $tmp['status'] == 'Sent' ? '1' : '0',
				'time' => $tmp['timestamp'],
				'additional_headers' => [
					'cc' => $tmp['cc'],
					'bcc' => $tmp['bcc'],
				]
			];
		}

//		var_dump($email_logs_data[0]);
//		var_dump($logs[0]);
//		var_dump($logs);
//		exit;

		return $logs;
	}
}
