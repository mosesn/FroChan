<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Filter content
 */
class gentlesource_module_word_filter extends gentlesource_module_common
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
    function gentlesource_module_word_filter()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_content'
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_word_filter_active',
                                        'module_word_filter_character',
                                        'module_word_filter_list',
                                        )
                                );
        
        // Default values
        $this->add_property('module_word_filter_active',    'N'); // Y/N
        $this->add_property('module_word_filter_character', '!@#$%');        
        
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_word_filter_active', 'N');
        
        if ($this->get_property('module_word_filter_active') == 'Y') {
            $this->word_filter_settings($settings);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Word filter settings
     */
    function word_filter_settings(&$settings)
    {   
        // Prepare word list
        $list = $this->get_property('module_word_filter_list');
        if ($list == '') {
            return false;
        }
        $arr = explode("\n", $list);
        $preg_word = array();
        while (list($key, $var) = each($arr))
        {
            $word = trim($var);
            if ($word == '') {
                continue;
            }
            
            // Check for wildcards
            $preg_word[] = '#' . str_replace('%asterisk%', '(.*?)', preg_quote(str_replace('*', '%asterisk%', $word))) . '#i';
        }
        $this->add_property('module_word_filter_character_array', $preg_word);
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
        
        $settings['module_word_filter_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_module_name'],
            'description'   => $this->text['txt_module_description'],
            'required'      => true
            );
        
        $settings['module_word_filter_character'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_replacement_character'],
            'description'   => '',            
            'required'      => true
            );
        
        $settings['module_word_filter_list'] = array(
            'type'          => 'textarea',
            'label'         => $this->text['txt_word_list'],
            'description'   => $this->text['txt_word_list_description'],            
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
        if ($trigger == 'frontend_content') {
            $this->filter($data);
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Word filter
     * 
     * @access public
     */
    function filter(&$input)
    {
        if (!is_array($input) or sizeof($input) <= 0) {
            return false;
        }
        
        $replacement    = $this->get_property('module_word_filter_character');
        $preg_word      = $this->get_property('module_word_filter_character_array');

        foreach ($input AS $field => $content)
        {
            if ($content == '') {
                continue;
            }
            $input[$field] = preg_replace($preg_word, $replacement, $content);
        }        
    }

// -----------------------------------------------------------------------------




} // End of class








?>
