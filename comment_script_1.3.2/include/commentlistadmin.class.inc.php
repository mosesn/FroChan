<?php

/**
 * GentleSource Comment Script -  commentlist.class.inc.php
 *
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 *
 */

require_once 'list.class.inc.php';




/**
 * Generate comment list
 */
class c5t_comment_list extends c5t_list
{


    /**
     * Database fields to be selected
     */
    var $fields = array('comment_id',
                        'comment_id AS id',
                        'comment_author_name',
                        'comment_author_email',
                        'comment_author_homepage',
                        'comment_author_ip',
                        'comment_author_ip AS ip_address',
                        'comment_author_host',
                        'comment_author_user_agent',
                        'comment_title',
                        'comment_text',
                        'comment_text AS frontend_text',
                        'comment_timestamp',
                        'comment_status'
                        );


    /**
     * Columns that can be sorted
     */
    var $order_columns = array( 'date'  => 'comment_timestamp');


    /**
     * Identifier to tell different list settings in session apart
     */
    var $identifier = 'commentlistadmin';


    /**
     * Default order direction for SQL statement
     * Possible values: ascending|descending
     */
    var $default_order_direction = 'descending';


    /**
     * Default order field for SQL statement
     */
    var $default_order_field = 'tc.comment_timestamp';

//------------------------------------------------------------------------------




    /**
     * Constructor
     */
    function c5t_comment_list($use_session, $setup = array())
    {
        global $c5t;

        // Search field select menu
        $this->search_field_list = array(
                                    'comment_text'          => $c5t['text']['txt_search_in'] . ' ' . $c5t['text']['txt_comment_text'],
                                    'comment_title'         => $c5t['text']['txt_search_in'] . ' ' . $c5t['text']['txt_title'],
                                    'comment_author_name'   => $c5t['text']['txt_search_in'] . ' ' . $c5t['text']['txt_name'],
                                    'comment_author_email'  => $c5t['text']['txt_search_in'] . ' ' . $c5t['text']['txt_email']
                                    );

        // Search SQL statements
        $this->search_statements = array(
                                    'comment_text'          => " AND tc.comment_text LIKE '%{query}%'",
                                    'comment_title'         => " AND tc.comment_title LIKE '%{query}%'",
                                    'comment_author_name'   => " AND tc.comment_author_name LIKE '%{query}%'",
                                    'comment_author_email'  => " AND tc.comment_author_email LIKE '%{query}%'",
                                    );

        // Configuration and setup
        if ($use_session == true) {
            $this->use_session = true;
        }
        $this->c5t_list($setup);
    }

//------------------------------------------------------------------------------




    /**
     * Get comment list
     */
    function get_list($identifier_id)
    {
        global $c5t;
        list($where, $data) = $this->where();
        $data[] = $identifier_id;
        $cql = "SELECT      COUNT(*) AS num_result
                FROM        " . C5T_COMMENT_TABLE . " AS tc
                            " .  $where . "
                AND         comment_identifier_id = ?";

        $sql = "SELECT      " . c5t_database::fields('tc', $this->fields) . "
                FROM        " . C5T_COMMENT_TABLE . " AS tc
                            " .  $where . "
                AND         comment_identifier_id = ?
                ORDER BY    " . $this->order_field . " " . $this->order_direction;

        if ($res = $this->query($cql, $sql, $data)) {

            // Comment number
            if ($this->order_direction == 'ASC') {
                $comment_number = 0;
            } else {
                $comment_number = $this->num_results + 1 - $this->valid_offset();
            }

            // Last comment id for delete anchor
            $previous_id = 0;

            // Fetch comments
            while ($row = $res->fetchRow())
            {
                c5t_clean_output($row);
                c5t_escape_output($row);

                // Comment number
                if ($this->order_direction == 'ASC') {
                    $comment_number++;
                } else {
                    $comment_number--;
                }

                // Enhance user data
                $row['comment_author_name'] = ($row['comment_author_name'] == '' ? $c5t['text']['txt_anonymous'] : $row['comment_author_name']);

                $enhance = array(
                            'previous_id'       => $previous_id,
                            'comment_date'      => c5t_time::format_date($row['comment_timestamp']),
                            'comment_time'      => c5t_time::format_time($row['comment_timestamp']),
                            'comment_number'    => $comment_number,
                            'hostname_output'   => (strlen($row['comment_author_host']) > $c5t['hostname_length']) ? substr($row['comment_author_host'], 0, $c5t['hostname_length']) . $c5t['cut_off_string'] : $row['comment_author_host'],
                            'user_agent_output' => (strlen($row['comment_author_user_agent']) > $c5t['user_agent_length']) ? substr($row['comment_author_user_agent'], 0, $c5t['user_agent_length']) . $c5t['cut_off_string'] : $row['comment_author_user_agent'],
                            );

                $final  = array_merge(
                                    $row,
                                    $enhance
                                    );

                c5t_module::call_module('backend_content', $final, $c5t['module_additional']);

                $list[] = $final;

                // Last comment number for delete anchor
                $previous_id = $row['comment_id'];
            }
        }
        if (isset($list)) {
            return $list;
        }
    }

//------------------------------------------------------------------------------




