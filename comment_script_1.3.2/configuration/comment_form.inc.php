<?php

/**
 * GentleSource Comment Script - comment_form.inc.php
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$tabindex = 9000;

$c5t_form->addElement('text', 'name', $c5t['text']['txt_name'], array('tabindex' => $tabindex++));
$c5t_form->addElement('text', 'email', $c5t['text']['txt_email_hidden'], array('tabindex' => $tabindex++));
$c5t_form->addElement('text', 'homepage', $c5t['text']['txt_homepage'], array('tabindex' => $tabindex++));
$c5t_form->addElement('text', 'title', $c5t['text']['txt_title'], array('tabindex' => $tabindex++));
$c5t_form->addElement('textarea', $c5t['comment_field_name'], $c5t['text']['txt_comment'], array('rows' => 8, 'cols' => 30, 'tabindex' => $tabindex++));
$c5t_form->addElement('hidden', 'page');
$c5t_form->addElement('submit', 'save', $c5t['text']['txt_submit'], array('tabindex' => $tabindex++ +1));

$c5t_form->addRule('email',     $c5t['text']['txt_valid_email'], 'email');
//$c5t_form->addRule('email',     $c5t['text']['txt_email_required'], 'required');
$c5t_form->addRule('name',      $c5t['text']['txt_enter_name'], 'required');
$c5t_form->addRule($c5t['comment_field_name'],   $c5t['text']['txt_enter_comment'], 'required');
$c5t_form->addRule($c5t['comment_field_name'],   sprintf($c5t['text']['txt_comment_maxlength'], $c5t['comment_maxlength']), 'maxlength', $c5t['comment_maxlength']);

$c5t_form->setDefaults(array('homepage' => 'http://'));

//var_dump($c5t_form->getRegisteredRules());



?>
