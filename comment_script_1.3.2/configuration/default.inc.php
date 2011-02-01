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



$c5t['script_url']              = './';
$c5t['template_directory']      = 'template/';
$c5t['cache_directory']         = 'cache/';
$c5t['backup_directory']        = 'cache/backup/';
$c5t['session_directory']       = 'cache/sessions/';
$c5t['use_session_directory']   = false;
$c5t['default_template']        = 'default';
$c5t['global_template_file']    = 'layout.tpl.html';
$c5t['mail_template_file']      = 'layout.tpl.txt';
$c5t['time_difference']         = +0; // Time difference in minutes
$c5t['automatic_identifier']    = false; // false = off, true = on
$c5t['identifier_key']          = 'c5t_identifier_key'; // If automatic identifier = off ?c5t_identifier_key=ID provides identifier
$c5t['default_language']        = 'en';
$c5t['frontend_language']       = 'en';
$c5t['session_vars_name']       = 'C5T_SESS';
$c5t['language_cookie_name']    = 'C5T_LANG';
$c5t['cookie_path']             = '/';
$c5t['cookie_domain']           = '.' . $_SERVER['HTTP_HOST'];
$c5t['backup_file_prefix']      = 'database_backup_';
$c5t['server_protocol']         = 'http://';
$c5t['server_name']             = $_SERVER['HTTP_HOST'];
$c5t['login_redirect']          = $c5t['server_protocol'] . $c5t['server_name'];
$c5t['logout_redirect']         = $c5t['server_protocol'] . $c5t['server_name'];

$c5t['mail_link']               = array('protocol'  => 'http://',
                                        'server'    => $_SERVER['SERVER_NAME'],
                                        'path'      => dirname($_SERVER['PHP_SELF']) . '/'
                                        );

$c5t['debug_mode']              = false;
$c5t['demo_mode']               = false;
$c5t['log_erros']               = 'Y';

$c5t['module_directory']        = 'module/';
$c5t['installed_modules']       = array(
                                    'gentlesource_module_dummy',
                                    'gentlesource_module_nl2br'
                                    );

$c5t['mail_type']               = 'mail'; // (mail, smtp)
$c5t['mail_from']               = 'postmaster@' . $_SERVER['SERVER_NAME'];
$c5t['smtp']['host']            = 'example.com';
$c5t['smtp']['port']            = 25;
$c5t['smtp']['helo']            = $_SERVER['SERVER_NAME'];
$c5t['smtp']['auth']            = false;
$c5t['smtp']['user']            = '';
$c5t['smtp']['pass']            = '';

$c5t['language_directory']      = 'language/';
$c5t['language_directory_utf8'] = 'language/utf-8/';
$c5t['use_utf8']                = 'Y';

$c5t['available_languages'] = array(
    'en'    => 'English',
    'de'    => 'German',
    'el'    => 'Greek',
    'pt_br' => 'Portuguese (BR)',
    'ru'    => 'Russian',
    );

$c5t['domain_language'] = array(
    'de'    => 'de',
    );
$c5t['display_language_selection'] = 'Y';
$c5t['language_selector_mode']  = 'links'; // links, form

$c5t['frontend_result_number']  = 0;
$c5t['frontend_order']          = 'ascending';
$c5t['hostname_length']         = 15;
$c5t['user_agent_length']       = 15;
$c5t['cut_off_string']          = '&nbsp;...';
$c5t['enable_moderation']       = 'N';
$c5t['publish_delay']           = 0;
$c5t['display_turn_off_messages'] = 'Y';
$c5t['display_comments']        = 'Y';
$c5t['display_comment_form']    = 'Y';
$c5t['page_registration']       = 'Y';
$c5t['comment_maxlength']       = 30000; // 65536 is maximum
$c5t['remember_user']           = 'Y';
$c5t['dynamic_comment_field_name'] = 'Y';
$c5t['output_ignore_tags']      = '';
$c5t['output_htmlentities']     = true;
$c5t['comment_info_cache']      = 300;







?>
