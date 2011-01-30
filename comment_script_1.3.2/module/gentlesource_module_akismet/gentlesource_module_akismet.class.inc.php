<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * Dependencies
 * PEAR Package: HTTP_Request
 * PEAR Package: Net_URL 1.0.12 or newer
 * PEAR Package: Net_Socket 1.0.2 or newer
 */




/**
 * Manage modules
 */
class gentlesource_module_akismet extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

// -----------------------------------------------------------------------------




    /**
     *  Setup
     *
     * @access public
     */
    function gentlesource_module_akismet()
    {
        $this->text = $this->load_language();

        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',
                                array(
                                        'frontend_save_content'
                                        )
                                );

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',
                                array(
                                        'module_akismet_mode',
                                        'module_akismet_key',
                                        )
                                );

        // Default values
        $this->add_property('module_akismet_mode',  'off'); // off, reject, moderate

        // Get settings from database
        $this->get_settings();

        // Set module status
        $this->status('module_akismet_mode', 'off');
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     *
     * @access public
     */
    function administration()
    {
        $settings = array();

        $settings['module_akismet_mode'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_module_name'],
            'description'   => $this->text['txt_module_description'],
            'required'      => true,
            'option'        => array(
                                'off'       => $this->text['txt_off'],
                                'moderate'  => $this->text['txt_moderate'],
                                'reject'    => $this->text['txt_reject']
                                ),
            );

        $settings['module_akismet_key'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_akismet_key'],
            'description'   => $this->text['txt_akismet_key_description'],
            'required'      => false,
            );

        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     *
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        if ($this->get_property('module_akismet_key') == '') {
            return false;
        }

        if ($trigger == 'frontend_save_content') {

            // Skip check if comment has already been blocked
            if ($additional['page_allow_comment'] == 'N') {
                return false;
            }

            if ($this->get_property('module_akismet_mode') == 'reject'
                    and $this->akismet($settings, $data, $additional)) {
                $settings['message']['module_spam_check'] = $this->text['txt_error_spam'];
                $additional['page_allow_comment'] = 'N';
                return false;
            }


            // Skip check if moderation has already been turned on
            if ($settings['enable_moderation'] == 'Y'
                    or isset($data['comment_status'])) {
                return false;
            }
            if ($this->get_property('module_akismet_mode') == 'moderate'
                    and $this->akismet($settings, $data, $additional)) {
                $settings['message']['module_spam_check'] = $this->text['txt_notice_moderation'];
                $data['comment_status'] = 100;
            }
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Block content
     *
     * @access public
     */
    function akismet(&$settings, &$data, &$additional)
    {
        require_once 'HTTP/Request.php';

        $akismet_data = array(
          'blog'                    => $settings['server_protocol'] . $settings['server_name'] . '/',
          'user_agent'              => getenv('HTTP_USER_AGENT'),
          'referrer'                => getenv('HTTP_REFERER'),
          'user_ip'                 => $_SERVER['REMOTE_ADDR'] != getenv('SERVER_ADDR') ? $_SERVER['REMOTE_ADDR'] : getenv('HTTP_X_FORWARDED_FOR'),
          'permalink'               => $settings['server_protocol'] . $settings['server_name'] . $additional['identifier_value'],
          'comment_type'            => 'comment',
          'comment_author'          => isset($data['name']) ? $data['name'] : '',
          'comment_author_email'    => isset($data['email']) ? $data['email'] : '',
          'comment_author_url'      => isset($data['homepage']) ? $data['homepage'] : '',
          'comment_content'         => isset($data['comment']) ? $data['comment'] : '',
        );

        $request_options = array(
            'method'            => 'POST',
            'http'              => '1.1',
            'timeout'           => 20,
            'allowRedirects'    => true,
            'maxRedirects'      => 3
        );

        $request = new HTTP_Request('http://rest.akismet.com/1.1/verify-key', $request_options);

        $request->addPostData('key',  $this->get_property('module_akismet_key'));
        $request->addPostData('blog', $settings['server_protocol'] . $settings['server_name'] . '/');

        if (PEAR::isError($request->sendRequest()) || $request->getResponseCode() != '200') {
            return false;
        } else {
            // Fetch response
            $request_data = $request->getResponseBody();
        }

        if (!preg_match('#valid#i', $request_data)) {
            return false;
        }

        $request = new HTTP_Request('http://' . $this->get_property('module_akismet_key') . '.rest.akismet.com/1.1/comment-check', $request_options);

        foreach($akismet_data AS $key => $value) {
            $request->addPostData($key, $value);
        }

        if (PEAR::isError($request->sendRequest()) or $request->getResponseCode() != '200') {
            return false;
        } else {
            // Fetch response
            $request_data = $request->getResponseBody();
        }

        if (preg_match('#true#i', $request_data)) {
            return true;
        } elseif (preg_match('#false#i', $request_data)) {
            return false;
        } else {
            return false;
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
