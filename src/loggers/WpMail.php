<?php

namespace MailCatcher\Loggers;

use WP_Error;

class WpMail extends Logger
{
	public function __construct()
	{
		add_filter('wp_mail', array($this, 'recordMail'));
		add_action('wp_mail_failed', array($this, 'recordError'), 999999);
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
