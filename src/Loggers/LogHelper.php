<?php

namespace WpMailCatcher\Loggers;

use WpMailCatcher\GeneralHelper;
use WpMailCatcher\Models\Cache;
use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Settings;

trait LogHelper
{
    protected $id = null;

    /**
     * Save the mail to the database, override this method if you wish
     * to save the data elsewhere or change how it is saved
     *
     * @param  array  $args the details of the mail going to be sent
     * @param $transformFunc($args) called before inserting the db entry to transform the mail into log format
     *
     * @return array must return an array in the same format
     */
    public function saveMail(array $args, $transformFunc): array
    {
        global $wpdb;

        $wpdb->insert($wpdb->prefix . GeneralHelper::$tableName, $transformFunc($args));

        Cache::flush();

        $this->id = $wpdb->insert_id;

        if (!isset($args['to']) || $args['to'] == null) {
            $args['to'] = [];
        }

        do_action(GeneralHelper::$actionNameSpace . '_mail_success', Logs::getFirst(['post__in' => $this->id]));

        return $args;
    }

    /**
     * Sending the mail has failed, record the error and update
     * the log to show it has failed
     *
     * @param $error string of the error
     */
    public function saveError(string $error)
    {
        if ($this->id === null) {
            return;
        }

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . GeneralHelper::$tableName,
            [
                'status' => 0,
                'error' => $error,
            ],
            ['id' => $this->id]
        );

        Cache::flush();

        do_action(GeneralHelper::$actionNameSpace . '_mail_failed', Logs::getFirst(['post__in' => $this->id]));
    }

    public function saveIsHtml($contentType)
    {
        if ($this->id === null || Settings::get('db_version') < '2.0.0') {
            // Because this is triggered from add_filter we need to return the unmodified content type
            return $contentType;
        }

        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . GeneralHelper::$tableName,
            [
                'is_html' => $contentType === 'text/html',
            ],
            ['id' => $this->id]
        );

        Cache::flush();

        // Because this is triggered from add_filter we need to return the unmodified content type
        return $contentType;
    }

    /**
     * Convert attachment ids or urls into a format to be usable
     * by the logs
     *
     * @param  array | string  $attachments either array of attachment ids or their urls
     *
     * @return array [id, url] of attachments
     */
    protected function getAttachmentLocations($attachments): array
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

        for ($i = 0, $iMax = count($attachments); $i < $iMax; $i++) {
            $result[] = [
                'id' => $attachmentIds[$i],
                'url' => GeneralHelper::$uploadsFolderInfo['url'] . $attachments[$i]
            ];
        }

        return $result;
    }

    /**
     * Get the details of the method that originally triggered wp_mail
     *
     * @return array a single element of the debug_backtrace function
     */
    private function getBacktrace($functionName = 'wp_mail'): ?array
    {
        $backtraceSegment = null;
        $backtrace = debug_backtrace();

        foreach ($backtrace as $segment) {
            if ($segment['function'] == $functionName) {
                $backtraceSegment = $segment;
            }
        }

        return $backtraceSegment;
    }
}
