<?php

use WpMailCatcher\Models\Logs;

class TestEmailBatch extends WP_UnitTestCase
{
	public function testMail()
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

		$emailLog = Logs::get()[0];

		$this->assertEquals($emailLog['email_to'], $to);
		$this->assertEquals($emailLog['subject'], $subject);
		$this->assertEquals($emailLog['message'], $message);

		$this->assertEquals($emailLog['additional_headers'][0], $additionalHeaders[0]);
		$this->assertEquals($emailLog['additional_headers'][1], $additionalHeaders[1]);

		$this->assertEquals($emailLog['attachments'][0]['id'], $imgAttachmentId);
		$this->assertEquals($emailLog['attachments'][0]['url'], wp_get_attachment_url($imgAttachmentId));

		$this->assertEquals($emailLog['attachments'][1]['id'], $pdfAttachmentId);
		$this->assertEquals($emailLog['attachments'][1]['url'], wp_get_attachment_url($pdfAttachmentId));

		wp_delete_attachment($imgAttachmentId);
		wp_delete_attachment($pdfAttachmentId);

		Logs::truncate();
	}

	public function testCorrectTos()
	{
		wp_mail('test@test.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 1);
		Logs::truncate();
	}

	public function testIncorrectTos()
	{
		wp_mail('testtest.com', 'subject', 'message');
		$this->assertEquals(Logs::get()[0]['status'], 0);
		Logs::truncate();
	}
}
