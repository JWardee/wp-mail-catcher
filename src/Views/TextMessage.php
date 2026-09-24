<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

?>
<div style="white-space: pre;"><?php echo wp_kses($log['message'] ?? '', GeneralHelper::getAllowedTags()); ?></div>
