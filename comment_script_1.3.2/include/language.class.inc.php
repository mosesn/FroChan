<?php

/**
 * GentleSource - language.class.inc.php
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Class name and unique identifier for $GLOBALS array that contains the
 * instance
 */
define('LANGUAGE_CLASS', 'c5t_language');
define('LANGUAGE_INSTANCE', 'c5t_language_instance');


/**
 * Handle file names and pramameters
 *
 * @access public
 */
class c5t_language
{

    /**
     * @var array Query string values
     * @access private
     */
    var $language;

    //--------------------------------------------------------------------------




    /**
     * Constructor
     *
     * @access private
     */
    function c5t_language()
    {
    }

    //--------------------------------------------------------------------------




    /**
     * Create single instance
     *
     */
    function &get_instance()
    {
        if (!isset($GLOBALS[LANGUAGE_INSTANCE])) {
            $GLOBALS[LANGUAGE_INSTANCE] = new c5t_language;
        }

        return $GLOBALS[LANGUAGE_INSTANCE];
    }

    //--------------------------------------------------------------------------




    /**
     *
     */
    function get($default)
    {
        global $c5t;
        $ref =& c5t_language::get_instance();
        $list = array();
        $redirect = false;

        // From post
        if (isset($c5t['_post']['c5t_language_selector']) and
            $c5t['_post']['c5t_language_selector'] != '') {
            $list[] = $c5t['_post']['c5t_language_selector'];
            $redirect = true;
        }

        // From get
        if (isset($c5t['_get']['c5t_language_selector']) and
            $c5t['_get']['c5t_language_selector'] != '') {
            $list[] = $c5t['_get']['c5t_language_selector'];
            $redirect = true;
        }

        // From cookie
        if (isset($c5t['_cookie'][$c5t['language_cookie_name']]) and
            $c5t['_cookie'][$c5t['language_cookie_name']] != '') {
            $list[] = $c5t['_cookie'][$c5t['language_cookie_name']];
        }

        // From domain
        $tld = substr($_SERVER['SERVER_NAME'], strrpos($_SERVER['SERVER_NAME'], '.') + 1);
        if (array_key_exists($tld, $c5t['domain_language'])) {
            $list[] = $c5t['domain_language'][$tld];
        }


        // From browser environment
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($accept AS $key => $val)
            {
                if ($pos = strpos($val, ';') and $pos !== 0) {
                    $val = substr($val, 0, $pos);
                }
                $list[] = trim($val);
            }
        }


        $new_list = array();
        $language_folder = $c5t['language_directory'];

        // Use utf-8 folder if it exists
        if ($c5t['use_utf8'] == 'Y') {
            $language_folder = $c5t['language_directory_utf8'];
        }
        foreach ($list AS $key => $val)
        {
            if (!array_key_exists($val, $c5t['available_languages'])) {
                continue;
            }
            if (!is_file(C5T_ROOT . $language_folder . 'language.' . $val . '.php')) {
                // Go back to default language folder if language does not exists in utf-8 folder
                if (!is_file(C5T_ROOT . $c5t['language_directory'] . 'language.' . $val . '.php')) {
                    continue;
                }
            }
            $new_list[] = $val;
        }
        if (sizeof($new_list) > 0) {
            $new_language = $new_list[0];
        } else {
//            $language_setting = c5t_setting::read('default_language');
            $new_language = $default;
        }

        if (!isset($c5t['_cookie'][$c5t['language_cookie_name']])
                or $c5t['_cookie'][$c5t['language_cookie_name']] != $new_language) {

            $ref->set($new_language);
            $ref->language = $new_language;
        }

        if (true == $redirect) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . urldecode(trim(c5t_gpc_vars('r'))));
            exit;
        }

        return $new_language;
    }

    //--------------------------------------------------------------------------




    /**
     *
     */
    function set($language)
    {
        global $c5t;
        $ref =& c5t_language::get_instance();

        // Write cookie
        setcookie(  $c5t['language_cookie_name'],
                    $language,
                    time()+(3600*24*360*10),
                    $c5t['cookie_path'],
                    $c5t['cookie_domain']);



//        echo   $c5t['language_cookie_name'] . ' ' .
//                    $language . ' ' .
//                    (time()+(3600*24*360*10)) . ' ' .
//                    $c5t['cookie_path'] . ' ' .
//                    $c5t['cookie_domain'];
    }

    //--------------------------------------------------------------------------




    /**
     * Load the content of a specified language file
     *
     * @access public
     * @param string $language
     * @param string $item Part of the language file
     */
    function load($language)
    {
        global $c5t;
        $text = array();

        $language_folder = $c5t['language_directory'];


        // Use utf-8 folder if it exists
        if ($c5t['use_utf8'] == 'Y') {
            $language_folder = $c5t['language_directory_utf8'];
        }

        // Go back to default language folder if language does not exists in utf-8 folder
        if (!is_file(C5T_ROOT . $language_folder . 'language.' . $language . '.php')
                and is_file(C5T_ROOT . $c5t['language_directory'] . 'language.' . $language . '.php')) {
            $language_folder = $c5t['language_directory'];
        }

        $path = C5T_ROOT . $language_folder . 'language.' . $language . '.php';

        include $path;
        if (is_file($path)) {
            include $path;
        }

        return $text;
    }

    //--------------------------------------------------------------------------





} // End of class
?>