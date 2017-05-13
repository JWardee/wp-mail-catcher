<?php
/*
Plugin Name: Mail Catcher
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: derp
Author: James Ward
Version: 1
Author URI: http://jamesward.io
*/
spl_autoload_register(function ($class_name) {
    include __DIR__ . '/libs/' . $class_name . '.php';
});

new MailCatcher();








