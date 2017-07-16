<?php
class DefaultWpMailCest
{
	public function __before(FunctionalTester $I)
	{
		// TODO: Should use composer namespacing
		require_once __DIR__ . '/EmailBatch.php';
	}

	public function tryDefaultWpMail(FunctionalTester $I)
	{
		//new EmailBatch($I);
	}
}
