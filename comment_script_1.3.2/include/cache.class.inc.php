<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




class c5t_cache
{




    /**
     * 
     */     
    function &get_instance()
    {
        static $obj;
    
        if (!is_object($obj)) {
            include 'Cache/Lite.php';
            $obj = new Cache_Lite($options);
        }
        return $obj;
    }

// -----------------------------------------------------------------------------




    /**
     * 
     */
    function get()
    {
        $cache = c5t_cache::get_instance();
    }

// -----------------------------------------------------------------------------




    /**
     * 
     */
    function save()
    {
    }

// -----------------------------------------------------------------------------



} // End of class








?>
