<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

echo wp_kses(htmlspecialchars_decode($log['message'] ?? ''), GeneralHelper::getAllowedTags());
