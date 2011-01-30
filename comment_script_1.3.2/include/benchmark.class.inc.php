<?php
 
/**
 * GentleSource -  benchmark.class.inc.php
 * 
 * @package		Debug
 * @author      Ralf Stadtaus, <info@stadtaus.com>
 * @copyright   (C) Ralf Stadtaus , {@link http://www.stadtaus.com/}
 * 
 */

require 'Benchmark/Timer.php';




/**
 * Class name and unique identifier for $GLOBALS array that contains the
 * instance
 */
define('BENCHMARK_INSTANCE', 'benchmark_instance');




/**
 * Manage benchmark class
 */
class c5t_benchmark
{
    
    /**
     * Benchmark object
     */
    var $ben;




    /**
     * Constructor
     * 
     */
    function c5t_benchmark()
    {
    }
  
    //--------------------------------------------------------------------------




    /**
     * Start timing
     * 
     */
    function start()
    {
        if (!isset($GLOBALS['benchmark_object'])) {
            $ben = new Benchmark_Timer;
            $ben->start();
            $GLOBALS['benchmark_object'] = $ben;
        }
    }
  
    //--------------------------------------------------------------------------




    /**
     * Stop timing
     * 
     */
    function stop()
    {
        $GLOBALS['benchmark_object']->stop();
    }
  
    //--------------------------------------------------------------------------




    /**
     * Stop timing
     * 
     * @param String $name Marker name
     */
    function mark($name)
    {
        $GLOBALS['benchmark_object']->setMarker($name);
    }
  
    //--------------------------------------------------------------------------




    /**
     * Output results
     * 
     */
    function output()
    {
        return $GLOBALS['benchmark_object']->getOutput(true);
    }
  
    //--------------------------------------------------------------------------




    /**
     * Profiling
     * 
     */
    function profiling()
    {
        return $GLOBALS['benchmark_object']->getProfiling();
    }
  
    //--------------------------------------------------------------------------
    
    
} // End of class



?>
