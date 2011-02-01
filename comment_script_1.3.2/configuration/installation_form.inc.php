<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'hostname', $c5t['text']['txt_hostname']);
$form->addElement('text', 'database', $c5t['text']['txt_database']);
$form->addElement('text', 'username', $c5t['text']['txt_username']);
$form->addElement('password', 'dbpassword', $c5t['text']['txt_password']);
$form->addElement('text', 'prefix', $c5t['text']['txt_table_prefix']);
$form->setDefaults(array('prefix' => 'c5t_'));

$form->addElement('text', 'login_name', $c5t['text']['txt_login_name']);
$form->addElement('text', 'email', $c5t['text']['txt_email']);
$form->addElement('password', 'password', $c5t['text']['txt_password']);
$form->addElement('password', 'repeat', $c5t['text']['txt_password_repeat']);

$form->addElement('submit', 'install', $c5t['text']['txt_install']);

$form->addRule('hostname', $c5t['text']['txt_enter_hostname'], 'required');
$form->addRule('database', $c5t['text']['txt_enter_database'], 'required');
$form->addRule('username', $c5t['text']['txt_enter_username'], 'required');
$form->addRule('dbpassword', $c5t['text']['txt_enter_password'], 'required');

$form->addRule('login_name', $c5t['text']['txt_enter_login_name'], 'required');
$form->addRule('login_name', $c5t['text']['txt_syntax_alphanumeric'], 'alphanumeric');
$form->addRule('email', $c5t['text']['txt_enter_email'], 'required');
$form->addRule('email', $c5t['text']['txt_syntax_email'], 'email');
$form->addRule('password', $c5t['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $c5t['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $c5t['text']['txt_passwords_do_not_match'], 'compare');






?>
