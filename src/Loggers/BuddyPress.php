<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;

class BuddyPress implements LoggerContract
{
    use LogHelper;

    /**
     * Register any filters and actions
     * that need to be called in order to log the outgoing mail
     */
    public function __construct()
    {
        add_action('bp_send_email_success', [$this, 'recordMail']);
        add_action('bp_send_email_failure', [$this, 'recordError']);
    }

    public function recordMail($args)
    {
        $this->saveMail($this->getMailArgs($args));
    }

    public function recordError($error)
    {
        $this->saveError($error->errors['wp_mail_failed'][0]);
    }

    /**
     * Transform the incoming details of the mail into the
     * correct format for our log (data fractal)
     *
     * @param BP_Email $bpMail the details of the mail going to be sent
     * @return array must return an array in the same format
     */
    protected function getMailArgs($bpMail)
    {
        return [
            'time' => time(),
            'email_to' => GeneralHelper::arrayToString($bpMail->get_to()),
            'subject' => $bpMail->get_subject(),
            'message' => $this->sanitiseInput($bpMail->get_content()),
            'backtrace_segment' => json_encode($this->getBacktrace('bp_send_email')),
            'status' => 1,
            'attachments' => '',//json_encode($this->getAttachmentLocations($args['attachments'])),
            'additional_headers' => json_encode($bpMail->get_headers())
        ];
    }
}
