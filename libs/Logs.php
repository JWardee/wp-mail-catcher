<?php
class Logs
{
    // TODO: Make singleton and cache results so db query isn't run everytime
    public static $posts_per_page = 10;

    public static function getTotalPages()
    {
        return ceil(Logs::getTotalAmount() / Logs::$posts_per_page);
    }

	/**
	 * @param array $args
	 * @return array|null|object
     */
	public static function get($args = array())
    {
		// TODO: Need to add caching?
		global $wpdb;

		if (empty($args['posts_per_page'])) {
			$args['posts_per_page'] = Logs::$posts_per_page;
		}

        if (empty($args['paged'])) {
			$args['paged'] = 1;
        }

        if (empty($args['orderby'])) {
			$args['orderby'] = 'time';
		}

        if (empty($args['order'])) {
			$args['order'] = 'DESC';
        }

		if (empty($args['paged'])) {
			$args['paged'] = 1;
		}

		$sql = "SELECT id, time, emailto, subject, message,
                status, error, backtrace_segment, attachments,
                additional_headers
                FROM " . $wpdb->prefix . MailCatcher::$table_name . " ";

	   	if (!empty($args['post__in'])) {
			$sql .= "WHERE id IN(" . GeneralHelper::arrayToString($args['post__in']) . ") ";
		} elseif (!empty($args['subject'])) {
			$sql .= "WHERE subject LIKE '%" . $args['subject'] . "%'";
		}

		$sql .=	"ORDER BY " . $args['orderby'] . " " . $args['order'] . "
				 LIMIT " . $args['posts_per_page'] . "
                 OFFSET " . ($args['posts_per_page'] * ($args['paged'] - 1));

        return $wpdb->get_results($sql, ARRAY_A);
    }

    public static function getTotalAmount()
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . MailCatcher::$table_name);
    }

    public static function delete($ids)
    {
        global $wpdb;

        $wpdb->query("DELETE FROM " . $wpdb->prefix . MailCatcher::$table_name . "
                      WHERE id IN(" . GeneralHelper::arrayToString($ids) . ")");
    }
}
