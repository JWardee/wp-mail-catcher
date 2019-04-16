<?php

namespace WpMailCatcher;

use WpMailCatcher\Loggers\WpMail;

class LoggerFactory
{
    static public function Set()
    {
        /**
         *  When more loggers are added, the logic
         *  that determines which one to use will go here
         */
        new WpMail();
    }
}
