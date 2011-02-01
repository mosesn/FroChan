<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



define('C5T_ROOT', '../');



// Settings
$c5t_detail_template                = 'admin_account.tpl.html';

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
require 'account_form.inc.php';


// Validate form
$show_form  = 'yes';
$message    = array();
if ($form->validate()) {
    $show_form = 'no';
    
    // Write data as settings
    if (false == $c5t['demo_mode']) {
        $arr = array(   'login'     => $c5t['_post']['login_name'],
                        'email'     => $c5t['_post']['email'],
                        'password'  => md5($c5t['_post']['password'])
                        );
        $ser = serialize($arr);
        c5t_setting::write('administration_login', $ser);
        $c5t['message'][] = $c5t['text']['txt_update_data_successful'];
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
} else {
    if (sizeof($c5t['_post']) > 0) {
        $c5t['message'][] = $c5t['text']['txt_fill_out_required'];
    }
}


// Get login data
$ser = c5t_setting::read('administration_login');
$login_data = unserialize($ser['setting_value']);
$input_data = array('login_name'    => $login_data['login'],
                    'email'         => $login_data['email']);
$form->setDefaults($input_data);



require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray()); 




// Output
$out->assign('show_form', $show_form);
$out->assign('message', $message);
$out->finish();






?>
