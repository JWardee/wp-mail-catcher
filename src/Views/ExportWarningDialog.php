<?php

use WpMailCatcher\GeneralHelper;

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($wpMailCatcherLogs)) {
    return;
}
?>

<div id="export-warning-dialog" class="modal">
    <div class="modal-content">
        <form class="form-horizontal"
              action="?page=<?php echo esc_attr(GeneralHelper::$adminPageSlug); ?>&action=export-all"
              method="POST">
            <div class="modal-body">
                <div class="content-container">
                    <div class="content -active">
                        <div>
                            <h2><?php esc_html_e('Warning', 'wp-mail-catcher'); ?></h2>
                            <hr/>
                            <p>
                                <?php
                                printf(
                                    wp_kses(
                                        /* translators: 1: number of messages to export, 2: recommended maximum */
                                        __(
                                            'You are trying to export <strong>%1$s</strong> messages when the
                                             recommended limit is no more than <strong>%2$s</strong>, this can cause the
                                             server to timeout before the export is complete, we recommend reducing the
                                             amount of messages exported, or exporting them in batches.',
                                            'wp-mail-catcher'
                                        ),
                                        ['strong' => []]
                                    ),
                                    esc_html($wpMailCatcherLogs->totalItems),
                                    esc_html(GeneralHelper::$logLimitBeforeWarning)
                                );
                                ?>
                            </p>

                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th scope="row">
                                        <label>
                                            <?php esc_html_e('Number of logs to export', 'wp-mail-catcher'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <label>
                                            <input data-update-format name="posts_per_page" type="text"
                                                   value="<?php
                                                    echo esc_attr(GeneralHelper::$logLimitBeforeWarning);
                                                    ?>"
                                                   class="field -input"/>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label>
                                            <?php esc_html_e('Batch number', 'wp-mail-catcher'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <label>
                                            <input data-update-format name="paged" type="text" value="1"
                                                   class="field -input"/>
                                        </label>
                                        <p class="description"
                                           data-text-format="<?php
                                            /* translators: 1: first message number, 2: last message number */
                                            esc_attr_e(
                                                'This will export messages <strong>%1$s-%2$s</strong>',
                                                'wp-mail-catcher'
                                            );
                                            ?>"></p>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('bulk-logs'); ?>

            <div class="modal-footer">
                <button type="submit" class="button-primary">
                    <?php esc_html_e('Export', 'wp-mail-catcher'); ?>
                </button>
                <button type="button" class="button-secondary dismiss-modal">
                    <?php esc_html_e('Cancel', 'wp-mail-catcher'); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>
