<?php
$logs_controller = new Logs();
$logs = $logs_controller->get($_REQUEST['paged']);

$testListTable = new MailAdminTable();
$testListTable->prepare_items();
?>
<div class="wrap">
    <h2>Mail Catcher</h2>

    <form id="movies-filter" method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
        <?php $testListTable->display() ?>
    </form>
</div>

<?php foreach ($logs as $log) : ?>
    <div id="<?php echo $log['id']; ?>" class="modal fade">
        <div class="modal-dialog" role="document">
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
                            <p>Triggered from: <strong style="white-space: pre;"><?php echo $debug['file']; ?></strong></p>
                            <p>On line: <strong><?php echo $debug['line']; ?></strong></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>