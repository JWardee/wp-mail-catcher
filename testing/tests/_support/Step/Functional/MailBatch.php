<?php
namespace Step\Functional;

class MailBatch extends \FunctionalTester
{

    public function can_send_single_to()
    {
		$GLOBALS['mail_catcher']->uninstall();
		$GLOBALS['mail_catcher']->install();

        $I = $this;

		wp_mail('test@test.com', 'SINGLE TO', 'message');
		$first_mail = \Logs::get()[0];

		$I->assertEquals($first_mail['emailto'], 'test@test.com');
		$I->assertEquals($first_mail['subject'], 'SINGLE TO');
		$I->assertEquals($first_mail['message'], 'message');
		$I->assertEquals($first_mail['status'], 1);

		return;
    }

}
