<?php

namespace MailCatcher;

$logs = new MailAdminTable();
$logs->prepare_items();

require GeneralHelper::$pluginViewDirectory . '/NewMessageModal.php';
?>

<div class="wrap">
	<h2 class="heading">Mail Catcher - <?php _e('logs', 'MailCatcher'); ?></h2>
	<button class="button-primary" data-toggle="modal" data-target="#new-message">
		<?php _e('New Message', 'MailCatcher'); ?>
	</button>

	<form action="?page=<?php echo GeneralHelper::$adminPageSlug; ?>" method="post">
		<?php $logs->display(); ?>
	</form>

	<?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
</div>

<?php
foreach ($logs->items as $log) :
	require GeneralHelper::$pluginViewDirectory . '/LogModal.php';
endforeach;
