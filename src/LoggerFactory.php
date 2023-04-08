<?php

namespace WpMailCatcher;

use WpMailCatcher\Loggers\WpMail;

class LoggerFactory
{
    public static function set()
    {
        /**
         *  When more loggers are added, the logic
         *  that determines which one to use will go here
         */
        new WpMail();
        // new BuddyPress();
    }
}
