<?php
class MailgunCest
{
	private $already_installed = true;

	public function _before(FunctionalTester $I)
	{
		if ($I->cli('plugin is-installed mailgun') == 1) {
			$this->already_installed = false;

			// Download and install Mailgun
			$I->cli('plugin install mailgun --force --activate');
		}

		$mailgun_obj = unserialize(get_option('mailgun'));

		$mailgun_obj['apikey'] = env('MAILGUN_API_KEY');
		$mailgun_obj['domain'] = env('MAILGUN_DOMAIN');

		update_option('mailgun', serialize($mailgun_obj));
	}

	public function tryMailgun(\Step\Functional\MailBatch $I)
	{
		$I->can_send_single_to();
	}

	private function uninstall_mailgun(FunctionalTester $I)
	{
		if ($this->already_installed == false) {
			$I->cli('plugin uninstall mailgun --deactivate');
		}
	}

	public function _after(FunctionalTester $I)
	{
		$this->uninstall_mailgun($I);
	}

	public function _failed(FunctionalTester $I)
	{
		$this->uninstall_mailgun($I);
	}
}
