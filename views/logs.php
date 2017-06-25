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
                                <?php foreach ($attachments as $attachment) : ?>
                                    <li><?php echo $attachment; ?></li>
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
                                    <li><strong><?php echo $additional_header; ?>:</strong> <?php echo $value; ?></li>
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
        <form class="form-horizontal">
            <div class="modal-body">
                <div class="content-container">
                    <h2 class="nav-tab-wrapper">
                        <a href="#" class="nav-tab nav-tab-active">Message</a>
                        <a href="#" class="nav-tab">Advanced</a>
                    </h2>
                    <div class="content -active">
                        <div>
                            <h2>Address</h2>
                            <hr />

                            <div class="cloneable">
                                <div class="field-block">
                                    <a href="#" class="add-field">
                                        <span class="dashicons dashicons-plus-alt -icon"></span>
                                    </a>

                                    <a href="#" class="remove-field -disabled">
                                        <span class="dashicons dashicons-dismiss -icon"></span>
                                    </a>

                                    <select name="heading_key[]" class="field -select">
                                        <option value="to">to</option>
                                        <option value="cc">cc</option>
                                        <option value="bcc">bcc</option>
                                    </select>

                                    <input name="heading_value[]" type="text" class="field -input" />
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2>Attachments</h2>
                            <hr />

                            <div class="attachments-container">
                                <div class="attachment-clones">
                                    <span class="attachment-item -original">
                                        <span class="remove">X</span>
                                        <input type="hidden" name="attachment_ids[]" value="" class="attachment-input" />
                                    </span>
                                </div>

                                <div class="attachment-button-container">
                                    <a href="#" class="button-primary" id="myprefix_media_manager">Add attachments</a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h2>Message</h2>
                            <hr />

                            <?php wp_editor('test', 'editor', $settings = array()); ?>
                        </div>
                    </div>
                    <div class="content">
                        <h2>Additional Headers</h2>
                        <hr />

                        <div class="cloneable">
                            <div class="field-block">
                                <a href="#" class="add-field">
                                    <span class="dashicons dashicons-plus-alt -icon"></span>
                                </a>

                                <a href="#" class="remove-field -disabled">
                                    <span class="dashicons dashicons-dismiss -icon"></span>
                                </a>

                                <input name="additional_headers[]" type="text" class="field -input" />
                            </div>
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