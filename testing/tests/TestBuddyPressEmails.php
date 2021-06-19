<?php

use WpMailCatcher\Models\Logs;

class TestBuddyPressEmails extends WP_UnitTestCase
{
	public function setUp()
	{
        require __DIR__ . '/../../vendor/wpackagist-plugin/buddypress/bp-loader.php';
//        require __DIR__ . '/../../vendor/wpackagist-plugin/buddypress/bp-core/admin/bp-core-admin-schema.php';
//        require __DIR__ . '/../../vendor/autoload.php';
        activate_plugin(__DIR__ . '/../../vendor/wpackagist-plugin/buddypress/bp-loader.php');
        Logs::truncate();
	}

	public function testMail()
	{
		$to = 'test@test.com';

//        bp_core_install();
//        bp_update_option('bp-active-components', true);
//        bp_core_add_page_mappings();
//        buddypress()->autoload();

//        \bp_core_install_emails();
        $emailTemplate = wp_insert_post([
            'post_status'      => 'publish',
            'post_type'        => bp_get_email_post_type(),
            'post_content' => 'Thanks for registering!

To complete the activation of your account, go to the following link and click on the <strong>Activate</strong> button:
<a href="{{{activate.url}}}">{{{activate.url}}}</a>

If the \'Activation Key\' field is empty, copy and paste the following into the field - {{key}}'
        ]);

        wp_set_post_terms($emailTemplate, 'core-user-registration', bp_get_email_tax_type());

        add_filter('bp_get_email_args', function($args) use ($emailTemplate) {
//            print_r($args);
//            exit;
            return [
                'p' => $emailTemplate,
                'post_type' => bp_get_email_post_type()
            ];
        });

//        $q = new WP_Query(['p' => $emailTemplate, 'post_type' => bp_get_email_post_type(), 		'tax_query'        => array(
//            array(
//                'field'    => 'slug',
//                'taxonomy' => bp_get_email_tax_type(),
//                'terms'    => 'core-user-registration',
//            )
//        ),]);
//
//        print_r($emailTemplate);
//        print_r($q->posts);
//        exit;

        print_r(\bp_send_email('core-user-registration', 'test@test.com'));

//        do_action('bp_send_email_success');

        $emailLogs = Logs::get();

//        print_r($GLOBALS['wp_filter']['bp_send_email_success']);
//        print_r($emailLogs);
//        print_r(bp_version_updater());
//        exit;

        $this->assertCount(1, $emailLogs);
		$this->assertEquals($to, $emailLogs[0]['email_to']);
        $this->assertTrue($emailLogs[0]['status']);
    }
}
