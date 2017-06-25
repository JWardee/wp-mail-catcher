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
$plugin_path = plugin_dir_url(__FILE__);

spl_autoload_register(function ($class_name) {
    $dir = __DIR__ . '/libs/' . $class_name . '.php';

    if (file_exists($dir)) {
        include $dir;
    }
});

new MailCatcher();
