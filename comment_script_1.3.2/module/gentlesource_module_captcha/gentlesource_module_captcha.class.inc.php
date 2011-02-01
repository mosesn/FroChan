<?php

/**
 * GentleSource Guestbook Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * Dependencies:
 * PEAR Package: Text_Password
 * PEAR Package: Image_Text
 * PEAR Package: Find
 * PHP Extension: gd
 */


define('MODULE_CAPTCHA_IMAGE_FOLDER', 'image/');




/**
 * Manage modules
 */
class gentlesource_module_captcha extends gentlesource_module_common
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
    function gentlesource_module_captcha()
    {
        $this->text = $this->load_language();

        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',
                                array(  'frontend_comment_form',
                                        'frontend_save_content',
                                        'standalone'
                                        )
                                );

        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',
                                array(
                                        'module_captcha_active',
                                        'module_captcha_alternative',
                                        'module_captcha_garbage_collector_active',
                                        'module_captcha_font_size',
                                        'module_captcha_image_width',
                                        'module_captcha_image_height',
                                        )
                                );

        // Default values
        $this->add_property('module_captcha_active',                    'N');
        $this->add_property('module_captcha_alternative',               'Y');
        $this->add_property('module_captcha_garbage_collector_active',  'Y');
        $this->add_property('module_captcha_font_size',                 20);
        $this->add_property('module_captcha_image_width',               150);
        $this->add_property('module_captcha_image_height',              60);
        $this->add_property('module_captcha_phrase_length',              5);

        // Get settings from database
        $this->get_settings();

        // Set module status
        $this->status('module_captcha_active', 'N');
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

        $form['module_captcha_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_captcha'],
            'description'   => $this->text['txt_enable_captcha_description'],
            'required'      => true
            );

        $form['module_captcha_font_size'] = array(
            'type'          => 'numeric',
            'label'         => $this->text['txt_font_size'],
            'description'   => '',
            'required'      => true
            );

        $form['module_captcha_image_width'] = array(
            'type'          => 'numeric',
            'label'         => $this->text['txt_image_width'],
            'description'   => '',
            'required'      => true
            );

        $form['module_captcha_image_height'] = array(
            'type'          => 'numeric',
            'label'         => $this->text['txt_image_height'],
            'description'   => '',
            'required'      => true
            );

        $form['module_captcha_alternative'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_alternative_captcha'],
            'description'   => $this->text['txt_alternative_captcha_description'],
            'required'      => true
            );

        $form['module_captcha_garbage_collector_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_garbage_collector'],
            'description'   => $this->text['txt_garbage_collector_description'],
            'required'      => true
            );

        return $form;
    }

