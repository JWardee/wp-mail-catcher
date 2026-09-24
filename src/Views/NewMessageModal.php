<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="new-message" class="modal">
    <div class="modal-content">
        <form class="form-horizontal"
              action="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>&action=new_mail"
              method="POST">
            <div class="modal-body">
                <div class="content-container">
                    <div class="content -active">
                        <div>
                            <h2><?php esc_html_e('Headers', 'wp-mail-catcher'); ?></h2>
                            <hr/>

                            <div class="cloneable">
                                <div class="field-block">
                                    <a href="#" class="add-field">
                                        <span class="dashicons dashicons-plus-alt -icon"></span>
                                    </a>

                                    <a href="#" class="remove-field -disabled">
                                        <span class="dashicons dashicons-dismiss -icon"></span>
                                    </a>

                                    <label>
                                        <select name="header_keys[]" class="field -select">
                                            <option value="to"><?php esc_html_e('To', 'wp-mail-catcher'); ?></option>
                                            <option value="cc"><?php esc_html_e('Cc', 'wp-mail-catcher'); ?></option>
                                            <option value="bcc"><?php esc_html_e('Bcc', 'wp-mail-catcher'); ?></option>
                                            <option value="from">
                                                <?php esc_html_e('From', 'wp-mail-catcher'); ?>
                                            </option>
                                            <option value="custom">
                                                <?php esc_html_e('Custom', 'wp-mail-catcher'); ?>
                                            </option>
                                        </select>
                                    </label>

                                    <label>
                                        <input name="header_values[]" type="text" class="field -input"/>
                                    </label>
                                </div>
                            </div>

                            <label class="is-html-email">
                                <input
                                    type="checkbox"
                                    value="<?php echo esc_attr(GeneralHelper::$htmlEmailHeader); ?>"
                                    name="header_keys[]"
                                />
                                <?php esc_html_e('Is HTML email?', 'wp-mail-catcher'); ?> </label>
                        </div>
                        <div>
                            <h2><?php esc_html_e('Subject', 'wp-mail-catcher'); ?></h2>
                            <hr/>

                            <label>
                                <input name="subject" type="text" class="field -input"/>
                            </label>
                        </div>
                        <div>
                            <h2><?php esc_html_e('Attachments', 'wp-mail-catcher'); ?></h2>
                            <hr/>

                            <div class="attachments-container">
                                <div class="attachment-clones">
                                    <span class="attachment-item -original">
                                        <span class="dashicons dashicons-dismiss remove"></span>
                                        <input type="hidden" name="attachment_ids[]" value="" class="attachment-input"/>
                                    </span>
                                </div>

                                <div class="attachment-button-container">
                                    <a href="#" class="button-primary" id="add_attachments">
                                        <?php esc_html_e('Add Attachments', 'wp-mail-catcher'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2><?php esc_html_e('Message', 'wp-mail-catcher'); ?></h2>
                            <hr />

                            <?php wp_editor(__('My Message', 'wp-mail-catcher'), 'message'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('new_mail'); ?>

            <div class="modal-footer">
                <button type="submit" class="button-primary">
                    <?php esc_html_e('Send Message', 'wp-mail-catcher'); ?>
                </button>
                <button type="button" class="button-secondary dismiss-modal">
                    <?php esc_html_e('Close', 'wp-mail-catcher'); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>
