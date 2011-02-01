<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


define('C5T_ROOT', '../');


// Settings
$c5t_detail_template                = 'admin_identifier_list.tpl.html';
$message                            = array();

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';



// Start output handling
$out = new c5t_output($c5t_detail_template);


// Start identifier handling
$identifier = new c5t_identifier_list(true, array('limit' => 25, 'identifier' => 'pagelistadmin'));


// Delete identifier
$delete_confirmation = array('dialogue' => 0);
if ($identifier_id = c5t_gpc_vars('i')
        and c5t_gpc_vars('do') == 'd') {
    $delete_confirmation = array(
                            'dialogue'      => 1,
                            'identifier_id' => $identifier_id
                            );
}
$out->assign('delete_confirmation', $delete_confirmation);
if ($identifier_id = c5t_gpc_vars('i')
        and c5t_gpc_vars('do') == 'dc') {
    if (false == $c5t['demo_mode']) {
        require 'identifier.class.inc.php';
        if (c5t_identifier::delete($identifier_id)) {
            $c5t['message'][] = $c5t['text']['txt_delete_identifier_successful'];
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------



// Get data from table
if ($identifier_data = $identifier->get_list()) {
//    array_walk($identifier_data, 'c5t_clean_output');
    $out->assign('identifier_list', $identifier_data);
}
$out->assign($identifier->values());


// Handle and validate form
require_once 'HTML/QuickForm.php';


// Start form handler
$form = new HTML_QuickForm('form', 'POST');


// Get list form elements (grouping, sorting, searching)
$search_field_list  = $identifier->search_field_list();


// Get form configuration
require 'list_form.inc.php';

$form->setDefaults($identifier->default_values());


require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray());


// Show search form
$display_advanced = false;
if (c5t_gpc_vars('search')) {
    $display_advanced = true;
}
$out->assign('display_advanced', $display_advanced);


// Output
$out->finish();






?>
