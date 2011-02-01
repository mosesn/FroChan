<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('select', 'search_field', $c5t['text']['txt_search_column'], $search_field_list);
$form->addElement('text', 'search_query', $c5t['text']['txt_search_text']);
$form->addElement('submit', 'search', $c5t['text']['txt_search']);
$form->addElement('submit', 'search_delete', $c5t['text']['txt_delete_search']);









?>
