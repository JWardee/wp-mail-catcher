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
                        <a href="#" class="nav-tab">Debug</a>
                    </h2>
                    <div class="content-container">
                        <div class="content -active">
                            <?php echo $log['message']; ?>
                        </div>
                        <div class="content">
                            <?php var_dump(unserialize($log['backtrace_segment'])); ?>
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