<?php

namespace MailCatcher;

use PHPMailer;
use WP_Error;

class MailCatcherLog
{
    protected $id = null;

    public function phpMailerInit($mailer)
    {
        $backtrace_segment = null;
        $backtrace = debug_backtrace();

        foreach ($backtrace as $segment) {
            if ($segment['function'] == 'wp_mail') {
                $backtrace_segment = $segment;
            }
        }

        $to = $mailer['to'];//GeneralHelper::arrayToString($mailer->getToAddresses()[0]);
        $attachments = $mailer['attachments'];//$this->getAttachmentLocations($mailer->getAttachments());
        $additional_headers = $mailer['headers'];//$this->getAdditionalHeaders($mailer);

//        DEBUG
//        var_dump($additional_headers);
//        var_dump($mailer->getAllRecipientAddresses());
//        var_dump($mailer->getToAddresses());
//        var_dump($mailer->getCcAddresses());
//        var_dump($mailer->getBccAddresses());
//        var_dump($mailer->getCustomHeaders());
//        var_dump($mailer->ContentType);
//        var_dump($mailer->Body);
//        var_dump($mailer->Subject);
//        var_dump($mailer);
//        exit;

		// TODO: Add grunt support
        // TODO: Change 'time' to be timestamp and change human diff functions
        // TODO: Add additional headers column and ensure htmlspecialchars
        // TODO: Test "to" addresses accepts and processes all to formats in WP docs
        // TODO: Test plugin works with Mailgun, Sparkpost etc
        // TODO: Check all errors are logged by phpMailerFailed
        // TODO: Redo db schema to just seralize a modified version of the $mailer object like getAdditionalHeaders()

        global $wpdb;

//		var_dump($mailer);
//		exit;

//        if (!empty($mailer->ErrorInfo)) {
//            $wpdb->insert(
//                $wpdb->prefix . GeneralHelper::$table_name,
//                array(
//                    'time' => current_time('mysql'),
//                    'emailto' => $to,
//                    'subject' => $mailer['subject'],
//                    'message' => $mailer['message'],
//                    'backtrace_segment' => serialize($backtrace_segment),
//                    'status' => 0,
//                    'error' => 'derp',//$mailer->ErrorInfo,
//                    'attachments' => serialize($attachments),
//                    'additional_headers' => serialize($additional_headers)
//                )
//            );
//
//            remove_action('wp_mail_failed', array($this, 'phpMailerFailed'));
//        } else {
//		var_dump(serialize($additional_headers));
//		exit;
            $wpdb->insert(
                $wpdb->prefix . GeneralHelper::$tableName,
                array(
                    'time' => current_time('mysql'),
                    'emailto' => GeneralHelper::arrayToString($to),
                    'subject' => $mailer['subject'],
                    'message' => $mailer['message'],
                    'backtrace_segment' => serialize($backtrace_segment),
                    'status' => 1,
                    'attachments' => serialize($attachments),
                    'additional_headers' => serialize($additional_headers)
                )
            );
//        }

        $this->id = $wpdb->insert_id;

		//remove_filter('wp_mail', array($GLOBALS['mail_catcher'], 'logWpMail'));
        //remove_action('phpmailer_init', array($this, 'phpMailerInit'));
    }

    public function phpMailerFailed(WP_Error $error)
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . GeneralHelper::$tableName,
            array(
                'status' => 0,
                'error' => $error->errors['wp_mail_failed'][0],
            ),
            array('id' => $this->id)
        );

        remove_action('wp_mail_failed', array($this, 'phpMailerFailed'));
    }

    public function getAttachmentLocations($attachments)
    {
        $result = array();

        foreach ($attachments as $attachment) {
            $key = str_replace(get_home_path(), get_site_url() . '/', $attachment[0]);
            $result[$key] = basename($attachment[0]);
        }

        return $result;
    }

    public function getAdditionalHeaders(PHPMailer $mailer)
    {
        return array(
            'from' => GeneralHelper::arrayToString($mailer->From),
            'from_name' => GeneralHelper::arrayToString($mailer->FromName),
            'charset' => GeneralHelper::arrayToString($mailer->CharSet),
            'content_type' => GeneralHelper::arrayToString($mailer->ContentType),
            'host' => GeneralHelper::arrayToString($mailer->Host),
            'port' => GeneralHelper::arrayToString($mailer->Port),
            'reply_to' => GeneralHelper::arrayToString($mailer->getReplyToAddresses()),
            'cc' => GeneralHelper::arrayToString($mailer->getCcAddresses()),
            'bcc' => GeneralHelper::arrayToString($mailer->getBccAddresses())
        );
    }
}
