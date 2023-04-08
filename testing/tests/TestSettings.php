<?php

use WpMailCatcher\Bootstrap;
use WpMailCatcher\CronManager;
use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Settings;

class TestSettings extends WP_UnitTestCase
{
	private $timescale = 'daily';
	private $cronManager;

	public function __construct($name = null, $data = [], $dataName = '')
	{
		$this->cronManager = CronManager::getInstance();
		parent::__construct($name, $data, $dataName);
	}

	public function setUp(): void
	{
		Settings::installOptions(true);
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
		// Simulate a third party invalidating the serialized object
		$invalidSerializationObj = 'a:1:{s:3:"foo";s:3:"bar";}';
		update_option(Settings::$optionsName, $invalidSerializationObj);

		// Assert that the default value is used if the value cannot be
		// found within the serialized object
		$this->assertEquals(Settings::$defaultSettings['auto_delete'], Settings::get(null, true)['auto_delete']);
		$this->assertEquals(Settings::$defaultSettings['auto_delete'], Settings::get('auto_delete', true));
	}
}
