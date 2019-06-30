<?php

use WpMailCatcher\GeneralHelper;

class TestMultiSite extends WP_UnitTestCase
{
    private $sites;

	public function setup()
	{
	    parent::setup();

	    $this->sites = [
            self::factory()->blog->create(),
            self::factory()->blog->create()
        ];
	}

	public function testRemovingSiteDropsTable()
    {
        global $wpdb;

        $tableName = $wpdb->prefix . GeneralHelper::$tableName;
        $this->sites = wp_get_sites();

        switch_to_blog($this->sites[0]);
        $this->assertTrue($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") == $tableName);

        wpmu_delete_blog($this->sites[0], true);
        $this->assertFalse($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") == $tableName);

        switch_to_blog($this->sites[1]);
        $this->assertTrue($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") == $tableName);
    }
}
