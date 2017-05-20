<?php
class Logs
{
    // TODO: Make singleton and cache results so db query isn't run everytime
    public static $posts_per_page = 5;

    public static function getTotalPages()
    {
        return ceil(Logs::getTotalAmount() / Logs::$posts_per_page);
    }

    public static function get($current_page)
    {
        if (empty($current_page)) {
            $current_page = 1;
        }

        global $wpdb;

        $sql = "SELECT id, time, emailto, subject, message, status, error
                FROM " . $wpdb->prefix . MailCatcher::$table_name;

        // TODO: Sanitise $_REQUEST
        if (!empty($_REQUEST['orderby'])) {
            $sql .= " ORDER BY " . $_REQUEST['orderby'];
        } else {
            $sql .= " ORDER BY time";
        }

        if (!empty($_REQUEST['order'])) {
            $sql .= " " . $_REQUEST['order'];
        } else {
            $sql .= " DESC";
        }

        $sql .= " LIMIT " . Logs::$posts_per_page . "
                  OFFSET " . (Logs::$posts_per_page * ($current_page - 1));

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function getTotalAmount()
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . MailCatcher::$table_name);
    }

    public static function getFromIds($ids)
    {
        global $wpdb;

        $sql = "SELECT id, time, emailto, subject, message, status, error
                FROM " . $wpdb->prefix . MailCatcher::$table_name . "
                WHERE id IN(" . GeneralHelper::arrayToSqlString($ids) . ")";

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function delete($ids)
    {
        global $wpdb;

        $wpdb->query("DELETE FROM " . $wpdb->prefix . MailCatcher::$table_name . "
                      WHERE id IN(" . GeneralHelper::arrayToSqlString($ids) . ")");
    }
}