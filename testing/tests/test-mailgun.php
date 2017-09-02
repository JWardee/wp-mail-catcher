<?php

class DefaultMailgun extends WP_UnitTestCase
{
	function test_mailgun() {
		shell_exec('wp plugin install mailgun --activate --require=/tmp/wordpress-tests-lib/wp-tests-config.php --path=/tmp/wordpress');

		$mailgun_obj = unserialize(get_option('mailgun'));

		$mailgun_obj['apikey'] = 'key-419ac8ff56b1568c8bd0a25f523c82cc';//env('MAILGUN_API_KEY');
		$mailgun_obj['domain'] = 'sandboxed6601b2f6f746a7b9e5b61de5474f4f.mailgun.org';//env('MAILGUN_DOMAIN');

		update_option('mailgun', serialize($mailgun_obj));

		new EmailBatch();

		shell_exec('wp plugin uninstall mailgun --deactivate --require=/tmp/wordpress-tests-lib/wp-tests-config.php --path=/tmp/wordpress');
	}

}
