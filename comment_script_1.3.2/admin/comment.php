<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



define('C5T_ROOT', '../');


// Settings
$c5t_detail_template                = 'admin_comment.tpl.html';

define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);



// Include
require C5T_ROOT . 'include/core.inc.php';
require 'comment.class.inc.php';


// Start output handling
$out = new c5t_output($c5t_detail_template);


// Start comment handling
$comment = new c5t_comment;


// Handle and validate form
require_once 'HTML/QuickForm.php';


// Start form handler
$c5t_form = new HTML_QuickForm('form', 'POST', getenv('REQUEST_URI'));


// Get form configuration
require 'comment_form.inc.php';


// Get identifier data from table
require 'identifier.class.inc.php';
$identifier = new c5t_identifier;
if ($identifier_id = c5t_gpc_vars('i')
        and $identifier_data = $identifier->get($identifier_id)) {
    $out->assign('identifier_data', $identifier_data);
}


// Validate form
$message = array();
$show_form = 'yes';
if ($comment_id = c5t_gpc_vars('c')
        and $c5t_form->validate()) {
    $comment->update($comment_id);
//    $show_form = 'no';
//    $c5t_form->freeze();
        $c5t['message'][] = $c5t['text']['txt_update_data_successful'];
} else {
    if (sizeof($c5t['_post']) > 0) {
        $c5t['message'][] = $c5t['text']['txt_fill_out_required'];
    }
}
$out->assign('show_form', $show_form);


// Get comment data
if ($comment_id = c5t_gpc_vars('c')
        and $comment_data = $comment->get($comment_id)) {
    array_walk($comment_data, 'c5t_clean_output');
    $defaults = array(
        'comment_id'=> $comment_data['comment_id'],
        'name'      => $comment_data['comment_author_name'],
        'email'     => $comment_data['comment_author_email'],
        'homepage'  => $comment_data['comment_author_homepage'],
        'title'     => $comment_data['comment_title'],
        'comment'   => $comment_data['comment_text'],
        $c5t['comment_field_name']   => $comment_data['comment_text']
    );
    $c5t_form->setDefaults($defaults);
    $out->assign('comment_data', $comment_data);
}


require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

$c5t_form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray());


// Output
$out->finish();





?>
