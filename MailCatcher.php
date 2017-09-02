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

register_activation_hook(__FILE__, array($bootstrap, 'install'));
register_uninstall_hook(__FILE__, array('Bootstrap', 'uninstall'));

/**
 * Only use to test uninstall method
 */
//register_deactivation_hook(__FILE__, array($bootstrap, 'uninstall'));
