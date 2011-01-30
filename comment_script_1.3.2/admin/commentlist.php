<?php

/**
 * GentleSource - Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


define('C5T_ROOT', '../');


// Settings
$c5t_detail_template                = 'admin_comment_list.tpl.html';
$message                            = array();

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';


// Handle and validate form
require_once 'HTML/QuickForm.php';


// Start form handler
$identifier_form = new HTML_QuickForm('commentlist', 'POST');

$identifier_form->setDefaults(array('do' => 'rename'));


// Get form configuration
require 'identifier_form.inc.php';


// Start output handling
$out = new c5t_output($c5t_detail_template);


// Get identifier data from table
require 'identifier.class.inc.php';
$identifier = new c5t_identifier;
if ($identifier_id = c5t_gpc_vars('i')
        and $identifier_data = $identifier->get($identifier_id)) {
    $out->assign('identifier_data', $identifier_data);
    $identifier_form->setDefaults($identifier_data);
    $identifier_form->setDefaults(array('i' => $identifier_id));
}


// Delete comment
$delete_confirmation = array('dialogue' => 0);
if ($comment_id = c5t_gpc_vars('c')
        and c5t_gpc_vars('do') == 'd') {
    $delete_confirmation = array(
                            'dialogue'      => 1,
                            'identifier_id' => $identifier_id,
                            'comment_id'    => $comment_id,
                            'anchor'        => c5t_gpc_vars('p')
                            );
}
$out->assign('delete_confirmation', $delete_confirmation);
if ($comment_id = c5t_gpc_vars('c')
        and c5t_gpc_vars('do') == 'dc') {
    require 'comment.class.inc.php';
    if (c5t_comment::delete($comment_id)) {
        $c5t['message'][] = $c5t['text']['txt_delete_comment_successful'];
    }
}

// Delete comment list
$delete_confirmation = array('dialogue' => 0);
if (c5t_gpc_vars('submit_delete_comments')) {
    $delete_confirmation = array(
                            'dialogue'      => 1,
                            'list'   =>     c5t_gpc_vars('delete_comment'),
                            );
}
$out->assign('delete_list_confirmation', $delete_confirmation);
if (c5t_gpc_vars('submit_delete_comments_c')) {
    require 'comment.class.inc.php';
    if (c5t_comment::delete_list(c5t_gpc_vars('delete_comment'))) {
        $c5t['message'][] = $c5t['text']['txt_delete_comments_successful'];
    }
}

// Approve/disapprove comment
if ($comment_id = c5t_gpc_vars('c')
        and c5t_gpc_vars('do') == 'a') {
    require 'comment.class.inc.php';
    if (c5t_comment::status($comment_id, $c5t['comment_status']['approved'])) {
    }
}
if ($comment_id = c5t_gpc_vars('c')
        and c5t_gpc_vars('do') == 'da') {
    require 'comment.class.inc.php';
    if (c5t_comment::status($comment_id, $c5t['comment_status']['unapproved'])) {
    }
}


// Get comment data
require 'commentlistadmin.class.inc.php';
$comment = new c5t_comment_list(true, array('identifier' => 'commentlistadmin'));
if ($identifier_id = c5t_gpc_vars('i')
        and $comment_data = $comment->get_list($identifier_id)) {
    $out->assign('comment_list', $comment_data);
}
$out->assign($comment->values());




// Validate form
$show_form = 'yes';
if (c5t_gpc_vars('save')
        and $identifier_id = c5t_gpc_vars('i')
        and $identifier_form->validate()) {
    $show_form = 'no';
    // Write data
    if ($identifier->put($identifier_id)) {
        $c5t['message'][] = $c5t['text']['txt_update_data_successful'];
    }
}

$show_rename_form = 'false';
if (c5t_gpc_vars('do') == 'rename') {
    $show_rename_form = 'yes';
    $show_form = 'no';
}
if (c5t_gpc_vars('do') == 'rename'
        and c5t_gpc_vars('rename')) {

    if ($identifier->change(c5t_gpc_vars('i'))) {
        $c5t['message'][] = $c5t['text']['txt_update_data_successful'];
    }
}
$out->assign('show_rename_form', $show_rename_form);
$out->assign('show_form', $show_form);




require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$identifier_renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

$identifier_form->accept($identifier_renderer);


// Assign array with form data
$out->assign('identifier_form', $identifier_renderer->toArray());



// Search form
require_once 'HTML/QuickForm.php';


// Start form handler
$form = new HTML_QuickForm('form', 'POST');


// Get list form elements (grouping, sorting, searching)
$search_field_list  = $comment->search_field_list();


// Get form configuration
require 'list_form.inc.php';
$form->addElement('hidden', 'i');
$form->setConstants(array('i' => c5t_gpc_vars('i')));
$form->setDefaults($comment->default_values());


require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray());







// Output
$out->assign('status_approved', $c5t['comment_status']['approved']);
$out->finish();






?>
