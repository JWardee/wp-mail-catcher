<?php

namespace MailCatcher;

class Mail
{
    static public function resend($ids)
    {
        $logs = Logs::get(array(
			'post__in' => $ids
		));

        foreach ($logs as $log) {
            wp_mail($log['emailto'], $log['subject'], $log['message']);
        }
    }

    static public function export($ids)
    {
		$logs = Logs::get(array(
			'post__in' => $ids
		));

		/**
		 * Only export the "legal columns"
		 * so no seralised objects are exported etc
		 */
		foreach ($logs as &$log) {
			$log = array_filter($log, function($key) {
				return in_array($key, GeneralHelper::$csvExportLegalColumns);
			}, ARRAY_FILTER_USE_KEY);
		}

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . GeneralHelper::$csvExportFileName . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($logs[0]));

        foreach ($logs as $k => $v) {
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

        //TODO: add wp nonce
        header('Location: ' . GeneralHelper::$adminUrl . '/admin.php?page=' . GeneralHelper::$adminPageSlug);
        exit;
    }
}
