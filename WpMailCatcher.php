<?php
/*
Plugin Name: Mail logging - WP Mail Catcher
Plugin URI: https://wordpress.org/plugins/wp-mail-catcher/
Text Domain: wp-mail-catcher
Domain Path: /languages
Description: Logging your mail will stop you from ever losing your emails again! This fast, lightweight plugin (under 140kb in size!) is also useful for debugging or backing up your messages.
Author: James Ward
Version: 2.2.0
Author URI: https://jamesward.io
Donate link: https://paypal.me/jamesmward
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

use WpMailCatcher\Bootstrap;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

$wpMailCatcherBootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$wpMailCatcherBootstrap, 'install']);
register_deactivation_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'uninstall']);
