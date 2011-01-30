<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Dummy Module
 * 
 * The class name consist of "gentlesource_module_" plus a self chosen name. The
 * class must always extend the class gentlesource_module_common.
 */
class gentlesource_module_dummy extends gentlesource_module_common
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
    function gentlesource_module_dummy(&$settings)
    {
        // Load the language file located in the 
        // folder /module/gentlesource_module_*/language/
        $this->text = $this->load_language();
        
        
        // Hide module from link list and navigation in admin area (values true or false) 
        $this->add_property('hidden',       true);
        
        
        // Name and description of the module displayed in the link list
        // and navigation of the admin area
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        
        
        // List of all triggers where the module is to be called
        $this->add_property('trigger',  
                                array(  
                                        'frontend_comment_form',
                                        )
                                );
        
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_dummy_active',
                                        )
                                );
        
        // Set default values
        $this->add_property('module_dummy_active',  'N');
        
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_dummy_active', 'N');
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
        
        $settings['module_dummy_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
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
        // Check for current trigger and process
        if ($trigger == 'frontend_comment_form') {
            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');        
            $out->assign('dummy_text', 'Dummy Module');
            $dummy_content = $out->fetch('dummy.tpl.html');
            $this->set_output($trigger, $dummy_content);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
