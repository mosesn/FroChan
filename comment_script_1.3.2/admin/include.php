<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('C5T_ROOT', '../');

$c5t_detail_template                = 'include.tpl.html';

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';



// Start output handling
$out = new c5t_output($c5t_detail_template);



// Download include.php
if (isset($c5t['_post']['download_include'])) {
    $filename = 'include_php.php.tpl';
    if ($c5t['_post']['include_type'] == 'ssi') {
        $filename = 'include.php.tpl';
    }
    require_once 'download.class.inc.php';
    if (false == $c5t['demo_mode']) {
        $script_server_path = str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF'])));
    } else {
        $script_server_path = '/example/path/to/comment/script/';
    }
    $include_data = array('server_script_path' => str_replace('admin', '', str_replace('\\', '/', $script_server_path)));
    $content = join('', file(C5T_ROOT . 'include/' . $filename));
    reset($include_data);
    foreach ($include_data AS $marker => $value)
    {
        $content = str_replace('{$' . $marker . '}', $value, $content);
    }
    c5t_download::send($content, 'include.php', DH_DATA);
    exit;
}



// Output
$out->finish();






?>
