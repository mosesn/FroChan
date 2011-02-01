<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('C5T_ROOT', '../');

$c5t_detail_template                = 'admin_start.tpl.html';

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';






// Start output handling
$out = new c5t_output($c5t_detail_template);

if (c5t_gpc_vars('utf8encode') == 1) {

    $paths = array(
                '../language/',
                '../module/',
                );

    while (list($key, $val) = each($paths))
    {
        if (is_dir($val)) {
            if ($handle = opendir($val)) {
                while (false !== ($file = readdir($handle)))
                {
                    if (strpos($file, '.') === 0) {
                        continue;
                    }
                    if (strpos($file, 'gentlesource_module') !== false) {
                        $paths[] = $val . $file . '/language/';
                    }
                    if (strpos($file, 'language.') === false) {
                        continue;
                    }
                    if (!is_dir($val . 'utf-8/')) {
                        mkdir($val . 'utf-8');
                    }
                    $content = preg_replace("/'txt_charset'(.*?)=> '(.*?)'/", "'txt_charset' => 'utf-8'", file_get_contents($val . $file));
                    file_put_contents($val . 'utf-8/' . $file, c5t_utf8_encode($content, c5t_get_language_file_charset($val . $file)));
                    echo nl2br("$val$file\n");
                }
                closedir($handle);
            }
        }
    }
}




// Output
$out->finish();






?>
