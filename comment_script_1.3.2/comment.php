<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */

  /*****************************************************
  **
  ** THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY
  ** OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
  ** LIMITED   TO  THE WARRANTIES  OF  MERCHANTABILITY,
  ** FITNESS    FOR    A    PARTICULAR    PURPOSE   AND
  ** NONINFRINGEMENT.  IN NO EVENT SHALL THE AUTHORS OR
  ** COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
  ** OR  OTHER  LIABILITY,  WHETHER  IN  AN  ACTION  OF
  ** CONTRACT,  TORT OR OTHERWISE, ARISING FROM, OUT OF
  ** OR  IN  CONNECTION WITH THE SOFTWARE OR THE USE OR
  ** OTHER DEALINGS IN THE SOFTWARE.
  **
  *****************************************************/




// Settings
if (!defined('C5T_ROOT')) {
    define('C5T_ROOT', './');
}


$c5t_detail_template        = 'comment.tpl.html';

define('C5T_LOGIN_LEVEL', 0);



// Include
require C5T_ROOT . 'include/core.inc.php';

if ($c5t_cache_output == true) {
    return;
}

require 'comment.class.inc.php';

// -----------------------------------------------------------------------------




// Check for module standalone call
if (c5t_gpc_vars('module')) {
    $module_data = array('data' => c5t_gpc_vars('module'));
    c5t_module::call_module('standalone', $module_data, $c5t['module_additional']);
    exit;
}

// -----------------------------------------------------------------------------




require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
require_once 'HTML/QuickForm.php';

// -----------------------------------------------------------------------------


// Start output handling
$c5t_out = new c5t_output($c5t_detail_template);


// Start comment handling
$c5t_comment = new c5t_comment;


// Start form handler
$c5t_form_action = getenv('REQUEST_URI');
if (c5t_gpc_vars('c5t_ssi') or c5t_gpc_vars('c5t_ssi_redirect')) {
    $c5t_form_action = $c5t['script_url'] . 'include.php';
}
$c5t_form = new HTML_QuickForm('form', 'POST', $c5t_form_action . '#c5t_form');




// Add redirect URL
if (c5t_gpc_vars('c5t_ssi') or c5t_gpc_vars('c5t_ssi_redirect')) {
    $c5t_form->addElement('hidden', 'c5t_ssi_redirect');
    if ($c5t_ssi_redirect = c5t_gpc_vars('c5t_ssi_redirect')) {
        $c5t['alternative_template'] = 'standalone';
    } else {
        $c5t_ssi_redirect = getenv('REQUEST_URI');
    }
    $c5t_form->setDefaults(array('c5t_ssi_redirect' => $c5t_ssi_redirect));
}

// -----------------------------------------------------------------------------




// Get form configuration
require 'comment_form.inc.php';

$c5t_form->setDefaults($c5t_comment->remembered_user());


// Validate form
$c5t_message = array();
if ($c5t['display_comment_form'] == 'Y') {
    $c5t_show_form = 'yes';
    if (c5t_gpc_vars('save')) {
        if ($c5t_form->validate()) {
            if ($c5t_comment->put()) {
                $c5t_show_form = 'no';
            }
            if ($c5t_ssi_redirect = c5t_gpc_vars('c5t_ssi_redirect')) {
                header('Location: ' . $c5t['server_protocol'] . $c5t['server_name'] . $c5t_ssi_redirect);
                exit;
            }
        } else {
            if (sizeof($c5t['_post']) > 0) {
                $c5t['message'][] = $c5t['text']['txt_fill_out_required'];
            }
        }
    }

    $c5t_out->assign('show_form', $c5t_show_form);

    $c5t_form_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
    $c5t_form->accept($c5t_form_renderer);
    $c5t_out->assign('form', $c5t_form_renderer->toArray());
}

if ($c5t['display_comment_form'] != 'Y' and $c5t['display_turn_off_messages'] == 'Y') {
    $c5t_message[]['message'] = $c5t['text']['txt_comment_form_turned_off'];
    $c5t_show_form = 'yes';
} else {
    $c5t_show_form = 'no';
}

// -----------------------------------------------------------------------------




