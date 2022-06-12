<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;

class BuddyPress
{
    use LogHelper;

    /**
     * Register any filters and actions
     * that need to be called in order to log the outgoing mail
     */
    public function __construct()
    {
        add_action('bp_send_email_success', [$this, 'recordMail'], 10, 2);
        add_action('bp_send_email_failure', [$this, 'recordError']);
    }

    public function recordMail($status, $bpMail)
    {
        $this->saveMail($bpMail, $this->getTransformedMailArgs($bpMail));
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
    protected function getTransformedMailArgs($bpMail)
    {
        $tos = array_map(function($bpRecipient) {
            return $bpRecipient->get_address();
        }, $bpMail->get_to());

        return [
            'time' => time(),
            'email_to' => GeneralHelper::arrayToString($tos),
            'subject' => $bpMail->get_subject(),
            'message' => $this->sanitiseInput($bpMail->get_content()),
            'backtrace_segment' => json_encode($this->getBacktrace('bp_send_email')),
            'status' => 1,
            'attachments' => '',//json_encode($this->getAttachmentLocations($args['attachments'])),
            'additional_headers' => json_encode($bpMail->get_headers())
        ];
    }
}
