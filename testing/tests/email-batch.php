<?php

class EmailBatch extends WP_UnitTestCase {

	// TODO: Add test for plugins (SMTP, Mailgun etc)
	// TODO: Add test for attachments
	// TODO: Add test for additional headers

	function __construct() {
		$db = mysqli_connect('localhost', 'homestead', 'secret', 'wordpress-test');
		mysqli_query($db, 'TRUNCATE TABLE wptests_mail_catcher_logs');

		$this->test_correct_tos();
		$this->test_incorrect_tos();
	}

	function test_correct_tos() {
		wp_mail('test@test.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 1);
	}

	function test_incorrect_tos() {
		wp_mail('testtest.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 0);
	}
}
