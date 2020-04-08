<?php use WpMailCatcher\GeneralHelper; ?>

<div id="export-warning-dialog" class="modal">
    <div class="modal-content">
        <form class="form-horizontal" action="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&action=export-all"
              method="POST">
            <div class="modal-body">
                <div class="content-container">
                    <div class="content -active">
                        <div>
                            <h2><?php _e('Warning', 'WpMailCatcher'); ?></h2>
                            <hr/>
                            <p>
                                <?php
                                printf(__('You are trying to export <strong>%s</strong> messages when the recommended limit is no more than <strong>%s</strong>,
                                           this can cause the server to timeout before the export is complete, we recommend reducing the amount of messages exported, or exporting them in batches.',
                                    'WpMailCatcher'),
                                    $logs->totalItems,
                                    GeneralHelper::$logLimitBeforeWarning
                                );
                                ?>
                            </p>

                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th scope="row">
                                        <label>
                                            <?php _e('Number of logs to export', 'WpMailCatcher'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input data-update-format name="posts_per_page" type="text"
                                               value="<?php echo GeneralHelper::$logLimitBeforeWarning; ?>"
                                               class="field -input"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label>
                                            <?php _e('Batch number', 'WpMailCatcher'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input data-update-format name="paged" type="text" value="1"
                                               class="field -input"/>
                                        <p class="description"
                                           data-text-format="<?php _e('This will export messages <strong>%s-%s</strong>',
                                               'WpMailCatcher'); ?>"></p>
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
                    <?php _e('Export', 'WpMailCatcher'); ?>
                </button>
                <button type="button" class="button-secondary dismiss-modal">
                    <?php _e('Cancel', 'WpMailCatcher'); ?>
                </button>
            </div>
        </form>
    </div>
    <div class="backdrop dismiss-modal"></div>
</div>
