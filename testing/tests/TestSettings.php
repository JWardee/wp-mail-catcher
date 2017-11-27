<?php

use WpMailCatcher\Bootstrap;
use WpMailCatcher\CronManager;
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
		$this->assertEquals($this->cronManager->prefix . '0', $cronTasks[0]['hook']);
	}

	public function testCronDisable()
	{
		Settings::update(['auto_delete' => false]);
		new Bootstrap();

		$cronTasks = $this->cronManager->getTasks();
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
