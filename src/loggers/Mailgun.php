<?php

namespace MailCatcher\Loggers;

use WP_Error;

class Mailgun extends Logger
{
	public function __construct($args)
	{
		add_action('wp_mail_failed', array($this, 'recordError'), 999999);
		$this->recordMail($args);
	}

	public function recordMail($args)
	{
		parent::recordMail($args);
		remove_filter('wp_mail', array($this, 'recordMail'));
	}

	public function recordError(WP_Error $error)
	{
		parent::recordError($error);
		remove_action('wp_mail_failed', array($this, 'recordError'));
	}
}
