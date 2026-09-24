<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

if (!defined('ABSPATH')) {
    exit;
}

$wpMailCatcherSettings = Settings::get();
$wpMailCatcherCapabilities = array_keys($GLOBALS['wp_roles']->roles['administrator']['capabilities']);
$wpMailCatcherAdminSlug = GeneralHelper::$adminPageSlug;
$wpMailCatcherViewRole = $wpMailCatcherSettings['default_view_role'];
$wpMailCatcherSettingsRole = $wpMailCatcherSettings['default_settings_role'];
$wpMailCatcherCronJobs = CronManager::getInstance()->getTasks();
$wpMailCatcherGetQueryFlag = function ($key) {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag set by the redirect after a verified action
    return isset($_GET[$key]) ? sanitize_text_field(wp_unslash($_GET[$key])) : null;
};
?>

<div class="wp-mail-catcher-page">
    <div class="wrap">
        <?php if ($wpMailCatcherGetQueryFlag('update_success') !== null) : ?>
            <?php if ($wpMailCatcherGetQueryFlag('update_success') == 1) : ?>
                <div class="notice notice-success">
                    <p>
                        <?php esc_html_e('Settings were successfully updated!', 'wp-mail-catcher'); ?>
                    </p>
                </div>
            <?php else : ?>
                <div class="notice notice-error">
                    <p>
                        <?php esc_html_e('You didn\'t change any settings', 'wp-mail-catcher'); ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($wpMailCatcherGetQueryFlag('trigger-auto-delete-success') == 1) : ?>
            <div class="notice notice-success">
                <p>
                    <?php esc_html_e('The auto delete was successfully triggered', 'wp-mail-catcher'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($wpMailCatcherGetQueryFlag('trigger-rerun-migration-success') == 1) : ?>
            <div class="notice notice-success">
                <p>
                    <?php esc_html_e('Database migrations were successfully rerun', 'wp-mail-catcher'); ?>
                </p>
            </div>
        <?php endif; ?>

        <h2 class="heading">WP Mail Catcher - <?php esc_html_e('settings', 'wp-mail-catcher'); ?></h2>

        <form action="?page=<?php echo esc_attr($wpMailCatcherAdminSlug); ?>&action=update_settings"
              method="post">
            <?php wp_nonce_field('update_settings'); ?>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php esc_html_e('User capability needed to see logs', 'wp-mail-catcher'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <select name="default_view_role">
                                    <?php foreach ($wpMailCatcherCapabilities as $wpMailCatcherCap) : ?>
                                        <option value="<?php echo esc_attr($wpMailCatcherCap); ?>"
                                            <?php selected($wpMailCatcherViewRole, $wpMailCatcherCap); ?>>
                                            <?php echo esc_html($wpMailCatcherCap); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php esc_html_e('User capability needed to edit settings', 'wp-mail-catcher'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <select name="default_settings_role">
                                    <?php foreach ($wpMailCatcherCapabilities as $wpMailCatcherCap) : ?>
                                        <option value="<?php echo esc_attr($wpMailCatcherCap); ?>"
                                            <?php selected($wpMailCatcherSettingsRole, $wpMailCatcherCap); ?>>
                                            <?php echo esc_html($wpMailCatcherCap); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="blogname">
                                <?php esc_html_e('Auto delete logs?', 'wp-mail-catcher'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="radio" name="auto_delete"
                                       value="false"<?php checked(!$wpMailCatcherSettings['auto_delete']); ?>>
                                <span class="date-time-text date-time-custom-text">
                                    <?php esc_html_e('No', 'wp-mail-catcher'); ?>
                                </span>
                            </label>
                            <fieldset>
                                <label>
                                    <input type="radio" name="auto_delete"
                                           value="true"<?php checked((bool)$wpMailCatcherSettings['auto_delete']); ?>>
                                    <span class="date-time-text date-time-custom-text">
                                        <?php
                                        $wpMailCatcherTimescaleSelect = '<span><select name="timescale">';

                                        $wpMailCatcherIntervals = ExpiredLogManager::deletionIntervals();

                                        foreach ($wpMailCatcherIntervals as $wpMailCatcherKey => $wpMailCatcherLabel) :
                                            $wpMailCatcherTimescaleSelect .= sprintf(
                                                '<option value="%1$s"%2$s>%3$s</option>',
                                                esc_attr($wpMailCatcherKey),
                                                selected($wpMailCatcherSettings['timescale'], $wpMailCatcherKey, false),
                                                esc_html($wpMailCatcherLabel)
                                            );
                                        endforeach;

                                        $wpMailCatcherTimescaleSelect .= '</select></span>';

                                        echo wp_kses(
                                            sprintf(
                                                /* translators: %s: dropdown of time periods, e.g. "4 weeks" */
                                                esc_html__(
                                                    'Yes - delete messages that are over %s old',
                                                    'wp-mail-catcher'
                                                ),
                                                $wpMailCatcherTimescaleSelect
                                            ),
                                            [
                                                'span' => [],
                                                'select' => ['name' => []],
                                                'option' => ['value' => [], 'selected' => []],
                                            ]
                                        );
                                        ?>
                                    </span>
                                </label>
                                <?php if (isset($wpMailCatcherCronJobs[0])) : ?>
                                    <p class="description">
                                        <?php
                                        printf(
                                            wp_kses(
                                                /* translators: 1: time until next auto delete, 2: URL */
                                                __(
                                                    'Will next run in: %1$s. <a href="%2$s">Trigger now</a>',
                                                    'wp-mail-catcher'
                                                ),
                                                ['a' => ['href' => []]]
                                            ),
                                            esc_html($wpMailCatcherCronJobs[0]['nextRun']),
                                            esc_url(wp_nonce_url(
                                                '?page=' . $wpMailCatcherAdminSlug . '&action=trigger-auto-delete',
                                                'trigger_auto_delete'
                                            ))
                                        );
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="blogname">
                                <?php esc_html_e('Database version', 'wp-mail-catcher'); ?>
                            </label>
                        </th>
                        <td>
                            <p class="description">
                                <?php
                                printf(
                                    wp_kses(
                                        /* translators: 1: database version number, 2: URL that reruns migrations */
                                        __('%1$s. <a href="%2$s">Rerun migrations</a>', 'wp-mail-catcher'),
                                        ['a' => ['href' => []]]
                                    ),
                                    esc_html($wpMailCatcherSettings['db_version']),
                                    esc_url(wp_nonce_url(
                                        '?page=' . $wpMailCatcherAdminSlug . '&action=rerun-migrations',
                                        'rerun_migrations'
                                    ))
                                );
                                ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Save Changes', 'wp-mail-catcher'); ?>
                </button>
            </p>
        </form>

        <?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
    </div>
</div>
