<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('C5T_ROOT', '../');
define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);

$c5t_detail_template    = 'configuration.tpl.html';

// -----------------------------------------------------------------------------




// Include
require C5T_ROOT . 'include/core.inc.php';

// Start output handling
$out = new c5t_output($c5t_detail_template);


$out->assign('module_list', c5t_module::module_list());

// -----------------------------------------------------------------------------




// Output
$out->assign('display_setting_navigation', true);
$out->finish();






?>
