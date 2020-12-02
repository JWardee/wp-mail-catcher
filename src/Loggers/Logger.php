<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;
use WP_Error;
use WpMailCatcher\Models\Cache;
use WpMailCatcher\Models\Logs;

abstract class Logger implements LoggerContract
{
    protected $id = null;

    /**
     * Register any filters and actions
     * that need to be called in order to log the outgoing mail
     */
    public function __construct()
    {
        add_action('wp_mail', [$this, 'recordMail'], 999999);
        add_action('wp_mail_failed', [$this, 'recordError'], 999999);
    }

    /**
     * Save the mail to the database, override this method if you wish
     * to save the data elsewhere or change how it is saved
     *
     * @param array $args the details of the mail going to be sent
     * @return mixed must return an array in the same format
     */
    public function recordMail($args)
    {
        global $wpdb;

        $wpdb->insert($wpdb->prefix . GeneralHelper::$tableName, $this->getMailArgs($args));

        Cache::flush();

        $this->id = $wpdb->insert_id;

        if (!isset($args['to']) || $args['to'] == null) {
            $args['to'] = [];
        }

        do_action(GeneralHelper::$actionNameSpace . '_mail_success', Logs::getFirst(['id' => $this->id]));

        return $args;
    }

    /**
     * Sending the mail has failed, record the error and update
     * the log to show it has failed
     *
     * @param WP_Error $error the WordPress error object
     */
    public function recordError(WP_Error $error)
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . GeneralHelper::$tableName, [
            'status' => 0,
            'error' => $error->errors['wp_mail_failed'][0],
        ], [
                'id' => $this->id
            ]
        );

        Cache::flush();

        do_action(GeneralHelper::$actionNameSpace . '_mail_failed', Logs::getFirst(['id' => $this->id]));
    }

    /**
     * Transform the incoming details of the mail into the
     * correct format for our log (data fractal)
     *
     * @param array $args the details of the mail going to be sent
     * @return array must return an array in the same format
     */
    protected function getMailArgs($args)
    {
        return [
            'time' => time(),
            'email_to' => GeneralHelper::arrayToString($args['to']),
            'subject' => $args['subject'],
            'message' => $this->sanitiseInput($args['message']),
            'backtrace_segment' => json_encode($this->getBacktrace()),
            'status' => 1,
            'attachments' => json_encode($this->getAttachmentLocations($args['attachments'])),
            'additional_headers' => json_encode($args['headers'])
        ];
    }


    /**
     * Convert attachment ids or urls into a format to be usable
     * by the logs
     *
     * @param array $attachments either array of attachment ids or their urls
     * @return array [id, url] of attachments
     */
    protected function getAttachmentLocations($attachments)
    {
        if (empty($attachments)) {
            return [];
        }

        if (is_string($attachments)) {
            $attachments = (array)$attachments;
        }

        $result = [];

        array_walk($attachments, function (&$value) {
            $value = str_replace(GeneralHelper::$uploadsFolderInfo['basedir'] . '/', '', $value);
        });

        if (isset($_POST['attachment_ids'])) {
            $attachmentIds = array_values(array_filter($_POST['attachment_ids']));
        } else {
            $attachmentIds = GeneralHelper::getAttachmentIdsFromUrl($attachments);

            if (empty($attachmentIds)) {
                return [
                    [
                        'id' => -1,
                    ]
                ];
            }
        }

        if (empty($attachmentIds)) {
            return [];
        }

        for ($i = 0; $i < count($attachments); $i++) {
            $result[] = [
                'id' => $attachmentIds[$i],
                'url' => GeneralHelper::$uploadsFolderInfo['url'] . $attachments[$i]
            ];
        }

        return $result;
    }

    protected function sanitiseInput($input)
    {
        return htmlspecialchars(
            preg_replace('#<script(.*?)>(.*?)</script>#is', '', $input)
        );
    }

    /**
     * Get the details of the method that originally triggered wp_mail
     *
     * @return array a single element of the debug_backtrace function
     */
    private function getBacktrace()
    {
        $backtraceSegment = null;
        $backtrace = debug_backtrace();

        foreach ($backtrace as $segment) {
            if ($segment['function'] == 'wp_mail') {
                $backtraceSegment = $segment;
            }
        }

        return $backtraceSegment;
    }
}
