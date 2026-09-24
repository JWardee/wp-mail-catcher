<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

if (isset($wpMailCatcherLog)) :
    $wpMailCatcherIframeLink = '?page=' . GeneralHelper::$adminPageSlug .
        '&action=single_mail&id=' . $wpMailCatcherLog['id'];
    $wpMailCatcherResendLink = '?page=' . GeneralHelper::$adminPageSlug .
        '&action=resend&id=' . $wpMailCatcherLog['id'];
    ?>
    <div id="<?php echo esc_attr($wpMailCatcherLog['id']); ?>" class="modal">
        <div class="modal-content <?php echo $wpMailCatcherLog['is_html'] ? 'is-html' : 'is-not-html'; ?>">
            <div class="modal-body">
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active"><?php esc_html_e('Message', 'wp-mail-catcher'); ?></a>
                    <a href="#" class="nav-tab"><?php esc_html_e('Detail', 'wp-mail-catcher'); ?></a>
                    <a href="#" class="nav-tab"><?php esc_html_e('Debug', 'wp-mail-catcher'); ?></a>
                </h2>
                <div class="content-container">
                    <div class="content -active">
                        <iframe class="html-preview"
                                data-src="<?php echo esc_url($wpMailCatcherIframeLink); ?>"></iframe>
                    </div>
                    <div class="content">
                        <p>
                            <?php esc_html_e('Is HTML email?', 'wp-mail-catcher'); ?>
                            <strong>
                                <?php
                                echo $wpMailCatcherLog['is_html']
                                    ? esc_html__('Yes', 'wp-mail-catcher')
                                    : esc_html__('No', 'wp-mail-catcher');
                                ?>
                            </strong>
                        </p>
                        <?php if (empty($wpMailCatcherLog['attachments'])) : ?>
                            <p><?php esc_html_e('No attachments to show', 'wp-mail-catcher'); ?></p>
                        <?php else : ?>
                            <h3><?php esc_html_e('Attachments', 'wp-mail-catcher'); ?></h3>
                            <hr/>
                            <ul>
                                <?php foreach ($wpMailCatcherLog['attachments'] as $wpMailCatcherAttachment) : ?>
                                    <li class="attachment-container">
                                        <?php
                                        if (isset($wpMailCatcherAttachment['note'])) :
                                            echo esc_html($wpMailCatcherAttachment['note']);
                                            continue;
                                        endif;
                                        ?>

                                        <a href="<?php echo esc_url($wpMailCatcherAttachment['url']); ?>"
                                           target="_blank"
                                           class="attachment-item"
                                           style="background-image: url(<?php
                                            echo esc_url($wpMailCatcherAttachment['src']);
                                            ?>);"></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php
                        if (
                            !isset($wpMailCatcherLog['additional_headers']) ||
                            empty(array_filter($wpMailCatcherLog['additional_headers']))
                        ) :
                            ?>
                            <p><?php esc_html_e('No additional headers to show', 'wp-mail-catcher'); ?></p>
                        <?php else : ?>
                            <h3><?php esc_html_e('Additional Headers', 'wp-mail-catcher'); ?></h3>
                            <hr/>
                            <ul>
                                <?php foreach ($wpMailCatcherLog['additional_headers'] as $wpMailCatcherHeader) : ?>
                                    <li><?php echo esc_html($wpMailCatcherHeader); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <?php $wpMailCatcherDebug = json_decode($wpMailCatcherLog['backtrace_segment']); ?>
                        <ul>
                            <li><?php esc_html_e('Triggered from:', 'wp-mail-catcher'); ?>
                                <strong><?php echo esc_html($wpMailCatcherDebug->file); ?></strong></li>
                            <li><?php esc_html_e('On line:', 'wp-mail-catcher'); ?>
                                <strong><?php echo esc_html($wpMailCatcherDebug->line); ?></strong>
                            </li>
                            <li><?php esc_html_e('Sent at:', 'wp-mail-catcher'); ?>
                                <strong>
                                    <?php
                                    echo esc_html(
                                        gmdate(GeneralHelper::$humanReadableDateFormat, $wpMailCatcherLog['timestamp'])
                                    );
                                    ?>
                                    (<?php echo esc_html($wpMailCatcherLog['timestamp']); ?>)
                                </strong>
                            </li>
                        </ul>

                        <?php if (!empty($wpMailCatcherLog['error'])) : ?>
                            <h3 class="subheading"><?php esc_html_e('Errors:', 'wp-mail-catcher'); ?></h3>
                            <hr/>
                            <ul>
                                <li><?php echo esc_html($wpMailCatcherLog['error']); ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="<?php echo esc_url(wp_nonce_url($wpMailCatcherResendLink, 'modal-resend')); ?>"
                   class="resend-link">
                    <?php esc_html_e('Resend', 'wp-mail-catcher'); ?>
                </a>
                <button type="button" class="button-primary dismiss-modal">
                    <?php esc_html_e('Close', 'wp-mail-catcher'); ?>
                </button>
            </div>
        </div>
        <div class="backdrop dismiss-modal"></div>
    </div>
<?php endif; ?>
