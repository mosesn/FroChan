<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Check and get GPC vars
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

// -----------------------------------------------------------------------------




/**
 * Format numbers to given format
 *
 * @param float $number
 * @return string
 */
function c5t_format_number($number)
{
    global $conf;

    $number = number_format($number, $conf['decimal_places'], $conf['decimals_delimiter'], $conf['thousands_delimiter']);
    return $number;
}

// -----------------------------------------------------------------------------




/**
 * Convert given number into float
 *
 * @param string $number
 * @return float
 */
function c5t_clean_number($number)
{
    global $conf;
    $pieces    = explode($conf['decimals_delimiter'], $number);
    $pieces[0] = preg_replace('#[^0-9]#', '', $pieces[0]);
    return (float) join('.', $pieces);
}

// -----------------------------------------------------------------------------




/**
 * Provide the content of a specified language file
 *
 */
function c5t_load_language($language)
{
    global $conf;
    $res = array();
    $path = $conf['language_directory'] . 'language.' . $language . 'inc.php';
    if (is_file($path)) {
        include $path;
        $res = $txt;
    }
    return $res;
}

//------------------------------------------------------------------------------




/**
 *
 */
function c5t_print_a($ar, $htmlize = 0)
{
    if ($htmlize == 1) {
        if (is_array($ar)) {
            array_walk($ar, create_function('&$ar', 'if (is_string($ar)) {$ar = htmlspecialchars($ar);}'));
        } else {
            $ar = htmlspecialchars($ar);
        }
    }

    echo '<pre>';
    print_r($ar);
    echo '</pre>';
}

//------------------------------------------------------------------------------




/**
 *
 */
function c5t_array_append()
{
    $args = func_get_args();
    $arr  = array();

    for ($i = 0; $i < count($args); $i++)
    {
        if (empty($args[$i])) {
            continue;
        }

        if (!is_array($args[$i])) {
            trigger_error('Supplied argument is not an array', E_USER_NOTICE);
        }

        while (list($key, $val) = each($args[$i]))
        {
            $arr[$key] = $val;
        }
    }
    return $arr;
}

//------------------------------------------------------------------------------






// HTML entities for input
function c5t_entity_input(&$value)
{
    if (is_array($value)) {
        array_walk($value, 'c5t_entity_input');
        return;
    }
//    $value = htmlentities($value);
    $value = strip_tags($value, '<img>');
}

// Strip tags from output
function c5t_escape_output(&$value)
{
    global $c5t;

    if (is_array($value)) {
        array_walk($value, 'c5t_escape_output');
        return;
    }
    $value = strip_tags($value, $c5t['output_ignore_tags']);
    if ($c5t['output_htmlentities'] == true) {
        $value = htmlentities($value, ENT_QUOTES, $c5t['text']['txt_charset']);
    }
}




// Clean input
function c5t_clean_input(&$value)
{
    if (is_array($value)) {
        array_walk($value, 'c5t_clean_input');
        return;
    }

    if (ini_get('magic_quotes_gpc')) {
        $value = stripslashes($value);
    }
    $value = addslashes($value);
}




// Clean output
function c5t_clean_output(&$value)
{
    if (is_array($value)) {
        array_walk($value, 'c5t_clean_output');
        return;
    }
    $value = stripslashes($value);
}




// Unset all global variables
function c5t_unset_globals()
{
    if (ini_get('register_globals')) {
        foreach ($_REQUEST as $k => $v) {
            unset($GLOBALS[$k]);
        }
    }
}




/**
 * Create random string
 *
 */
function c5t_create_random($length, $pool = '')
{
    $random = '';

    if (empty($pool)) {
        $pool    = 'abcdefghkmnpqrstuvwxyz';
        $pool   .= '23456789';
    }

    srand ((double)microtime()*1000000);

    for($i = 0; $i < $length; $i++)
    {
        $random .= substr($pool,(rand()%(strlen ($pool))), 1);
    }

    return $random;
}




if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $content, $flags = 0) {
        if (!($file = fopen($filename, ($flags & 1) ? 'a' : 'w'))) {
            return false;
        }
        $n = fwrite($file, $content);
        fclose($file);
        return $n ? $n : false;
    }
}





/**
 * Request URI
 *
 * Either use it from the environment or create it
 */
function c5t_request_uri()
{
    if (isset($_SERVER['REQUEST_URI'])
            and $_SERVER['REQUEST_URI'] != '') {
        return $_SERVER['REQUEST_URI'];
    }


    $path = '';
    if (isset($_SERVER['PATH_INFO'])
            and $_SERVER['PATH_INFO'] != '') {
        $path = $_SERVER['PATH_INFO'];
    }

    $query = '';
    if (isset($_SERVER['QUERY_STRING'])
            and $_SERVER['QUERY_STRING'] != '') {
        $query = '?' . $_SERVER['QUERY_STRING'];
    }

    if (isset($_SERVER['SCRIPT_NAME'])
            and $_SERVER['SCRIPT_NAME'] != '') {
        return $_SERVER['SCRIPT_NAME'] . $path . $query;
    }

    if (isset($_SERVER['PHP_SELF'])
            and $_SERVER['PHP_SELF'] != '') {
        return $_SERVER['PHP_SELF'] . $query;
    }

    if (isset($_SERVER['SCRIPT_FILENAME'])
            and $_SERVER['SCRIPT_FILENAME'] != ''
            and isset($_SERVER['DOCUMENT_ROOT'])) {
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']) . $path . $query;
    }

    return false;
}







// UTF-8 encode
function c5t_utf8_encode($value, $charset = null)
{
    global $c5t;

    if ($charset == null) {
        $charset = $c5t['text']['txt_charset'];
    }
    $encoded = false;
    if (function_exists('mb_convert_encoding')) {
        $encoded = mb_convert_encoding($value, 'UTF-8', $charset);
    }

    if (function_exists('iconv')) {
        $encoded = iconv($charset, 'UTF-8', $value);
    }

    if ($encoded == false) {
        $encoded = utf8_encode($value);
    }
    return $encoded;
}

function c5t_get_language_file_charset($file)
{
    include $file;
    return $text['txt_charset'];
}

function c5t_set_permanent_cookie($name, $value)
{
    global $c5t;
    setcookie(
        $name,
        $value,
        time()+(3600*24*360*10),
        $c5t['cookie_path'],
        $c5t['cookie_domain']
        );
}




?>
