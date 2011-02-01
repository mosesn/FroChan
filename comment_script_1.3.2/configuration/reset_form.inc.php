<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('hidden', 'd', 'r');
$form->addElement('text', 'login_name', $c5t['text']['txt_login_name']);
$form->addElement('submit', 'send', $c5t['text']['txt_send']);

$form->addRule('login_name', $c5t['text']['txt_enter_login_name'], 'required');








?>
