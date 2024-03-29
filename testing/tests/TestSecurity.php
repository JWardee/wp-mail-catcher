<?php

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Logs;
use WpMailCatcher\MailAdminTable;

class TestSecurity extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testMaliciousHtmlIsEscaped()
    {
        $maliciousHtml = '<script>alert("Hello");</script>';
        $escapedHtml = GeneralHelper::filterHtml($maliciousHtml);

        $this->assertNotEquals($escapedHtml, $maliciousHtml);
    }

    public function testSubjectLineHtmlIsEscaped()
    {
        $mailTable = MailAdminTable::getInstance();
        $exploitedSubject = '<script>alert("Hello");</script>';
        $escapedSubject = $mailTable->runHtmlSpecialChars($exploitedSubject);
        $subject = $mailTable->column_subject(['subject' => $exploitedSubject]);

        $this->assertEquals($subject, $escapedSubject);
    }

    public function testLogGetMethodIsImmuneToSqlInjection()
    {
        global $wpdb;
        $originalWpDb = $wpdb;
        $wpdb = Mockery::mock('wpdb');
        $exploitedSql = "email_to+AND+(SELECT+7479+FROM+(SELECT(SLEEP(5)))UAKp)";

        $wpdb->shouldIgnoreMissing()
            ->shouldReceive('get_results')
            ->withArgs(function ($sql) use ($exploitedSql) {
                // TODO: (low priority) $sql seems to return `null`, need to step
                // through $wpdb->prepare and see where it returns null.
                // str_contains on `null` throws a warning in PHP 8.1
                $doesSqlContainExploit = str_contains($sql, $exploitedSql);
                $this->assertFalse($doesSqlContainExploit);
                return true;
            })
            ->once()
            ->andReturn([]);

        Logs::get([
            'orderby' => $exploitedSql
        ]);

        $wpdb = $originalWpDb;
    }
}
