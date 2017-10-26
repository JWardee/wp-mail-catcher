<?php

namespace MailCatcher\Models;

use MailCatcher\GeneralHelper;
use Underscore\Types\Arrays;

class Mail
{
	/**
	 * TODO: Likely a bug whereby attachments and additional
	 * headers aren't resent because they're an array with ['url']
	 * and ['id'] in them
    */
	static public function resend($ids)
    {
        $logs = Logs::get(array(
			'post__in' => $ids
		));

        foreach ($logs as $log) {
            wp_mail($log['email_to'], $log['subject'], $log['message'], $log['additional_headers'], $log['attachment_file_paths']);
        }
    }

    static public function export($ids)
    {
		$logs = Logs::get(array(
			'post__in' => $ids,
			'date_time_format' => 'd-M-Y @ H:s'
		));

		/**
		 * Only export the "legal columns"
		 * so no seralised objects are exported etc
		 */
		foreach ($logs as &$log) {
			$log = array_filter($log, function($key) {
				return in_array($key, GeneralHelper::$csvExportLegalColumns);
			}, ARRAY_FILTER_USE_KEY);

			if (isset($log['attachments']) && !empty($log['attachments']) && !is_array($log['attachments'])) {
				$log['attachments'] = json_decode($log['attachments']);
				$log['attachments'] = array_column($log['attachments'], 'url');
				$log['attachments'] = GeneralHelper::arrayToString($log['attachments']);
			}

			if (isset($log['additional_headers']) && !empty($log['additional_headers']) && !is_array($log['additional_headers'])) {
				$log['additional_headers'] = json_decode($log['additional_headers']);
				$log['additional_headers'] = GeneralHelper::arrayToString($log['additional_headers']);
			}
		}

		$headings = array_keys($logs[0]);
		array_walk($headings, function(&$heading) {
			$heading = GeneralHelper::slugToLabel($heading);
		});

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . GeneralHelper::$csvExportFileName . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, $headings);

        foreach ($logs as $k => $v) {
			if (is_array($v)) {
				$v = Arrays::flatten($v, ', ');
			}

            fputcsv($out, $v);
        }

		fclose($out);
		exit;
    }

    static public function add($headerKeys, $headerValues, $attachmentIds, $subject, $message)
    {
        $tos = array();
        $headers = array();
        $attachments = array();

        for ($i = 0; $i < count($headerKeys); $i++) {
            switch ($headerKeys[$i]) {
                case ('to') :
                    $tos[] = $headerValues[$i];
                break;
                case ('other') :
                    $headers[] = $headerValues[$i];
                break;
                default:
                    $headers[] = $headerKeys[$i] . ': ' . $headerValues[$i];
                break;
            }
        }

        foreach ($attachmentIds as $attachment_id) {
            if (empty($attachment_id)) {
                continue;
            }

            $attachments[] = get_attached_file($attachment_id);
        }

        wp_mail($tos, $subject, $message, $headers, $attachments);
    }
}
