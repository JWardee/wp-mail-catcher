<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Settings;

$settings = Settings::get();
$capabilities = $GLOBALS['wp_roles']->roles['administrator']['capabilities'];
$cronJobs = CronManager::getInstance()->getTasks();
?>

<div class="wp-mail-catcher-page">
    <div class="wrap">
        <?php if (isset($_GET['update_success'])) : ?>
            <?php if ($_GET['update_success'] == 1) : ?>
                <div class="notice notice-success">
                    <p>
                        <?php _e('Settings were successfully updated!', 'WpMailCatcher'); ?>
                    </p>
                </div>
            <?php else : ?>
                <div class="notice notice-error">
                    <p>
                        <?php _e('You didn\'t change any settings', 'WpMailCatcher'); ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <h2 class="heading">WP Mail Catcher - <?php _e('settings', 'WpMailCatcher'); ?></h2>

        <form action="?page=<?php echo GeneralHelper::$adminPageSlug; ?>&action=update_settings" method="post">
            <?php wp_nonce_field('update_settings'); ?>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php _e('User capability needed to see logs', 'WpMailCatcher'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="default_view_role">
                                <?php foreach ($capabilities as $capability => $value) : ?>
                                    <option<?php if ($settings['default_view_role'] == $capability) : ?> selected<?php endif; ?>>
                                        <?php echo $capability; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php _e('User capability needed to edit settings', 'WpMailCatcher'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="default_settings_role">
                                <?php foreach ($capabilities as $capability => $value) : ?>
                                    <option<?php if ($settings['default_settings_role'] == $capability) : ?> selected<?php endif; ?>>
                                        <?php echo $capability; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="blogname">
                                <?php _e('Auto delete logs?', 'WpMailCatcher'); ?>
                            </label>
                        </th>
                        <td>
                            <label>
                                <input type="radio" name="auto_delete"
                                       value="false"<?php if ($settings['auto_delete'] == false) : ?> checked<?php endif; ?>>
                                <span class="date-time-text date-time-custom-text">
                                    <?php _e('No', 'WpMailCatcher'); ?>
                                </span>
                            </label>
                            <fieldset>
                                <label>
                                    <input type="radio" name="auto_delete"
                                           value="true"<?php if ($settings['auto_delete'] == true) : ?> checked<?php endif; ?>>
                                    <span class="date-time-text date-time-custom-text">
                                        <?php _e('Yes', 'WpMailCatcher'); ?>
                                    </span>
                                </label>
                                <span class="example">
                                    <select name="timescale">
                                        <?php foreach (wp_get_schedules() as $key => $cronSchedule) : ?>
                                            <option value="<?php echo $key; ?>" <?php if (isset($settings['timescale']) && $settings['timescale'] == $key) : ?> selected<?php endif; ?>>
                                                <?php echo $cronSchedule['display']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </span>
                                <?php if (isset($cronJobs[0])) : ?>
                                    <p class="description">
                                        <?php printf(__('Will next run in: %s.', 'WpMailCatcher'), $cronJobs[0]['nextRun']); ?>
                                    </p>
                                <?php endif; ?>
                            </fieldset>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php _e('Save Changes', 'WpMailCatcher'); ?>
                </button>
            </p>
        </form>

        <?php require GeneralHelper::$pluginViewDirectory . '/Footer.php'; ?>
    </div>
</div>
