<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * @todo Add @ to eval
 *
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


// Prevent hacking attempt
if (!defined('C5T_ROOT')) {
    die();
}


// Define path separator
if (!defined('PATH_SEPARATOR')) {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}


// Set include path
$c5t_include_path =
                C5T_ROOT . 'configuration'. PATH_SEPARATOR .
                C5T_ROOT . 'include'. PATH_SEPARATOR .
                C5T_ROOT . 'include/library'. PATH_SEPARATOR .
                './' . PATH_SEPARATOR .
                ini_get('include_path') . PATH_SEPARATOR;

if (function_exists('set_include_path')) {
    set_include_path($c5t_include_path);
} else {
    ini_set('include_path', $c5t_include_path);
}




// Include
require 'functions.inc.php';

$c5t = array();



// Caching
$c5t['caching'] = false;

if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $c5t['caching'] = false;
}
$c5t_cache_output = false;
if ($c5t['caching'] == true) {
    $c5t['cache_prefix'] = 'c5t_cache_';
    require 'Cache/Lite.php';
    $options = array(
                'caching'  => $c5t['caching'],
                'cacheDir' => C5T_ROOT . 'cache/',
                'lifeTime' => 3600
                );
    $c5t['cache_object'] = new Cache_Lite($options);
    $page = '_page_1';
    if (isset($_POST['page']) and $_POST['page'] != '') {
        $page = '_page_' . $_POST['page'];
    }
    $c5t['cache_id'] = md5($_SERVER['REQUEST_URI'] . $page);
    if (isset($_POST['save'])) {
        $c5t['cache_object']->remove($c5t['cache_id']);
        $c5t['caching'] = false;
    } elseif ($data = $c5t['cache_object']->get($c5t['cache_id'])) {
        $c5t_output = $data;
        $c5t_cache_output = true;
        return;
    }
}



// Clean input
c5t_unset_globals();
$c5t['_post']   = $_POST;
$c5t['_get']    = $_GET;
$c5t['_cookie'] = $_COOKIE;
array_walk($c5t['_get'],       'c5t_clean_input');
array_walk($c5t['_post'],      'c5t_clean_input');
array_walk($c5t['_cookie'],    'c5t_clean_input');





// Settings
$c5t['software']                = 'Comment Script';
$c5t['version']                 = '1.3.2';
$c5t['script_prefix']           = 'c5t';
$c5t['login_status']            = false;
$c5t['alternative_template']    = defined('C5T_ALTERNATIVE_TEMPLATE') ? C5T_ALTERNATIVE_TEMPLATE : '';
$c5t['message']                 = array();
$c5t['module_additional']       = array();
$c5t['output']                  = array();



// Include
require 'system_debug.class.inc.php';
require 'query.class.inc.php';
require 'database.class.inc.php';
require 'setting.class.inc.php';
require 'time.class.inc.php';
require 'module.class.inc.php';
require 'output.class.inc.php';
require 'default.inc.php';
require 'language.class.inc.php';
require_once 'identifierlist.class.inc.php';


if ($c5t['debug_mode'] == true) {
    ini_set('error_reporting', E_ALL);
} else {
    ini_set('error_reporting', 0);
}
if ($c5t['log_erros'] == 'Y') {
     ini_set('log_errors', true);
     ini_set('error_log', C5T_ROOT . 'cache/php_error_log.txt');
     ini_set('error_reporting', E_ALL);
}

if (c5t_gpc_vars('c5t_ssi') or c5t_gpc_vars('c5t_ssi_redirect')) {
    ini_set('session.use_trans_sid', true);
    ini_set('session.use_cookies', false);
} else {
    ini_set('session.use_trans_sid', false);
}




// Start benchmark
require 'benchmark.class.inc.php';
c5t_benchmark::start();




// Set path
$c5t['template_path']   = C5T_ROOT . $c5t['template_directory'];
$c5t['cache_path']      = C5T_ROOT . $c5t['cache_directory'];




/**
 * Database field - form field mapping
 * Key:   database field name
 * Value: form field name
 */
$c5t['mapping']['comment'] = array(
                                'comment_id'                => 'id',
                                'comment_identifier'        => 'identifier',
                                'comment_author_name'       => 'name',
                                'comment_author_email'      => 'email',
                                'comment_author_homepage'   => 'homepage',
                                'comment_title'             => 'title',
                                'comment_text'              => 'comment'
                                );

$c5t['mapping']['setting'] = array(
                                'setting_name'              => 'setting_name',
                                'setting_value'             => 'setting_value'
                                );




// Table fields to be inserted or updated in database
$c5t['db_fields']['comment'] = array(
                                'comment_id',
                                'comment_identifier_id',
                                'comment_identifier',
                                'comment_identifier_hash',
                                'comment_author_name',
                                'comment_author_email',
                                'comment_author_homepage',
                                'comment_author_ip',
                                'comment_author_host',
                                'comment_author_user_agent',
                                'comment_title',
                                'comment_text',
                                'comment_timestamp',
                                'comment_status'
                                );

