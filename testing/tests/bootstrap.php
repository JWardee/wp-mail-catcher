<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Sample_Plugin
 */

//require __DIR__ . '/../vendor/autoload.php';

//print_r(dirname(__DIR__));
//exit;

//$_tests_dir = getenv( 'WP_TESTS_DIR' );
//if ( ! $_tests_dir ) {
	$_tests_dir = dirname(__DIR__) . '/tmp/wordpress-tests-lib';
//}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';


/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/../MailCatcher.php';
	$bootstrap->install();


}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require_once __DIR__ . '/EmailBatch.php';
