<?php
/*
Plugin Name: WP Mail Catcher
Plugin URI: https://wordpress.org/plugins/wp-mail-catcher/
Text Domain: WpMailCatcher
Domain Path: /languages
Description: A fast, lightweight plugin that saves emails sent by your WordPress website.
Author: James Ward
Version: 1.3.1
Author URI: https://jamesward.io
*/

use WpMailCatcher\Bootstrap;

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$bootstrap, 'install']);
register_deactivation_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'uninstall']);
