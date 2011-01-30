<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



define('C5T_ROOT', '../');



// Settings
$c5t_detail_template                = 'setting.tpl.html';

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';



// Start output handling
$out = new c5t_output($c5t_detail_template);


// Handle and validate form
require_once 'HTML/QuickForm.php';


// Start form handler
$form = new HTML_QuickForm('account', 'POST');


// Get form configuration
require 'setting_form.inc.php';


// Validate form
$message = array();
if ($form->validate()) {

    // Write data as settings
    if (false == $c5t['demo_mode']) {
        foreach ($c5t['_post'] AS $name => $value)
        {
            if (!in_array($name, $c5t['setting_names'])) {
                continue;
            }
            c5t_setting::write($name, $value);
        }
        $c5t['message'][] = $c5t['text']['txt_update_data_successful'];
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}


// Get setting data
$settings = c5t_setting::read_all();
$input_data = array_merge($c5t, $settings);
$form->setDefaults($input_data);


require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray());


// Current script server path
if (false == $c5t['demo_mode']) {
    $script_server_path = str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF'])));
} else {
    $script_server_path = '/example/path/to/comment/script/';
}
$out->assign('script_server_path', $script_server_path);

// -----------------------------------------------------------------------------



// Module list
$out->assign('module_list', c5t_module::module_list());

// -----------------------------------------------------------------------------


// Output
$out->assign('display_setting_navigation', true);
$out->finish();






?>
