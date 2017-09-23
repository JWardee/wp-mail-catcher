<?php

use MailCatcher\Models\Logs;
use MailCatcher\Models\Mail;

class TestLogFunctions extends WP_UnitTestCase
{
	function testCanDeleteSingleLog()
	{
		wp_mail('test@test.com', 'subject', 'message');

		$mail = Logs::get(array(
			'posts_per_page' => 1
		));

		$this->assertEquals(count($mail), 1);

		Logs::delete(array(
			$mail[0]['id']
		));

		$mail = Logs::get(array(
			'post__in' => $mail[0]['id']
		));

		$this->assertEquals(count($mail), 0);
	}

	function testCanDeleteMultipleLogs()
	{
		wp_mail('test@test.com', 'subject', 'message');
		wp_mail('test@test.com', 'subject', 'message');

		$mail = Logs::get(array(
			'posts_per_page' => 2
		));

		$this->assertEquals(count($mail), 2);

		Logs::delete(array(
			$mail[0]['id'],
			$mail[1]['id']
		));

		$mail = Logs::get(array(
			'post__in' => array(
				$mail[0]['id'],
				$mail[1]['id']
			)
		));

		$this->assertEquals(count($mail), 0);
	}

	function testCanResendSingleMail()
	{
		wp_mail('test@test.com', 'RESEND ME', 'message');

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$this->assertEquals(count($mail), 1);

		Mail::resend(array(
			$mail[0]['id']
		));

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$this->assertEquals(count($mail), 2);
	}

	function testCanResendMultipleMail()
	{
		wp_mail('test@test.com', 'RESEND ME 1', 'message');
		wp_mail('test@test.com', 'RESEND ME 2', 'message');

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$this->assertEquals(count($mail), 2);

		Mail::resend(array(
			$mail[0]['id'],
			$mail[1]['id']
		));

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$this->assertEquals(count($mail), 4);
	}

	//	function test_can_export_multiple_log() {
//		wp_mail('test@test.com', 'RESEND 1', 'message');
//		wp_mail('test@test.com', 'RESEND 2', 'message');
//		wp_mail('test@test.com', 'RESEND 3', 'message');
//
//		$last_3_emails = Logs::get(array(
//			'posts_per_page' => 3
//		));
//
//		$export = Mail::export(array(
//			$last_3_emails[0]['id'],
//			$last_3_emails[1]['id'],
//			$last_3_emails[2]['id']
//		));

//		$this->assertContains($export, array(
//			$last_3_emails[0]['subject'],
//			$last_3_emails[1]['subject'],
//			$last_3_emails[2]['subject']
//		));
//	}
}
