<?php
class LogCest
{
	public function __before(FunctionalTester $I)
	{
		$GLOBALS['mail_catcher']->uninstall();
		$GLOBALS['mail_catcher']->install();
	}

	public function try_to_delete_single_log(FunctionalTester $I)
	{
		wp_mail('test@test.com', 'subject', 'message');

		$mail = Logs::get(array(
			'posts_per_page' => 1
		));

		$I->assertEquals(count($mail), 1);

		Logs::delete(array(
			$mail[0]['id']
		));

		$mail = Logs::get(array(
			'post__in' => $mail[0]['id']
		));

		$I->assertEquals(count($mail), 0);
	}

	function try_to_delete_multiple_logs(FunctionalTester $I)
	{
		wp_mail('test@test.com', 'subject', 'message');
		wp_mail('test@test.com', 'subject', 'message');

		$mail = Logs::get(array(
			'posts_per_page' => 2
		));

		$I->assertEquals(count($mail), 2);

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

		$I->assertEquals(count($mail), 0);
	}

	public function try_to_resend_single_mail(FunctionalTester $I)
	{
		wp_mail('test@test.com', 'RESEND ME', 'message');

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$I->assertEquals(count($mail), 1);

		Mail::resend(array(
			$mail[0]['id']
		));

		$mail = Logs::get(array(
			'subject' => 'RESEND ME'
		));

		$I->assertEquals(count($mail), 2);
	}

	public function try_to_resend_multiple_mail(FunctionalTester $I)
	{
		wp_mail('test@test.com', 'RESEND MULTIPLE 1', 'message');
		wp_mail('test@test.com', 'RESEND MULTIPLE 2', 'message');

		$mail = Logs::get(array(
			'subject' => 'RESEND MULTIPLE'
		));

		$I->assertEquals(count($mail), 2);

		Mail::resend(array(
			$mail[0]['id'],
			$mail[1]['id']
		));

		$mail = Logs::get(array(
			'subject' => 'RESEND MULTIPLE'
		));

		$I->assertEquals(count($mail), 4);
	}
}
