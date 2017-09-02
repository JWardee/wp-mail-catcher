<?php
use MailCatcher\GeneralHelper;
use MailCatcher\Logs;
use MailCatcher\MailAdminTable;

$logs = Logs::get(array(
	'paged' => isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1
));

$testListTable = new MailAdminTable();
$testListTable->prepare_items();
?>
<div class="wrap">
    <h2><?php _e('Mail Catcher', GeneralHelper::$languageDomain); ?></h2>

    <a href="#" class="button button-primary" data-toggle="modal" data-target="#new-message">
		<?php _e('New Message', GeneralHelper::$languageDomain); ?>
	</a>

    <form id="movies-filter" method="post" action="?page=<?php if (isset($_REQUEST['page'])) : echo $_REQUEST['page']; endif; ?>">
        <?php $testListTable->display() ?>
    </form>
</div>

<?php foreach ($logs as $log) : ?>
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
                        <?php echo $log['message']; ?>
                    </div>
                    <div class="content">
                        <?php
                        $attachments = unserialize($log['attachments']);
                        if (!empty($attachments)) :
                            ?>
                            <h3><?php _e('Attachments', GeneralHelper::$languageDomain); ?></h3>
                            <hr />
                            <ul>
                                <?php foreach ($attachments as $attachment_url => $attachment_name) : ?>
                                    <li>
                                        <a href="<?php echo $attachment_url ?>" target="_blank">
											<?php echo $attachment_name; ?>
										</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php
                        $additional_headers = unserialize($log['additional_headers']);
                        if (!empty($additional_headers)) :
                            ?>
                            <h3><?php _e('Additional Headers', GeneralHelper::$languageDomain); ?></h3>
                            <hr />
                            <ul>
                                <?php foreach ($additional_headers as $additional_header => $value) : ?>
                                    <li><strong><?php echo GeneralHelper::slugToLabel($additional_header); ?>:</strong> <?php echo $value; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <?php $debug = unserialize($log['backtrace_segment']); ?>
                        <ul>
                            <li><?php _e('Triggered from:', GeneralHelper::$languageDomain); ?> <strong style="white-space: pre;"><?php echo $debug['file']; ?></strong></li>
                            <li><?php _e('On line:', GeneralHelper::$languageDomain); ?> <strong><?php echo $debug['line']; ?></strong></li>
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
<?php endforeach; ?>

<link rel="stylesheet" href="/wp-includes/css/dashicons.css">

<div id="new-message" class="modal">
    <div class="modal-content">
        <form class="form-horizontal" action="?page=mail-catcher&action=new_mail" method="POST">
            <div class="modal-body">
                <div class="content-container">
                    <div class="content -active">
                        <div>
                            <h2><?php _e('Headers', GeneralHelper::$languageDomain); ?></h2>
                            <hr />

                            <div class="cloneable">
                                <div class="field-block">
                                    <a href="#" class="add-field">
                                        <span class="dashicons dashicons-plus-alt -icon"></span>
                                    </a>

                                    <a href="#" class="remove-field -disabled">
                                        <span class="dashicons dashicons-dismiss -icon"></span>
                                    </a>

                                    <select name="header_keys[]" class="field -select">
                                        <option value="to"><?php _e('To', GeneralHelper::$languageDomain); ?></option>
                                        <option value="cc"><?php _e('Cc', GeneralHelper::$languageDomain); ?></option>
                                        <option value="bcc"><?php _e('Bcc', GeneralHelper::$languageDomain); ?></option>
                                        <option value="other"><?php _e('Other', GeneralHelper::$languageDomain); ?></option>
                                    </select>

                                    <input name="header_values[]" type="text" class="field -input" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2><?php _e('Subject', GeneralHelper::$languageDomain); ?></h2>
                            <hr />

                            <input name="subject" type="text" class="field -input" />
                        </div>
                        <div>
                            <h2><?php _e('Attachments', GeneralHelper::$languageDomain); ?></h2>
                            <hr />

                            <div class="attachments-container">
                                <div class="attachment-clones">
                                    <span class="attachment-item -original">
                                        <span class="dashicons dashicons-dismiss remove"></span>
                                        <input type="hidden" name="attachment_ids[]" value="" class="attachment-input" />
                                    </span>
                                </div>

                                <div class="attachment-button-container">
                                    <a href="#" class="button-primary" id="add_attachments">
										<?php _e('Add Attachments', GeneralHelper::$languageDomain); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2><?php _e('Message', GeneralHelper::$languageDomain); ?></h2>
                            <hr />

                            <?php wp_editor('My Message', 'message', $settings = array()); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="button-primary">
					<?php _e('Send Message', GeneralHelper::$languageDomain); ?>
                </button>
                <button type="button" class="button-secondary dismiss-modal">
					<?php _e('Close', GeneralHelper::$languageDomain); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>
