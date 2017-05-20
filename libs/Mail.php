<?php
class Mail
{
    public static function resend($ids)
    {
        $logs = Logs::getFromIds($ids);

        foreach ($logs as $log) {
            wp_mail($log['emailto'], $log['subject'], $log['message']);
        }
    }

    public static function export($ids)
    {
        $logs = Logs::getFromIds($ids);
        $filename = 'MailCatcher_Export_' . date('His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');

        fputcsv($out, array_keys($logs[0]));

        foreach ($logs as $k => $v) {
            fputcsv($out, $v);
        }

        fclose($out);
        exit;
    }
}