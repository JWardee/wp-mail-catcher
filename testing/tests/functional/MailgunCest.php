<?php
class MailgunCest
{
	private $already_installed = false;
	private $old_settings = array();

	public function _before(\FunctionalTester $I)
	{
		// TODO: Should use composer namespacing
		require_once __DIR__ . '/EmailBatch.php';

		if ($I->cli('plugin is-installed mailgun') == 0) {
			$this->already_installed = true;
		}

		if ($this->already_installed == true) {
			//$this->old_settings['api_key'] = get_option('api_key');
		} else {
			// Download and install Mailgun
			$I->cli('plugin install mailgun --force --activate');
		}

		//update_option('api_key', env('MAILGUN_API_KEY'));
	}

	public function tryMailgun(FunctionalTester $I)
	{
		// Run new email batch
		// new EmailBatch($I);
	}

	public function _after(\FunctionalTester $I)
	{
		if ($this->already_installed == false) {
			$I->cli('plugin uninstall mailgun --deactivate');
			return;
		}

		// Reset Mailgun back to it's previous settings
		//update_option('api_key', $this->old_settings['api_key']);



	}
}
