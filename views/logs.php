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
    <div id="<?php echo $log['id']; ?>" class="modal"></div>
<?php endforeach; ?>