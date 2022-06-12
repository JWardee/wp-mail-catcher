<?php use WpMailCatcher\GeneralHelper; ?>

<div id="new-message" class="modal">
    <div class="modal-content">
        <form class="form-horizontal" action="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&action=new_mail"
              method="POST">
            <div class="modal-body">
                <div class="content-container">
                    <div class="content -active">
                        <div>
                            <h2><?php _e('Headers', 'WpMailCatcher'); ?></h2>
                            <hr/>

                            <div class="cloneable">
                                <div class="field-block">
                                    <a href="#" class="add-field">
                                        <span class="dashicons dashicons-plus-alt -icon"></span>
                                    </a>

                                    <a href="#" class="remove-field -disabled">
                                        <span class="dashicons dashicons-dismiss -icon"></span>
                                    </a>

                                    <select name="header_keys[]" class="field -select">
                                        <option value="to"><?php _e('To', 'WpMailCatcher'); ?></option>
                                        <option value="cc"><?php _e('Cc', 'WpMailCatcher'); ?></option>
                                        <option value="bcc"><?php _e('Bcc', 'WpMailCatcher'); ?></option>
                                        <option value="from"><?php _e('From', 'WpMailCatcher'); ?></option>
                                        <option value="custom"><?php _e('Custom', 'WpMailCatcher'); ?></option>
                                    </select>

                                    <input name="header_values[]" type="text" class="field -input"/>
                                </div>
                            </div>

                            <label class="is-html-email"><input type="checkbox" value="<?php echo GeneralHelper::$htmlEmailHeader; ?>"
                                                                name="header_keys[]"/>
                                <?php _e('Is HTML email?', 'WpMailCatcher'); ?> </label>
                        </div>
                        <div>
                            <h2><?php _e('Subject', 'WpMailCatcher'); ?></h2>
                            <hr/>

                            <input name="subject" type="text" class="field -input"/>
                        </div>
                        <div>
                            <h2><?php _e('Attachments', 'WpMailCatcher'); ?></h2>
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
                                        <?php _e('Add Attachments', 'WpMailCatcher'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2><?php _e('Message', 'WpMailCatcher'); ?></h2>
                            <hr />

                            <?php wp_editor(__('My Message', 'WpMailCatcher'), 'message'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('new_mail'); ?>

            <div class="modal-footer">
                <button type="submit" class="button-primary">
                    <?php _e('Send Message', 'WpMailCatcher'); ?>
                </button>
                <button type="button" class="button-secondary dismiss-modal">
                    <?php _e('Close', 'WpMailCatcher'); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>
