<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'login_name', $c5t['text']['txt_login_name'], array('tabindex' => 1));
$form->addElement('password', 'password', $c5t['text']['txt_password'], array('tabindex' => 2));
$form->addElement('submit', 'login', $c5t['text']['txt_login'], array('tabindex' => 3));

$form->addRule('login_name', $c5t['text']['txt_enter_login_name'], 'required');
$form->addRule('password', $c5t['text']['txt_enter_password'], 'required');








?>