// Get comment data
$c5t_comment_data = array();
if ($c5t['display_comments'] == 'Y') {
    c5t_benchmark::mark('Begin Comment List');
    require 'commentlist.class.inc.php';
    $c5t_list_setup = array('direction' => $c5t['frontend_order'],
                            'limit'     => 0);
    if ((int) $c5t['frontend_result_number'] >= 1) {
        $c5t_list_setup['limit'] = (int) $c5t['frontend_result_number'];
        // Pagination does not work with SSI
        if (c5t_gpc_vars('c5t_ssi')) {
            $c5t_list_setup['limit'] = 0;
        }
        $c5t_out->assign('display_pagination', true);
    }
    $c5t_comment_list = new c5t_comment_list(false, $c5t_list_setup);
    if ($c5t_comment_data_temp = $c5t_comment_list->get_list(c5t_comment::identifier())) {
        $c5t_comment_data = $c5t_comment_data_temp;
    }
    $c5t_comment_list_values = $c5t_comment_list->values();
    c5t_benchmark::mark('End Comment List');
    $c5t_out->assign($c5t_comment_list_values);
    if ($c5t_comment_list_values['result_limit'] > 0){
        $c5t_page = ceil(($c5t_comment_list_values['result_number'] + 1) / $c5t_comment_list_values['result_limit']);
    } else {
        $c5t_page = 1;
    }
    $c5t_form->setConstants(array('page' => $c5t_page));


    // Pagination form
    $c5t_next_page = new HTML_QuickForm('nextpage', 'POST', $c5t_form_action .'#c5t_comment');
    $c5t_next_page->addElement('submit', 'next', $c5t['text']['txt_next_page']);
    $c5t_next_page->addElement('hidden', 'page');
    $c5t_next_page->setConstants(array('page' => $c5t_comment_list_values['next_page']));
    $c5t_next_page_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
    $c5t_next_page->accept($c5t_next_page_renderer);
    $c5t_next_page_renderer_to_array = $c5t_next_page_renderer->toArray();
    $c5t_out->assign('nextpage', $c5t_next_page_renderer_to_array);


    $c5t_end_page = new HTML_QuickForm('endpage', 'POST', $c5t_form_action .'#c5t_comment');
    $c5t_end_page->addElement('submit', 'end', $c5t['text']['txt_end']);
    $c5t_end_page->addElement('hidden', 'page');
    $c5t_end_page->setConstants(array('page' => $c5t_comment_list_values['result_pages']));
    $c5t_end_page_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
    $c5t_end_page->accept($c5t_end_page_renderer);
    $c5t_end_page_renderer_to_array = $c5t_end_page_renderer->toArray();
    $c5t_out->assign('endpage', $c5t_end_page_renderer_to_array);

    $c5t_start_page = new HTML_QuickForm('startpage', 'POST', $c5t_form_action .'#c5t_comment');
    $c5t_start_page->addElement('submit', 'start', $c5t['text']['txt_start']);
    $c5t_start_page->addElement('hidden', 'page');
    $c5t_start_page->setConstants(array('page' => 1));
    $c5t_start_page_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
    $c5t_start_page->accept($c5t_start_page_renderer);
    $c5t_start_page_renderer_to_array = $c5t_start_page_renderer->toArray();
    $c5t_out->assign('startpage', $c5t_start_page_renderer_to_array);


    $c5t_previous_page = new HTML_QuickForm('previouspage', 'POST', $c5t_form_action .'#c5t_comment');
    $c5t_previous_page->addElement('submit', 'previous', $c5t['text']['txt_previous_page']);
    $c5t_previous_page->addElement('hidden', 'page');
    $c5t_previous_page->setConstants(array('page' => $c5t_comment_list_values['previous_page']));
    $c5t_previous_page_renderer = new HTML_QuickForm_Renderer_ArraySmarty($c5t_out->get_object, true);
    $c5t_previous_page->accept($c5t_previous_page_renderer);
    $c5t_previous_page_renderer_to_array = $c5t_previous_page_renderer->toArray();
    $c5t_out->assign('previouspage', $c5t_previous_page_renderer_to_array);
}
$c5t_out->assign('comment_list', $c5t_comment_data);

if ($c5t['display_comments'] != 'Y' and $c5t['display_turn_off_messages'] == 'Y') {
    $c5t_turned_off = array('comment_title'         => $c5t['text']['txt_comment_display_turned_off'],
                            'comment_author_name'   => $c5t['text']['txt_administrator'],
                            'comment_number'        => 1,
                            'comment_date'          => c5t_time::format_date(c5t_time::current_timestamp()),
                            'comment_time'          => c5t_time::format_time(c5t_time::current_timestamp())
                            );
    $c5t_out->assign('comment_list', array($c5t_turned_off));
}

// -----------------------------------------------------------------------------




// Get current page data
require_once 'identifier.class.inc.php';
$page_data = c5t_comment::select_identifier(c5t_comment::identifier());
$c5t_out->assign('page_data', $page_data);

// -----------------------------------------------------------------------------




// Output
$c5t_output = $c5t_out->finish(false);
//echo $c5t_output;

?>
