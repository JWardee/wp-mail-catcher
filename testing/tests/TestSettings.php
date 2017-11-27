<?php

use WpMailCatcher\Bootstrap;
use WpMailCatcher\CronManager;
use WpMailCatcher\Models\Settings;

class TestSettings extends WP_UnitTestCase
{
	private $timescale = 'weekly';

	public function testCronEnable()
	{
		Settings::installOptions();
		$cronManager = CronManager::getInstance();
		$cronManager->clearTasks();

		Settings::update(['auto_delete' => true, 'timescale' => $this->timescale]);
		new Bootstrap();

		$cronTasks = $cronManager->getTasks();

		$this->assertEquals('1', count($cronTasks));
		$this->assertEquals($this->timescale, $cronTasks[0]['schedule']);
		$this->assertEquals($cronManager->prefix . '0', $cronTasks[0]['hook']);
	}

	public function testCronDisable()
	{
		Settings::installOptions();
		$cronManager = CronManager::getInstance();
		$cronManager->clearTasks();

		Settings::update(['auto_delete' => false]);
		new Bootstrap();

		$cronManager = CronManager::getInstance();
		$cronTasks = $cronManager->getTasks();

		$this->assertEquals('0', count($cronTasks));
	}

	public function testCanViewLogs()
	{
		/** TODO: Write test */
		$this->markTestSkipped();
	}

	public function testCannotViewLogs()
	{
		/** TODO: Write test */
		$this->markTestSkipped();
	}

	public function testCanViewSettings()
	{
		/** TODO: Write test */
		$this->markTestSkipped();
	}

	public function testCannotViewSettings()
	{
		/** TODO: Write test */
		$this->markTestSkipped();
	}
}
