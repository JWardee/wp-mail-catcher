<?php
/*
Plugin Name: Mail logging - WP Mail Catcher
Plugin URI: https://wordpress.org/plugins/wp-mail-catcher/
Text Domain: WpMailCatcher
Domain Path: /languages
Description: Logging your mail will stop you from ever losing your emails again! This fast, lightweight plugin (under 140kb in size!) is also useful for debugging or backing up your messages.
Author: James Ward
Version: 2.0.5
Author URI: https://jamesward.io
Donate link: https://paypal.me/jamesmward
*/

use WpMailCatcher\Bootstrap;

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$bootstrap, 'install']);
register_deactivation_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'uninstall']);
