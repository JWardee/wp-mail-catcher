<?php

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Settings;

class TestRedaction extends WP_UnitTestCase
{
    private $resetUrl = 'https://example.com/wp-login.php?action=rp&key=abc123&login=admin';
    private $resetKey = 'abc123';
    private $redactedText = '[redacted]';

    public function setUp(): void
    {
        parent::setUp();
        Logs::truncate();

        // Reset to the default settings, keeping db_version so is_html is still saved/read
        $dbVersion = Settings::get('db_version', true);
        Settings::installOptions(true);
        Settings::update(['db_version' => $dbVersion]);
    }

    private function filterName(): string
    {
        return GeneralHelper::$actionNameSpace . '_redact';
    }

    private function addSecretFilter()
    {
        // Hooks are restored by WP_UnitTestCase::tearDown so there is no need to remove this
        add_filter($this->filterName(), function ($content) {
            return str_replace('secret', '***', $content);
        });
    }

    // Read straight from the db, bypassing Logs::get, to prove what was actually stored
    private function getRawLog(): array
    {
        global $wpdb;

        return $wpdb->get_row(
            "SELECT subject, message FROM " . $wpdb->prefix . GeneralHelper::$tableName . " ORDER BY id DESC LIMIT 1",
            ARRAY_A
        );
    }

    // Simulates a log that was saved before redaction existed
    private function insertRawLog($subject, $message, $isHtml = false)
    {
        global $wpdb;

        $wpdb->insert($wpdb->prefix . GeneralHelper::$tableName, [
            'time' => time(),
            'email_to' => 'test@test.com',
            'subject' => $subject,
            'message' => $message,
            'status' => 1,
            'error' => '',
            'backtrace_segment' => 'null',
            'attachments' => '[]',
            'additional_headers' => '[]',
            'is_html' => $isHtml,
        ]);
    }

    private function getLog()
    {
        return Logs::getFirst(['ignore_cache' => true]);
    }

    private function renderView($view, $log): string
    {
        ob_start();
        require GeneralHelper::$pluginViewDirectory . '/' . $view;
        return ob_get_clean();
    }

    private function renderSettingsPage(): string
    {
        wp_roles();
        ob_start();
        require GeneralHelper::$pluginViewDirectory . '/Settings.php';
        return ob_get_clean();
    }

    private function redactionCheckboxPattern($checked): string
    {
        $checkedPattern = $checked ? '[^>]*checked' : '(?![^>]*checked)[^>]*';
        return '/<input[^>]*name="redact_sensitive_urls"' . $checkedPattern . '[^>]*>/';
    }

    /**
     * Custom redaction filter
     */
    public function testRedactFilterIsAppliedToMessageBeforeSave()
    {
        $this->addSecretFilter();

        wp_mail('test@test.com', 'Subject', 'My secret message');

        $this->assertEquals('My *** message', $this->getRawLog()['message']);
    }

    public function testRedactFilterIsAppliedToSubjectBeforeSave()
    {
        $this->addSecretFilter();

        wp_mail('test@test.com', 'My secret subject', 'Message');

        $this->assertEquals('My *** subject', $this->getRawLog()['subject']);
    }

    public function testRedactFilterReceivesFieldName()
    {
        $fields = [];

        add_filter($this->filterName(), function ($content, $field) use (&$fields) {
            $fields[] = $field;
            return $content;
        }, 10, 2);

        wp_mail('test@test.com', 'Subject', 'Message');

        $this->assertContains('subject', $fields);
        $this->assertContains('message', $fields);
    }

    public function testRedactFilterIsAppliedBeforeRender()
    {
        $this->insertRawLog('My secret subject', 'My secret message');
        $this->addSecretFilter();

        $log = $this->getLog();

        $this->assertEquals('My *** subject', $log['subject']);
        $this->assertEquals('My *** message', $log['message']);
    }

    public function testRedactFilterIsAppliedInMessageView()
    {
        $this->insertRawLog('Subject', '<p>My secret message</p>', true);
        $this->addSecretFilter();

        $log = $this->getLog();

        foreach (['HtmlMessage.php', 'TextMessage.php'] as $view) {
            $html = $this->renderView($view, $log);

            $this->assertStringNotContainsString('secret', $html, "Unredacted content in {$view}");
            $this->assertStringContainsString('***', $html, "Redacted content missing from {$view}");
        }
    }

