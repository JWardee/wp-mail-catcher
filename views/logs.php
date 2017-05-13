<?php
//global $wpdb;

//if (!empty($_GET['action']) && $_GET['action'] == 'delete') {
//    $wpdb->delete('table', array('id' => $_GET['id']));
//}
//include __DIR__ . '/../MailAdminTable.php';
//Create an instance of our package class...
$testListTable = new MailAdminTable();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();
?>
<div class="wrap">
    <h2>Mail Catcher</h2>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="movies-filter" method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>
</div>