<?php
/*
Plugin Name: Backup & Save Contact Form Emails - WP Mail Catcher
Plugin URI: https://wordpress.org/plugins/wp-mail-catcher/
Text Domain: WpMailCatcher
Domain Path: /languages
Description: Backup and save your contact form emails (including Contact Form 7) to your database with this fast, lightweight plugin (under 140kb in size!)
Author: James Ward
Version: 1.3.2
Author URI: https://jamesward.io
*/

use WpMailCatcher\Bootstrap;

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$bootstrap, 'install']);
register_deactivation_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['WpMailCatcher\Bootstrap', 'uninstall']);
