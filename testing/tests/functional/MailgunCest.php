<?php
class MailgunCest
{
	public function _before(\FunctionalTester $I)
	{
		// TODO: Should use composer namespacing
		require_once __DIR__ . '/EmailBatch.php';

		if ($I->cli('plugin is-installed mailgun') == 1) {
			// Download and install Mailgun
			$I->cli('plugin install mailgun --force --activate');
		}

		$mailgun_obj = unserialize(get_option('mailgun'));

		$mailgun_obj['apikey'] = env('MAILGUN_API_KEY');
		$mailgun_obj['domain'] = env('MAILGUN_DOMAIN');

		update_option('mailgun', serialize($mailgun_obj));
	}

	public function tryMailgun(FunctionalTester $I)
	{
		// Run new email batch
		 new EmailBatch($I);
	}

	public function _after(\FunctionalTester $I)
	{
		$I->cli('plugin uninstall mailgun --deactivate');
	}
}
