<?php

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Logs;

class TestEmails extends WP_UnitTestCase
{
    public function setUp(): void
    {
        Logs::truncate();
    }

    public function testMail()
    {
        $to = 'test@test.com';
        $subject = 'subject';
        $message = 'message';
        $additionalHeaders = [GeneralHelper::$htmlEmailHeader, 'cc: test1@test.com'];

        $imgAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/img-attachment.png');
        $pdfAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/pdf-attachment.pdf');

        wp_mail($to, $subject, $message, $additionalHeaders, [
            get_attached_file($imgAttachmentId),
            get_attached_file($pdfAttachmentId)
        ]);

        $emailLogs = Logs::get();

        $this->assertCount(1, $emailLogs);
        $this->assertEquals($to, $emailLogs[0]['email_to']);
        $this->assertEquals($subject, $emailLogs[0]['subject']);
        $this->assertEquals($message, $emailLogs[0]['message']);
        $this->assertTrue($emailLogs[0]['is_html']);

        $this->assertEquals($additionalHeaders[0], $emailLogs[0]['additional_headers'][0]);
        $this->assertEquals($additionalHeaders[1], $emailLogs[0]['additional_headers'][1]);

        $this->assertEquals($imgAttachmentId, $emailLogs[0]['attachments'][0]['id']);
        $this->assertEquals(wp_get_attachment_url($imgAttachmentId), $emailLogs[0]['attachments'][0]['url']);

        $this->assertEquals($pdfAttachmentId, $emailLogs[0]['attachments'][1]['id']);
        $this->assertEquals(wp_get_attachment_url($pdfAttachmentId), $emailLogs[0]['attachments'][1]['url']);

        wp_delete_attachment($imgAttachmentId);
        wp_delete_attachment($pdfAttachmentId);
    }

    public function testCorrectTos()
    {
        wp_mail('test@test.com', 'subject', 'message');
        $this->assertTrue(Logs::getFirst()['status']);
    }

    public function testIncorrectTos()
    {
        wp_mail('testtest.com', 'subject', 'message');
        $this->assertFalse(Logs::getFirst()['status']);
    }

    public function testHtmlEmailSetViaFilter()
    {
        $contentTypeFilterPriority = 999;
        $updateContentType = function () {
            return 'text/html';
        };

        add_filter('wp_mail_content_type', $updateContentType, $contentTypeFilterPriority);

        // Send an email without explicitly setting the html header
        wp_mail('test@test.com', 'subject', 'message');

        remove_filter('wp_mail_content_type', $updateContentType, $contentTypeFilterPriority);

        $this->assertTrue(Logs::get()[0]['is_html']);
    }

    public function testHtmlEmail()
    {
        // Test various formats
        wp_mail('test@test.com', 'subject', 'message', [GeneralHelper::$htmlEmailHeader]);
        wp_mail('test@test.com', 'subject', 'message', ['content-type:text/html']);
        wp_mail('test@test.com', 'subject', 'message', ['Content-Type: text/html']);
        wp_mail('test@test.com', 'subject', 'message', ['Content-Type: text/html;']);

        $this->assertTrue(Logs::get()[0]['is_html']);
        $this->assertTrue(Logs::get()[1]['is_html']);
        $this->assertTrue(Logs::get()[2]['is_html']);
        $this->assertTrue(Logs::get()[3]['is_html']);
    }

    public function testNonHtmlEmail()
    {
        wp_mail('test@test.com', 'subject', 'message');
        $this->assertFalse(Logs::get()[0]['is_html']);
    }

    public function testWpFiltersWithMailCatcherAreUnchanged()
    {
        $originalTo = 'test@test.com';
        $originalSubject = 'subject';
        $originalMessage = 'message';
        $originalContentType = 'multipart/alternative';
        $originalAdditionalHeaders = ['content-type: ' . $originalContentType, 'cc: test1@test.com'];

        $isWpMailFilterCalled = false;
        $isWpMailContentFilterCalled = false;

        $wpMailFilter = function ($args) use (&$isWpMailFilterCalled, $originalTo, $originalSubject, $originalMessage, $originalAdditionalHeaders) {
            $isWpMailFilterCalled = true;

            $this->assertEquals($args['to'], $originalTo);
            $this->assertEquals($args['subject'], $originalSubject);
            $this->assertEquals($args['message'], $originalMessage);

            for ($i = 0; $i < count($args['headers']); $i++) {
                $this->assertEquals($args['headers'][$i], $originalAdditionalHeaders[$i]);
            }

            return $args;
        };

        $wpMailContentFilter = function ($contentType) use (&$isWpMailContentFilterCalled, $originalContentType) {
            $isWpMailContentFilterCalled = true;
            $this->assertEquals($contentType, $originalContentType);
            return $originalContentType;
        };

        // Add filters
        add_filter('wp_mail', $wpMailFilter);
        add_filter('wp_mail', $wpMailFilter, 9999991);
        add_filter('wp_mail_content_type', $wpMailContentFilter);
        add_filter('wp_mail_content_type', $wpMailContentFilter, 9999991);

        // Send message
        wp_mail($originalTo, $originalSubject, $originalMessage, $originalAdditionalHeaders);

        // Assert filters were called
        $this->assertTrue($isWpMailFilterCalled);
        $this->assertTrue($isWpMailContentFilterCalled);

        $emailLogs = Logs::get();

        // Assert email was logged correctly
        $this->assertCount(1, $emailLogs);
        $this->assertEquals($originalTo, $emailLogs[0]['email_to']);
        $this->assertEquals($originalSubject, $emailLogs[0]['subject']);
        $this->assertEquals($originalMessage, $emailLogs[0]['message']);
        $this->assertFalse($emailLogs[0]['is_html']);

        $this->assertEquals($originalAdditionalHeaders[0], $emailLogs[0]['additional_headers'][0]);
        $this->assertEquals($originalAdditionalHeaders[1], $emailLogs[0]['additional_headers'][1]);

        // Tidy up
        remove_filter('wp_mail', $wpMailFilter);
        remove_filter('wp_mail', $wpMailFilter, 9999991);
        remove_filter('wp_mail_content_type', $wpMailContentFilter);
        remove_filter('wp_mail_content_type', $wpMailContentFilter, 9999991);
    }

    public function testSpecialCharHtmlEmailCanStillBeViewed()
    {
        $htmlMessage = '<strong>Hello <a href="https://example.com" target="_blank">world</a></strong>';
        wp_mail('test@test.com', 'html encoded', htmlspecialchars($htmlMessage));

        $log = Logs::getFirst([
            'subject' => 'html encoded'
        ]);

        ob_start();
        require __DIR__ . '/../../src/Views/HtmlMessage.php';
        $actualMessage = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($htmlMessage, $actualMessage);
    }
}
