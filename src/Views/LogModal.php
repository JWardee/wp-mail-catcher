<?php

use WpMailCatcher\GeneralHelper;

if (isset($log)) :
    ?>
    <div id="<?php echo $log['id']; ?>" class="modal">
        <div class="modal-content <?php echo $log['is_html'] ? 'is-html' : 'is-not-html'; ?>">
            <div class="modal-body">
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active"><?php _e('Message', 'WpMailCatcher'); ?></a>
                    <a href="#" class="nav-tab"><?php _e('Detail', 'WpMailCatcher'); ?></a>
                    <a href="#" class="nav-tab"><?php _e('Debug', 'WpMailCatcher'); ?></a>
                </h2>
                <div class="content-container">
                    <div class="content -active">
                        <iframe class="html-preview"
                                data-src="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&action=single_mail&id=<?php echo $log['id']; ?>"></iframe>
                    </div>
                    <div class="content">
                        <p>
                            <?php _e('Is HTML email?', 'WpMailCatcher'); ?>
                            <strong><?php echo $log['is_html'] ? __('Yes', 'WpMailCatcher') : __('No', 'WpMailCatcher'); ?></strong>
                        </p>
                        <?php if (empty($log['attachments'])) : ?>
                            <p><?php _e('No attachments to show', 'WpMailCatcher'); ?></p>
                        <?php else : ?>
                            <h3><?php _e('Attachments', 'WpMailCatcher'); ?></h3>
                            <hr/>
                            <ul>
                                <?php foreach ($log['attachments'] as $attachment) : ?>
                                    <li class="attachment-container">
                                        <?php
                                        if (isset($attachment['note'])) :
                                            echo $attachment['note'];
                                            continue;
                                        endif;
                                        ?>

                                        <a href="<?php echo $attachment['url'] ?>" target="_blank"
                                            class="attachment-item"
                                            style="background-image: url(<?php echo $attachment['src']; ?>);"></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!isset($log['additional_headers']) || empty(array_filter($log['additional_headers']))) : ?>
                            <p><?php _e('No additional headers to show', 'WpMailCatcher'); ?></p>
                        <?php else : ?>
                            <h3><?php _e('Additional Headers', 'WpMailCatcher'); ?></h3>
                            <hr/>
                            <ul>
                                <?php foreach ($log['additional_headers'] as $additionalHeader) : ?>
                                    <li><?php echo esc_html($additionalHeader); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <?php $debug = json_decode($log['backtrace_segment']); ?>
                        <ul>
                            <li><?php _e('Triggered from:', 'WpMailCatcher'); ?><strong><?php echo $debug->file; ?></strong></li>
                            <li><?php _e('On line:', 'WpMailCatcher'); ?> <strong><?php echo $debug->line; ?></strong></li>
                            <li><?php _e('Sent at:', 'WpMailCatcher'); ?> <strong><?php echo date(GeneralHelper::$humanReadableDateFormat, $log['timestamp']); ?> (<?php echo $log['timestamp']; ?>)</strong></li>
                        </ul>

                        <?php if (!empty($log['error'])) : ?>
                            <h3 class="subheading"><?php _e('Errors:', 'WpMailCatcher'); ?></h3>
                            <hr/>
                            <ul>
                                <li><?php echo $log['error']; ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button-primary dismiss-modal"><?php _e('Close',
                        'WpMailCatcher'); ?></button>
            </div>
        </div>
        <div class="backdrop dismiss-modal"></div>
    </div>
<?php endif; ?>
