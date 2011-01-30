<?php

/**
 * GentleSource
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 */




/**
 * Module Smiley
 */
class gentlesource_module_smiley extends gentlesource_module_common
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
    function gentlesource_module_smiley(&$settings)
    {
        $this->text = $this->load_language();

        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',
                                array(
                                        'module_smiley_active',
                                        'module_smiley_size',
                                        )
                                );

        // Default values
        $this->add_property('module_smiley_active',     'N');
        $this->add_property('module_smiley_size',       'smiley_16'); // smiley_16, smiley_24, smiley_32


        // Get settings from database
        $this->add_property('trigger',
                                array(
                                        'frontend_textarea',
                                        'backend_textarea',
                                        'frontend_content',
                                        'backend_content',
                                        'module_demo',
                                        )
                                );
        $this->get_settings();

        // Set module status
        $this->status('module_smiley_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     * Smiley settings
     */
    function smiley_settings(&$settings)
    {
        // Smiley list
        $search = array(
                        ';-)',
                        ':-)',
                        ':-D',
                        ':-(',
                        ':-o',
                        ':-O',
                        'B-)',
                        ':oops:',
                        ':-[]',
                        ':-P',
                        );
        $replace = array(
                        'wink.png',
                        'smile.png',
                        'laugh.png',
                        'frown.png',
                        'gasp.png',
                        'angry.png',
                        'cool.png',
                        'embarrassed.png',
                        'foot_in_mouth.png',
                        'sticking_out_tounge.png',
                        );

        $this->add_property('module_smiley_search',  $search);
        $this->add_property('module_smiley_replace_raw',  $replace);

        // Get settings from database
        $this->get_settings();


        // Add image path
        $temp = array();
        $form = array();
        $path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/' . $this->get_property('module_smiley_size') . '/';
        while (list($key, $val) = each($replace))
        {
            $temp[] = '<img src="' . $path . $val . '" align="absmiddle" alt="' . $search[$key] . '" title="' . $search[$key] . '" />';
        }
        $this->add_property('module_smiley_replace',  $temp);
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     *
     * @access public
     */
    function administration(&$settings)
    {
        $this->smiley_settings($settings);

        $form = array();

        $form['module_smiley_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );

        $form['module_smiley_size'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_size'],
            'description'   => '',
            'required'      => true,
            'option'        => array(   'smiley_16' => $this->text['txt_small'],
                                        'smiley_24' => $this->text['txt_medium'],
                                        'smiley_32' => $this->text['txt_large'],
                                        )
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
        if ($trigger == 'module_demo'
                and isset($data['module'])
                and trim($data['module']) == get_class($this)) {

            $this->smiley_settings($settings);

            $path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/' . $this->get_property('module_smiley_size') . '/';
            $form = array();
            $replace = $this->get_property('module_smiley_replace_raw');
            $search = $this->get_property('module_smiley_search');
            while (list($key, $val) = each($replace))
            {
                $form[] = '<img src="' . $path . $val . '" align="absmiddle" alt="' . $search[$key] . '" title="' . $search[$key] . '" border="0" />';
            }

            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');
            $out->assign('smiley_list', $form);
            $smiley_content = $out->fetch('smileydemo.tpl.html');
            $this->set_output($trigger, $smiley_content);
        }

        $this->smiley_settings($settings);

        if ($trigger == 'frontend_content'
                or $trigger == 'backend_content') {


            $replace = $this->get_property('module_smiley_replace');
            $data['frontend_text'] = str_replace(
                                        $this->get_property('module_smiley_search'),
                                        $replace,
                                        $data['frontend_text']
                                        );
            if (isset($data['frontend_teaser'])) {
                $data['frontend_teaser'] = str_replace(
                                            $this->get_property('module_smiley_search'),
                                            $replace,
                                            $data['frontend_teaser']
                                            );
            }
        }

        if ($trigger == 'frontend_textarea'
                or $trigger == 'backend_textarea') {


            $path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/' . $this->get_property('module_smiley_size') . '/';
            $form = array();
            $replace = $this->get_property('module_smiley_replace_raw');
            $search = $this->get_property('module_smiley_search');

            $input_mode = 'plain';
            if ($trigger == 'frontend_textarea'
                    and isset($settings['frontend_wysiwyg'])
                    and $settings['frontend_wysiwyg'] == 'Y') {
                $input_mode = 'wysiwyg';
            }
            if ($trigger == 'backend_textarea'
                    and isset($settings['enable_wysiwyg'])
                    and $settings['enable_wysiwyg'] == 'Y') {
                $input_mode = 'wysiwyg';
            }


            while (list($key, $val) = each($replace))
            {

                if ($input_mode == 'wysiwyg') {
                    $form[] = '<a href="javascript:void(0);" onclick="tinyMCE.execCommand(\'mceInsertContent\', false, \''. $search[$key] . '\');"><img src="' . $path . $val . '" align="absmiddle" alt="' . $search[$key] . '" title="' . $search[$key] . '" border="0" /></a>';
                } else {
                    $form[] = '<a href="" onclick="var text_area = document.form.' . $data['field'] . '; text_area.value += \''. $search[$key] . '\'; text_area.focus();return false;"><img src="' . $path . $val . '" align="absmiddle" alt="' . $search[$key] . '" title="' . $search[$key] . '" border="0" /></a>';
                }
            }

            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');
            $out->assign('smiley_list', $form);
            $smiley_content = $out->fetch('smiley.tpl.html');
            $this->set_output($trigger, $smiley_content);
        }
    }




} // End of class








?>
