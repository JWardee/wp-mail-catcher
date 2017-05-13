<?php
class MailCatcherLog
{
    protected $id = null;

    public function phpMailerInit(PHPMailer $mailer)
    {
        $backtrace_segment = null;
        $backtrace = debug_backtrace();

        foreach ($backtrace as $segment) {
            if ($segment['function'] == 'wp_mail') {
                $backtrace_segment = $segment;
            }
        }

        $to = '';

        foreach ($mailer->getToAddresses() as $emails) {
            $to .= implode(', ', $emails);
        }

//        DEBUG
//        var_dump($mailer->getAllRecipientAddresses());
//        var_dump($mailer->getToAddresses());
//        var_dump($mailer->Body);
//        var_dump($mailer->Subject);
//        var_dump($backtrace_segment);
//        exit;

        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . MailCatcher::$table_name,
            array(
                'time' => current_time('mysql'),
                'emailto' => $to,
                'subject' => $mailer->Subject,
                'message' => $mailer->Body,
                'backtrace_segment' => serialize($backtrace_segment),
                'status' => 1
            )
        );

        $this->id = $wpdb->insert_id;

        remove_action('phpmailer_init', array($this, 'phpMailerInit'));
    }

    public function phpMailerFailed(WP_Error $error)
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . MailCatcher::$table_name,
            array(
                'status' => 0,
                'error' => $error->errors['wp_mail_failed'][0]
            ),
            array('id' => $this->id)
        );

        remove_action('wp_mail_failed', array($this, 'phpMailerFailed'));
    }
}