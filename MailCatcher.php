<?php
/*
Plugin Name: Mail Catcher
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: derp
Author: James Ward
Version: 1
Author URI: http://jamesward.io
*/

// TODO: Refactor away from global namespace
global $plugin_path;
$plugin_path = __DIR__;

spl_autoload_register(function ($class_name) {
    global $plugin_path;
    $dir = $plugin_path . '/libs/' . $class_name . '.php';

    if (file_exists($dir)) {
        include $dir;
    }
});

new MailCatcher();






