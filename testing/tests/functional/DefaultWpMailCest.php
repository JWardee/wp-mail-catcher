<?php
class DefaultWpMailCest
{
	public function tryDefaultWpMail(\Step\Functional\MailBatch $I)
	{
		$I->can_send_single_to();
	}
}
