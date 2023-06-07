<?php

namespace WpMailCatcher;

use WP_List_Table;
use WpMailCatcher\Models\Logs;

class MailAdminTable extends WP_List_Table
{
    public $totalItems;
    private static $instance = false;
    private $emailSubjectBase64Encoded = '=?utf-8?B?';
    private $emailSubjectQuotedEncoded = '=?utf-8?Q?';
    private $asciSubjectHelpLink = 'https://ncona.com/2011/06/using-utf-8-characters-on-an-e-mail-subject/';

    public function __construct()
    {
        parent::__construct([
            'singular' => 'log',
            'plural' => 'logs',
            'ajax' => false
        ]);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new MailAdminTable();
        }

        return self::$instance;
    }

    private function runHtmlSpecialChars($value)
    {
        $value = GeneralHelper::filterHtml($value);

        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
            null,
            false
        );
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'time':
            case 'subject':
            case 'status':
                return $item[$column_name];
            case 'email_to':
            case 'email_from':
                return esc_html($item[$column_name]);
            default:
                return print_r($item, true);
        }
    }

    function column_subject($item)
    {
        $subject = $item['subject'];

        if (strpos($subject, $this->emailSubjectBase64Encoded) === 0) {
            $subjectEncoded = substr(
                $subject,
                strlen($this->emailSubjectBase64Encoded),
                strlen($subject) - strlen($this->emailSubjectBase64Encoded) - 1
            );

            $subjectDecoded = base64_decode($subjectEncoded);
            $subjectDecoded = $this->runHtmlSpecialChars($subjectDecoded);

            return '<span class="asci-help" data-hover-message="' . __("This subject was base64 decoded") . '">
                        <a href="' . $this->asciSubjectHelpLink . '" target="_blank">(?)</a>
                        ' . $subjectDecoded . '
                    </span>';
        }

        if (strpos($subject, $this->emailSubjectQuotedEncoded) === 0) {
            $subjectEncoded = substr(
                $subject,
                strlen($this->emailSubjectQuotedEncoded),
                strlen($subject) - strlen($this->emailSubjectQuotedEncoded) - 1
            );

            $subjectDecoded = quoted_printable_decode($subjectEncoded);
            $subjectDecoded = base64_decode($subjectEncoded);
            $subjectDecoded = $this->runHtmlSpecialChars($subjectDecoded);

            return '<span class="asci-help" data-hover-message="' . __("This subject was quoted printable decoded") . '">
                        <a href="' . $this->asciSubjectHelpLink . '" target="_blank">(?)</a>
                        ' . $subjectDecoded . '
                    </span>';
        }

        return $this->runHtmlSpecialChars($subject);
    }

    function column_time($item): string
    {
        return '<span data-hover-message="' . date(GeneralHelper::$humanReadableDateFormat, $item['timestamp']) . '">' . $item['time'] . '</span>';
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'id',
            $item['id']
        );
    }

    function column_more_info($item): string
    {
        return '<a href="#" class="button button-secondary" data-toggle="modal" data-target="#' . $item['id'] . '">' . __('More Info', 'WpMailCatcher') . '</a>';
    }

    function get_columns(): array
    {
        return [
            'cb' => '<input type="checkbox" />',
            'status' => '',
            'email_to' => __('To', 'WpMailCatcher'),
            'subject' => __('Subject', 'WpMailCatcher'),
            'email_from' => __('From', 'WpMailCatcher'),
            'time' => __('Sent', 'WpMailCatcher'),
            'more_info' => ''
        ];
    }

    function column_email_to($item): string
    {
        $actions = [
            'delete' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=delete&id=' . $item['id'], 'bulk-logs') . '">' . __('Delete', 'WpMailCatcher') . '</a>',
            'resend' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=resend&id=' . $item['id'], 'bulk-logs') . '">' . __('Resend', 'WpMailCatcher') . '</a>',
            'export' => '<a href="' . wp_nonce_url('?page=' . GeneralHelper::$adminPageSlug . '&action=export&id=' . $item['id'], 'bulk-logs') . '">' . __('Export', 'WpMailCatcher') . '</a>',
            'view' => '<a href="#" data-toggle="modal" data-target="#' . $item['id'] . '">' . __('View', 'WpMailCatcher') . '</a>',
        ];

        $emailTo = $this->runHtmlSpecialChars($item['email_to']);

        return sprintf('%1$s %2$s', $emailTo, $this->row_actions($actions));
    }

    function column_status($item): string
    {
        return $item['status'] ? '<div class="status-indicator"></div>' : '<div class="-right" data-hover-message="' . $item['error'] . '"><div class="status-indicator -error"></div></div>';
    }

    function get_hidden_columns()
    {
        $userSaved = get_user_meta(
            get_current_user_id(),
            ScreenOptions::$optionIdsToWatch['logs_hidden_table_columns'],
            true
        );

        return !empty($userSaved) ? $userSaved : [
            'email_from'
        ];
    }

    function get_sortable_columns(): array
    {
        return [
            'time' => ['time', false],
            'email_to' => ['email_to', false],
            'subject' => ['subject', false],
        ];
    }

    function get_bulk_actions(): array
    {
        return [
            'delete' => __('Delete', 'WpMailCatcher'),
            'resend' => __('Resend', 'WpMailCatcher'),
            'export' => __('Export', 'WpMailCatcher')
        ];
    }

    function process_bulk_action()
    {
    }

    public function getLogsPerPage(): int
    {
        $userSaved = get_user_meta(
            get_current_user_id(),
            ScreenOptions::$optionIdsToWatch['logs_per_page'],
            true
        );

        return !empty($userSaved) ? (int)$userSaved : GeneralHelper::$logsPerPage;
    }

    function prepare_items()
    {
        $per_page = $this->getLogsPerPage();

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->process_bulk_action();

        $overrideParams = array_intersect_key($_REQUEST, Logs::$whitelistedParams);

        $this->items = Logs::get(array_merge([
            'paged' => $this->get_pagenum(),
            'post_status' => $_GET['post_status'] ?? 'any',
            'posts_per_page' => $per_page,
            'column_blacklist' => ['message']
        ], $overrideParams));

        $this->totalItems = Logs::getTotalAmount();

        $this->set_pagination_args([
            'total_items' => $this->totalItems,
            'per_page' => $per_page,
            'total_pages' => Logs::getTotalPages($per_page)
        ]);
    }
}
