<?php

use MailCatcher\Models\Logs;

class EmailBatch extends WP_UnitTestCase {

	// TODO: Add test for plugins (SMTP, Mailgun etc)
	// TODO: Add test for attachments
	// TODO: Add test for additional headers

	function __construct() {
//		$db = mysqli_connect('localhost', 'homestead', 'secret', 'wordpress-test');
//		mysqli_query($db, 'TRUNCATE TABLE wptests_mail_catcher_logs');

		$GLOBALS['wpdb']->query('TRUNCATE TABLE wptests_mail_catcher_logs');

		$this->testCorrectTos();
		$this->testIncorrectTos();
	}

	function testCorrectTos() {
//		wp_mail('test@test.com', 'subject', 'message');
		wp_mail('james@impact-designs.net', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 1);
	}

	function testIncorrectTos() {
//		wp_mail('testtest.com', 'subject', 'message');
		wp_mail('jamesimpact-designs.net', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 0);
	}
}
