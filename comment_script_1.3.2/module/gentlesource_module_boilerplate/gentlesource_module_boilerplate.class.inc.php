<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 */

define('MODULE_BOILERPLATE_COUNT_START', 10000);



/**
 * Module Boilerplate (text that can be reused)
 */
class gentlesource_module_boilerplate extends gentlesource_module_common
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
    function gentlesource_module_boilerplate(&$settings)
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'backend_textarea',
                                        )
                                );
        
        // Default values
        $this->add_property('module_boilerplate_active',  'N');
        
        
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names', array(  
                                                'module_boilerplate_active',
                                                'module_boilerplate_item_title-' . MODULE_BOILERPLATE_COUNT_START,
                                                'module_boilerplate_item_content-' . MODULE_BOILERPLATE_COUNT_START,
                                                )
                                );
        
        
        // Get settings from database
        $this->get_settings();
        
        // Show module status 
        $this->status('module_boilerplate_active', 'N');
                                
        
    }

// -----------------------------------------------------------------------------




    /**
     * Get boilerplate items
     */
    function boilerplate_settings(&$settings)
    {
        // Read module items from settings
        $boilerplate_title = array();
        $boilerplate_content = array();
        ksort($settings);
        while (list($key, $value) = each($settings))
        {
            if ($key == '') {
                continue;
            }

            if (strpos($key, 'module_boilerplate_item_title-') !== false) {
                $id = (int)trim(substr($key, strlen('module_boilerplate_item_title-')));
                $boilerplate_title[$id] = $value;
            }

            if (strpos($key, 'module_boilerplate_item_content-') !== false) {
                $id = (int)trim(substr($key, strlen('module_boilerplate_item_content-')));
                $boilerplate_content[$id] = $value;
            }
        }
        $this->add_property('boilerplate_title', $boilerplate_title);
        $this->add_property('boilerplate_content', $boilerplate_content);
        
        $num = sizeof($boilerplate_title);
        $list = array();
        $boilerplate = array();
        $boilerplate[] = array('title' => $this->text['txt_module_name'], 'content' => '');
        $last = MODULE_BOILERPLATE_COUNT_START;
        while (list($id, $val) = each($boilerplate_title))
        {
            $last = $id+1;
            
            if ($val == '' 
                    and (!isset($boilerplate_content[$id]) or $boilerplate_content[$id] == '')) {
                continue;
            }
            $list[] = 'module_boilerplate_item_title-' . $id;
            $list[] = 'module_boilerplate_item_content-' . $id;
            
            $boilerplate[$id]['title'] = $val;
            $boilerplate[$id]['content'] = htmlentities($boilerplate_content[$id]);
        }
        $this->add_property('boilerplate', $boilerplate);
        $list[] = 'module_boilerplate_item_title-' . $last;
        $list[] = 'module_boilerplate_item_content-' . $last;
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array_merge(array(  
                                        'module_boilerplate_item_title-' . MODULE_BOILERPLATE_COUNT_START,
                                        'module_boilerplate_item_content-' . MODULE_BOILERPLATE_COUNT_START,
                                        ), $list)
                                );
        
        
        // Get settings from database
        $this->get_settings();
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
        
        $form['module_boilerplate_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );
        
        
        
        

        $this->boilerplate_settings($settings);
        $boilerplate_title   = $this->get_property('boilerplate_title');
        $boilerplate_content =  $this->get_property('boilerplate_content');


        $last = MODULE_BOILERPLATE_COUNT_START;
        foreach ($boilerplate_title AS $id => $value)
        {
            $last = $id+1;
            
            if ($value == '' 
                    and (!isset($boilerplate_content[$id]) or $boilerplate_content[$id] == '')) {
                continue;
            }
            $form['module_boilerplate_item_title-' . $id] = array(
                'type'          => 'string',
                'label'         => $this->text['txt_title'],
                'description'   => '',
                'required'      => false
                );
            $form['module_boilerplate_item_content-' . $id] = array(
                'type'          => 'textarea',
                'label'         => $this->text['txt_content'],
                'description'   => '',
                'required'      => false,
                'attribute'     => array('cols' => 10, 'rows' => 3)
                );
        }
        
        
        $form['module_boilerplate_item_title-' . $last] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_title'],
            'description'   => '',
            'required'      => false
            );
        $form['module_boilerplate_item_content-' . $last] = array(
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
        if ($trigger == 'backend_textarea') {
            $this->boilerplate_settings($settings);
            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');        
            
            $out->assign($this->text);
            $out->assign('boilerplate', $this->get_property('boilerplate'));
            $out->assign('form', $data['form']);
            $out->assign('field', $data['field']);
            
            $boilerplate_content = $out->fetch('boilerplate.tpl.html');
            $this->set_output($trigger, $boilerplate_content);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
