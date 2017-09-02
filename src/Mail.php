<?php

namespace MailCatcher;

class Mail
{
    public static function resend($ids)
    {
        $logs = Logs::get(array(
			'post__in' => $ids
		));

        foreach ($logs as $log) {
            wp_mail($log['emailto'], $log['subject'], $log['message']);
        }
    }

    public static function export($ids)
    {
		$logs = Logs::get(array(
			'post__in' => $ids
		));

        $filename = 'MailCatcher_Export_' . date('His') . '.csv';

		if (!isset($GLOBALS['phpunit_test'])) {
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}

        $out = fopen('php://output', 'w');

        fputcsv($out, array_keys($logs[0]));

		// TODO: don't use seralize for security - use json_encode instead
        foreach ($logs as $k => $v) {
//			if (is_serialized($v) !== false) {
////				$v = unserialize($v);
//				continue;
//			}

            fputcsv($out, $v);
        }


		fclose($out);
    }

    public static function add($headerKeys, $headerValues, $attachmentIds, $subject, $message)
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
        //TODO: change url
        //TODO: add wp nonce
        header('Location: http://wordpress.local/wp-admin/admin.php?page=mail-catcher');
        exit;
    }
}
