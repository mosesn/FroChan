<?php
 
/** 
 * GentleSource Comment Script - list.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Handle record lists
 */
class c5t_list
{


    /**
     * Columns that can be sorted
     */
    var $order_columns = array();

    /**
     * Contains setup for sorting, grouping, searching
     */
    var $setup = array( 'order_by'      => '',
                        'group_by'      => '',
                        'direction'     => '',
                        'group_value'   => '',
                        'search_field'  => '',
                        'search_query'  => '',
                        'page'          => 1,
                        'limit'         => 20,
                        'session'       => true,
                        'identifier'    => 'list',
                        );

    /**
     * Output object
     */
    var $out = '';


    /**
     * Identifier
     */
    var $identifier = 'default';


    /**
     * Order direction for SQL statement
     */
    var $order_direction = '';


    /**
     * Order direction for SQL statement
     */
    var $order_direction_value = array();


    /**
     * Order field for SQL statement
     */
    var $order_field = '';


    /**
     * Default order direction
     * 
     * Possible values: ascending|descending
     */
    var $default_order_direction = 'ascending';


    /**
     * Default order field
     */
    var $default_order_field = '';


    /**
     * Group and search form template (can be overwritten in child class)
     */
    var $form_template = 'list_form.tpl.html';


    /**
     * Grouping statements
     */
    var $group_statements = array();


    /**
     * Grouping statement text
     */
    var $group_value_text= array();


    /**
     * Search field select menu
     */
    var $search_field_list = array();
        
        
    /**
     * Search SQL statements
     */
    var $search_statements = array();
        
        
    /**
     * Number of rows in a result set
     */
    var $num_results = null;

        
    /**
     * Use session to store setup
     */
    var $use_session = false;
        
        
    /**
     * Holds number of next page
     */
    var $next_page_number = 1;
        
        
    /**
     * Holds number of previous page
     */
    var $previous_page_number = 1;
        
        
    /**
     * Holds number of current page
     */
    var $current_page_number = 1;

// -----------------------------------------------------------------------------




