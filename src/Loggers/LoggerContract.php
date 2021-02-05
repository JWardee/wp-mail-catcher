<?php

namespace WpMailCatcher\Loggers;

interface LoggerContract
{
    public function recordMail($args);

    public function recordError($error);
}
