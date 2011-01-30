<?php
 
/**
 * GentleSource Comment Script -  time.class.inc.php
 * 
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 * 
 */




/**
 * Date and time handler
 */
class c5t_time
{




    /**
     * Current timestamp
     */    
    function current_timestamp()
    {
        global $c5t;
        return mktime() + ($c5t['time_difference'] * 60);
    }
    
//------------------------------------------------------------------------------




    /**
     * Current day (00:00) as timestamp
     */    
    function current_day()
    {
        $timestamp = c5t_time::current_timestamp();
        $day = mktime(  0, 
                        0, 
                        0, 
                        date('m', $timestamp),
                        date('d', $timestamp),
                        date('Y', $timestamp));
        return $day;
    }
    
//------------------------------------------------------------------------------




    /**
     * Formats timestamp to date
     */    
    function format_date($timestamp = 0)
    {
        global $c5t;

        if ($timestamp <= 0) {
            return '';
        }
        
        $date = date($c5t['text']['txt_date_format'], $timestamp);
        return $date;
    }
    
//------------------------------------------------------------------------------




    /**
     * Formats timestamp to time
     */    
    function format_time($timestamp = 0)
    {
        global $c5t;

        if ($timestamp <= 0) {
            return '';
        }
        
        $date = date($c5t['text']['txt_time_format'], $timestamp);
        return $date;
    }
    
//------------------------------------------------------------------------------




    /**
     * Get days in seconds
     */    
    function days_to_seconds($days)
    {
        return ($days * 24 * 60 * 60);
    }
    
//------------------------------------------------------------------------------





}








?>
