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

    <div id="icon-users" class="icon32"><br/></div>
    <h2>List Table Test</h2>

    <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
        <p>This page demonstrates the use of the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt> class in plugins.</p>
        <p>For a detailed explanation of using the <tt><a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WP_List_Table</a></tt>
            class in your own plugins, you can view this file <a href="<?php echo admin_url( 'plugin-editor.php?plugin='.plugin_basename(__FILE__) ); ?>" style="text-decoration:none;">in the Plugin Editor</a> or simply open <tt style="color:gray;"><?php echo __FILE__ ?></tt> in the PHP editor of your choice.</p>
        <p>Additional class details are available on the <a href="http://codex.wordpress.org/Class_Reference/WP_List_Table" target="_blank" style="text-decoration:none;">WordPress Codex</a>.</p>
    </div>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="movies-filter" method="post" action="?page=<?php echo $_REQUEST['page'] ?>">
        <!-- Now we can render the completed list table -->
        <?php $testListTable->display() ?>
    </form>

</div>