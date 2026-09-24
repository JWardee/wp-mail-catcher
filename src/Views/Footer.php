<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

?>

<p class="leave-a-review">
    <a href="<?php echo esc_url(GeneralHelper::$reviewLink); ?>" target="_blank">
        <?php esc_html_e('If you\'ve found this useful - please rate us!', 'wp-mail-catcher'); ?>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
    </a>
</p>
