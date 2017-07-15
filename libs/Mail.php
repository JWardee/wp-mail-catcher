<?php
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

		if (!empty($GLOBALS['phpunit_test'])) {
//			return $out;
		}


		exit;
    }

    public static function add($header_keys, $header_values, $attachment_ids, $subject, $message)
    {
        $tos = array();
        $headers = array();
        $attachments = array();

        for ($i = 0; $i < count($header_keys); $i++) {
            switch ($header_keys[$i]) {
                case ('to') :
                    $tos[] = $header_values[$i];
                break;
                case ('other') :
                    $headers[] = $header_values[$i];
                break;
                default:
                    $headers[] = $header_keys[$i] . ': ' . $header_values[$i];
                break;
            }
        }

        foreach ($attachment_ids as $attachment_id) {
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
