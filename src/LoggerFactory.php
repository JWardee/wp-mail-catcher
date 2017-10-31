<?php

namespace MailCatcher;

use MailCatcher\Loggers\BuddyPress;
use MailCatcher\Loggers\Smtp;
use MailCatcher\Loggers\WpMail;
use MailCatcher\Models\Logs;

class LoggerFactory
{
	static public function Set()
	{
//		new Smtp();
		new WpMail();
//		new BuddyPress();
	}
}
