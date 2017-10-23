<?php

use MailCatcher\Models\Logs;
use MailCatcher\Models\Mail;

class TestLogFunctions extends WP_UnitTestCase
{
	function testCanDeleteSingleLog()
	{
		Logs::truncate();

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
		Logs::truncate();

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
		Logs::truncate();

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
		Logs::truncate();

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

	function testCanExportSingleLog() {
		/** TODO: Write test */
		$this->markTestSkipped();

//		Logs::truncate();
//
//		wp_mail('test@test.com', 'EXPORT SINGLE', 'message');
//
//		$lastEmail = Logs::get(array(
//			'posts_per_page' => 1
//		));
//
//		$export = Mail::export(array(
//			$lastEmail[0]['id'],
//		));
//
//		$this->assertContains($export, array(
//			$lastEmail[0]['subject'],
//		));
	}

	function testCanExportMultipleLogs() {
		/** TODO: Write test */
		$this->markTestSkipped();

//		Logs::truncate();
//
//		wp_mail('test@test.com', 'EXPORT 1', 'message');
//		wp_mail('test@test.com', 'EXPORT 2', 'message');
//		wp_mail('test@test.com', 'EXPORT 3', 'message');
//
//		$last3Emails = Logs::get(array(
//			'posts_per_page' => 3
//		));
//
//		$export = Mail::export(array(
//			$last3Emails[0]['id'],
//			$last3Emails[1]['id'],
//			$last3Emails[2]['id']
//		));
//
//		$this->assertContains($export, array(
//			$last3Emails[0]['subject'],
//			$last3Emails[1]['subject'],
//			$last3Emails[2]['subject']
//		));
	}
}
