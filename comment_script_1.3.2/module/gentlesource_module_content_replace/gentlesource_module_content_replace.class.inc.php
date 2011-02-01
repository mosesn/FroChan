<?php

/**
 * GentleSource Guestbook Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * Version 1.0
 */

define('MODULE_CONTENT_REPLACE_COUNT_START', 10000);



/**
 * Module Content Replace
 */
class gentlesource_module_content_replace extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

    var $content_replace_settings_flag = false;



    /**
     *  Setup
     *
     * @access public
     */
    function gentlesource_module_content_replace(&$settings)
    {
        $this->text = $this->load_language();

        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',
                                array(
                                        'frontend_content',
                                        'backend_content',
                                        )
                                );

        // Default values
        $this->add_property('module_content_replace_active',  'N');

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names', array(
                                        'module_content_replace_active',
                                        'module_content_replace_item_title-' . MODULE_CONTENT_REPLACE_COUNT_START,
                                        'module_content_replace_item_content-' . MODULE_CONTENT_REPLACE_COUNT_START,
                                        )
                                );

        // Get settings from database
        $this->get_settings();

        // Set module status
        $this->status('module_content_replace_active', 'N');

    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     *
     * @access public
     */
    function administration(&$settings)
    {
        $form = array();

        $form['module_content_replace_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );


        $this->content_replace_settings($settings);
        $content_replace_title   = $this->get_property('content_replace_title');
        $content_replace_content =  $this->get_property('content_replace_content');


        $last = MODULE_CONTENT_REPLACE_COUNT_START;
        if (is_array($content_replace_title)) {
            foreach ($content_replace_title AS $id => $value)
            {
                $last = $id+1;

                if ($value == ''
                        and (!isset($content_replace_content[$id]) or $content_replace_content[$id] == '')) {
                    continue;
                }
                $form['module_content_replace_item_title-' . $id] = array(
                    'type'          => 'string',
                    'label'         => $this->text['txt_title'],
                    'description'   => '',
                    'required'      => false
                    );
                $form['module_content_replace_item_content-' . $id] = array(
                    'type'          => 'textarea',
                    'label'         => $this->text['txt_content'],
                    'description'   => '',
                    'required'      => false,
                    'attribute'     => array('cols' => 10, 'rows' => 3)
                    );
            }
        }


        $form['module_content_replace_item_title-' . $last] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_title'],
            'description'   => '',
            'required'      => false
            );
        $form['module_content_replace_item_content-' . $last] = array(
            'type'          => 'textarea',
            'label'         => $this->text['txt_content'],
            'description'   => '',
            'required'      => false,
            'attribute'     => array('cols' => 10, 'rows' => 3)
            );

        return $form;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     *
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        $this->content_replace_settings($settings);
        if ($trigger == 'frontend_content') {
            if (isset($data['frontend_content'])) {
                $data['frontend_content'] = str_replace(
                                            $this->get_property('module_content_replace_search'),
                                            $this->get_property('module_content_replace_replace'),
                                            stripslashes($data['frontend_content'])
                                            );
            }
            if (isset($data['frontend_text'])) {
                $data['frontend_text'] = str_replace(
                                            $this->get_property('module_content_replace_search'),
                                            $this->get_property('module_content_replace_replace'),
                                            stripslashes($data['frontend_text'])
                                            );
            }
            if (isset($data['frontend_teaser'])) {
                $data['frontend_teaser'] = str_replace(
                                            $this->get_property('module_content_replace_search'),
                                            $this->get_property('module_content_replace_replace'),
                                            stripslashes($data['frontend_teaser'])
                                            );
            }
            if (isset($data['frontend_title'])) {
                $data['frontend_title'] = str_replace(
                                            $this->get_property('module_content_replace_search'),
                                            $this->get_property('module_content_replace_replace'),
                                            stripslashes($data['frontend_title'])
                                            );

            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Content replace settings
     */
    function content_replace_settings(&$settings)
    {
        if ($this->content_replace_settings_flag != false) {
            return true;
        }
        // Read module items from settings
        $content_replace_title = array();
        $content_replace_content = array();
        ksort($settings);
        while (list($key, $value) = each($settings))
        {
            if ($key == '') {
                continue;
            }

            if (strpos($key, 'module_content_replace_item_title-') !== false) {
                $id = (int)trim(substr($key, strlen('module_content_replace_item_title-')));
                $content_replace_title[$id] = $value;
            }

            if (strpos($key, 'module_content_replace_item_content-') !== false) {
                $id = (int)trim(substr($key, strlen('module_content_replace_item_content-')));
                $content_replace_content[$id] = $value;
            }
        }
        $this->add_property('content_replace_title', $content_replace_title);
        $this->add_property('content_replace_content', $content_replace_content);

        $num = sizeof($content_replace_title);
        $list = array();
        $content_replace_search  = array();
        $content_replace_replace = array(); //        $content_replace[] = array('title' => $this->text['txt_module_name'], 'content' => '');
        $last = MODULE_CONTENT_REPLACE_COUNT_START;
        while (list($id, $val) = each($content_replace_title))
        {
            $last = $id+1;

            if ($val == ''
                    and (!isset($content_replace_content[$id]) or $content_replace_content[$id] == '')) {
                continue;
            }
            $list[] = 'module_content_replace_item_title-' . $id;
            $list[] = 'module_content_replace_item_content-' . $id;

            $content_replace_search[$id] = $val;
            $content_replace_replace[$id] = stripslashes($content_replace_content[$id]);
        }
        $this->add_property('module_content_replace_search', $content_replace_search);
        $this->add_property('module_content_replace_replace', $content_replace_replace);
        $list[] = 'module_content_replace_item_title-' . $last;
        $list[] = 'module_content_replace_item_content-' . $last;

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',
                                array_merge(
                                    $this->get_property('setting_names'),
                                    $list)
                                );

        // Get settings from database
        $this->get_settings();
        $this->content_replace_settings_flag = true;
    }

// -----------------------------------------------------------------------------




} // End of class








?>