    /**
     * Constructor
     */
    function c5t_list($setup = array())
    {
        global $c5t;
        
        // Use outside configuration
        $this->setup['identifier'] = $this->identifier;
        foreach ($setup AS $key => $val)
        {
            if (isset($this->setup[$key]) and $val !== '') {
                $this->setup[$key] = $val;
            }
        }
        
        // Get current setup from session
        if ($this->use_session == true) {
            require_once 'session.class.inc.php';
                if ($set = c5t_session::get('setup_' . $this->setup['identifier'])) {
                $this->setup = array_merge($this->setup, $set);
            }
        }
    
        // Write grouping, searching, sorting, browsing data into session
        if ($order_by = $this->c5t_gpc_vars('o')) {
            $this->setup['order_by'] = trim($order_by);
        }
        if ($group_by = $this->c5t_gpc_vars('g')) {
            $this->setup['group_by'] = trim($group_by);
        }
        if ($direction = $this->c5t_gpc_vars('d')) {
            $this->setup['direction'] = trim($direction);
        }
        $this->order_direction($this->setup['direction']);
        if ($group_list = $this->c5t_gpc_vars('group_list')) {
            $this->setup['group_value'] = trim($group_list);
        }
        if ($search_field = $this->c5t_gpc_vars('search_field')) {
            $this->setup['search_field'] = trim($search_field);
        }
        if ($search_query = $this->c5t_gpc_vars('search_query')) {
            $this->setup['search_query'] = trim($search_query);
        }
        if ($search_delete = $this->c5t_gpc_vars('search_delete')) {
            $this->setup['search_field'] = '';
            $this->setup['search_query'] = '';
        }
        if ($page = $this->c5t_gpc_vars('page')) {
            $this->setup['page'] = (int) trim($page);
        }
        if ($limit = $this->c5t_gpc_vars('limit')) {
            $this->setup['limit'] = trim($limit);
        }

        if ($this->use_session == true) {
            c5t_session::add(array('setup_' . $this->setup['identifier'] => $this->setup));
        }
    }

// -----------------------------------------------------------------------------
    


    
    /**
     * Parse list specific content (direction arrows, group menu, search box)
     */
//    function parse($content)
//    {   
//        $this->out->set_content($content);
//        $this->out->set_var(array('list_form' => $this->form()));         
//        $content = $this->out->parse();
//        return $content;
//    }

// -----------------------------------------------------------------------------
    


    
    /**
     * Create form for grouping and searching
     */
//    function form()
//    {   
//        global $txt;
//
//        // Start output handling
////        $out = new output_handler(array('template_file' => $this->form_template));
////        $content = $out->get_content();
//        
//        // Start form field handling
////        require_once 'listform.inc.php';
//        $group_list                 = $this->group_value_text;
////        $search_fields              = $this->search_field_list;
////        $default['group_list']      = $this->setup['group_value'];
////        $default['search_field']    = $this->setup['search_field'];
////        $default['search_query']    = $this->setup['search_query'];
//        $form = new form_handler('list_form', $list_form);
//        $content = $form->output('list_form', $content, $default);
//                   
//        $out->set_content($content);
//        $out->assign(array( 'browse_previous'  => $this->previous(),
//                            'browse_next'      => $this->next()
//                            ));  
//        $content = $out->parse();
//        return $content;
//    }

// -----------------------------------------------------------------------------
    


    
    /**
     * Make data available
     */
    function values()
    {
        $data = array(  'browse_previous'   => $this->previous(),
                        'browse_next'       => $this->next(),
                        'next_page'         => $this->next_page_number,
                        'previous_page'     => $this->previous_page_number,
                        'current_page'      => $this->current_page_number,
                        'result_pages'      => $this->pagination(),
                        'result_number'     => $this->num_results,
                        'result_limit'      => $this->setup['limit'],
                        'display_pagination'    => $this->num_results <= $this->setup['limit'] ? false : true,
                        );
                        
        $data = array_merge($data, $this->order_direction_value);
        return $data;
    }

// -----------------------------------------------------------------------------
    


    
    /**
     * Handle order direction
     */
    function order_direction($direction)
    {
        if (sizeof($this->order_columns) <= 0) {
            return false;
        }
        if ($direction == '') {
            $direction = $this->default_order_direction;
        }
        $dir = $this->select_direction($direction);
        $this->order_direction = $dir['statement'];
        $this->order_field = $this->default_order_field;
        while (list($column, $field) = each($this->order_columns))
        {
            $alternative = '';
            if ($column == $this->setup['order_by']) {
                $alternative = $dir['image'];
                $this->order_field = $field;
            }
            $this->order_direction_value['order_' . $column] = $alternative;
            c5t_query::add_element('d', $dir['vice_versa'], $column);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Select order direction
     */
    function select_direction($dir)
    {              
        if ($dir == 'ascending') {
            $direction['current']    = 'ascending';
            $direction['vice_versa'] = 'descending';
            $direction['image']      = 2;
            $direction['statement']  = 'ASC';
        } else {
            $direction['current']    = 'descending';
            $direction['vice_versa'] = 'ascending';
            $direction['image']      = 1;
            $direction['statement']  = 'DESC';
        }
       
        return $direction;
  }

// -----------------------------------------------------------------------------




    /**
     * Get where statement for list
     */
    function where()
    {
        $statement  = array();
        $data       = array();
        $statement[] = " WHERE 1 ";
        
        // Group statement
        if (isset($this->setup['group_value']) and
            isset($this->group_statements[$this->setup['group_value']])) {
            
            $statement[] = $this->group_statements[$this->setup['group_value']];
        }
        
        // Search statement
        if (isset($this->setup['search_query']) and
            $this->setup['search_query'] != '' and
            isset($this->setup['search_field']) and
            isset($this->search_statements[$this->setup['search_field']])) {
            
            $statement[] = str_replace( '{query}',
                                        c5t_database::escape($this->setup['search_query']), 
                                        $this->search_statements[$this->setup['search_field']]);
        }
        $res = join('', $statement);
        return array($res, $data);
    }

//------------------------------------------------------------------------------




    /**
     * Get pagination values
     */
    function pagination()
    {
        if ($this->setup['limit'] <= 0) {
            $this->setup['limit'] = $this->num_results;
        }
        if ($this->setup['limit'] <= 0) {
            return 0;
        }
        $pages = ceil($this->num_results / $this->setup['limit']);
        return $pages;
    }

//------------------------------------------------------------------------------




    /**
     * Get valid offset
     */
    function valid_offset()
    {
        $total = $this->num_results;
        $limit = $this->setup['limit'];
        $page  = ($this->setup['page'] <= 0) ? 1 : $this->setup['page'];
        $this->current_page_number = $page; 
        $start = $page * $limit - $limit;
        $start = (isset($start)) ? abs((int)$start) : 0;
        $start = ($start >= $total) ? $total - $limit : $start;
        $start = ($start < 0) ? 0 : $start;
        return $start;
    }

//------------------------------------------------------------------------------




    /**
     * Get limit statement for list
     */
    function limit()
    {
        if ($this->setup['limit'] <= 0) {
            $this->setup['limit'] = $this->num_results;
        }
        $statement = " LIMIT " . $this->valid_offset(). ", " . $this->setup['limit']; 
        return $statement;
    }

//------------------------------------------------------------------------------




    /**
     * Execute query, num rows and return final result
     */
//    function query($sql, $data)
//    {
//        if ($res = c5t_database::query($sql, $data)) {
//            $this->num_results = $res->numRows();
//            $sql .= $this->limit();
//            if ($res = c5t_database::query($sql, $data)) {
//                return $res;
//            }
//        }
//    }
    function query($cql, $sql, $data)
    {
        if ($res = c5t_database::query($cql, $data)) {
            if ($num = $res->fetchRow()) {
                $this->num_results = $num['num_result'];
                $sql .= $this->limit();
                if ($res = c5t_database::query($sql, $data)) {
                    return $res;
                }
            }
        }
    }

//------------------------------------------------------------------------------




    /**
     * Returns the offset number of the next result page
     *
     * @param total
     * @return int
     */
    function previous()
    {
        $start = $this->valid_offset();
        $limit = $this->setup['limit'];
        if ($start > 0) {
            $offset = ($start - $limit < 0) ? 0 : ($start - $limit);
            $page = ($offset/$limit <= 0) ? 1 : $offset/$limit + 1;
            $this->previous_page_number = $page;
            c5t_query::add_element('page', $page, 'previous');
            return 2;
        }
        return 1;
    }

//------------------------------------------------------------------------------




    /**
     * Returns the offset number of the previous result page
     *
     * @param total
     * @return int
     */
    function next()
    {
        $start = $this->valid_offset();   
        $limit = $this->setup['limit'];         
        if ($start + $limit < $this->num_results) {
            $offset = $start + $limit;
            $page = ($offset/$limit <= 0) ? 1 : ($offset/$limit + 1);
            $this->next_page_number = $page;
            c5t_query::add_element('page', $page, 'next');
            return 2;
        }
        return 1;
    }

//------------------------------------------------------------------------------




    /**
     * Returns Get, Post or Cookie vars
     *
     */
    function c5t_gpc_vars($variable, $default = '')
    {   
        global $c5t;
        if (isset($c5t['_get'][$variable])) {
            return $c5t['_get'][$variable];
        }  
        if (isset($c5t['_post'][$variable])) {
            return $c5t['_post'][$variable];
        }  
        if (isset($c5t['_cookie'][$variable])) {
            return $c5t['_cookie'][$variable];
        }  
        if ($default != '') {
            return $default;
        }
    }

//------------------------------------------------------------------------------




    /**
     * Get search field list
     *
     */
    function search_field_list()
    {   
        return $this->search_field_list;
    }

//------------------------------------------------------------------------------




    /**
     * Get default values
     *
     */
    function default_values()
    {   
        $default = array(
                    'group_list'    => $this->setup['group_value'],
                    'search_field'  => $this->setup['search_field'],
                    'search_query'  => $this->setup['search_query']
                    );
        return $default;
    }

//------------------------------------------------------------------------------
    
    
    
} // End of class


?>
