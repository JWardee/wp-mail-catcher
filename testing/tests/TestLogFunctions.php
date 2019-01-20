<?php

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Mail;

class TestLogFunctions extends WP_UnitTestCase
{
	public function setUp()
	{
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

		$csvString = $export = Mail::export([
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
            wp_mail('test' . $i . '@test.com', 'subject ' . $i, 'message ' . $i, ['Content-type: text/html', 'cc: test' . ($i + 1) . '@test.com'], [
                get_attached_file($imgAttachmentId),
                get_attached_file($pdfAttachmentId)
            ]);
        }

        for ($i = 0; $i < $numberOfBatches; $i++) {
            $batch = wp_list_pluck(Logs::get([
                'posts_per_page' => $logsPerBatch,
                'paged' => ($i + 1),
            ]), 'id');

            $csvString = $export = Mail::export($batch, false);
            $csvArray = explode(',', $csvString);

            array_walk($csvArray, function (&$element) {
                $element = str_replace('"', '', $element);
                $element = str_replace(["\r", "\n", '"'], '', $element);
            });

            for ($j = ($i * $logsPerBatch); $j < ($i * $logsPerBatch); $j++) {
                $this->assertContains('test' . $j . '@test.com', $csvArray);
                $this->assertContains('subject ' . $j, $csvArray);
                $this->assertContains('message ' . $j, $csvArray);
                $this->assertContains('Content-type: text/html' . GeneralHelper::$csvItemDelimiter . 'cc: test' . ($j + 1) . '@test.com', $csvArray);
                $this->assertContains(wp_get_attachment_url($imgAttachmentId) . GeneralHelper::$csvItemDelimiter . wp_get_attachment_url($pdfAttachmentId), $csvArray);
            }
        }

        wp_delete_attachment($imgAttachmentId);
        wp_delete_attachment($pdfAttachmentId);
    }
}
