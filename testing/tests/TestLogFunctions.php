<?php

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\ExpiredLogManager;
use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Mail;
use WpMailCatcher\Models\Settings;
use WpMailCatcher\MailAdminTable;

class TestLogFunctions extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Logs::truncate();
    }

    public function testCanDeleteSingleLog()
    {
        wp_mail('test@test.com', 'subject', 'message');

        $mail = Logs::get([
            'posts_per_page' => 1
        ]);

        $this->assertEquals(count($mail), 1);

        Logs::delete([
            $mail[0]['id']
        ]);

        $mail = Logs::get([
            'post__in' => $mail[0]['id']
        ]);

        $this->assertEquals(count($mail), 0);
    }

    public function testCanDeleteMultipleLogs()
    {
        wp_mail('test@test.com', 'subject', 'message');
        wp_mail('test@test.com', 'subject', 'message');

        $mail = Logs::get([
            'posts_per_page' => 2
        ]);

        $this->assertEquals(count($mail), 2);

        Logs::delete([
            $mail[0]['id'],
            $mail[1]['id']
        ]);

        $mail = Logs::get([
            'post__in' => [
                $mail[0]['id'],
                $mail[1]['id']
            ]
        ]);

        $this->assertEquals(count($mail), 0);
    }

    public function testCanResendSingleMail()
    {
        wp_mail('test@test.com', 'RESEND ME', 'message');

        $mail = Logs::get([
            'subject' => 'RESEND ME'
        ]);

        $this->assertEquals(count($mail), 1);

        Mail::resend([
            $mail[0]['id']
        ]);

        $mail = Logs::get([
            'subject' => 'RESEND ME'
        ]);

        $this->assertEquals(count($mail), 2);
    }

    public function testCanResendMultipleMail()
    {
        wp_mail('test@test.com', 'RESEND ME 1', 'message');
        wp_mail('test@test.com', 'RESEND ME 2', 'message');

        $mail = Logs::get([
            'subject' => 'RESEND ME'
        ]);

        $this->assertEquals(count($mail), 2);

        Mail::resend([
            $mail[0]['id'],
            $mail[1]['id']
        ]);

        $mail = Logs::get([
            'subject' => 'RESEND ME'
        ]);

        $this->assertEquals(count($mail), 4);
    }

    public function testOrderLogsByTime()
    {
        $oldestSubject = 'I am the oldest';
        $mostRecentSubject = 'I am the most recent';

        wp_mail('test@test.com', $oldestSubject, 'message');
        sleep(1);
        wp_mail('test@test.com', $mostRecentSubject, 'message');

        $log = Logs::getFirst([
            'orderby' => 'time',
            'order' => 'DESC'
        ]);

        $this->assertEquals($mostRecentSubject, $log['subject']);

        $log = Logs::getFirst([
            'orderby' => 'time',
            'order' => 'ASC'
        ]);

        $this->assertEquals($oldestSubject, $log['subject']);
    }

    public function testOrderLogsBySubject()
    {
        $firstSubject = 'abc';
        $secondSubject = 'def';

        wp_mail('test@test.com', $firstSubject, 'message');
        wp_mail('test@test.com', $secondSubject, 'message');

        $log = Logs::getFirst([
            'orderby' => 'subject',
            'order' => 'ASC'
        ]);

        $this->assertEquals($firstSubject, $log['subject']);

        $log = Logs::getFirst([
            'orderby' => 'subject',
            'order' => 'DESC'
        ]);

        $this->assertEquals($secondSubject, $log['subject']);
    }

    public function testCanExportSingleLog()
    {
        $to = 'test@test.com';
        $subject = 'subject';
        $message = 'message';

        $additionalHeaders = ['Content-type: text/html', 'cc: test1@test.com'];
        $imgAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/img-attachment.png');
        $pdfAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/pdf-attachment.pdf');

        wp_mail($to, $subject, $message, $additionalHeaders, [
            get_attached_file($imgAttachmentId),
            get_attached_file($pdfAttachmentId)
        ]);

        $lastEmail = Logs::get([
            'posts_per_page' => 1
        ]);

        $csvString = $export = Mail::export([
            $lastEmail[0]['id'],
        ], false);

        $csvArray = explode(',', $csvString);

        array_walk($csvArray, function (&$element) {
            $element = str_replace('"', '', $element);
            $element = str_replace(["\r", "\n", '"'], '', $element);
        });

        $this->assertContains($to, $csvArray);
        $this->assertContains($subject, $csvArray);
        $this->assertContains($additionalHeaders[0] . GeneralHelper::$csvItemDelimiter . $additionalHeaders[1], $csvArray);
        $this->assertContains(wp_get_attachment_url($imgAttachmentId) . GeneralHelper::$csvItemDelimiter . wp_get_attachment_url($pdfAttachmentId), $csvArray);

        wp_delete_attachment($imgAttachmentId);
        wp_delete_attachment($pdfAttachmentId);
    }

    public function testCanExportMultipleLogs()
    {
        $to1 = 'test@test.com';
        $subject1 = 'subject';
        $message1 = 'message';

        $to2 = 'test2@test.com';
        $subject2 = 'subject 2';
        $message2 = 'message 2';

        $additionalHeaders = ['Content-type: text/html', 'cc: test1@test.com'];
        $imgAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/img-attachment.png');
        $pdfAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/pdf-attachment.pdf');

        wp_mail($to1, $subject1, $message1, $additionalHeaders, [
            get_attached_file($imgAttachmentId),
            get_attached_file($pdfAttachmentId)
        ]);

        wp_mail($to2, $subject2, $message2, $additionalHeaders, [
            get_attached_file($imgAttachmentId),
            get_attached_file($pdfAttachmentId)
        ]);

        $lastEmail = Logs::get([
            'posts_per_page' => 2
        ]);

        $csvString = Mail::export([
            $lastEmail[0]['id'],
            $lastEmail[1]['id']
        ], false);

        $csvArray = explode(',', $csvString);

        array_walk($csvArray, function (&$element) {
            $element = str_replace('"', '', $element);
            $element = str_replace(["\r", "\n", '"'], '', $element);
        });

        $this->assertContains($to1, $csvArray);
        $this->assertContains($subject1, $csvArray);
        $this->assertContains($message1, $csvArray);
        $this->assertContains($to2, $csvArray);
        $this->assertContains($subject2, $csvArray);
        $this->assertContains($message2, $csvArray);
        $this->assertContains($additionalHeaders[0] . GeneralHelper::$csvItemDelimiter . $additionalHeaders[1], $csvArray);
        $this->assertContains(wp_get_attachment_url($imgAttachmentId) . GeneralHelper::$csvItemDelimiter . wp_get_attachment_url($pdfAttachmentId), $csvArray);

        wp_delete_attachment($imgAttachmentId);
        wp_delete_attachment($pdfAttachmentId);
    }

    public function testCanExportLogsInBatches()
    {
        $numberOfBatches = 2;
        $totalNumberOfLogs = 10;
        $logsPerBatch = $totalNumberOfLogs / $numberOfBatches;
        $imgAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/img-attachment.png');
        $pdfAttachmentId = $this->factory()->attachment->create_upload_object(__DIR__ . '/../assets/pdf-attachment.pdf');

        for ($i = 0; $i < $totalNumberOfLogs; $i++) {
            wp_mail(
                'test' . $i . '@test.com',
                'subject ' . $i,
                'message ' . $i,
                ['Content-type: text/html', 'cc: test' . ($i + 1) . '@test.com'],
                [
                    get_attached_file($imgAttachmentId),
                    get_attached_file($pdfAttachmentId)
                ]
            );
        }


        for ($i = 0; $i < $numberOfBatches; $i++) {
            $batch = wp_list_pluck(Logs::get([
                'posts_per_page' => $logsPerBatch,
                'paged' => ($i + 1),
            ]), 'id');

            $csvString = Mail::export($batch, false);

            // Split each csv line into an array
            $csvArrays = explode(PHP_EOL, $csvString);

            // Remove csv headings
            array_shift($csvArrays);

            // Concat csv arrays into a single array
            $csvArray = explode(",", implode(",", $csvArrays));

            // Format values ready for assertions
            array_walk($csvArray, function (&$element) {
                $element = str_replace('"', '', $element);
                $element = str_replace(["\r", "\n", '"'], '', $element);
            });

            for ($j = ($i * $logsPerBatch); $j < ((($i + 1) * $logsPerBatch) - 1); $j++) {
                $this->assertContains('test' . $j . '@test.com', $csvArray);
                $this->assertContains('subject ' . $j, $csvArray);
                $this->assertContains('message ' . $j, $csvArray);
                $this->assertContains(
                    'Content-type: text/html' . GeneralHelper::$csvItemDelimiter . 'cc: test' . ($j + 1) . '@test.com',
                    $csvArray
                );
                $this->assertContains(
                    wp_get_attachment_url($imgAttachmentId) . GeneralHelper::$csvItemDelimiter . wp_get_attachment_url($pdfAttachmentId),
                    $csvArray
                );
            }
        }

        wp_delete_attachment($imgAttachmentId);
        wp_delete_attachment($pdfAttachmentId);
    }

    public function testCanSearchByEmail()
    {
        $email = 'look_for_me_email@test.com';
        wp_mail('dont_find_me@test.com', 'subject', 'message');
        wp_mail($email, 'subject', 'message');

        $emailLogAddress = Logs::getFirst(['s' => $email]);

        $this->assertEquals($email, $emailLogAddress['email_to']);
    }

    public function testCanSearchBySubject()
    {
        $subject = 'look_for_me_subject';
        wp_mail('test@test.com', 'dont find me', 'message');
        wp_mail('test@test.com', $subject, 'message');

        $emailLogSubject = Logs::getFirst(['s' => $subject]);

        $this->assertEquals($subject, $emailLogSubject['subject']);
    }

    public function testCanSearchByMessage()
    {
        $message = 'look_for_me_message';
        wp_mail('test@test.com', 'subject', 'dont find me');
        wp_mail('test@test.com', 'subject', $message);

        $emailLogMessage = Logs::getFirst(['s' => $message]);

        $this->assertEquals($message, $emailLogMessage['message']);
    }

    public function testMailSuccessActionTriggered()
    {
        $message = 'hello world';
        $actionWasCalled = false;

        $func = function ($log) use ($message, &$actionWasCalled) {
            $this->assertEquals($message, $log['message']);
            $this->assertTrue((bool)$log['status']);
            $actionWasCalled = true;
        };

        add_action(GeneralHelper::$actionNameSpace . '_mail_success', $func);
        wp_mail('test@test.com', 'subject', $message);

        $this->assertTrue($actionWasCalled);
        remove_action(GeneralHelper::$actionNameSpace . '_mail_success', $func);
    }

    public function testMailFailedActionTriggered()
    {
        $message = 'hello world';
        $actionWasCalled = false;
        $func = function ($log) use ($message, &$actionWasCalled) {
            $this->assertEquals($message, $log['message']);
            $this->assertFalse((bool)$log['status']);
            $actionWasCalled = true;
        };

        add_action(GeneralHelper::$actionNameSpace . '_mail_failed', $func);

        wp_mail('', 'subject', $message);
        $this->assertTrue($actionWasCalled);
        remove_action(GeneralHelper::$actionNameSpace . '_mail_failed', $func);
    }

    public function testCanAddTimeIntervalViaFilter()
    {
        $filterName = GeneralHelper::$actionNameSpace . '_deletion_intervals';
        $newDeletionInterval = [
            'test' => 123
        ];

        $func = function ($deletionIntervals) use ($newDeletionInterval) {
            return array_merge($deletionIntervals, $newDeletionInterval);
        };

        add_filter($filterName, $func);

        $this->assertEquals(
            ExpiredLogManager::deletionIntervals(),
            array_merge(Settings::$defaultDeletionIntervals, $newDeletionInterval)
        );

        remove_filter($filterName, $func);
    }

    public function testAllExpiredMailAreDeleted()
    {
        $noOfExpiredMailToSend = 10;

        for ($i = 0; $i < $noOfExpiredMailToSend; $i++) {
            wp_mail('test@test.com', 'Old message - should be deleted', 'My message');
        }

        sleep(1);
        wp_mail('test@test.com', 'New message', 'My message');

        $logs = Logs::get();
        $this->assertEquals($noOfExpiredMailToSend + 1, count($logs));

        ExpiredLogManager::removeExpiredLogs(1);

        $logs = Logs::get();
        $this->assertEquals(1, count($logs));
        $this->assertEquals('New message', $logs[0]['subject']);
    }

    public function testDoesUnevenHeaderKeysAndValuesCorrectItself()
    {
        Mail::add(
            ['to', GeneralHelper::$htmlEmailHeader, 'foo: bar'],
            [''],
            [],
            '',
            ''
        );

        $logs = Logs::get();
        $this->assertTrue($logs[0]['is_html']);
        $this->assertEquals(GeneralHelper::$htmlEmailHeader, $logs[0]['additional_headers'][0]);
        $this->assertEquals('foo: bar', $logs[0]['additional_headers'][1]);
    }

    public function testCanNotReturnDbColumnsViaBlacklist()
    {
        wp_mail('test@test.com', 'New message', 'My message');

        $log = Logs::get(['column_blacklist' => ['message', 'is_html', 'additional_headers']])[0];

        $this->assertFalse(isset($log['message']));
        $this->assertFalse(isset($log['is_html']));
        $this->assertFalse(isset($log['additional_headers']));
    }

    public function testCanDecodeAsciBase64SubjectLine()
    {
        $mailTable = MailAdminTable::getInstance();
        $expectedOutput = '<span class="asci-help" data-hover-message="This subject was base64 decoded">
                               <a href="https://ncona.com/2011/06/using-utf-8-characters-on-an-e-mail-subject/"
                                  target="_blank">(?)</a>
                               Subject with non ASCII ó¿¡á
                           </span>';

        $subject = 'Subject with non ASCII ó¿¡á';
        $encodedSubject = '=?utf-8?B?' . base64_encode($subject) . '?=';

        $decodedSubject = $mailTable->column_subject(['subject' => $encodedSubject]);

        $this->assertEquals(
            preg_replace('/\s+/', '', $decodedSubject),
            preg_replace('/\s+/', '', $expectedOutput)
        );
    }

    public function testCanDecodeAsciQuotedEncodedSubjectLine()
    {
        $mailTable = MailAdminTable::getInstance();
        $expectedOutput = '<span class="asci-help" data-hover-message="This subject was quoted printable decoded">
                               <a href="https://ncona.com/2011/06/using-utf-8-characters-on-an-e-mail-subject/"
                                  target="_blank">(?)</a>
                               Subject with non ASCII ó¿¡á
                           </span>';

        $subject = 'Subject with non ASCII ó¿¡á';
        $encodedSubject = '=?utf-8?Q?' . quoted_printable_decode(base64_encode($subject)) . '?=';

        $decodedSubject = $mailTable->column_subject(['subject' => $encodedSubject]);

        $this->assertEquals(
            preg_replace('/\s+/', '', $decodedSubject),
            preg_replace('/\s+/', '', $expectedOutput)
        );
    }

    public function testCanAlterSuccessfulLogBeforeSavingViaFilter()
    {
        $beforeTo = 'before@test.com';
        $beforeSubject = 'Before subject';

        $afterTo = 'after@test.com';
        $afterSubject = 'After subject';
        $afterTime = 123;
        $afterBacktrace = 'Hello world';
        $afterMessage = 'My new message';

        $filterName = GeneralHelper::$actionNameSpace . '_before_success_log_save';

        $func = function ($log) use ($afterTo, $afterSubject, $afterMessage, $afterTime, $afterBacktrace) {
            $log['email_to'] = $afterTo;
            $log['subject'] = $afterSubject;
            $log['message'] = $afterMessage;
            $log['time'] = $afterTime;
            $log['backtrace_segment'] = $afterBacktrace;
            return $log;
        };

        add_filter($filterName, $func);

        wp_mail($beforeTo, $beforeSubject, 'Hello');

        $emailLog = Logs::getFirst();

        $this->assertEquals($afterTo, $emailLog['email_to']);
        $this->assertEquals($afterSubject, $emailLog['subject']);
        $this->assertEquals($afterMessage, $emailLog['message']);
        $this->assertEquals($afterTime, $emailLog['timestamp']);
        $this->assertEquals($afterBacktrace, $emailLog['backtrace_segment']);

        remove_filter($filterName, $func);
    }

    public function testCanStopSuccessfulLogFromSavingViaFilter()
    {
        $filterName = GeneralHelper::$actionNameSpace . '_before_success_log_save';

        $func = function ($log) {
            return false;
        };

        add_filter($filterName, $func);

        wp_mail('test@test.com', 'Subject', 'Hello');

        $emailLogs = Logs::get();
        $this->assertCount(0, $emailLogs);

        remove_filter($filterName, $func);
    }

    public function testCanAlterErroredLogBeforeSavingViaFilter()
    {
        // Use invalid email address to trigger an error
        $beforeTo = 'beforetest.com';
        $beforeSubject = 'Before subject';

        $afterTo = 'after@test.com';
        $afterSubject = 'After subject';
        $afterErrorMessage = 'Something went wrong';
        $afterMessage = 'My new message';
        $afterTime = 123;
        $afterBacktrace = 'Hello world';

        $filterName = GeneralHelper::$actionNameSpace . '_before_error_log_save';

        $func = function ($log) use ($afterTo, $afterSubject, $afterErrorMessage, $afterMessage, $afterTime, $afterBacktrace) {
            $log['email_to'] = $afterTo;
            $log['subject'] = $afterSubject;
            $log['message'] = $afterMessage;
            $log['error'] = $afterErrorMessage;
            $log['time'] = $afterTime;
            $log['backtrace_segment'] = $afterBacktrace;
            return $log;
        };

        add_filter($filterName, $func);

        wp_mail($beforeTo, $beforeSubject, 'Hello');

        $emailLog = Logs::getFirst();

        $this->assertEquals($afterTo, $emailLog['email_to']);
        $this->assertEquals($afterSubject, $emailLog['subject']);
        $this->assertEquals($afterErrorMessage, $emailLog['error']);
        $this->assertEquals($afterMessage, $emailLog['message']);
        $this->assertEquals($afterTime, $emailLog['timestamp']);
        $this->assertEquals($afterBacktrace, $emailLog['backtrace_segment']);

        remove_filter($filterName, $func);
    }

    public function testCanStopErroredLogFromSavingViaFilter()
    {
        $filterName = GeneralHelper::$actionNameSpace . '_before_error_log_save';

        $func = function ($log) {
            return false;
        };

        add_filter($filterName, $func);

        // Use invalid email address to trigger an error
        wp_mail('testtest.com', 'Subject', 'Hello');

        $emailLogs = Logs::get();
        $this->assertCount(0, $emailLogs);

        remove_filter($filterName, $func);
    }

    private function getFilterChainFunction(&$wasChainedCalled, $to, $subject, $message)
    {
        return function ($args) use (&$wasChainedCalled, $to, $subject, $message) {
            $wasChainedCalled = true;
            $this->assertEquals($to, $args['to']);
            $this->assertEquals($subject, $args['subject']);
            $this->assertEquals($message, $args['message']);
            return $args;
        };
    }

    /**
     * Other plugins hook into the `wp_mail` filter AFTER mail catcher, as such
     * they will rely on the values we return from our function hooked into `wp_mail`.
     * This test ensures that we correctly return the value so other plugins can make
     * use of it
     */
    public function testCanChainWpMailFiltersWhenLog()
    {
        $to = 'test@test.com';
        $subject = 'Subject';
        $message = '<strong>Hello</strong>';
        $wasChainedCalled = false;
        $successFilterName = GeneralHelper::$actionNameSpace . '_before_success_log_save';

        $func = function ($log) {
            return $log;
        };

        $chainedFunc = $this->getFilterChainFunction($wasChainedCalled, $to, $subject, $message);

        add_filter($successFilterName, $func);
        // Ensure our filter runs AFTER mail catcher
        add_filter('wp_mail', $chainedFunc, 9999999);

        wp_mail($to, $subject, $message);

        $this->assertTrue($wasChainedCalled);

        remove_filter($successFilterName, $func);
        remove_filter('wp_mail', $chainedFunc);
    }

    public function testCanChainWpMailFiltersWhenLogIsStopped()
    {
        $to = 'test@test.com';
        $subject = 'Subject';
        $message = '<strong>Hello</strong>';
        $wasChainedCalled = false;
        $successFilterName = GeneralHelper::$actionNameSpace . '_before_success_log_save';

        $func = function ($log) {
            return false;
        };

        $chainedFunc = $this->getFilterChainFunction($wasChainedCalled, $to, $subject, $message);

        add_filter($successFilterName, $func);
        // Ensure our filter runs AFTER mail catcher
        add_filter('wp_mail', $chainedFunc, 9999999);

        wp_mail($to, $subject, $message);

        $this->assertTrue($wasChainedCalled);

        remove_filter($successFilterName, $func);
        remove_filter('wp_mail', $chainedFunc);
    }

    public function testCanChainWpMailFiltersWhenLogIsErrored()
    {
        $to = 'testtest.com';
        $subject = 'Subject';
        $message = '<strong>Hello</strong>';
        $wasChainedCalled = false;
        $erroredFilterName = GeneralHelper::$actionNameSpace . '_before_error_log_save';

        $func = function ($log) {
            return false;
        };

        $chainedFunc = $this->getFilterChainFunction($wasChainedCalled, $to, $subject, $message);

        add_filter($erroredFilterName, $func);
        // Ensure our filter runs AFTER mail catcher
        add_filter('wp_mail', $chainedFunc, 9999999);

        wp_mail($to, $subject, $message);

        $this->assertTrue($wasChainedCalled);

        remove_filter($erroredFilterName, $func);
        remove_filter('wp_mail', $chainedFunc);
    }
}
