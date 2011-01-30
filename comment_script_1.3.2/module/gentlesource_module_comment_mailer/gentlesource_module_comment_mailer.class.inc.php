<?php

/** 
 * GentleSource Module
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Mail comments to admin
 */
class gentlesource_module_comment_mailer extends gentlesource_module_common
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
    function gentlesource_module_comment_mailer()
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
                                        'module_comment_mailer_active',
                                        'module_comment_mailer_recipient',
                                        )
                                );
        
        // Default values
        $this->add_property('module_comment_mailer_active',  'N');
        
        // Get settings from database
        $this->get_settings();

        // Set module status 
        $this->status('module_comment_mailer_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     * 
     * @access public
     */
    function administration()
    {
        $form = array();
        
        $form['module_comment_mailer_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_mailer'],
            'description'   => $this->text['txt_enable_mailer_description'],
            'required'      => true
            );
        
        $form['module_comment_mailer_recipient'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_recipient'],
            'description'   => $this->text['txt_recipient_description'],
            'required'      => true
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
        if ($trigger == 'frontend_save_content') {
            
            // Skip it if comment has been blocked
            if ($additional['page_allow_comment'] == 'N') {
                return false;
            }
            
            $enhance = array(
                            'comment_date'      => date($settings['text']['txt_date_format'], $data['comment_timestamp']),
                            'comment_time'      => date($settings['text']['txt_time_format'], $data['comment_timestamp']),
                            );
            $this->notification($settings, array_merge($data, $additional, $enhance));
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Send notification mail
     * 
     */
    function notification(&$settings, &$data)
    {
        $recipient = $this->get_property('module_comment_mailer_recipient');
        if ($recipient == '') {
            return false;
        }
        $recipient_list = explode(',', $recipient);
                
        // Create link
        $link = array(  $settings['server_protocol'],
                        $settings['server_name'],
                        str_replace('//', '/', $settings['script_url'] . '/admin/')
                        );

        // Start output handling
        $out = $this->get_output_object();
        $out->set_template_dir($this->get_property('module_path') . 'template/');     
        $out->assign($this->text);
        $out->assign($settings['text']);
        $out->assign('link', join('', $link));         
        $out->assign($data); 
        $mail_body = $out->fetch('notification.tpl.txt');
        
        // Send mail off
        foreach ($recipient_list AS $address)
        {        
            $this->send_mail(   trim($address), 
                                $this->text['txt_notification_mail_subject'],                            
                                $mail_body, 
                                $settings['mail_from']);
        }
        
    }

// -----------------------------------------------------------------------------




} // End of class








?>
