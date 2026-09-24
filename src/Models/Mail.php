<?php

namespace WpMailCatcher\Models;

use WpMailCatcher\GeneralHelper;

class Mail
{
    private static $contentTypeFilterPriority = 9999;

    public static function resend($ids)
    {
        $logs = Logs::get([
            'post__in' => $ids
        ]);

        foreach ($logs as $log) {
            $updateContentType = function ($contentType) use ($log) {
                return $log['is_html'] ? 'text/html' : $contentType;
            };

            add_filter('wp_mail_content_type', $updateContentType, self::$contentTypeFilterPriority);

            if (isset($log['message'])) {
                $log['message'] = GeneralHelper::filterHtml($log['message']);
            }

            if (isset($log['subject'])) {
                $log['subject'] = GeneralHelper::filterHtml($log['subject']);
            }

            wp_mail(
                $log['email_to'],
                $log['subject'],
                $log['message'],
                $log['additional_headers'],
                $log['attachment_file_paths']
            );

            remove_filter('wp_mail_content_type', $updateContentType, self::$contentTypeFilterPriority);
        }
    }

    public static function export($ids, $forceBrowserDownload = true)
    {
        $logs = Logs::get([
            'post__in' => $ids,
            'date_time_format' => 'd-M-Y @ H:i',
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

            if (isset($log['message'])) {
                $log['message'] = GeneralHelper::filterHtml($log['message']);
            }

            if (isset($log['subject'])) {
                $log['subject'] = GeneralHelper::filterHtml($log['subject']);
            }

            if (isset($log['attachments']) && !empty($log['attachments']) && is_array($log['attachments'])) {
                $log['attachments'] = array_column($log['attachments'], 'url');
                $log['attachments'] = GeneralHelper::arrayToString(
                    $log['attachments'],
                    GeneralHelper::$csvItemDelimiter
                );
            } else {
                $log['attachments'] = '-';
            }

            if (
                isset($log['additional_headers']) &&
                !empty($log['additional_headers']) && is_array($log['additional_headers'])
            ) {
                $log['additional_headers'] = GeneralHelper::arrayToString(
                    $log['additional_headers'],
                    GeneralHelper::$csvItemDelimiter
                );
            } else {
                $log['additional_headers'] = '-';
            }

            if ($log['status']) {
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

        if ($forceBrowserDownload) {
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

    private static function processLogToCsv($headings, $logs)
    {
        $out = fopen('php://output', 'w');
        fputcsv($out, $headings);

        foreach ($logs as $k => $v) {
            if (is_array($v)) {
                $v = GeneralHelper::flatten($v, ', ');
            }

            fputcsv($out, $v);
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Stream to php://output
        fclose($out);
    }

    public static function add($headerKeys, $headerValues, $attachmentIds, $subject, $message, $isHtml = false)
    {
        $headerKeys = self::sanitizeEach($headerKeys, 'sanitize_text_field');
        // Header values may contain "Name <email>" so strip line breaks (header injection) rather than tags
        $headerValues = self::sanitizeEach($headerValues, function ($value) {
            return trim(str_replace(["\r", "\n"], '', $value));
        });
        $attachmentIds = self::sanitizeEach($attachmentIds, 'absint');
        $subject = is_scalar($subject) ? sanitize_text_field($subject) : '';
        $message = is_scalar($message) ? wp_kses_post($message) : '';

        $tos = [];
        $headers = [];
        $attachments = [];

        for ($i = 0; $i < count($headerKeys); $i++) {
            if (!isset($headerValues[$i])) {
                $headerValues[$i] = $headerKeys[$i];
                $headerKeys[$i] = '';
            }

            switch ($headerKeys[$i]) {
                case ('to'):
                    $tos[] = $headerValues[$i];
                    break;
                case ('cc'):
                    $headers[] = 'Cc: ' . $headerValues[$i];
                    break;
                case ('bcc'):
                    $headers[] = 'Bcc: ' . $headerValues[$i];
                    break;
                case ('from'):
                    $headers[] = 'From: ' . $headerValues[$i];
                    break;
                default:
                    $headers[] = str_ireplace('custom', '', $headerKeys[$i]) . $headerValues[$i];
                    break;
            }
        }

        foreach ($attachmentIds as $attachment_id) {
            if (empty($attachment_id)) {
                continue;
            }

            $attachments[] = get_attached_file($attachment_id);
        }

        if ($isHtml) {
            $updateContentType = function () {
                return 'text/html';
            };

            add_filter('wp_mail_content_type', $updateContentType, self::$contentTypeFilterPriority);
        }

        wp_mail($tos, $subject, $message, $headers, $attachments);

        if ($isHtml) {
            remove_filter('wp_mail_content_type', $updateContentType, self::$contentTypeFilterPriority);
        }
    }

    /**
     * Keeps the array indexes intact as header keys and values are matched by index
     */
    private static function sanitizeEach($values, callable $sanitizer): array
    {
        return array_map(function ($value) use ($sanitizer) {
            return is_scalar($value) ? $sanitizer($value) : '';
        }, (array)$values);
    }
}