    /**
     * Get comment list
     */
    function get_list_all()
    {
        global $c5t;
        list($where, $data) = $this->where();
        $cql = "SELECT     COUNT(*) AS num_result
                FROM        (" . C5T_COMMENT_TABLE . " AS tc,
                            " . C5T_IDENTIFIER_TABLE . " AS ti)
                            " .  $where . "
                AND         tc.comment_identifier_id = ti.identifier_id";

        $sql = "SELECT      " . c5t_database::fields('tc', $this->fields) . ",
                            ti.*
                FROM        (" . C5T_COMMENT_TABLE . " AS tc,
                            " . C5T_IDENTIFIER_TABLE . " AS ti)
                            " .  $where . "
                AND         tc.comment_identifier_id = ti.identifier_id
                ORDER BY    " . $this->order_field . " " . $this->order_direction;

        if ($res = $this->query($cql, $sql, array())) {

            // Comment number
            if ($this->order_direction == 'ASC') {
                $comment_number = 0;
            } else {
                $comment_number = $this->num_results + 1 - $this->valid_offset();
            }

            // Last comment id for delete anchor
            $previous_id = 0;

            // Fetch comments
            while ($row = $res->fetchRow())
            {
                c5t_clean_output($row);
                c5t_escape_output($row);

                if ($row['identifier_name'] != '') {
                    $row['identifier_output'] = $row['identifier_name'];
                } else {
                    $row['identifier_output'] = $row['identifier_value'];
                }

                // Comment number
                if ($this->order_direction == 'ASC') {
                    $comment_number++;
                } else {
                    $comment_number--;
                }

                // Enhance user data
                $row['comment_author_name'] = ($row['comment_author_name'] == '' ? $c5t['text']['txt_anonymous'] : $row['comment_author_name']);

                $enhance = array(
                            'previous_id'       => $previous_id,
                            'comment_date'      => c5t_time::format_date($row['comment_timestamp']),
                            'comment_time'      => c5t_time::format_time($row['comment_timestamp']),
                            'comment_number'    => $comment_number,
                            'comment_number'    => $comment_number,
                            'hostname_output'   => (strlen($row['comment_author_host']) > $c5t['hostname_length']) ? substr($row['comment_author_host'], 0, $c5t['hostname_length']) . $c5t['cut_off_string'] : $row['comment_author_host'],
                            'user_agent_output' => (strlen($row['comment_author_user_agent']) > $c5t['user_agent_length']) ? substr($row['comment_author_user_agent'], 0, $c5t['user_agent_length']) . $c5t['cut_off_string'] : $row['comment_author_user_agent'],
                            );

                $final  = array_merge(
                                    $row,
                                    $enhance
                                    );

                c5t_module::call_module('backend_content', $final, $c5t['module_additional']);

                $list[] = $final;

                // Last comment number for delete anchor
                $previous_id = $row['comment_id'];
            }
        }
        if (isset($list)) {
            return $list;
        }
    }

//------------------------------------------------------------------------------



}
?>
