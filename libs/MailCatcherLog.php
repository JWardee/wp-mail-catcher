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

        $to = GeneralHelper::arrayToSqlString($mailer->getToAddresses());

//        DEBUG
//        var_dump($mailer->getAllRecipientAddresses());
//        var_dump($mailer->getToAddresses());
//        var_dump($mailer->Body);
//        var_dump($mailer->Subject);
//        var_dump($backtrace_segment);
//        exit;

        global $wpdb;

        // TODO: Change 'time' to be timestamp and change human diff functions
        // TODO: Add additional headers column and ensure htmlspecialchars
        // TODO: Test "to" addresses accepts and processes all to formats in WP docs
        // TODO: Add email attachment functionality
        // TODO: Test plugin works with Mailgun, Sparkpost etc
        // TODO: Add actual error message as tooltip (or similar to the "failed" bit of the table)
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