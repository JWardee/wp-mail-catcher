<?php
class MailCest
{
	public function tryStandardMail(FunctionalTester $I)
	{
		// TODO: Should use composer namespacing
		require_once __DIR__ . '/EmailBatch.php';
		new EmailBatch($I);
	}

	public function tryMailgun(FunctionalTester $I)
	{
		// Download and install Mailgun
		$I->cli('plugin install mailgun --force --activate');

		// Insert configs into db

		// Run new email batch
		// new EmailBatch($I);

		// Deactivate Mailgun
		$I->cli('plugin uninstall mailgun --deactivate');
	}
}
