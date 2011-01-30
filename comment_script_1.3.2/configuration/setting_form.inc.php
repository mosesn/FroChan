<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Show or hide comments
$display_comments = array();
$display_comments[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_yes'], 'Y');
$display_comments[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_no'], 'N');
$form->addGroup($display_comments, 'display_comments', $c5t['text']['txt_display_comments']);


// Show or hide comment form
$display_comment_form = array();
$display_comment_form[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_yes'], 'Y');
$display_comment_form[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_no'], 'N');
$form->addGroup($display_comment_form, 'display_comment_form', $c5t['text']['txt_display_comment_form']);


// Show or hide turn off messages
$turn_off_messages = array();
$turn_off_messages[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_yes'], 'Y');
$turn_off_messages[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_no'], 'N');
$form->addGroup($turn_off_messages, 'display_turn_off_messages', $c5t['text']['txt_display_turn_off_messages']);

 

// Enable page registration
$page_registration = array();
$page_registration[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_yes'], 'Y');
$page_registration[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_no'], 'N');
$form->addGroup($page_registration, 'page_registration', $c5t['text']['txt_enable_page_registration']);


// Language
$select =& $form->addElement('select', 'default_language', $c5t['text']['txt_language'], $c5t['available_languages']);
$select->setSize(1);

// Frontend order
$select =& $form->addElement('select', 'frontend_order', $c5t['text']['txt_frontend_order'], $c5t['available_order']);
$select->setSize(1);

// Frontend results
$form->addElement('text', 'frontend_result_number', $c5t['text']['txt_frontend_result_number']);
$form->addRule('frontend_result_number', $c5t['text']['txt_error_required'], 'required');
$form->addRule('frontend_result_number', $c5t['text']['txt_error_number_syntax'],'numeric');



// Script URL
$form->addElement('text', 'script_url', $c5t['text']['txt_script_url']);

// Enable moderation
$enable_moderation = array();
$enable_moderation[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_yes'], 'Y');
$enable_moderation[] = &HTML_QuickForm::createElement('radio', null, null, $c5t['text']['txt_no'], 'N');
$form->addGroup($enable_moderation, 'enable_moderation', $c5t['text']['txt_enable_moderation']);

// Publish delay 
$form->addElement('text', 'publish_delay', $c5t['text']['txt_publish_delay']);
$form->addRule('publish_delay', $c5t['text']['txt_error_required'], 'required');
$form->addRule('publish_delay', $c5t['text']['txt_error_number_syntax'],'numeric');


$form->addElement('submit', 'save', $c5t['text']['txt_save_settings']);



//$arr = $form->getRegisteredRules();
//c5t_print_a($arr);




?>
