<?php

/** 
 * GentleSource Guestbook Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 * 
 * Check http://www.twistermc.com/
 */




/**
 * Dummy Module
 */
class gentlesource_module_social_links extends gentlesource_module_common
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
    function gentlesource_module_social_links()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_content_footer',
                                        'module_demo',
                                        'backend_textarea',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_social_links_active',
                                        'module_social_links_place',
                                        )
                                );
        
        // Default values
        $this->add_property('module_social_links_active',  'N');
        $this->add_property('module_social_links_place',  'frontend');
        
        // Get settings from database
        $this->get_settings();

        // Set module status 
        $this->status('module_social_links_active', 'N');
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
        
        $settings['module_social_links_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );
        
        $settings['module_social_links_place'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_enable_backend'],
            'description'   => $this->text['txt_enable_backend_description'],
            'required'      => true,
            'option'        => array(   'frontend'  => $this->text['txt_frontend'],
                                        'both'      => $this->text['txt_frontend_backend'],
                                        'backend'   => $this->text['txt_backend'],
                                        )
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
        $image_path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/template/image/';
        
        if ($trigger == 'module_demo' and $data['module'] == get_class($this)) {        
            $page_url   = urlencode($settings['server_protocol'] . $settings['server_name'] . $settings['script_url']);
            $page_title = urlencode($this->text['txt_module_name']);
            $this->create_output($trigger, $settings, $page_url, $page_title);
        }
        
        $frontend   = false;
        $backend    = false;
        if ($this->get_property('module_social_links_place') == 'frontend') {
            $frontend   = true;
        }
        if ($this->get_property('module_social_links_place') == 'backend') {
            $backend    = true;
        }
        if ($this->get_property('module_social_links_place') == 'both') {
            $frontend   = true;
            $backend    = true;
        }
        
        if ($trigger == 'frontend_content_footer' and $frontend == true) {
            $page_url   = urlencode($settings['server_protocol'] . $settings['server_name'] . $_SERVER['REQUEST_URI']);
            $page_title = urlencode(utf8_encode($data['data']['page_title']));
            $this->create_output($trigger, $settings, $page_url, $page_title);
        }

        if ($trigger == 'backend_textarea' and isset($data['data']['frontend_url']) and $backend == true) {
            $page_url   = urlencode($data['data']['page_url']);
            $page_title = urlencode(utf8_encode($data['data']['page_title']));
            $this->create_output($trigger, $settings, $page_url, $page_title);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Prepare output
     * 
     */
    function create_output($trigger, &$settings, $page_url, $page_title)
    {
        $image_path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/template/image/';
        
        $out = $this->get_output_object();
        $out->set_template_dir($this->get_property('module_path') . 'template/');
        $out->assign($this->text);
        
        $out->assign('page_url',    $page_url);        
        $out->assign('page_title',  $page_title);
        $out->assign('image_path',  $image_path);
                
        $content = $out->fetch('social_link.tpl.html');
        $this->set_output($trigger, $content);
    }

// -----------------------------------------------------------------------------




} // End of class








?>
