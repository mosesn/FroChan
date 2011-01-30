<?php

/**
 * GentleSource Comment Script - identifier.class.inc.php
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */

require_once 'session.class.inc.php';




/**
 * Handle logins
 */
class c5t_login
{




    /**
     * Start login process
     *
     */
    function c5t_login($level = 1)
    {
        global $c5t;


        if ($level <= 0) {
            return true;
        }

        if ($this->status() == true) {
            $this->login_exists();
        } else {
            if (c5t_gpc_vars('d') == 'r') {
                $this->reset_form();
            } elseif (c5t_gpc_vars('c')) {
                $this->reset_password();
            } else {
                $this->login_starts();
            }
        }

        //Log user out
        if (c5t_gpc_vars('l') == 'o') {
            c5t_session::destroy();
            header('Location: ' . $c5t['logout_redirect'] . dirname($_SERVER['PHP_SELF']) . '/');
            exit;
        }

    }

// -----------------------------------------------------------------------------




    /**
     * Return login status
     *
     */
    function status()
    {
        if ($data = c5t_session::get()
                and isset($data['login_status'])
                and $data['login_status'] == true) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Manage existing login
     *
     */
    function login_exists()
    {
        return true;
    }

// -----------------------------------------------------------------------------




    /**
     * Start login
     *
     */
    function login_starts()
    {
        global $c5t;

        // Configuration
        $detail_template                = 'login.tpl.html';
        $c5t['alternative_template']    = 'admin';
        $message                        = array();

        // Includes
        require_once 'HTML/QuickForm.php';

        // Start output handling
        $out = new c5t_output($detail_template);

        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'login_form.inc.php';


        // Validate form
        if ($form->validate()) {
            // Get login data
            if ($ser = c5t_setting::read('administration_login')) {
                $login_data = unserialize($ser['setting_value']);
                if (c5t_gpc_vars('login_name') == $login_data['login']
                        and md5(c5t_gpc_vars('password')) == $login_data['password']) {
                    $login_data['login_status'] = true;
                    c5t_session::add($login_data);
                    header('Location: ' . $c5t['login_redirect'] . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $c5t['message'][] = $c5t['text']['txt_login_failed'];
                }
            }
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray());


        // Output
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------




    /**
     * Reset
     *
     */
    function reset_form()
    {
        global $c5t;

        // Configuration
        $detail_template                = 'reset.tpl.html';
        $message                        = array();
        $show_form                      = true;

        // Includes
        require_once 'HTML/QuickForm.php';

        // Start output handling
        $out = new c5t_output($detail_template);

        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'reset_form.inc.php';


        // Validate form
        if ($form->validate()) {
            // Get login data
            if ($ser = c5t_setting::read('administration_login')) {
                $login_data = unserialize($ser['setting_value']);
                if (isset($c5t['_post']['login_name'])
                        and $c5t['_post']['login_name'] == $login_data['login']) {
                    if ($this->reset_mail() == true) {
                        $c5t['message'][] = $c5t['text']['txt_reset_mail_sent'];
                        $show_form = false;
                    } else {
                        $c5t['message'][] = $c5t['text']['txt_reset_mail_not_sent'];
                    }
                } else {
                    $c5t['message'][] = $c5t['text']['txt_login_name_not_exists'];
                }
            }

        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray());


        // Output
        $c5t['alternative_template'] = 'admin';
        $out->assign('show_form', $show_form);
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------




    /**
     * Send reset mail
     *
     */
    function reset_mail()
    {
        global $c5t;

        // Create link
        $random = c5t_create_random(20);
        $part   = $c5t['mail_link'];
        $link[] = $part['protocol'];
        $link[] = $part['server'];
        $link[] = $part['path'];
        $link[] = '?c=' .  $random;

        // Add code to admin account
        if ($ser = c5t_setting::read('administration_login')) {
            $login_data = unserialize($ser['setting_value']);
            $arr = array(   'login'         => $login_data['login'],
                            'email'         => $login_data['email'],
                            'password'      => $login_data['password'],
                            'reset_code'    => $random
                            );
            $ser = serialize($arr);
            c5t_setting::write('administration_login', $ser);
        } else {
            return false;
        }


        // Send reset mail
        $detail_template                = 'reset.tpl.txt';
        $c5t['alternative_template']    = 'mail';

        // Start output handling
        $out = new c5t_output($detail_template);
        $out->assign('reset_link', join('', $link));
        $coutput = $out->finish_mail();

        // Send mail off
        include 'mail.class.inc.php';
        if (c5t_mail::send( $login_data['email'],
                            $c5t['text']['txt_reset_mail_subject'],
                            $coutput,
                            $c5t['mail_from'])) {
            return true;
        }

    }

// -----------------------------------------------------------------------------




    /**
     * Reset user password
     *
     */
    function reset_password()
    {
        global $c5t;

        // Configuration
        $detail_template                = 'reset_password.tpl.html';
        $c5t['alternative_template']    = 'admin';
        $message                        = array();

        // Includes
        require_once 'HTML/QuickForm.php';

        // Start output handling
        $out = new c5t_output($detail_template);

        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'reset_password_form.inc.php';
        $form->setDefaults(array('c' => c5t_gpc_vars('c')));


        // Validate form
        $show_form = true;
        if ($form->validate()) {
            // Get login data
            if ($ser = c5t_setting::read('administration_login')) {
                // Change admin password
                if ($ser = c5t_setting::read('administration_login')) {
                    $login_data = unserialize($ser['setting_value']);
                    if (isset($login_data['reset_code'])
                            and $login_data['reset_code'] == $c5t['_post']['c']) {
                        $arr = array(   'login'         => $login_data['login'],
                                        'email'         => $login_data['email'],
                                        'password'      => md5($c5t['_post']['password'])
                                        );
                        $ser = serialize($arr);
                        c5t_setting::write('administration_login', $ser);
                        $c5t['message'][] = $c5t['text']['txt_new_password_set'];
                        $show_form = false;
                    } else {
                        $c5t['message'][] = $c5t['text']['txt_reset_code_not_exists'];
                    }
                }
            }

        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray());


        // Output
        $out->assign(array('show_form' => $show_form));
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------


} // End of class







?>
