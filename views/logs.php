<?php
$logs_controller = new Logs();
$logs = $logs_controller->get($_REQUEST['paged']);

$testListTable = new MailAdminTable();
$testListTable->prepare_items();
//wp_editor('teste', 'editor', array('tinymce' => false));
?>
<div class="wrap">
    <h2>Mail Catcher</h2>

    <a href="#" class="button button-primary" data-toggle="modal" data-target="#new-message">New Message</a>

    <form id="movies-filter" method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
        <?php $testListTable->display() ?>
    </form>
</div>

<?php foreach ($logs as $log) : ?>
    <div id="<?php echo $log['id']; ?>" class="modal">
        <div class="modal-content">
            <div class="modal-body">
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab nav-tab-active">Message</a>
                    <a href="#" class="nav-tab">Detail</a>
                    <a href="#" class="nav-tab">Debug</a>
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
                            <h3>Attachments</h3>
                            <hr />
                            <ul>
                                <?php foreach ($attachments as $attachment_url => $attachment_name) : ?>
                                    <li>
                                        <a href="<?php echo $attachment_url ?>" target="_blank"><?php echo $attachment_name; ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php
                        $additional_headers = unserialize($log['additional_headers']);
                        if (!empty($additional_headers)) :
                            ?>
                            <h3>Additional Headers</h3>
                            <hr />
                            <ul>
                                <?php foreach ($additional_headers as $additional_header => $value) : ?>
                                    <li><strong><?php echo GeneralHelper::slug_to_label($additional_header); ?>:</strong> <?php echo $value; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <?php $debug = unserialize($log['backtrace_segment']); ?>
                        <ul>
                            <li>Triggered from: <strong style="white-space: pre;"><?php echo $debug['file']; ?></strong></li>
                            <li>On line: <strong><?php echo $debug['line']; ?></strong></li>
                        </ul>

                        <?php if (!empty($log['error'])) : ?>
                            <h3>Errors</h3>
                            <hr />
                            <ul>
                                <li><?php echo $log['error']; ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button-primary dismiss-modal">Close</button>
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
                            <h2>Headers</h2>
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
                                        <option value="to">To</option>
                                        <option value="cc">Cc</option>
                                        <option value="bcc">Bcc</option>
                                        <option value="other">Other</option>
                                    </select>

                                    <input name="header_values[]" type="text" class="field -input" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2>Subject</h2>
                            <hr />

                            <input name="subject" type="text" class="field -input" />
                        </div>
                        <div>
                            <h2>Attachments</h2>
                            <hr />

                            <div class="attachments-container">
                                <div class="attachment-clones">
                                    <span class="attachment-item -original">
                                        <span class="dashicons dashicons-dismiss remove"></span>
                                        <input type="hidden" name="attachment_ids[]" value="" class="attachment-input" />
                                    </span>
                                </div>

                                <div class="attachment-button-container">
                                    <a href="#" class="button-primary" id="add_attachments">Add attachments</a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2>Message</h2>
                            <hr />

                            <?php wp_editor('My Message', 'message', $settings = array()); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="button-primary">Send Message</button>
                <button type="button" class="button-secondary dismiss-modal">Close</button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>