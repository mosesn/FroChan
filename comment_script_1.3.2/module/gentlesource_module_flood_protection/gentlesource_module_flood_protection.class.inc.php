<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Module Flood Protection
 */
class gentlesource_module_flood_protection extends gentlesource_module_common
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
    function gentlesource_module_flood_protection(&$settings)
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('hidden',       false);
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_save_content',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_flood_protection_mode',
                                        )
                                );
        
        // Default values
        $this->add_property('module_flood_protection_mode',  'off');
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_flood_protection_mode', 'off');
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

        $settings['module_flood_protection_mode'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true,
            'option'        => array(
                                'off'       => $this->text['txt_off'],
                                'moderate'  => $this->text['txt_moderate'],
                                'reject'    => $this->text['txt_reject']
                                ),
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
            
            if ($this->get_property('module_flood_protection_mode') == 'reject'
                    and $this->identical($data, $settings)) {
                $settings['message']['module_spam_check'] = $this->text['txt_error_content_block'];
                $additional['page_allow_comment'] = 'N';
                return false;
            }
            
            
            // Skip check if moderation has already been turned on
            if ($settings['enable_moderation'] == 'Y'
                    or isset($data['comment_status'])) {
                return false;
            }
            
            if ($this->get_property('module_flood_protection_mode') == 'moderate'
                    and $this->identical($data, $settings)) {
                $settings['message']['module_spam_check'] = $this->text['txt_notice_moderation'];
                $data['comment_status'] = 100;
                return false;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check for identical entry
     * 
     * This method assumes that this module is used on a table called "comment"
     * and with fields prefixed "comment_".
     * 
     */
    function identical($data, &$settings)
    {   
        $sql = "SELECT  comment_id
                FROM    " . $settings['tables']['comment'] . "
                WHERE   comment_text = ?";
        

        if ($db = c5t_database::query($sql, array($data['comment']))) {                        
            $res = $db->fetchRow();            
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            }                

            if (sizeof($res) > 0) {
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
