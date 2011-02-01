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
                        'comment_author_name',
                        'comment_author_email',
                        'comment_author_homepage',
                        'comment_title',
                        'comment_text',
                        'comment_text AS frontend_text',
                        'comment_timestamp'
                        );


    /**
     * Columns that can be sorted
     */
    var $order_columns = array('comment_timestamp');


    /**
     * Identifier to tell different list settings in session apart
     */
    var $identifier = 'commentlist';


    /**
     * Default order direction for SQL statement
     * Possible values: ascending|descending
     */
    var $default_order_direction = 'ascending';


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
                                    'comment_text' => $c5t['text']['txt_comment_text']
                                    );

        // Search SQL statements
        $this->search_statements = array(
                                    'comment_text'  => " AND tc.comment_text LIKE '%{query}%'"
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
    function get_list($identifier)
    {
        global $c5t;

        list($where, $data) = $this->where();
        $data[] = md5($identifier);
        $data[] = $c5t['comment_status']['approved'];
        $data[] = c5t_time::current_timestamp() - ((int) $c5t['publish_delay'] * 60);

        $cql = "SELECT      COUNT(*) AS num_result
                FROM        (" . C5T_COMMENT_TABLE . " AS tc,
                            " . C5T_IDENTIFIER_TABLE . " AS ti)
                            " .  $where . "
                AND         ti.identifier_hash = ?
                AND         tc.comment_identifier_id = ti.identifier_id
                AND         tc.comment_status = ?
                AND         tc.comment_timestamp <= ?";


        $sql = "SELECT      " . c5t_database::fields('tc', $this->fields) . ",
                            ti.*
                FROM        (" . C5T_COMMENT_TABLE . " AS tc,
                            " . C5T_IDENTIFIER_TABLE . " AS ti)
                            " .  $where . "
                AND         ti.identifier_hash = ?
                AND         tc.comment_identifier_id = ti.identifier_id
                AND         tc.comment_status = ?
                AND         tc.comment_timestamp <= ?
                ORDER BY    " . $this->order_field . " " . $this->order_direction;

        if ($res = $this->query($cql, $sql, $data)) {

            // Comment number
            if ($this->order_direction == 'ASC') {
                $comment_number =  0 + $this->valid_offset();
            } else {
                $comment_number = $this->num_results + 1 - $this->valid_offset();
            }

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

                $row['comment_author_name'] = ($row['comment_author_name'] == '' ? $c5t['text']['txt_anonymous'] : $row['comment_author_name']);

                // Enhance user data
                $enhance = array(
                            'comment_date'      => c5t_time::format_date($row['comment_timestamp']),
                            'comment_time'      => c5t_time::format_time($row['comment_timestamp']),
                            'comment_text'      => nl2br($row['comment_text']),
                            'comment_number'    => $comment_number
                            );

                $final  = array_merge($row, $enhance);

                c5t_module::call_module('frontend_content', $final, $c5t['module_additional']);

                $list[] = $final;
            }
        }
        if (isset($list)) {
            return $list;
        }
    }

//------------------------------------------------------------------------------



}
?>
