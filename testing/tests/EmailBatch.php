<?php

use MailCatcher\Models\Logs;

class EmailBatch extends WP_UnitTestCase {

	// TODO: Add test for plugins (SMTP, Mailgun etc)
	// TODO: Add test for attachments
	// TODO: Add test for additional headers

	function __construct() {
		Logs::truncate();
		$this->testCorrectTos();
		$this->testIncorrectTos();
	}

	function testCorrectTos() {
		wp_mail('test@test.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 1);
	}

	function testIncorrectTos() {
		wp_mail('testtest.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 0);
	}
}
