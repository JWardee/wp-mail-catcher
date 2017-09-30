<?php

namespace MailCatcher;

$logs = new MailAdminTable();
$logs->prepare_items();

require __DIR__ . '/NewMessageModal.php';
?>

<div class="wrap">
	<h2>Mail Catcher</h2>
	<button class="button-primary" data-toggle="modal" data-target="#new-message">New Message</button>

	<?php $logs->display(); ?>
</div>

<?php
foreach ($logs->items as $log) :
	require __DIR__ . '/LogModal.php';
endforeach;
