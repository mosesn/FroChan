<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'login_name', $c5t['text']['txt_login_name']);
$form->addElement('text', 'email', $c5t['text']['txt_email']);
$form->addElement('password', 'password', $c5t['text']['txt_password']);
$form->addElement('password', 'repeat', $c5t['text']['txt_password_repeat']);
$form->addElement('submit', 'save', $c5t['text']['txt_save_account']);

$form->addRule('login_name', $c5t['text']['txt_enter_login_name'], 'required');
$form->addRule('login_name', $c5t['text']['txt_syntax_alphanumeric'], 'alphanumeric');
$form->addRule('email', $c5t['text']['txt_enter_email'], 'required');
$form->addRule('email', $c5t['text']['txt_syntax_email'], 'email');
$form->addRule('password', $c5t['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $c5t['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $c5t['text']['txt_passwords_do_not_match'], 'compare');








?>
