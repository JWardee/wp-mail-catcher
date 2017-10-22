<?php
/*
Plugin Name: Mail Catcher
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: derp
Author: James Ward
Version: 1
Author URI: https://jamesward.io
*/
use MailCatcher\Bootstrap;

require_once __DIR__ . '/vendor/autoload.php';

$bootstrap = new Bootstrap();

register_activation_hook(__FILE__, [$bootstrap, 'install']);
register_deactivation_hook(__FILE__, ['MailCatcher\Bootstrap', 'deactivate']);
register_uninstall_hook(__FILE__, ['MailCatcher\Bootstrap', 'uninstall']);
