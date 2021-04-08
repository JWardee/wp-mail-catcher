<?php

use WpMailCatcher\Models\Logs;

class TestBuddyPressEmails extends WP_UnitTestCase
{
	public function setUp()
	{
        require __DIR__ . '/../../vendor/wpackagist-plugin/buddypress/bp-loader.php';
        Logs::truncate();
	}

//	public function testMail()
//	{
//		$to = 'test@test.com';
//
//        print_r(bp_send_email('core-user-registration', $to));
//
////        do_action('bp_send_email_success');
//
//        $emailLogs = Logs::get();
//
////        print_r($GLOBALS['wp_filter']['bp_send_email_success']);
//        print_r($emailLogs);
//        print_r(bp_version_updater());
//        exit;
//
//        $this->assertCount(1, $emailLogs);
//		$this->assertEquals($to, $emailLogs[0]['email_to']);
//        $this->assertTrue($emailLogs[0]['status']);
//    }
}
