<?php

use WpMailCatcher\DatabaseUpgradeService;

class TestDbUpgrade extends WP_UnitTestCase
{
    public function testMultipleUpgradesAreHandledCorrectly()
    {
        $wasV05UpgradeCalled = false;
        $wasV2UpgradeCalled = false;
        $wasV25UpgradeCalled = false;

        $dbUpgradeManager = new DatabaseUpgradeService([
            '0.5.0' => function () use (&$wasV05UpgradeCalled) {
                $wasV05UpgradeCalled = true;
            },
            '2.0.0' => function () use (&$wasV2UpgradeCalled) {
                $wasV2UpgradeCalled = true;
            },
            '2.5.0' => function () use (&$wasV25UpgradeCalled) {
                $wasV25UpgradeCalled = true;
            },
        ], '1.0.0');

        $dbUpgradeManager->doUpgrade();

        $this->assertFalse($wasV05UpgradeCalled);
        $this->assertTrue($wasV2UpgradeCalled);
        $this->assertTrue($wasV25UpgradeCalled);
    }

    public function testCanForceMigrationRerun()
    {
        $wasV15UpgradeCalled = false;
        $wasV2UpgradeCalled = false;

        $dbUpgradeManager = new DatabaseUpgradeService([
            '1.5.0' => function () use (&$wasV15UpgradeCalled) {
                $wasV15UpgradeCalled = true;
            },
            '2.0.0' => function () use (&$wasV2UpgradeCalled) {
                $wasV2UpgradeCalled = true;
            },
        ], '2.0.0');

        $dbUpgradeManager->doUpgrade();

        $this->assertFalse($wasV15UpgradeCalled);
        $this->assertFalse($wasV2UpgradeCalled);

        $dbUpgradeManager->doUpgrade(true);

        $this->assertTrue($wasV15UpgradeCalled);
        $this->assertTrue($wasV2UpgradeCalled);
    }
}
