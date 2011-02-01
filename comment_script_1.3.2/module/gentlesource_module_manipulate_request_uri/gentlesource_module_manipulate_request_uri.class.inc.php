<?php

/** 
 * GentleSource Module
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Filter content
 */
class gentlesource_module_manipulate_request_uri extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();
    
    var $replacement = '';
    var $maximum = 10000;

// -----------------------------------------------------------------------------




    /**
     *  Setup
     * 
     * @access public
     */
    function gentlesource_module_manipulate_request_uri()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_uri'
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_manipulate_request_uri_active',
                                        'module_manipulate_request_uri_type',
                                        'module_manipulate_request_uri_levels'
                                        )
                                );
        
        // Default values
        $this->add_property('module_manipulate_request_uri_active', 'N'); // Y/N
        $this->add_property('module_manipulate_request_uri_type',   'none'); // See $this->setup()
        $this->add_property('module_manipulate_request_uri_levels',  '0');
        
        // Get settings from database
        $this->get_settings();

        // Set module status 
        $this->status('module_manipulate_request_uri_active', 'N');
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
        
        $form['module_manipulate_request_uri_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_activate_module'],
            'description'   => $this->text['txt_activate_module_description'],
            'required'      => true
            );            
        
        $form['module_manipulate_request_uri_type'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_manipulation_type'],
            'description'   => '',
            'required'      => true,
            'option'        => array(
                                'none'          => $this->text['txt_none'],         // Do nothing
                                'parameters'    => $this->text['txt_parameters'],   // Cut off parameters includng question mark
                                'filename'      => $this->text['txt_filename'],     // Cut off file name
                                'cutdir'        => $this->text['txt_cutdir'],       // Cut off directory/directories - counted from the end
                                'keepdir'       => $this->text['txt_keepdir'],      // Keep directory/directories and cut the rest of - countend from the start
                                'all'           => $this->text['txt_all'],          // Cut off everything, keep only domain                               
                                ),
            );
        
        $form['module_manipulate_request_uri_levels'] = array(
            'type'          => 'numeric',
            'label'         => $this->text['txt_directory_levels'],
            'description'   => $this->text['txt_directory_levels_description'],
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
        if ($trigger == 'frontend_uri') {
            $this->modify($data);
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Modify content
     * 
     * @access public
     */
    function modify(&$input)
    {
        if (!is_array($input) or sizeof($input) <= 0) {
            return false;
        }

        $type   = $this->get_property('module_manipulate_request_uri_type');
        $levels = (int) $this->get_property('module_manipulate_request_uri_levels');
        
        foreach ($input AS $field => $content)
        {
            if ($content == '') {
                continue;
            }
            
            $temp_content = $content;
            
            switch ($type) {
				case 'parameters':
                    if (strpos($temp_content, '?') !== false) {
					   $temp_content = substr($temp_content, 0, strpos($temp_content, '?'));
                    }
					break;
			
				case 'filename':
                    if (strpos($temp_content, '?') !== false) {
                       $temp_content = substr($temp_content, 0, strpos($temp_content, '?'));
                    }
                    if (strrpos($temp_content, '/') !== false) {
                        $temp_content = substr($temp_content, 0, strrpos($temp_content, '/')+1);
                    }
					break;
			
				case 'cutdir':
                    if (strpos($temp_content, '?') !== false) {
                       $temp_content = substr($temp_content, 0, strpos($temp_content, '?'));
                    }
                    if (strrpos($temp_content, '/') !== false) {
                        $temp_content = substr($temp_content, 0, strrpos($temp_content, '/')+1);
                    }
                    for ($i = 1; $i <= $levels + 1; $i++)
                    {
                        if (strrpos($temp_content, '/') !== false) {
                            $temp_content = substr($temp_content, 0, strrpos($temp_content, '/'));
                        }
                    }
                    $temp_content = $temp_content . '/';
					break;
			
				case 'keepdir':
                    if (strpos($temp_content, '?') !== false) {
                       $temp_content = substr($temp_content, 0, strpos($temp_content, '?'));
                    }
                    if (strrpos($temp_content, '/') !== false) {
                        $temp_content = substr($temp_content, 0, strrpos($temp_content, '/')+1);
                    }
                    $arr = explode('/', $temp_content);
                    $num = 0;
                    $new_arr = array();
                    foreach ($arr AS $dir)
                    {
                        if ($levels == 0) {
                            break;
                        }
                        if ($dir == '') {
                            continue;
                        }
                        $new_arr[] = $dir . '/';
                        $num++;
                        if ($num >= $levels) {
                            break;
                        }
                    }
                    $temp_content = implode('', $new_arr);
                    $temp_content = '/' . $temp_content;
					break;
			
                case 'all':
                    $temp_content = '/';
                    break;
                    
                case 'none':
				default:
					break;
			}
            
            $input[$field] = $temp_content;
        }
    }



} // End of class








?>
