<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

if (!defined('ABSPATH')) {
    exit;
}

$wpMailCatcherDbUpgradeManager = DatabaseUpgradeManager::getInstance();
$wpMailCatcherSettings = Settings::get();
$wpMailCatcherLogs = MailAdminTable::getInstance();
$wpMailCatcherLogs->prepare_items();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter used to highlight the current view
$wpMailCatcherPostStatus = isset($_GET['post_status']) ? sanitize_key(wp_unslash($_GET['post_status'])) : 'any';
?>

<div class="wp-mail-catcher-page">
    <?php
    require GeneralHelper::$pluginViewDirectory . '/NewMessageModal.php';
    require GeneralHelper::$pluginViewDirectory . '/ExportWarningDialog.php';
    ?>

    <div class="wrap<?php if (count($wpMailCatcherLogs->items) == 0) :
        ?> -empty<?php
                    endif; ?>">
        <h2 class="heading">WP Mail Catcher - <?php esc_html_e('logs', 'wp-mail-catcher'); ?></h2>

        <?php if ($wpMailCatcherDbUpgradeManager->isUpgradeRequired()) : ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(
                        wp_kses(
                            /* translators: %s: URL that performs the database upgrade */
                            __(
                                'Your WP Mail Catcher database needs upgrading. <strong>Click <a href="%s">here</a>
                                 to perform the upgrade.</strong>',
                                'wp-mail-catcher'
                            ),
                            ['strong' => [], 'a' => ['href' => []]]
                        ),
                        esc_url(wp_nonce_url(
                            '?page=' . GeneralHelper::$adminPageSlug . '&action=upgrade-database',
                            'upgrade-database'
                        ))
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        if (
            $wpMailCatcherLogs->totalItems > GeneralHelper::$logLimitBeforeWarning &&
            !$wpMailCatcherSettings['auto_delete']
        ) :
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(
                        wp_kses(
                            /* translators: 1: number of stored messages, 2: URL of the settings page */
                            __(
                                'You have <strong>over %1$s</strong> messages stored and <a href="%2$s">auto-delete is
                                 disabled</a>. As a result your database can become very large, please either allow
                                 auto-delete or delete some logs.',
                                'wp-mail-catcher'
                            ),
                            ['strong' => [], 'a' => ['href' => []]]
                        ),
                        esc_html(GeneralHelper::$logLimitBeforeWarning),
                        esc_url('?page=' . GeneralHelper::$settingsPageSlug)
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="button-container">
            <button class="btn button-primary" data-toggle="modal" data-target="#new-message">
                <?php esc_html_e('New Message', 'wp-mail-catcher'); ?>
            </button>

            <?php if ($wpMailCatcherLogs->totalItems > GeneralHelper::$logLimitBeforeWarning) : ?>
                <button class="btn button-secondary" data-toggle="modal" data-target="#export-warning-dialog">
                    <?php esc_html_e('Export all messages', 'wp-mail-catcher'); ?>
                </button>
            <?php else : ?>
                <a href="
                    <?php echo
                    esc_url(wp_nonce_url(
                        '?page=' . GeneralHelper::$adminPageSlug . '&action=export-all',
                        'bulk-logs'
                    ));
                    ?>" class="btn button-secondary">
                    <?php esc_html_e('Export all messages', 'wp-mail-catcher'); ?>
                </a>
            <?php endif; ?>
        </div>

        <ul class="subsubsub">
            <li>
                <a href="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>"
                    <?php if ($wpMailCatcherPostStatus == 'any') :
                        ?> class="current"<?php
                    endif; ?>>
                    <?php esc_html_e('All', 'wp-mail-catcher'); ?>
                    <span class="count">(<?php echo esc_html($wpMailCatcherLogs->totalItems); ?>)</span>
                </a> |
            </li>
            <li>
                <a href="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>&post_status=successful"
                    <?php if ($wpMailCatcherPostStatus == 'successful') :
                        ?> class="current"<?php
                    endif; ?>>
                    <?php esc_html_e('Successful', 'wp-mail-catcher'); ?>
                </a> |
            </li>
            <li>
                <a href="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>&post_status=failed"
                    <?php if ($wpMailCatcherPostStatus == 'failed') :
                        ?> class="current"<?php
                    endif; ?>>
                    <?php esc_html_e('Failed', 'wp-mail-catcher'); ?>
                </a>
            </li>
        </ul>

        <form method="get">
            <!--
            WordPress breaks the redirect unless we pass the query params as inputs
            instead of the <form> action param
            -->
            <?php foreach (GeneralHelper::getPreservedUrlParams() as $wpMailCatcherKey => $wpMailCatcherValue) : ?>
                <input type="hidden"
                       name="<?php echo esc_attr($wpMailCatcherKey); ?>"
                       value="<?php echo esc_attr($wpMailCatcherValue); ?>" />
            <?php endforeach; ?>

            <?php $wpMailCatcherLogs->search_box(__('Search Logs', 'wp-mail-catcher'), 'search_id'); ?>

            <?php $wpMailCatcherLogs->display(); ?>
        </form>

        <?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
    </div>

    <?php
    /** $wpMailCatcherLog is used in LogModal.php  */
    foreach ($wpMailCatcherLogs->items as $wpMailCatcherLog) :
        require GeneralHelper::$pluginViewDirectory . '/LogModal.php';
    endforeach;
    ?>
</div>
