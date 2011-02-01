<?php

/**
 * GentleSource Comment Script -  identifierlist.class.inc.php
 *
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 *
 */

require_once 'list.class.inc.php';




/**
 * Generate comment list
 */
class c5t_identifier_list extends c5t_list
{

    /**
     * Database fields to be selected
     */
    var $fields = array('identifier_id',
                        'identifier_value',
                        'identifier_name'
                        );


    /**
     * Columns that can be sorted
     */
    var $order_columns = array( 'id'            => 'ti.identifier_id',
                                'identifier'    => 'ti.identifier_name');


    /**
     * Identifier to tell different list settings in session apart
     */
    var $identifier = 'identifierlist';


    /**
     * Default order direction for SQL statement
     * Possible values: ascending|descending
     */
    var $default_order_direction = 'ascending';


    /**
     * Default order field for SQL statement
     */
    var $default_order_field = 'ti.identifier_value';






    /**
     * Constructor
     */
    function c5t_identifier_list($use_session, $setup = array())
    {
        global $c5t;

        // Search field select menu
        $this->search_field_list = array(
                                    'identifier_value' => $c5t['text']['txt_page_url'],
                                    'identifier_name'  => $c5t['text']['txt_page_name']
                                    );

        // Search SQL statements
        $this->search_statements = array(
                                    'identifier_value'  => " AND ti.identifier_value LIKE '%{query}%'",
                                    'identifier_name'   => " AND ti.identifier_name LIKE '%{query}%'"
                                    );

        // Configuration and setup
        if ($use_session == true) {
            $this->use_session = true;
        }
        $this->c5t_list($setup);
    }


    /**
     * Get identifier list
     */
    function get_list()
    {
        list($where, $data) = $this->where();
        $cql = "SELECT      COUNT(*) AS num_result
                FROM        " . C5T_IDENTIFIER_TABLE . " AS ti
                            " .  $where;

        $sql = "SELECT      " . c5t_database::fields('ti', $this->fields) . "
                FROM        " . C5T_IDENTIFIER_TABLE . " AS ti
                            " .  $where . "
                ORDER BY    " . $this->order_field . " " . $this->order_direction;

        if ($res = $this->query($cql, $sql, $data)) {
            while ($row = $res->fetchRow())
            {
                c5t_clean_output($row);
                if ($row['identifier_name'] != '') {
                    $row['identifier_output'] = $row['identifier_name'];
                } else {
                    $row['identifier_output'] = $row['identifier_value'];
                }
                $list[] = $row;
            }
        }
        if (isset($list)) {
            return $list;
        }
    }





}
?>
