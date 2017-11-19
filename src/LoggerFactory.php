<?php

namespace WpMailCatcher;

use WpMailCatcher\Loggers\BuddyPress;
use WpMailCatcher\Loggers\Smtp;
use WpMailCatcher\Loggers\WpMail;
use WpMailCatcher\Models\Logs;

class LoggerFactory
{
	static public function Set()
	{
//		new Smtp();
		new WpMail();
//		new BuddyPress();
	}
}
