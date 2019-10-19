<?php

namespace WpMailCatcher\Models;

use WpMailCatcher\GeneralHelper;

class Mail
{
    static public function resend($ids)
    {
        $logs = Logs::get([
            'post__in' => $ids
        ]);

        foreach ($logs as $log) {
            wp_mail(
                $log['email_to'],
                $log['subject'],
                $log['message'],
                $log['additional_headers'],
                $log['attachment_file_paths']
            );
        }
    }

    static public function export($ids, $forceBrowserDownload = true)
    {
        $logs = Logs::get([
            'post__in' => $ids,
            'date_time_format' => 'd-M-Y @ H:s',
            'posts_per_page' => -1
        ]);

        if (count($logs) == 0) {
            GeneralHelper::redirectToThisHomeScreen();
        }

        /**
         * Only export the "legal columns"
         * so no seralised objects are exported etc
         */
        foreach ($logs as &$log) {
            $log = array_filter($log, function ($key) {
                return in_array($key, GeneralHelper::$csvExportLegalColumns);
            }, ARRAY_FILTER_USE_KEY);

            if (isset($log['attachments']) && !empty($log['attachments']) && is_array($log['attachments'])) {
                $log['attachments'] = array_column($log['attachments'], 'url');
                $log['attachments'] = GeneralHelper::arrayToString($log['attachments'],
                    GeneralHelper::$csvItemDelimiter);
            } else {
                $log['attachments'] = '-';
            }

            if (isset($log['additional_headers']) && !empty($log['additional_headers']) && is_array($log['additional_headers'])) {
                $log['additional_headers'] = GeneralHelper::arrayToString($log['additional_headers'],
                    GeneralHelper::$csvItemDelimiter);
            } else {
                $log['additional_headers'] = '-';
            }

            if ($log['status'] == true) {
                $log['error'] = 'None';
                $log['status'] = 'Successful';
            } else {
                $log['status'] = 'Failed';
            }
        }

        $headings = array_keys($logs[0]);
        array_walk($headings, function (&$heading) {
            $heading = GeneralHelper::slugToLabel($heading);
        });

        if ($forceBrowserDownload == true) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . GeneralHelper::$csvExportFileName . '"');
            self::processLogToCsv($headings, $logs);
            exit;
        } else {
            ob_start();
            self::processLogToCsv($headings, $logs);
            return ob_get_clean();
        }
    }

    static private function processLogToCsv($headings, $logs)
    {
        $out = fopen('php://output', 'w');
        fputcsv($out, $headings);

        foreach ($logs as $k => $v) {
            if (is_array($v)) {
                $v = GeneralHelper::flatten($v, ', ');
            }

            fputcsv($out, $v);
        }

        fclose($out);
    }

    static public function add($headerKeys, $headerValues, $attachmentIds, $subject, $message)
    {
        $tos = [];
        $headers = [];
        $attachments = [];

        for ($i = 0; $i < count($headerKeys); $i++) {
            switch ($headerKeys[$i]) {
                case ('to') :
                    $tos[] = $headerValues[$i];
                    break;
                case ('cc') :
                    $headers[] = 'Cc: ' . $headerValues[$i];
                    break;
                case ('bcc') :
                    $headers[] = 'Bcc: ' . $headerValues[$i];
                    break;
                case ('from') :
                    $headers[] = 'From: ' . $headerValues[$i];
                    break;
                default:
                    $headers[] = str_replace('custom', '', $headerKeys[$i]) . $headerValues[$i];
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
