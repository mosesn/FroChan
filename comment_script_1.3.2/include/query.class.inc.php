<?php
 
/**
 * Member Tool -  query.class.inc.php
 * 
 * @package		Query
 * @access		public
 * 
 * @author		Ralf Stadtaus, <info@stadtaus.com>
 * @copyright	(C) Ralf Stadtaus , {@link http://www.stadtaus.com/}
 * @version		0.1
 * 
 */




/**
 * Class name and unique identifier for $GLOBALS array that contains the
 * instance
 */
define('QUERY_CLASS', 'query');
define('QUERY_INSTANCE', 'query_instance');


/**
 * Handle file names and pramameters
 * 
 * @access public
 */
class c5t_query
{
    
    /**
     * @var string Random characters for intern parameter label
     * @access private
     */
    var $intern;
    
    /**
     * @var array Query string values
     * @access private
     */
    var $query;
    
    /**
     * @var string Hidden form field template
     * @access private
     */
    var $tpl;

    //--------------------------------------------------------------------------




    /**
     * Constructor
     * 
     * @access private
     */
    function c5t_query()
    {                                                 
        $pool  = "abcdefghijklmnopqrstuvwxyz";
        $pool .= "0123456789"; 
  
        srand ((double)microtime()*1000000);
  
        for($i = 0; $i < 32; $i++)
        {
            $this->intern .= substr($pool,(rand()%(strlen ($pool))), 1);
        }
        
        $this->query[$this->intern]['query'] = array();
                          
        $this->tpl   = '<input type="hidden" name="{name}" value="{value}" />';
    }
  
    //--------------------------------------------------------------------------




    /**
     * Create single instance
     * 
     */
    function &get_instance()
    {
        if (!isset($GLOBALS[QUERY_INSTANCE])) {
            $GLOBALS[QUERY_INSTANCE] = new c5t_query;
        }
        
        return $GLOBALS[QUERY_INSTANCE];
    }
  
    //--------------------------------------------------------------------------




    /**
     * Assign file name to query 
     * 
     * @access public
     * @param string $file File name
     * @param string $label Identifier
     */
    function add_file($file, $label, $glue = '?')
    {
        $ref =& c5t_query::get_instance();
                
        $ref->query[$label]['file'] = array('name' => $file, 'glue' => $glue);        
    }
  
    //--------------------------------------------------------------------------




    /**
     * Add element to parameter string
     * 
     * @access public
     * @param string $name Variable name
     * @param string $value Variable value
     * @param string $label Identifier
     * @param string $glue Tie between variable name and value
     */
    function add_element($name, $value, $label = '', $glue = '=')
    {
        $ref =& c5t_query::get_instance();
        
        if ($label == '') {
            $label = $ref->intern;
        }

        $ref->query[$label]['query'][] = array( 'name'  => $name, 
                                                'glue'  => $glue, 
                                                'value' => rawurlencode($value));
        return $ref->query;
    }
  
    //--------------------------------------------------------------------------




    /**
     * Get parameter string for URI
     * 
     * @access public
     * @param string $label Identifier
     * @param string $glue Tie between parameter elements
     */
    function get_string($label = '', $glue = '&amp;')
    {   
        $ref =& c5t_query::get_instance();
        $output = '';

        // Intern label        
        if ($label == ''){
            $intern_query = $ref->query[$ref->intern]['query'];
            array_walk($intern_query, create_function('&$intern_query', '$intern_query = join("", $intern_query);'));
            $output .= join($glue, $intern_query);
        }
        
        // Extern label
        if ($label != '' and isset($ref->query[$label])) {
            $query = $ref->query[$label]['query'];
            array_walk($query, create_function('&$query', '$query = join("", $query);'));
            $output .= join($glue, $query);
        }
        
        // File associated query
        if (isset($ref->query[$label]['file'])) {
            $file    = $ref->query[$label]['file'];
            $output .= $file['name'] . $file['glue'] . $output; 
        }
        
        return $output;
    }
  
    //--------------------------------------------------------------------------




    /**
     * Get all query strings
     * 
     * @access public
     * @param string $label Identifier
     * @param string $glue Tie between parameter elements
     */
    function get_string_array($prefix = '', $glue = '&amp;')
    {   
        $ref =& c5t_query::get_instance();
        $output[$prefix . 'all'] = '';
        while (list($label, $values) = each($ref->query))
        {
            $tmp = $ref->get_string($label, $glue);
            
            if ($label == $ref->intern) {
                $label = 'all';
            }
            
            $output[$prefix . $label] = $tmp;  
        }
        
        if (isset($output)) {
			return $output;
		}
    }
  
    //--------------------------------------------------------------------------




    /**
     * Get hidden form fields
     * 
     * @access public
     * @param string $label Identifier
     * @param string $glue Tie between parameter elements
     */
    function get_fields($label = '')
    {   
        $ref =& c5t_query::get_instance();
        $output = '';
        

        // Intern label        
        if ($label == ''){
            $query_values = $ref->query[$ref->intern]['query'];
        }
        // Extern label
        if ($label != '' and isset($ref->query[$label])) {
            $query_values = $ref->query[$label]['query'];
        }
        
        if (!isset($query_values)) {
            return false;
        }

        while(list($key, $val) = each($query_values))
        {
            $output .= str_replace('{name}', $val['name'], str_replace('{value}', $val['value'], $ref->tpl));
        }
        return $output;
    }
  
    //--------------------------------------------------------------------------




    /**
     * Get all fields
     * 
     * @access public
     * @param string $label Identifier
     * @param string $glue Tie between parameter elements
     */
    function get_field_array($prefix = '', $glue = '&amp;')
    {   
        $ref =& c5t_query::get_instance();
        reset($ref->query);
        $output[$prefix . 'all'] = '';
        while (list($label, $values) = each($ref->query))
        {
            if ($tmp = $ref->get_fields($label, $glue)) {            
                if ($label == $ref->intern) {
                    $label = 'all';
                }
                
                $output[$prefix . $label] = $tmp;  
            }
        }
        
        if (isset($output)) {
            return $output;
        }
    }
  
    //--------------------------------------------------------------------------
    

}



 
?>
