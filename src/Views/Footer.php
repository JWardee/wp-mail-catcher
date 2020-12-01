<?php

use WpMailCatcher\GeneralHelper;

?>

<p class="leave-a-review">
    <a href="<?php echo GeneralHelper::$reviewLink; ?>" target="_blank">
        <?php _e('If you\'ve found this useful - please rate us!', 'WpMailCatcher'); ?>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
    </a>
</p>
