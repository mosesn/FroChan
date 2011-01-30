<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('hidden', 'c');
$form->addElement('password', 'password', $c5t['text']['txt_password']);
$form->addElement('password', 'repeat', $c5t['text']['txt_password_repeat']);
$form->addElement('submit', 'save', $c5t['text']['txt_submit']);

$form->addRule('password', $c5t['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $c5t['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $c5t['text']['txt_passwords_do_not_match'], 'compare');








?>
