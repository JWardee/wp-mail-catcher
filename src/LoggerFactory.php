<?php

namespace MailCatcher;

use MailCatcher\Loggers\WpMail;

class LoggerFactory
{
	static public function Set()
	{
		new WpMail();
	}
}
