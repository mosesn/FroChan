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

$form->addElement('text', 'login_name', $c5t['text']['txt_login_name']);
$form->addElement('password', 'password', $c5t['text']['txt_password']);

$form->addElement('submit', 'update', $c5t['text']['txt_update']);

$form->addRule('hostname', $c5t['text']['txt_enter_hostname'], 'required');
$form->addRule('database', $c5t['text']['txt_enter_database'], 'required');
$form->addRule('username', $c5t['text']['txt_enter_username'], 'required');
$form->addRule('dbpassword', $c5t['text']['txt_enter_password'], 'required');

$form->addRule('login_name', $c5t['text']['txt_enter_login_name'], 'required');
$form->addRule('password', $c5t['text']['txt_enter_password'], 'required');






?>
