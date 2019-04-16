<?php

use WpMailCatcher\GeneralHelper;

$pluginData = get_plugin_data(GeneralHelper::$pluginPath . '/WpMailCatcher.php');
?>

<p class="leave-a-review">
    <a href="<?php echo $pluginData['PluginURI']; ?>" target="_blank">
        If you've found this useful - please rate us!
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
        <span class="dashicons dashicons-star-filled"></span>
    </a>
</p>
