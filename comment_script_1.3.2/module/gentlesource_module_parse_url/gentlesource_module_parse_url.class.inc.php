<?php

/** 
 * GentleSource Module Parse URL
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Filter content
 */
class gentlesource_module_parse_url extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

// -----------------------------------------------------------------------------




    /**
     * Module Constructor
     * 
     * @param array setttings Application main setting array
     */
    function gentlesource_module_parse_url()
    {
        // Load the language file located in the 
        // folder /module/gentlesource_module_*/language/
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
                                        'module_parse_active',
                                        'module_parse_url_active',
                                        'module_parse_www_active',
                                        'module_parse_email_active',
                                        )
                                );
        
        // Default values
        $this->add_property('module_parse_active',      'N'); // Y/N
        $this->add_property('module_parse_url_active',  'N'); // Y/N
        $this->add_property('module_parse_www_active',  'N'); // Y/N
        $this->add_property('module_parse_email_active','N'); // Y/N
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_parse_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     * Administration form that is displayed in admin area in the
     * Configuration section
     * 
     * Possible array elements:
     * 
     * type				bool|string|email|numeric|select|radio|textarea|color
     * label 
     * description		
     * required			true|false
     * attribute		Associative array of attributes added to the form field
     * option			Associative array values for radio|select
     * 
     */
    function administration()
    {
        $settings = array();
        
        $settings['module_parse_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_activate_module'],
            'description'   => $this->text['txt_activate_module_description'],
            'required'      => true
            );
        
        $settings['module_parse_url_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_parse_url'],
            'description'   => $this->text['txt_parse_url_description'],
            'required'      => true
            );
        $settings['module_parse_www_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_parse_www'],
            'description'   => $this->text['txt_parse_www_description'],
            'required'      => true
            );
        $settings['module_parse_email_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_parse_email'],
            'description'   => $this->text['txt_parse_email_description'],
            'required'      => true
            );

        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content - This function will be called when triggered 
     * somewhere within the script.
     * 
     * @param string    $trigger 	Trigger that triggered the module call
     * @param array		$settings	Application main setting array
     * @param arrray	$data		Data to be used/modified
     * @param array		$additional Additinal data to be used/modified
     * 
     */
    function process($trigger, &$settings, &$data, &$additional)
    {        
        if ($trigger == 'frontend_content') {
            $this->parse($data);
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Parse
     * 
     * @access public
     */
    function parse(&$input)
    {
        if (!is_array($input) or sizeof($input) <= 0) {
            return false;
        }
        
        foreach ($input AS $field => $content)
        {
            if ($field != 'frontend_text'
                    and $field != 'frontend_teaser'
                    and $field != 'frontend_content') {
                continue;
            }
        
            if ($content == '') {
                continue;
            }

            if ($this->get_property('module_parse_url_active') == 'Y') {
                // protocol://url
                $content = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", ' ' . $content);
            }

            if ($this->get_property('module_parse_www_active') == 'Y') {
                /// www.example.com
            	$content = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", ' ' . $content);
            }

            if ($this->get_property('module_parse_email_active') == 'Y') {
            	// E-mail
            	$content = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", ' ' . $content);
            }

            $input[$field] = trim($content);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
