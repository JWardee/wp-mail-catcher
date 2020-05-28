<?php

use WpMailCatcher\Bootstrap;
use WpMailCatcher\CronManager;
use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Settings;

class TestSettings extends WP_UnitTestCase
{
	private $timescale = 'weekly';
	private $cronManager;

	public function __construct($name = null, $data = [], $dataName = '')
	{
		$this->cronManager = CronManager::getInstance();
		parent::__construct($name, $data, $dataName);
	}

	public function setUp()
	{
		Settings::installOptions();
		$this->cronManager->clearTasks();
	}

	public function testCronEnable()
	{
		Settings::update(['auto_delete' => true, 'timescale' => $this->timescale]);
		new Bootstrap();

		$cronTasks = $this->cronManager->getTasks();

		$this->assertEquals('1', count($cronTasks));
		$this->assertEquals($this->timescale, $cronTasks[0]['schedule']);

		/**
         * Assert 1 instead of 0 because the new default setting for
         * auto_delete is true so the next hook will start at 1 not 0
         */
		$this->assertEquals(GeneralHelper::$namespacePrefix . '1', $cronTasks[0]['hook']);
	}

	public function testCronDisable()
	{
		Settings::update(['auto_delete' => false]);
		new Bootstrap();

		$cronTasks = $this->cronManager->getTasks();
		$this->assertEquals('0', count($cronTasks));
	}

	public function testDefaultSettingsSerialization()
    {
        // Update the settings to have values missing
        Settings::update(['auto_delete' => false]);

        $settings = Settings::get();

        // Assert auto_delete was saved correctly
        $this->assertEquals(false, $settings['auto_delete']);

        // Assert that despite the default_view_role missing
        // from the DB the default value is still used
        $this->assertEquals('manage_options', $settings['default_view_role']);
    }
}
