<?php

class EmailTest extends WP_UnitTestCase {

	// TODO: Add test for plugins (SMTP, Mailgun etc)
	// TODO: Add test for attachments
	// TODO: Add test for additional headers

	function test_correct_tos() {
		wp_mail('test@test.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 1);
	}
}