    public function testCustomFilterStillAppliesWhenDefaultDisabled()
    {
        Settings::update(['redact_sensitive_urls' => false]);
        $this->addSecretFilter();

        wp_mail('test@test.com', 'Subject', 'My secret message');

        $this->assertEquals('My *** message', $this->getRawLog()['message']);
        $this->assertEquals('My *** message', $this->getLog()['message']);
    }

    /**
     * Default sensitive URL redaction
     */
    public function testRedactSensitiveUrlsIsEnabledByDefault()
    {
        $this->assertArrayHasKey('redact_sensitive_urls', Settings::$defaultSettings);
        $this->assertTrue(Settings::$defaultSettings['redact_sensitive_urls']);
        $this->assertTrue((bool)Settings::get('redact_sensitive_urls', true));
    }

    public function testDefaultRedactsPasswordResetUrl()
    {
        wp_mail('test@test.com', 'Password reset', "Reset your password: {$this->resetUrl}");

        $message = $this->getRawLog()['message'];

        $this->assertStringNotContainsString($this->resetKey, $message);
        $this->assertStringNotContainsString('action=rp', $message);
        $this->assertStringContainsString($this->redactedText, $message);
        $this->assertStringContainsString('Reset your password:', $message);
    }

    public function testDefaultRedactsUrlContainingKeyParam()
    {
        wp_mail('test@test.com', 'Subject', 'Confirm here https://example.com/?foo=bar&key=xyz789 thanks');

        $message = $this->getRawLog()['message'];

        $this->assertStringNotContainsString('xyz789', $message);
        $this->assertStringContainsString($this->redactedText, $message);
        $this->assertStringContainsString('Confirm here', $message);
        $this->assertStringContainsString('thanks', $message);
    }

    public function testDefaultRedactsUrlInsideHref()
    {
        $message = '<p>Click <a href="' . $this->resetUrl . '">Reset</a></p>';

        wp_mail('test@test.com', 'Subject', $message, [GeneralHelper::$htmlEmailHeader]);

        $this->assertStringNotContainsString($this->resetKey, $this->getRawLog()['message']);

        $html = $this->renderView('HtmlMessage.php', $this->getLog());

        $this->assertStringNotContainsString($this->resetKey, $html);
        $this->assertStringContainsString('Reset', $html);
    }

    public function testDefaultLeavesOtherUrlsAndTextUntouched()
    {
        $message = 'Read more at https://example.com/?page=2 today';

        wp_mail('test@test.com', 'Subject', $message);

        $this->assertEquals($message, $this->getRawLog()['message']);
        $this->assertEquals($message, $this->getLog()['message']);
    }

    public function testDefaultRedactsExistingLogsOnRender()
    {
        $this->insertRawLog('Password reset', "Reset your password: {$this->resetUrl}");

        $message = $this->getLog()['message'];

        $this->assertStringNotContainsString($this->resetKey, $message);
        $this->assertStringContainsString($this->redactedText, $message);
    }

    public function testDefaultRedactionCanBeDisabled()
    {
        Settings::update(['redact_sensitive_urls' => false]);

        wp_mail('test@test.com', 'Password reset', "Reset your password: {$this->resetUrl}");

        $this->assertStringContainsString($this->resetKey, $this->getRawLog()['message']);
        $this->assertStringContainsString($this->resetKey, $this->getLog()['message']);
    }

    public function testRealPasswordResetEmailIsRedacted()
    {
        $user = $this->factory()->user->create_and_get(['user_login' => 'redaction_test_user']);
        $resetKey = null;

        add_action('retrieve_password_key', function ($userLogin, $key) use (&$resetKey) {
            $resetKey = $key;
        }, 10, 2);

        $this->assertTrue(retrieve_password($user->user_login));
        $this->assertNotEmpty($resetKey);

        $this->assertStringNotContainsString($resetKey, $this->getRawLog()['message']);

        $message = $this->getLog()['message'];

        $this->assertStringNotContainsString($resetKey, $message);
        $this->assertStringContainsString($this->redactedText, $message);
    }

    /**
     * Settings page
     */
    public function testSettingsPageShowsRedactionCheckboxCheckedByDefault()
    {
        $this->assertMatchesRegularExpression($this->redactionCheckboxPattern(true), $this->renderSettingsPage());
    }

    public function testSettingsPageShowsUncheckedWhenDisabled()
    {
        Settings::update(['redact_sensitive_urls' => false]);

        $this->assertMatchesRegularExpression($this->redactionCheckboxPattern(false), $this->renderSettingsPage());
    }
}
