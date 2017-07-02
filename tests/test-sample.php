<?php
/**
 * Class SampleTest
 *
 * @package Sample_Plugin
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Assert that the 'status' of the mail sent will be 0
		// because of the incorrect 'to' email address
		wp_mail('testtest.com', 'subject', 'message');
		$this->assertEquals(Logs::get(1, 1)[0]['status'], 0);
	}
}
