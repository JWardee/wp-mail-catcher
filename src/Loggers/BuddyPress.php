<?php

namespace MailCatcher\Loggers;

use BP_Email;

class BuddyPress extends Logger
{
	public function __construct()
	{
		add_action('bp_send_email_success', array($this, 'recordMail'), 999999);
		add_action('bp_send_email_failure', array($this, 'recordMail'), 999999, 2);
//		add_action('bp_send_email_failure', array($this, 'recordError'), 999999);
	}

	protected function getMailArgs($status, $email)
	{
		var_dump($status, $email);
		exit;
	}
}
