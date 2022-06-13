<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

$dbUpgradeManager = DatabaseUpgradeManager::getInstance();
$settings = Settings::get();
$logs = MailAdminTable::getInstance();
$logs->prepare_items();
?>

<div class="wp-mail-catcher-page">
    <?php
    require GeneralHelper::$pluginViewDirectory . '/NewMessageModal.php';
    require GeneralHelper::$pluginViewDirectory . '/ExportWarningDialog.php';
    ?>

    <div class="wrap<?php if (count($logs->items) == 0) : ?> -empty<?php endif; ?>">
        <h2 class="heading">WP Mail Catcher - <?php _e('logs', 'WpMailCatcher'); ?></h2>

        <?php if ($dbUpgradeManager->isUpgradeRequired()) : ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(__('Your WP Mail Catcher database needs upgrading. <strong>Click <a href="%s">here</a> to perform the upgrade.</strong>',
                        'WpMailCatcher'),
                        '?page=' . GeneralHelper::$adminPageSlug . '&action=upgrade-database'
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($logs->totalItems > GeneralHelper::$logLimitBeforeWarning && $settings['auto_delete'] == false) : ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(__('You have <strong>over %s</strong> messages stored and <a href="%s">auto-delete is disabled</a>. As a result your database can become very large, please either allow auto-delete or delete some logs.',
                        'WpMailCatcher'),
                        GeneralHelper::$logLimitBeforeWarning,
                        '?page=' . GeneralHelper::$settingsPageSlug
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="button-container">
            <button class="btn button-primary" data-toggle="modal" data-target="#new-message">
                <?php _e('New Message', 'WpMailCatcher'); ?>
            </button>

            <?php if ($logs->totalItems > GeneralHelper::$logLimitBeforeWarning) : ?>
                <button class="btn button-secondary" data-toggle="modal" data-target="#export-warning-dialog">
                    <?php _e('Export all messages', 'WpMailCatcher'); ?>
                </button>
            <?php else : ?>
                <a href="<?php echo wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=export-all',
                    'bulk-logs'); ?>" class="btn button-secondary">
                    <?php _e('Export all messages', 'WpMailCatcher'); ?>
                </a>
            <?php endif; ?>
        </div>

        <ul class="subsubsub">
            <li>
                <a href="?page=<?php echo GeneralHelper::$adminPageSlug; ?>"
                    <?php if (!isset($_GET['post_status']) || $_GET['post_status'] == 'any') : ?> class="current"<?php endif; ?>>
                    <?php _e('All', 'WpMailCatcher'); ?> <span class="count">(<?php echo $logs->totalItems; ?>)</span>
                </a> |
            </li>
            <li>
                <a href="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&post_status=successful"
                    <?php if (isset($_GET['post_status']) && $_GET['post_status'] == 'successful') : ?> class="current"<?php endif; ?>>
                    <?php _e('Successful', 'WpMailCatcher'); ?>
                </a> |
            </li>
            <li>
                <a href="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&post_status=failed"
                    <?php if (isset($_GET['post_status']) && $_GET['post_status'] == 'failed') : ?> class="current"<?php endif; ?>>
                    <?php _e('Failed', 'WpMailCatcher'); ?>
                </a>
            </li>
        </ul>

        <form method="get">
            <!-- WordPress breaks the redirect unless we pass the query params as inputs instead of the <form> action param -->
            <?php foreach ($_GET as $key => $value) : ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
            <?php endforeach; ?>
            
            <?php $logs->search_box(__('Search Logs', 'WpMailCatcher'), 'search_id'); ?>

            <?php $logs->display(); ?>
        </form>

        <?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
    </div>

    <?php
    /** $log is used in LogModal.php  */
    foreach ($logs->items as $log) :
        require GeneralHelper::$pluginViewDirectory . '/LogModal.php';
    endforeach;
    ?>
</div>
