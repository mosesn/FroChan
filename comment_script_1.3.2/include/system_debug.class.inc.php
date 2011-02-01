<?php

  /*****************************************************
  ** Title........: Debugging and error handling
  ** Filename.....: debug.class.inc.php
  ** Author.......: Ralf Stadtaus
  ** Homepage.....: http://www.stadtaus.com/
  ** Contact......: mailto:info@stadtaus.com
  ** Version......: 0.1
  ** Notes........:
  ** Last changed.:
  ** Last change..:
  *****************************************************/




/**
 * Class name and unique identifier for $GLOBALS array that contains the
 * instance
 */
define('SYSTEM_DEBUG_CLASS', 'system_debug');
define('SYSTEM_DEBUG_INSTANCE', 'system_debug_instance');


/**
 * Core class for error handling and debug messaging
 *
 * @access public
 */
class system_debug
{

    var $messages;
    var $types = array('debug', 'system', 'error');

    //--------------------------------------------------------------------------




    /**
     * Constructor
     *
     * @access private
   	 */
    function system_debug()
    {

    }

    //--------------------------------------------------------------------------




    /**
     * Create single instance
     *
   	 */
    function &get_instance()
    {
        if (!isset($GLOBALS[SYSTEM_DEBUG_INSTANCE])) {
            $GLOBALS[SYSTEM_DEBUG_INSTANCE] = new system_debug;
        }

        return $GLOBALS[SYSTEM_DEBUG_INSTANCE];
    }

    //--------------------------------------------------------------------------




    /**
     * Add system, error or debug message
     *
     * debug  = Debugging information for developer
     * error  = Error message for developer and admin
     * system = System message for enduser
     *
     * Example:
     * <code>
     * system_debug::add_message('Title', 'Message text' [,
     * 'system|error|debug']);
     * </code>
     *
     * @access public
     * @param string $title Message title
     * @param string $message Message content
     * @param string $type Message type (system, error, debug [default])
   	 */
    function add_message($title, $message, $type = 'debug', $backtrace = array())
    {
        if ($message == '') {
            return;
        }

        $deb =& system_debug::get_instance();

        if (sizeof($backtrace) > 0) {
            while (list($key, $val) = each ($backtrace))
            {
                $temp = array();
                while (list($k, $v) = each($val))
                {
                    if ($k == 'args') {
                        if (!is_array($v)) {
                            $temp[] = $k . ': ' . join('<br />', $v);
                        }
                        continue;
                    }

                    if (is_object($v)) {
                        continue;
                    }

                    $temp[] = $k . ': ' . $v;
                }

                $result[] = join('<br />', $temp);
            }
            $backtrace = $result;
        }

        if (is_array($backtrace)) {
            $joint_backtrace = join('<br /><br />', $backtrace);
        } else {
            $joint_backtrace = $backtrace;
        }

        $arr = array('title'     => $title,
                     'message'   => $message,
                     'backtrace' => $joint_backtrace);

        $deb->messages[$type][] = $arr;

    }

    //--------------------------------------------------------------------------




    /**
     * Get system, error, debug or all messages (if type empty)
     *
     * @access public
     * @param string $type Message type (system, error, debug)
   	 */
    function get_messages($type = '')
    {
        $output = array();
        $deb =& system_debug::get_instance();
        reset($deb->types);
        while (list(, $val) = each($deb->types))
        {
            if (($type == '' or $type == $val) and isset($deb->messages[$val])) {
                $output +=  $deb->messages[$val];
            }
        }

        return $output;
    }

    //--------------------------------------------------------------------------


}

?>