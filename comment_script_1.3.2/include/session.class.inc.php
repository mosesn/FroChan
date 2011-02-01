<?php

/**
 * GentleSource Session -  session.class.inc.php
 *
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 *
 */




/**
 * Session handler
 */
class c5t_session
{




    var $session_vars;
    var $session_vars_name;   // Name of the session array that contains all stored data

// -----------------------------------------------------------------------------




    /**
     * Constructor
     *
     * @access private
     */
    function c5t_session()
    {
        $this->session_vars_name = 'MY_SESS';
    }

// -----------------------------------------------------------------------------




    /**
     * Set session vars name
     *
     * @access private
     */
    function sess_set_vars_name($name)
    {
        $this->session_vars_name = $name;
    }

// -----------------------------------------------------------------------------




    /**
     * Start session
     *
     * @access private
     */
    function sess_start($sess_id = 0, $sess_name = '')
    {
        if (session_id() == '') {
            ini_set('session.use_trans_sid', 0);
            session_set_cookie_params(false, '/');
            session_start();
        }

        if (!isset($_SESSION[$this->session_vars_name])) {
            $_SESSION[$this->session_vars_name] = array();
        }

        $this->session_vars = $_SESSION[$this->session_vars_name];
    }

// -----------------------------------------------------------------------------




    /**
     * Register vars
     *
     * @access private
     */
    function sess_register($data = array())
    {
        if (sizeof($data) <= 0) {
            return false;
        }

        while (list($key, $val) = each($data))
        {
            $_SESSION[$this->session_vars_name][$key] = $val;
            $this->session_vars[$key] = $val;
        }
        return $this->session_vars;
    }

// -----------------------------------------------------------------------------




    /**
     * Stop session
     *
     * @access private
     */
    function sess_stop($sess_id = 0, $sess_name = '')
    {
        unset($this->session_vars);
        unset($_SESSION[$this->session_vars_name]);
        if (isset($GLOBALS[$this->session_vars_name])) {
            unset($GLOBALS[$this->session_vars_name]);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Start session
     *
     * @access private
     */
    function start()
    {
        global $c5t;

        $path = C5T_ROOT . $c5t['session_directory'];
        if ($c5t['use_session_directory'] == true
                and !is_dir($path)) {
            mkdir($path);
            $htcontent = "deny from all";
            file_put_contents($path . '.htaccess', $htcontent);
        }
        if ($c5t['use_session_directory'] == true) {
            ini_set('session.save_path', $path);
        }

        if (!isset($GLOBALS['session_object'])) {
            $sess = new c5t_session();
            $sess->sess_set_vars_name($c5t['session_vars_name']);
            $sess->sess_start();

            $GLOBALS['session_object'] = $sess;
        }
        if (isset($GLOBALS['session_object'])) {
            return $GLOBALS['session_object'];
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Add values to session
     *
     * @access public
     * @param array $data Array of data to be
     */
    function add($data)
    {
        $sess = c5t_session::start();
        $sess->sess_register($data);
        $GLOBALS['session_object'] = $sess;
    }

//------------------------------------------------------------------------------




    /**
     * Get session data
     *
     * @access public
     * @return bool|array Returns session data if session exists or false
     */
    function get($value = null)
    {
        $sess = c5t_session::start();

        if ($value == null) {
            return $sess->session_vars;
        }
        if (isset($sess->session_vars[$value])) {
            return $sess->session_vars[$value];
        }
    }

//------------------------------------------------------------------------------




    /**
     * Destroy session
     *
     * @access public
     */
    function destroy()
    {
        $sess = c5t_session::start();
        $sess->sess_stop();
    }

//------------------------------------------------------------------------------









}






?>
