<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$identifier_form->addElement('text', 'identifier', $c5t['text']['txt_identifier_field'], array('style' => 'width:400px;'));
$identifier_form->addElement('text', 'identifier_name', $c5t['text']['txt_identifier_name_field']);
$identifier_form->addElement('hidden', 'i');
$identifier_form->addElement('hidden', 'do');
$identifier_form->addElement('submit', 'save', $c5t['text']['txt_save']);
$identifier_form->addElement('submit', 'rename', $c5t['text']['txt_save']);









?>
