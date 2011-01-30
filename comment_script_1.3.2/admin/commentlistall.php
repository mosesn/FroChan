<?php

/** 
 * GentleSource - Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


define('C5T_ROOT', '../');


// Settings
$c5t_detail_template                = 'admin_comment_list_all.tpl.html';
$message                            = array();

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);


// Include
require C5T_ROOT . 'include/core.inc.php';


// Start output handling
$out = new c5t_output($c5t_detail_template);


// Delete comment
$delete_confirmation = array('dialogue' => 0);
if ($comment_id = c5t_gpc_vars('c')
        and c5t_gpc_vars('do') == 'd') {
    $delete_confirmation = array(
                            'dialogue'      => 1,
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
        $c5t['message'][] = $c5t['text']['txt_delete_comment_successful'];
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
$comment = new c5t_comment_list(true, array('limit' => 50, 'identifier' => 'commentlistalladmin'));
if ($comment_data = $comment->get_list_all()) {
    $out->assign('comment_list', $comment_data);
}
$out->assign($comment->values());





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
