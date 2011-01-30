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

$c5t_detail_template    = 'module.tpl.html';

// -----------------------------------------------------------------------------




// Include
require C5T_ROOT . 'include/core.inc.php';

$data = array('module' => c5t_gpc_vars('m'));
c5t_module::call_module('module_send_file', $data, $c5t['module_additional']);



// Start output handling
$out = new c5t_output($c5t_detail_template);

// -----------------------------------------------------------------------------



// Install module
if ($module = c5t_gpc_vars('i')) {
    if (false == $c5t['demo_mode']) {
        if (c5t_module::install($module)) {
            $c5t['message'][] = $c5t['text']['txt_install_module_successful'];
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------



// Uninstall module
if ($module = c5t_gpc_vars('u') and c5t_gpc_vars('c') == 'y') {
    if (false == $c5t['demo_mode']) {
        if (c5t_module::uninstall($module)) {
            $c5t['message'][] = $c5t['text']['txt_uninstall_module_successful'];
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}
$delete_confirmation = array('dialogue' => 0);
if ($module = c5t_gpc_vars('u') and c5t_gpc_vars('c') != 'y') {
    if (false == $c5t['demo_mode']) {
        $delete_confirmation = array(
                                'dialogue'  => 1,
                                'module'    => $module
                                );

    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}
$out->assign('delete_confirmation', $delete_confirmation);

// -----------------------------------------------------------------------------



// Module order
if ($module = c5t_gpc_vars('o') and $direction = c5t_gpc_vars('d')) {
    if (false == $c5t['demo_mode']) {
        c5t_module::order($module, $direction);
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------




$display_form = false;
if ($module = c5t_gpc_vars('m')) {
//    $out->assign('administration_form', c5t_module::administration($module));
    $module_result = c5t_module::administration($module);
//    c5t_print_a($module_result['module_form']);
    $out->assign('module_message',      $module_result['module_form']['module_message']);
    //$out->assign('administration_form', array_merge($module_result['module_form']['elements'], $module_result['module_form']['module_additional']));
    $out->assign('administration_form', $module_result['module_form']['elements']);
    $out->assign('form_attributes',     $module_result['module_form']['attributes']);
    $out->assign('module_title',        $module_result['module_title']);
    $out->assign('module_description',  $module_result['module_description']);
    $out->assign('module_name',         $module_result['module_name']);
    $display_form = true;
}

// -----------------------------------------------------------------------------



// List all installed modules
$module_list = c5t_module::module_list();

// -----------------------------------------------------------------------------



// List all available modules
function sort_modules($a, $b)
{
    $x = $a['installed'];
    $y = $b['installed'];

    if ($x == $y) return 0;
    return ($x < $y) ? 1 : -1;
}
if (!c5t_gpc_vars('m')) {
    $available_modules = c5t_module::available_module_list();
//    usort($available_modules, 'sort_modules');
    $out->assign('available_modules', $available_modules);
}

// -----------------------------------------------------------------------------




// Output
$out->assign('module_list', $module_list);
$out->assign('display_setting_navigation', true);
$out->assign('display_form', $display_form);
$out->finish();






?>
