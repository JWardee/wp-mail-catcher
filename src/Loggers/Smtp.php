<?php

namespace MailCatcher\Loggers;

use MailCatcher\GeneralHelper;

class Smtp extends Logger
{
	public function __construct()
	{
		add_action('phpmailer_init', [$this, 'recordMail'], 99999);
		add_action('wp_mail_failed', [$this, 'recordError'], 99999);
	}

//	public function recordMail($args)
//	{
//		var_dump($args);
//		exit;
//	}
}
