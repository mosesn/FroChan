<?php

/**
 * GentleSource
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */

define('MODULE_CONTENT_BLOCK_LOG_FILENAME',  'spam_log.txt');
define('MODULE_CONTENT_BLOCK_LOG_FOLDER',    'logfile/');




/**
 * Manage modules
 */
class gentlesource_module_content_block extends gentlesource_module_common
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
    function gentlesource_module_content_block()
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
                                        'module_content_block_mode',
                                        'module_content_block_list',
                                        )
                                );

        // Default values
        $this->add_property('module_content_block_mode',    'off'); // off, reject, moderate
        $this->add_property('module_content_block_log_spam','N');

        // Get settings from database
        $this->get_settings();

        // Set module status
        $this->status('module_content_block_mode', 'off');
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

        $settings['module_content_block_mode'] = array(
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

        $settings['module_content_block_list'] = array(
            'type'          => 'textarea',
            'label'         => $this->text['txt_block_content'],
            'description'   => $this->text['txt_block_content_description'],
            'required'      => false,
            'attribute'     => array('rows' => 10, 'cols' => 30),
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
        if ($trigger == 'frontend_save_content') {

            // Skip check if comment has already been blocked
            if ($additional['page_allow_comment'] == 'N') {
                return false;
            }

            if ($this->get_property('module_content_block_mode') == 'reject'
                    and $this->block($data, $settings)) {
                $settings['message']['module_spam_check'] = $this->text['txt_error_content_block'];
                $additional['page_allow_comment'] = 'N';
                return false;
            }


            // Skip check if moderation has already been turned on
            if ($settings['enable_moderation'] == 'Y'
                    or isset($data['comment_status'])) {
                return false;
            }

            if ($this->get_property('module_content_block_mode') == 'moderate'
                    and $this->block($data, $settings)) {
                $settings['message']['module_spam_check'] = $this->text['txt_notice_moderation'];
                $data['comment_status'] = 100;
                return false;
            }
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Block content
     *
     * @access public
     */
    function block($data, &$settings)
    {
        $list = $this->get_property('module_content_block_list');
        if ($list == '') {
            return false;
        }

        $arr = explode("\n", $list);
        while (list($key, $var) = each($arr))
        {
            $word = trim($var);
            if ($word == '') {
                continue;
            }

            // Check for wildcards
            $preg_word = '#^' . str_replace('%asterisk%', '(.*?)', preg_quote(str_replace('*', '%asterisk%', $word))) . '$#im';

            foreach ($data AS $input)
            {
                if ($input == '') {
                    continue;
                }
                if ($word{0} != '*'
                        and $word{0} != strtolower($input{0})) {
                    continue;
                }
                if (preg_match($preg_word, $input)) {
                    if ($this->get_property('module_content_block_log_spam') == 'Y') {
                        $time = $this->current_timestamp();
                        $line[] = date($settings['text']['txt_date_format'], $time);
                        $line[] = ' (';
                        $line[] = date($settings['text']['txt_time_format'], $time);
                        $line[] = ') - ';
                        $line[] = $preg_word;
                        $line[] = ' - ';
                        $line[] = getenv('REQUEST_URI');

                        $this->write_log_file(
                            $this->get_property('module_path') . MODULE_CONTENT_BLOCK_LOG_FOLDER,
                            MODULE_CONTENT_BLOCK_LOG_FILENAME,
                            join('', $line)
                            );
                    }
                    return true;
                }
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
