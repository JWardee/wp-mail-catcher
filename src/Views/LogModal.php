<?php

use MailCatcher\GeneralHelper;

if (isset($log)) :
?>
	<div id="<?php echo $log['id']; ?>" class="modal">
		<div class="modal-content">
			<div class="modal-body">
				<h2 class="nav-tab-wrapper">
					<a href="#" class="nav-tab nav-tab-active"><?php _e('Message', GeneralHelper::$languageDomain); ?></a>
					<a href="#" class="nav-tab"><?php _e('Detail', GeneralHelper::$languageDomain); ?></a>
					<a href="#" class="nav-tab"><?php _e('Debug', GeneralHelper::$languageDomain); ?></a>
				</h2>
				<div class="content-container">
					<div class="content -active">
						<?php echo wpautop($log['message']); ?>
					</div>
					<div class="content">
						<?php if (empty($log['attachments']) && empty($log['additional_headers'])) : ?>
							<p>There aren't any details to show!</p>
						<?php else : ?>
							<?php if (!empty($log['attachments'])) : ?>
								<h3><?php _e('Attachments', GeneralHelper::$languageDomain); ?></h3>
								<hr />
								<ul>
									<?php foreach ($log['attachments'] as $attachment) : ?>
										<li class="attachment-container">
											<?php
											if (isset($attachment->note)) :
												echo $attachment->note;
												continue;
											endif;
											?>

											<a href="<?php echo $attachment->url ?>" target="_blank" class="attachment-item" style="background-image: url(<?php echo $attachment->src; ?>);"></a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if (!empty($log['additional_headers'])) : ?>
								<h3><?php _e('Additional Headers', GeneralHelper::$languageDomain); ?></h3>
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
							<li><?php _e('Triggered from:', GeneralHelper::$languageDomain); ?> <strong style="white-space: pre;"><?php echo $debug->file; ?></strong></li>
							<li><?php _e('On line:', GeneralHelper::$languageDomain); ?> <strong><?php echo $debug->line; ?></strong></li>
						</ul>

						<?php if (!empty($log['error'])) : ?>
							<h3><?php _e('Errors:', GeneralHelper::$languageDomain); ?></h3>
							<hr />
							<ul>
								<li><?php echo $log['error']; ?></li>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="button-primary dismiss-modal"><?php _e('Close', GeneralHelper::$languageDomain); ?></button>
			</div>
		</div>
		<div class="backdrop dismiss-modal"></div>
	</div>
<?php endif; ?>