// -----------------------------------------------------------------------------




    /**
     *
     * @param $trigger
     * @param $settings Global settings
     *
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        if ($trigger == 'frontend_comment_form') {
            // Generate captcha content
            if ($captcha = $this->create_captcha($settings)) {
                $this->set_output($trigger, $captcha);
            }

            // Garbage collector
            if ($this->get_property('module_captcha_garbage_collector_active') == 'Y') {
                $gc_config = array(
                                'number'    => 20,
                                'directory' => $this->get_property('system_root') . $settings['cache_directory'],
                                'time'      => 60,
                                'prefix'    => get_class($this) . '_'
                                );
                $this->delete($gc_config);
            }
        }

        if ($trigger == 'frontend_save_content') {
            if (!$this->check_captcha($settings)) {
                $settings['message']['module_spam_check'] = $this->text['txt_captcha_try_again'];
                $additional['page_allow_comment'] = 'N';
            }
        }

        if ($trigger == 'standalone'
                and $data['data'] == 'initiatecaptcha') {
            $this->get_session_property('phrase');
            return true;
        }

        if ($trigger == 'standalone'
                and preg_match('/^captcha/', $data['data']) != false
                and $this->get_property('module_captcha_alternative') == 'Y') {
            $position = (int)substr($data['data'], strpos($data['data'], '_')+1);
            $phrase = $this->get_session_property('phrase');
            $character = strtolower($phrase{$position});
            $source_image = $file = $this->get_property('module_path') . MODULE_CAPTCHA_IMAGE_FOLDER . 'captcha_' . $character . '.png';
            header('Content-Type: image/png');
            echo $png = file_get_contents($source_image);
            exit;
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Create captcha
     *
     * @access public
     */
    function create_captcha(&$settings)
    {
        require_once 'Text/CAPTCHA.php';

        $captcha_error = false;

        // Set CAPTCHA options (font must exist!)
        $options = array(
            'font_size'     => $this->get_property('module_captcha_font_size'),
            'font_path'     => $this->get_property('module_path') . 'font/',
            'font_file'     => 'daft.ttf',
            'phrase_len'    => $this->get_property('module_captcha_phrase_length'),
            );



        if ($this->get_property('module_captcha_alternative') == 'N') {

            // Generate a new Text_CAPTCHA object, Image driver
            $c = Text_CAPTCHA::factory('Image');

            $retval = $c->init( (int)$this->get_property('module_captcha_image_width'),
                                (int)$this->get_property('module_captcha_image_height'),
                                null,
                                $options);
            if (PEAR::isError($retval)) {
                return false;
            }

            // Get CAPTCHA image (as PNG)
            $png = $c->getCAPTCHAAsPNG();
            if (PEAR::isError($png)) {
                return false;
            }
            $captcha_phrase = $c->getPhrase();
        } else {
            $captcha_phrase = Text_Password::create($this->get_property('module_captcha_phrase_length'));

        }


        // Get CAPTCHA secret passphrase
        $this->set_session_property(array('phrase' => $captcha_phrase));
        unset($GLOBALS['phrase']);


        // Create captcha content
        if ($this->get_property('module_captcha_alternative') == 'N') {
            $image_path =   $this->get_property('system_root') .
                            $settings['cache_directory'] .
                            get_class($this) . '_' .
                            session_id() .
                            '.png';

            file_put_contents($image_path, $png);

            $image_url =    $settings['script_url'] .
                            $settings['cache_directory'] .
                            get_class($this) . '_' .
                            session_id() .
                            '.png';
            $captcha_image = '<img src="' . $image_url . '?'. time() . '" alt="" style="vertical-align:middle;" />';

        } else {
            //$image_url =    $settings['script_url'] . basename($_SERVER['SCRIPT_NAME']) . '?module=captcha_';
            $image_url =    '?module=captcha_';
            $captcha_image = '';
            $num = strlen($captcha_phrase)-1;
            for ($i = 0; $i <= $num; $i++)
            {
                $captcha_image .= '<img src="' . $image_url . $i . '&amp;'. time() . '" alt="" style="vertical-align:middle;" />';
            }
        }


        // Output captcha
        $out = $this->get_output_object();
        $out->set_template_dir($this->get_property('module_path') . 'template/');
        $out->assign($this->text);
        $out->assign('captcha_image', $captcha_image);
        return $out->fetch('captcha.tpl.html');
    }

// -----------------------------------------------------------------------------





    /**
     * Check captcha
     *
     * @access public
     */
    function check_captcha(&$settings)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST'
                and isset($settings['_post']['save'])) {

            if (is_file($this->get_property('system_root') . $settings['cache_directory'] . session_id() . '.png')) {
                unlink($this->get_property('system_root') . $settings['cache_directory'] . session_id() . '.png');
            }

            if (isset($settings['_post']['phrase'])
                    and $phrase = $this->get_session_property('phrase')
                    and strlen($settings['_post']['phrase']) > 0
                    and strlen($phrase) > 0
                    and strtolower($settings['_post']['phrase']) == strtolower($phrase)) {

                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Get file list
     */
    function file_list($config)
    {
        if (!is_dir($config['directory'])) {
            return false;
        }
        include 'Find.php';
        $items = &File_Find::glob(  '#' . preg_quote($config['prefix']) . '([a-zA-Z0-9]+)\.png#',
                                    $config['directory'],
                                    'perl');

        if (!is_array($items) or sizeof($items) <= 0) {
            return false;
        }
        $list = array();
        while (list($key, $val) = each($items))
        {
            if (sizeof($list) >= ($config['number'] - 1)) {
                return $list;
            }
            $diff = (time() - filectime($config['directory'] . $val))/60;

            if ($diff > $config['time']) {
                $list[] = $val;
            }
        }
        return $list;
    }

// -----------------------------------------------------------------------------




    /**
     * Delete files
     */
    function delete($config)
    {
        if ($list = $this->file_list($config)) {
            if (!is_array($list)) {
                return false;
            }
            reset($list);
            while (list($key, $val) = each($list))
            {
                if (!is_file($config['directory'] . $val)) {
                    continue;
                }
                unlink($config['directory'] . $val);
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
