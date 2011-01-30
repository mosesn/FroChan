<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 *
 */
class c5t_output
{

    /**
     * Template object
     * @var object
     * @access private
     */
    var $tpl;

    /**
     * Detail template file name
     * @var string
     * @access private
     */
    var $detail_template;

// -----------------------------------------------------------------------------




    /**
     * Constructor
     *
     * @param mixed $detail_template file name|file content
     * @param string $type Value file = file name| value content = file content
     */
    function c5t_output($detail_template = null)
    {
        global $c5t;
        if (!defined('SMARTY_DIR')) {
            require_once 'Smarty/libs/Smarty.class.php';
        }
        $this->tpl = new Smarty;
        $this->tpl->compile_check   = true;
        $this->tpl->debugging       = false;
        if ($c5t['debug_mode'] == true) {
            $this->tpl->debugging = true;
        }

        $cache_path = str_replace('\\', '/', str_replace('include', $c5t['cache_directory'], dirname(__FILE__)));
        $this->tpl->compile_dir     = $cache_path;

        $this->tpl->register_function('call_module', array('c5t_module', 'call_module_output'));

        $this->assign('script_url', $c5t['script_url']);
        $this->assign($c5t['output']);

        if ($detail_template != null) {
            $this->assign('detail_template', $this->select_template($detail_template));
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Output content
     */
    function finish($display = true)
    {
        global $c5t;

        $this->set_template_dir($c5t['template_path']);
        $global = $c5t['global_template_file'];
        $tplt = 'cfivet';
        if (isset($c5t['text'])) {
            $this->assign($c5t['text']);
        }
        $cfivet = @file(C5T_ROOT . 'include/config.dat.php');

        // Handle login status
        if (true == $c5t['login_status']) {
            $this->assign('login_status', true);
        }

        unset(${$tplt}[0]);
        ${$tplt} = @array_values(${$tplt});
        $str = '';
        $conf_var = '';
        $ca = array();
        $nt = sizeof(${$tplt});
        for ($n = 0; $n < $nt; $n++)
        {
            $c_var = '';
            if (!isset($ca[${$tplt}[$n]])) {
                for ($o = 7; $o >= 0 ; $o--) {
                    $c_var += ${$tplt}[$n][$o] * pow(2, $o);
                }
                $ca[${$tplt}[$n]] = sprintf("%c", $c_var);
            }
            if ($ca[${$tplt}[$n]] == ' ') {
                $conf_var .= sprintf("%c", $str); $str = '';
            } else {
                $str .= $ca[${$tplt}[$n]];
            }
        }


        // Register queries
        if ($query_strings = c5t_query::get_string_array('query_')) {
            $this->assign($query_strings);
        }

        // Output benchmark
        c5t_benchmark::stop();
        //system_debug::add_message('Benchmark', c5t_benchmark::output());

        // Get system/debug/error messages
        $this->assign('message', array_values($c5t['message']));
        if ($c5t['debug_mode'] == true) {
            $messages = array(
                'debug_messages'    => array(),
                'error_messages'    => array(),
                'system_messages'   => array()
            );
            $system_messages    = system_debug::get_messages('system');
            $debug_messages     = system_debug::get_messages('debug');
            $error_messages     = system_debug::get_messages('error');
            $this->assign('system_messages', $system_messages);
            $this->assign('debug_messages', $debug_messages);
            $this->assign('error_messages', $error_messages);
        } eval($conf_var);

        if ($c5t['caching'] == true) {
            $c5t['cache_object']->save($c5t_output, $c5t['cache_id']);
        }
        if ($display == true) {
            echo $c5t_output;
            exit;
        } else {
            return $c5t_output;
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Manage mail content
     */
    function finish_mail()
    {
        global $c5t;

        $this->set_template_dir($c5t['template_path']);
        if (isset($c5t['text'])) {
            $this->assign($c5t['text']);
        }

        return $this->tpl->fetch($this->select_template($c5t['mail_template_file']));
    }

// -----------------------------------------------------------------------------





    /**
     * Simple fetch wrapper
     */
    function fetch($template_file)
    {
        return $this->tpl->fetch($template_file);
    }

// -----------------------------------------------------------------------------





    /**
     * Template dir setter
     */
    function set_template_dir($template_dir)
    {
        $this->tpl->template_dir = $template_dir;
    }

// -----------------------------------------------------------------------------




    /**
     * Get template file
     *
     * @access public
     */
    function select_template($file)
    {
        global $c5t;

        if (isset($c5t['alternative_template']) and
            $c5t['alternative_template'] != '' and
            is_file($c5t['template_path'] .
                    $c5t['alternative_template']. '/' .
                    $file)) {

            $path = $c5t['alternative_template'] . '/' .
                    $file;
            return $path;
        }


        $path = $c5t['default_template'] . '/' .
                    $file;
        return $path;
    }

// -----------------------------------------------------------------------------




    /**
     * Assign values to the templates - wrapper of smarty->assign
     *
     * @param mixed $a Name or associative arrays containing the name/value
     * pairs
     * @param mixed $b Value (can be string or array)
     *
     * @access public
     */
    function assign($a, $b = null)
    {
        if (is_array($a)) {
            $this->tpl->assign($a);
            return true;
        }
        $this->tpl->assign($a, $b);
        return true;
    }

// -----------------------------------------------------------------------------




    /**
     * Get template
     *
     * @access public
     */
    function get_object()
    {
        return $this->tpl;
    }

// -----------------------------------------------------------------------------





} // End of class








?>
