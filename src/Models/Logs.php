<?php

namespace WpMailCatcher\Models;

use WpMailCatcher\GeneralHelper;

class Logs
{
    // Need to set null because we're using array_intersect_key
    public static $whitelistedParams = [
        'orderby' => null,
        'posts_per_page' => null,
        'paged' => null,
        'order' => null,
        'post_status' => null,
        'subject' => null,
        's' => null
    ];

    public static function getTotalPages($postsPerPage = false)
    {
        if (! $postsPerPage) {
            $postsPerPage = GeneralHelper::$logsPerPage;
        }

        return ceil(self::getTotalAmount() / $postsPerPage);
    }

    public static function getFirst($args = [])
    {
        $result = self::get($args);
        return $result[0] ?? false;
    }

    /**
     * @param  array  $args
     *
     * @return array|null|object
     */
    public static function get(array $args = [])
    {
        global $wpdb;

        if (!isset($args['ignore_cache']) || ! $args['ignore_cache']) {
            $cachedValue = Cache::get($args);

            if ($cachedValue != null) {
                return $cachedValue;
            }
        }

        /**
         * Set default arguments and combine with
         * those passed in get/post and passed directly
         * to the function
         */
        $defaults = [
            'orderby' => 'time',
            'posts_per_page' => -1,
            'paged' => 1,
            'order' => 'DESC',
            'date_time_format' => 'human',
            'post_status' => 'any',
            'post__in' => [],
            'subject' => null,
            's' => null,
            'column_blacklist' => []
        ];

        $args = array_merge($defaults, $args);

        $defaultColumns = [
            'id', 'time', 'email_to', 'subject', 'message',
            'status', 'error', 'backtrace_segment', 'attachments',
            'additional_headers'
        ];

        if (Settings::get('db_version') >= '2.0.0') {
            $defaultColumns[] = 'is_html';
        }

        $columnsToSelect = array_diff($defaultColumns, $args['column_blacklist']);

        /**
         * Sanitise each value in the array
         */
        array_walk_recursive($args, 'WpMailCatcher\GeneralHelper::sanitiseForDbQuery');

        $sql = "SELECT " . implode(',', $columnsToSelect) . "
            FROM " . $wpdb->prefix . GeneralHelper::$tableName . " ";

        $whereClause = false;

        if (!empty($args['post__in'])) {
            $whereClause = true;
            $sql .= "WHERE id IN(" . GeneralHelper::arrayToString($args['post__in']) . ") ";
        }

        if ($args['subject'] != null && $args['s'] == null) {
            $args['s'] = $args['subject'];
        }

        if ($args['s'] != null) {
            if ($whereClause) {
                $sql .= "AND ";
            } else {
                $sql .= "WHERE ";
                $whereClause = true;
            }

            $sql .= "(subject LIKE '%" . $args['s'] . "%') OR ";
            $sql .= "(message LIKE '%" . $args['s'] . "%') OR ";
            $sql .= "(email_to LIKE '%" . $args['s'] . "%') OR ";
            $sql .= "(attachments LIKE '%" . $args['s'] . "%') OR ";
            $sql .= "(additional_headers LIKE '%" . $args['s'] . "%') ";
        }

        if ($args['post_status'] != 'any') {
            if ($whereClause) {
                $sql .= "AND ";
            } else {
                $sql .= "WHERE ";
            }

            switch ($args['post_status']) {
                case ('successful'):
                    $sql .= "status = 1 ";
                    break;
                case ('failed'):
                    $sql .= "status = 0 ";
                    break;
            }
        }

        $sql .= "ORDER BY " . $args['orderby'] . " " . $args['order'] . " ";

        if ($args['posts_per_page'] != -1) {
            $sql .= "LIMIT " . $args['posts_per_page'] . "
               OFFSET " . ($args['posts_per_page'] * ($args['paged'] - 1));
        }

        $results = self::dbResultTransform($wpdb->get_results($sql, ARRAY_A), $args);

        if (!isset($args['ignore_cache']) || ! $args['ignore_cache']) {
            Cache::set($args, $results);
        }

        return $results;
    }

    private static function dbResultTransform($results, $args = [])
    {
        foreach ($results as &$result) {
            $result['attachment_file_paths'] = [];

            if (isset($result['status'])) {
                $result['status'] = (bool)$result['status'];
            }

            if (isset($result['additional_headers'])) {
                $result['additional_headers'] = json_decode($result['additional_headers'], true);

                if (is_string($result['additional_headers'])) {
                    $result['additional_headers'] = explode(PHP_EOL, $result['additional_headers']);
                }

                $result['email_from'] = self::getEmailFrom($result);
            }

            if (isset($result['time'])) {
                $result['timestamp'] = $result['time'];
                $result['time'] = $args['date_time_format'] == 'human' ?
                    GeneralHelper::getHumanReadableTimeFromNow($result['timestamp']) :
                    date($args['date_time_format'], $result['timestamp']);
            }

            // This will exist if the db_version is >= 2.0.0
            if (isset($result['is_html']) && $result['is_html']) {
                $result['is_html'] = (bool)$result['is_html'];
            // Otherwise resort to the original method
            } elseif (isset($result['additional_headers'])) {
                $result['is_html'] = GeneralHelper::doesArrayContainSubString(
                    str_replace(' ', '', $result['additional_headers']),
                    str_replace(' ', '', GeneralHelper::$htmlEmailHeader)
                );
            }

            if (!empty($result['attachments'])) {
                $result['attachments'] = json_decode($result['attachments'], true);

                foreach ($result['attachments'] as &$attachment) {
                    if ($attachment['id'] == -1) {
                        $attachment['note'] = GeneralHelper::$attachmentNotInMediaLib;
                        continue;
                    }

                    $attachment['src'] = GeneralHelper::$attachmentNotImageThumbnail;
                    $attachment['url'] = wp_get_attachment_url($attachment['id']);
                    $result['attachment_file_paths'][] = get_attached_file($attachment['id']);

                    $isImage = strpos(get_post_mime_type($attachment['id']), 'image') !== false;

                    if ($isImage) {
                        $attachment['src'] = $attachment['url'];
                    }
                }
            }
        }

        return $results;
    }

    public static function getTotalAmount(): ?string
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . GeneralHelper::$tableName);
    }

    public static function delete($ids)
    {
        if (empty($ids)) {
            return;
        }

        global $wpdb;

        $ids = GeneralHelper::arrayToString($ids);
        $ids = GeneralHelper::sanitiseForDbQuery($ids);

        $wpdb->query("DELETE FROM " . $wpdb->prefix . GeneralHelper::$tableName . "
                      WHERE id IN(" . $ids . ")");
    }

    public static function truncate()
    {
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE " . $wpdb->prefix . GeneralHelper::$tableName);
    }

    private static function getEmailFrom($logEntry)
    {
        $fullHeader = GeneralHelper::searchForSubStringInArray($logEntry['additional_headers'], 'From: ');

        /**
         * This cannot be removed because of a bug in previous versions
         * that caused the header to save as "custom: from: example@test.com"
         * @url https://github.com/JWardee/wp-mail-catcher/issues/56
         */
        return str_replace(['custom:', 'From:', ' '], '', $fullHeader);
    }

    public static function deleteOlderThan($timeInterval = null)
    {
        global $wpdb;

        $interval = $timeInterval == null ?  Settings::get('timescale') : $timeInterval;
        $timestamp = time() - $interval;

        $sql = $wpdb->prepare(
            "DELETE FROM " . $wpdb->prefix . GeneralHelper::$tableName . " WHERE time <= %d",
            $timestamp
        );

        $wpdb->query($sql);
    }
}