$c5t['db_fields']['identifier'] = array(
                                'identifier_id',
                                'identifier_value',
                                'identifier_hash',
                                'identifier_name',
                                'identifier_url'
                                );

$c5t['db_fields']['setting'] = array(
                                'setting_name',
                                'setting_value'
                                );




// Allowed form fields to be used for insert and update
$c5t['form_fields']['comment'] = array(
                                    'id',
                                    'identifier',
                                    'name',
                                    'email',
                                    'homepage',
                                    'title',
                                    'comment'
                                    );

$c5t['form_fields']['setting'] = array(
                                    'setting_name',
                                    'setting_value'
                                    );




// Setting names to be written and read
$c5t['setting_names'] = array(
                            'database_version',
                            'default_language',
                            'script_url',
                            'frontend_result_number',
                            'frontend_order',
                            'block_content',
                            'block_ip',
                            'word_filter',
                            'enable_moderation',
                            'publish_delay',
                            'email_notification',
                            'notification_email',
                            'display_turn_off_messages',
                            'display_comments',
                            'display_comment_form',
                            'page_registration',
                            'installed_modules',
                            );

// -----------------------------------------------------------------------------




// Manage installation
include 'installation.class.inc.php';
$c5t_installation = new c5t_installation;
if ($c5t_installation->status() != true) {
    $c5t_installation->start();
}

// -----------------------------------------------------------------------------




// Database tables
require C5T_ROOT . 'cache/dbconfig.php';
define('C5T_COMMENT_TABLE',     $c5t['database_table_prefix'] . 'comment');
define('C5T_IDENTIFIER_TABLE',  $c5t['database_table_prefix'] . 'identifier');
define('C5T_SETTING_TABLE',     $c5t['database_table_prefix'] . 'setting');

$c5t['tables']['comment']       = C5T_COMMENT_TABLE;
$c5t['tables']['identifier']    = C5T_IDENTIFIER_TABLE;
$c5t['tables']['setting']       = C5T_SETTING_TABLE;

// -----------------------------------------------------------------------------




// Include language file
$c5t_language = $c5t['default_language'];
if ($language = c5t_setting::read('default_language')) {
    $c5t_language = $language['setting_value'];
}
if (isset($frontend_language)) {
    $c5t_language = $c5t['frontend_language'];
    if ($language = c5t_setting::read('frontend_language')) {
        $c5t_language = $language['setting_value'];
    }
}

$c5t['current_language']    = c5t_language::get($c5t_language);
$c5t['text']                = c5t_language::load($c5t['current_language']);

// -----------------------------------------------------------------------------



// Get setting data
$c5t_settings = c5t_setting::read_all();
if (isset($c5t_settings['installed_modules'])) {
    $c5t_settings['installed_modules'] = unserialize($c5t_settings['installed_modules']);
}
$c5t = array_merge($c5t, $c5t_settings);




// Settings
$c5t['available_order'] = array('ascending'     => $c5t['text']['txt_frontend_order_asending'],
                                'descending'    => $c5t['text']['txt_frontend_order_desending']
                                );
$c5t['comment_status']  = array('approved'      => 0,
                                'unapproved'    => 100,
                                );
$c5t['page_status']     = array('active'        => 0,
                                'deactivated'   => 100,
                                );

// -----------------------------------------------------------------------------




/**
 * Comment text field name based on current week
 */
$comment_field_name = 'comment';
if ($c5t['dynamic_comment_field_name'] == 'Y') {
    $comment_field_name .= md5($c5t['dsn']['password'] . @date('W'));
}
$c5t['comment_field_name'] = $comment_field_name;
if (array_key_exists($c5t['comment_field_name'], $c5t['_post'])) {
    $c5t['_post']['comment'] = $c5t['_post'][$c5t['comment_field_name']];
}

// -----------------------------------------------------------------------------




// Prepare data for output
$c5t['output'] = array(
                    'software'                      => $c5t['software'],
                    'version'                       => $c5t['version'],
                    'display_setting_navigation'    => false,
                    'login_status'                  => false,
                    'display_language_selection'    => $c5t['display_language_selection'],
                    'language_selector_mode'        => $c5t['language_selector_mode'],
                    'available_languages'           => $c5t['available_languages'],
                    'page_url_encoded'              => urlencode($c5t['server_protocol'] . $c5t['server_name'] . c5t_request_uri()),
                    'comment_field_name'            => $c5t['comment_field_name']
                    );

// -----------------------------------------------------------------------------




// Manage update
include 'update.class.inc.php';
$c5t_update = new c5t_update;
if ($c5t_update->status() != true) {
    $c5t_update->start();
}

// -----------------------------------------------------------------------------




// Login
require 'login.class.inc.php';
if (C5T_LOGIN_LEVEL > 0) {
    $c5t_login = new c5t_login(C5T_LOGIN_LEVEL);
    if ($c5t_login->status() == true) {
        $c5t['login_status'] = true;
    }
}

// -----------------------------------------------------------------------------










?>
