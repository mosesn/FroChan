<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 */




/**
 * Module nl2br
 */
class gentlesource_module_nl2br extends gentlesource_module_common
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
    function gentlesource_module_nl2br()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('hidden',           true);
        $this->add_property('name',             $this->text['txt_module_name']);
        $this->add_property('description',      $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_content',
                                        'backend_content',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_nl2br_active',
                                        )
                                );
        
        // Default values
        $this->add_property('module_nl2br_active',  'Y');
        
        // Get settings from database
        $this->get_settings();
        
        // Show module status 
        $this->status('module_nl2br_active', 'N');
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
        
        $settings['module_nl2br_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
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
        if ($trigger == 'frontend_content'
                or $trigger == 'backend_content') {
            $data['frontend_text'] = nl2br($data['frontend_text']);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
