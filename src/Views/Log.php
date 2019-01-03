<?php

namespace WpMailCatcher;

$logs = new MailAdminTable();
$logs->prepare_items();

require GeneralHelper::$pluginViewDirectory . '/NewMessageModal.php';
?>

<div class="wrap<?php if (count($logs->items) == 0) : ?> -empty<?php endif ; ?>">
	<h2 class="heading">WP Mail Catcher - <?php _e('logs', 'WpMailCatcher'); ?></h2>

    <div class="button-container">
        <button class="btn button-primary" data-toggle="modal" data-target="#new-message">
            <?php _e('New Message', 'WpMailCatcher'); ?>
        </button>

        <a href="<?php echo wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=export-all', 'bulk-logs'); ?>" class="btn button-secondary">
            <?php _e('Export all messages', 'WpMailCatcher'); ?>
        </a>
    </div>

	<form action="?page=<?php echo GeneralHelper::$adminPageSlug; ?>" method="post">
		<?php $logs->display(); ?>
	</form>

	<?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
</div>

<?php
foreach ($logs->items as $log) :
	require GeneralHelper::$pluginViewDirectory . '/LogModal.php';
endforeach;
