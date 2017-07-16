<?php
class EmailBatch
{
	private $functional_test_obj;

	public function __construct(FunctionalTester $I)
	{
		$this->functional_test_obj = $I;
		$GLOBALS['mail_catcher']->uninstall();
		$GLOBALS['mail_catcher']->install();

		$this->can_send_single_to();
	}

	private function can_send_single_to()
	{
		wp_mail('test@test.com', 'SINGLE TO', 'message');
		$first_mail = Logs::get()[0];

		$this->functional_test_obj->assertEquals($first_mail['emailto'], 'test@test.com');
		$this->functional_test_obj->assertEquals($first_mail['subject'], 'SINGLE TO');
		$this->functional_test_obj->assertEquals($first_mail['message'], 'message');
		$this->functional_test_obj->assertEquals($first_mail['status'], 1);
	}
}
