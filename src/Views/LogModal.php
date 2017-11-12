<?php

if (isset($log)) :
?>
	<div id="<?php echo $log['id']; ?>" class="modal">
		<div class="modal-content">
			<div class="modal-body">
				<h2 class="nav-tab-wrapper">
					<a href="#" class="nav-tab nav-tab-active"><?php _e('Message', 'MailCatcher'); ?></a>
					<a href="#" class="nav-tab"><?php _e('Detail', 'MailCatcher'); ?></a>
					<a href="#" class="nav-tab"><?php _e('Debug', 'MailCatcher'); ?></a>
				</h2>
				<div class="content-container">
					<div class="content -active">
						<?php echo wpautop($log['message']); ?>
					</div>
					<div class="content">
						<?php if (empty($log['attachments']) && empty($log['additional_headers'])) : ?>
							<p>
								<?php _e('There aren\'t any details to show!', 'MailCatcher'); ?>
							</p>
						<?php else : ?>
							<?php if (!empty($log['attachments'])) : ?>
								<h3><?php _e('Attachments', 'MailCatcher'); ?></h3>
								<hr />
								<ul>
									<?php foreach ($log['attachments'] as $attachment) : ?>
										<li class="attachment-container">
											<?php
											if (isset($attachment['note'])) :
												echo $attachment['note'];
												continue;
											endif;
											?>

											<a href="<?php echo $attachment['url'] ?>" target="_blank" class="attachment-item" style="background-image: url(<?php echo $attachment['src']; ?>);"></a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if (!empty($log['additional_headers'])) : ?>
								<h3><?php _e('Additional Headers', 'MailCatcher'); ?></h3>
								<hr />
								<ul>
									<?php foreach ($log['additional_headers'] as $additionalHeader) : ?>
										<li><?php echo $additionalHeader; ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						<?php endif; ?>
					</div>
					<div class="content">
						<?php $debug = json_decode($log['backtrace_segment']); ?>
						<ul>
							<li><?php _e('Triggered from:', 'MailCatcher'); ?> <strong style="white-space: pre;"><?php echo $debug->file; ?></strong></li>
							<li><?php _e('On line:', 'MailCatcher'); ?> <strong><?php echo $debug->line; ?></strong></li>
						</ul>

						<?php if (!empty($log['error'])) : ?>
							<h3><?php _e('Errors:', 'MailCatcher'); ?></h3>
							<hr />
							<ul>
								<li><?php echo $log['error']; ?></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button-primary dismiss-modal"><?php _e('Close', 'MailCatcher'); ?></button>
			</div>
		</div>
		<div class="backdrop dismiss-modal"></div>
	</div>
<?php endif; ?>
