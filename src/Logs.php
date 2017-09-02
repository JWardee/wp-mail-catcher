<?php

namespace MailCatcher;

class Logs
{
    // TODO: Cache results so db query isn't run everytime
    static public $postsPerPage = 10;

    static public function getTotalPages()
    {
        return ceil(Logs::getTotalAmount() / Logs::$postsPerPage);
    }

	/**
	 * @param array $args
	 * @return array|null|object
     */
	static public function get($args = array())
    {
		// TODO: Need to add caching?
		global $wpdb;

		/**
		 * Set default arguments and combine with
		 * those passed in get/post and passed directly
		 * to the function
		 */
		$defaults = array(
			'orderby' => 'time',
			'posts_per_page' => Logs::$postsPerPage,
			'paged' => 1,
			'order' => 'DESC'
		);

		$args = array_merge($defaults, $_REQUEST, $args);

		/**
		 * Sanitise each value in the array
		 */
		array_walk_recursive($args, 'MailCatcher\GeneralHelper::sanitiseForQuery');

		$sql = "SELECT id, time, emailto, subject, message,
                status, error, backtrace_segment, attachments,
                additional_headers
                FROM " . $wpdb->prefix . GeneralHelper::$tableName . " ";

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

    static public function getTotalAmount()
    {
        global $wpdb;

        return $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . GeneralHelper::$tableName);
    }

    static public function delete($ids)
    {
        global $wpdb;

		$ids = GeneralHelper::arrayToString($ids);
		$ids = GeneralHelper::sanitiseForQuery($ids);

        $wpdb->query("DELETE FROM " . $wpdb->prefix . GeneralHelper::$tableName . "
                      WHERE id IN(" . $ids . ")");
    }
}
