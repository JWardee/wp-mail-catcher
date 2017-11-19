<?php

namespace WpMailCatcher;

use WpMailCatcher\Models\Logs;
use WpMailCatcher\Models\Mail;

class MailAdminTable extends WP_List_Table
{
    function __construct()
	{
        parent::__construct([
            'singular'  => 'log',
            'plural'    => 'logs',
            'ajax'      => false
        ]);
    }

    function column_default($item, $column_name)
	{
        switch($column_name)
		{
            case 'time':
            case 'email_to':
            case 'subject':
            case 'status':
                return $item[$column_name];
			break;
            default:
                return print_r($item, true);
			break;
        }
    }

    function column_time($item)
	{
        $actions = [
            'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Delete', 'WpMailCatcher') . '</a>',
                'mail-catcher',
                'delete',
                $item['id']),
            'resend' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Resend', 'WpMailCatcher') . '</a>',
                'mail-catcher',
                'resend',
                $item['id']),
            'export' => sprintf('<a href="?page=%s&action=%s&id=%s">' . __('Export', 'WpMailCatcher') . '</a>',
                'mail-catcher',
                'export',
                $item['id']),
        ];

		return sprintf('%1$s %2$s',
			$item['time'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
	{
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            'id',
            $item['id']
        );
    }

    function column_more_info($item)
	{
        return '<a href="#" class="button button-secondary" data-toggle="modal" data-target="#' . $item['id'] . '">More Info</a>';
    }

    function get_columns()
	{
        $columns = [
            'cb'       => '<input type="checkbox" />',
            'time'  => __('Sent', 'WpMailCatcher'),
            'email_to'  => __('To', 'WpMailCatcher'),
            'subject'  => __('Subject', 'WpMailCatcher'),
            'status'  => __('Status', 'WpMailCatcher'),
            'more_info' => ''
        ];

        return $columns;
    }

    function column_status($item)
	{
        if ($item['status'] == true) {
            return '<span class="status">' . __('Success', 'WpMailCatcher') . '</span>';
        }

        return '<span class="status" data-error="' . $item['error'] . '">' . __('Failed', 'WpMailCatcher') . '</span>';
    }

    function get_sortable_columns()
	{
        $sortable_columns = [
            'time'  => ['time', false],
            'email_to'  => ['email_to', false],
            'subject'  => ['subject', false],
            'status'  => ['status', false],
        ];

        return $sortable_columns;
    }

    function get_bulk_actions()
	{
        $actions = [
            'delete'    => __('Delete', 'WpMailCatcher'),
            'resend' => __('Resend', 'WpMailCatcher'),
            'export' => __('Export', 'WpMailCatcher')
        ];

        return $actions;
    }

    function process_bulk_action()
	{
        switch ($this->current_action()) {
            case 'delete' :
                Logs::delete($_REQUEST['id']);
            break;
            case 'resend' :
                Mail::resend($_REQUEST['id']);
            break;
        }
    }

    function prepare_items()
	{
        $per_page = 5;

        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = [$columns, $hidden, $sortable];
        $this->process_bulk_action();

        $this->items = Logs::get([
			'paged' => $this->get_pagenum()
		]);

        $total_items = Logs::getTotalAmount();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => Logs::getTotalPages()
        ]);
    }
}
