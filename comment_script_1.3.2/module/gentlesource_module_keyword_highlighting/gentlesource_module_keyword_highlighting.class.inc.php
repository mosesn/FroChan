<?php

/** 
 * GentleSource Module
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 */




/**
 * Module Search Engine Referrer URL Parameter Keyword Highlighting
 */
class gentlesource_module_keyword_highlighting extends gentlesource_module_common
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
    function gentlesource_module_keyword_highlighting(&$settings)
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_content',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_keyword_highlighting_active',
                                        'module_keyword_highlighting_format',
                                        )
                                );
        
        // Default values
        $this->add_property('module_keyword_highlighting_active', 'N');
        
        
        // Referrer list
        $referrer_list = array(
                                array(  'site'  => 'google',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'alltheweb',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'altavista',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'aol',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'dmoz.org',
                                        'query' => 'search'),
                                        
                                array(  'site'  => 'fireball',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'seekport',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'google',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'hotbot',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'ixquick',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'looksmart',
                                        'query' => 'qt'),
                                        
                                array(  'site'  => 'lycos',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'mamma',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'msn',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'netscape',
                                        'query' => 's'),
                                        
                                array(  'site'  => 'search.com',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 't-online.de',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'ask.com',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'yahoo',
                                        'query' => 'p'),
                                        
                                array(  'site'  => 'wisenut',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'gigablast',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'scrubtheweb',
                                        'query' => 'keyword'),
                                        
                                array(  'site'  => 'entireweb',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'mojeek',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'ulysseek',
                                        'query' => 'query'),
                                        
                                array(  'site'  => 'searchhippo',
                                        'query' => 'q'),
                                        
                                array(  'site'  => 'intra',
                                        'query' => 'q'),
                                        
                                array(  'site'  => '',
                                        'query' => ''),
                                        
                            );
        $this->add_property('referrer_list', $referrer_list);
        $this->add_property('module_keyword_highlighting_format', 'style="background-color:#CCCCCC;"');


        // Get keywords
        if ($this->get_keywords()) {
            $this->add_property('module_keyword_highlighting_process', true);
        } else {
            $this->add_property('module_keyword_highlighting_process', false);
        }

        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_keyword_highlighting_active', 'N');
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
        
        $settings['module_keyword_highlighting_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );
            
        $settings['module_keyword_highlighting_format'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_highlight_format'],
            'description'   => $this->text['txt_highlight_format_description'],
            'required'      => true,
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
                and $this->get_property('module_keyword_highlighting_process') == true) {

            $format   = $this->get_property('module_keyword_highlighting_format');
            $keywords = $this->get_property('search_engine_keywords');

            $data['frontend_text'] = preg_replace(
                                        $this->get_property('module_highlighting_search'),
                                        $this->get_property('module_highlighting_replace'),
                                        $data['frontend_text']
                                        );

//            $data['frontend_teaser'] = preg_replace(
//                                        $this->get_property('module_highlighting_search'),
//                                        $this->get_property('module_highlighting_replace'),
//                                        $data['frontend_teaser']
//                                        );
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Get keywords
     * 
     * @access private
     */
    function get_keywords()
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        if ($_SERVER['HTTP_REFERER'] == '') {
            return false;
        }
        $referrer = htmlentities(strip_tags(urldecode($_SERVER['HTTP_REFERER'])));
        if (preg_match('#' . $_SERVER['HTTP_HOST']. '#i', $referrer) === 1) {
            return false;
        }
        $url = parse_url($referrer);
        $host  = $url['host'];
        $query = $url['query'];
        $referrer_list = $this->get_property('referrer_list');
        foreach ($referrer_list AS $val)
        {
            if ($val['site'] == '' or $val['query'] == '') {
                continue;
            }
            if (preg_match('#' . $host . '#i', $val['site']) === 1) {
                $keyword = substr($query, strpos($query, $val['query']) + strlen($val['query']) + 1);
                if (strpos($keyword, '&') !== false) {
                    $keyword = substr($keyword, 0, strpos($keyword, '&'));
                }
                if (strpos($keyword, ' ') !== false) {
                    $arr = explode(' ', $keyword);
                } else {
                    $arr = array($keyword);
                }
                $regular = array();
                foreach ($arr AS $key)
                {
                    $search[]   = '/((<[^>]*)|' . $key . ')/ie';                     
                    $replace[]  = '"\2"=="\1"? "\1":"<span $format>\1</span>"';
                }
                $this->add_property('module_highlighting_search', $search);
                $this->add_property('module_highlighting_replace', $replace);
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
