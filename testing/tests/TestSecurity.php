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

    // Breaks out of an attribute as well as injecting tags, to cover both esc_attr and esc_html output
    private $exploitedError = '"><script>alert("Hello");</script><img src=x onerror=alert("Hello")>';

    public function testErrorHtmlIsStrippedWhenSaved()
    {
        Logs::truncate();

        // Runs before the plugin's own wp_mail_failed listener, so the payload is what gets saved
        $injectError = function (WP_Error $error) {
            $error->errors['wp_mail_failed'][0] = $this->exploitedError;
        };

        add_action('wp_mail_failed', $injectError, 10);

        // Use invalid email address to trigger an error
        wp_mail('testtest.com', 'Subject', 'Hello');

        remove_action('wp_mail_failed', $injectError, 10);

        $error = Logs::getFirst()['error'];

        $this->assertStringNotContainsString('<script', $error);
        $this->assertStringNotContainsString('<img', $error);
    }

    public function testErrorIsEscapedWhenDisplayed()
    {
        Logs::truncate();

        // The filter runs after the error is stripped, so its value reaches the db as-is
        // and output escaping is the only protection left
        $filterName = GeneralHelper::$actionNameSpace . '_before_error_log_save';
        $injectError = function ($log) {
            $log['error'] = $this->exploitedError;
            return $log;
        };

        add_filter($filterName, $injectError);

        // Use invalid email address to trigger an error
        wp_mail('testtest.com', 'Subject', 'Hello');

        remove_filter($filterName, $injectError);

        $wpMailCatcherLog = Logs::getFirst();

        $this->assertEquals($this->exploitedError, $wpMailCatcherLog['error']);

        $status = MailAdminTable::getInstance()->column_status($wpMailCatcherLog);

        ob_start();
        require GeneralHelper::$pluginViewDirectory . '/LogModal.php';
        $modal = ob_get_clean();

        foreach (['status column' => $status, 'log modal' => $modal] as $location => $html) {
            $this->assertStringNotContainsString('<script>', $html, "Unescaped script tag in the {$location}");
            $this->assertStringNotContainsString('<img', $html, "Unescaped img tag in the {$location}");
        }

        $this->assertStringContainsString('data-hover-message="' . esc_attr($this->exploitedError) . '"', $status);
        $this->assertStringContainsString(esc_html($this->exploitedError), $modal);
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
