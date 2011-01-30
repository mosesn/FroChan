<?php

/**
 * GentleSource Installation Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */






/**
 *
 */
class c5t_installation
{




    /**
     * Manage installation
     *
     */
    function c5t_installation()
    {
        global $c5t;


        $c5t['current_language']    = c5t_language::get($c5t['default_language']);
        $c5t['text']                = c5t_language::load($c5t['current_language']);

        $c5t['output'] = array(
            'login_status'                  => false,
            'display_language_selection'    => $c5t['display_language_selection'],
            'language_selector_mode'        => $c5t['language_selector_mode'],
            'available_languages'           => $c5t['available_languages'],
            'page_url_encoded'              => urlencode($c5t['server_protocol'] . $c5t['server_name'] . c5t_request_uri()),
            );

    }

// -----------------------------------------------------------------------------




    /**
     * Check if dbconfig.php exists
     *
     */
    function status()
    {
        if (is_file(C5T_ROOT . 'cache/dbconfig.php')) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Start installation process
     *
     */
    function start()
    {
        global $c5t;


        // Configuration
        $detail_template                = 'installation.tpl.html';
        $message                        = array();

        // Includes
        require_once 'HTML/QuickForm.php';

        // Start output handling
        $out = new c5t_output($detail_template);

        // Start form field handling
        $form = new HTML_QuickForm('install', 'POST');
        require_once 'installation_form.inc.php';



        // Check requirements
        $script_path = str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF'])));
        if (!is_writable(C5T_ROOT)) {
            $script_folder_writable_status = sprintf($c5t['text']['txt_not_okay'] . ': ' . $c5t['text']['txt_script_folder_not_writable'], $script_path);
        } else {
            $script_folder_writable_status = sprintf($c5t['text']['txt_okay'] . ': ' . $c5t['text']['txt_script_folder_is_writable'], $script_path);
        }
        if (!is_writable($c5t['cache_path'])) {
            $cache_folder_writable_status = sprintf($c5t['text']['txt_not_okay'] . ': ' . $c5t['text']['txt_cache_folder_not_writable'], '/' . $c5t['cache_directory']);
        } else {
            $cache_folder_writable_status = sprintf($c5t['text']['txt_okay'] . ': ' . $c5t['text']['txt_cache_folder_is_writable'], '/' . $c5t['cache_directory']);
        }


        // Validate form
        $show_form = true;
        $db_error  = false;
        if ($form->validate()) {
            define('C5T_COMMENT_TABLE',     strtolower($c5t['_post']['prefix']) . 'comment');
            define('C5T_IDENTIFIER_TABLE',  strtolower($c5t['_post']['prefix']) . 'identifier');
            define('C5T_SETTING_TABLE',     strtolower($c5t['_post']['prefix']) . 'setting');

            $c5t['tables']['comment']       = C5T_COMMENT_TABLE;
            $c5t['tables']['identifier']    = C5T_IDENTIFIER_TABLE;
            $c5t['tables']['setting']       = C5T_SETTING_TABLE;
            $dsn = array(   'phptype'   => 'mysql',
                            'hostspec'  => $c5t['_post']['hostname'],
                            'database'  => $c5t['_post']['database'],
                            'username'  => $c5t['_post']['username'],
                            'password'  => $c5t['_post']['dbpassword']
                            );
            if (!$db = $this->connect($dsn)) {
                $c5t['message'][] = $c5t['text']['txt_enter_correct_database_data'];
            } else {
                if (!$this->process($dsn)) {
                    $c5t['message'][]  = $c5t['text']['txt_installation_failed'];
                } else {

                    // Set admin account
                    $arr = array(   'login'     => $c5t['_post']['login_name'],
                                    'email'     => $c5t['_post']['email'],
                                    'password'  => md5($c5t['_post']['password'])
                                    );
                    $ser = serialize($arr);
                    c5t_setting::write('administration_login', $ser);

                    // Set script URL
                    // $script_url = str_replace('admin', '', str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])));
                    $script_url = str_replace('//', '/', str_replace('admin', '', str_replace('\\', '/', dirname($_SERVER['PHP_SELF']) . '/')));
                    c5t_setting::write('script_url', $script_url);
                    c5t_setting::write('database_version', $c5t['version']);

                    $c5t['message'][]  = $c5t['text']['txt_installation_successful'];
                    $show_form = false;



                    $write_file = true;

                    // Write dbconfig.php file
                    $dsn['prefix'] = strtolower($c5t['_post']['prefix']);
                    if (!$this->install_file(
                                    C5T_ROOT . 'include/dbconfig.php.tpl',
                                    $dsn,
                                    C5T_ROOT . 'cache/dbconfig.php')) {
                        $write_file = false;
                        $c5t['message'][]  = $c5t['text']['txt_write_dbconfig_failed'];
                    }

                    // Write include.php file
                    // Deprecated in order to prevent file write into the script's root folder
//                    $include_data = array('server_script_path' => str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF']))));
//                    if (!$this->install_file(
//                                    C5T_ROOT . 'include/include.php.tpl',
//                                    $include_data,
//                                    C5T_ROOT . 'include.php')) {
//                        $write_file = false;
//                        $c5t['message'][]  = $c5t['text']['txt_write_include_failed'];
//                    }

                }
            }

        } else {
            if (sizeof($c5t['_post']) > 0) {
                $c5t['message'][] = $c5t['text']['txt_fill_out_required'];
            }
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);

        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray());


        // Output
        $out->assign(array(
                'show_form'                     => $show_form,
                'cache_folder_writable_status'  => $cache_folder_writable_status,
                'script_folder_writable_status' => $script_folder_writable_status,
                )
            );

        $out->finish();
        exit;
    }

// -----------------------------------------------------------------------------




    /**
     * Connect to database
     *
     * @access private
     */
    function connect($dsn)
    {
        global $c5t;
        if (!isset($GLOBALS['database_connection'])) {
            $db =& MDB2::connect($dsn);
            if (PEAR::isError($db)) {
                system_debug::add_message($db->getMessage(), $db->getDebugInfo(), 'system');
            } else {
                $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
                $GLOBALS['database_connection'] = $db;
            }
        }
        if (isset($GLOBALS['database_connection'])) {
            return $GLOBALS['database_connection'];
        }
    }

//------------------------------------------------------------------------------




    /**
     * Database query
     *
     * @access public
     * @param string $sql SQL statement
     *
     * @return mixed  a new DB_result object for successful SELECT queries
     *                 or DB_OK for successul data manipulation queries.
     *                 A DB_Error object on failure.
     */
    function query($dsn, $sql)
    {
        if ($db = $this->connect($dsn)) {
            $res =& $db->query($sql);
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            } else {
                return $res;
            }
        }
    }

//------------------------------------------------------------------------------




    /**
     * Process SQL statements
     *
     * @access private
     */
    function process($dsn)
    {
        global $c5t;
        $file = C5T_ROOT . 'include/sql/install.sql';
        $error = false;
        if (is_file($file)) {
            $sql = $this->parse_sql(file($file));
            reset($sql);
            foreach ($sql AS $statement)
            {
                // Replace prefix
                $statement = str_replace('{prefix}', strtolower($c5t['_post']['prefix']), $statement);
                if (!$this->query($dsn, $statement)) {
                    $error = true;
                }
            }
        } else {
            system_debug::add_message('Install File Not Found', $file, 'system');
        }
        if ($error == false) {
            return true;
        }
    }

//------------------------------------------------------------------------------




    /**
     * Parse SQL file
     *
     * @access private
     */
    function parse_sql($sql)
    {
        if (!is_array($sql)) {
            $statement  = explode("\n", $sql);
        } else {
            $statement = $sql;
        }
        $num        = count($statement);
        $previous   = '';
        $result     = array();
        for ($i = 0; $i < $num; $i++) {
            $line = trim($statement[$i]);
            // Check for line breaks within lines
            if (substr($line, -1) != ';') {
                $previous .= $line;
                continue;
            }

            if ($previous != '') {
                $line = $previous . $line;
            }
            $previous = '';

            $result[] = $line;
        }

        if (isset($result)) {
            return $result;
        }
    }


//------------------------------------------------------------------------------




    /**
     * Write files
     *
     * @param Array $data Data to be written into file
     * @param String $path Path to the place where the file is to be written
     * @param String $template Path to the template to be used
     * @access static
     */
    function install_file($source, $data, $target)
    {
        $content = join('', file($source));

        reset($data);
        foreach ($data AS $marker => $value)
        {
            $content = str_replace('{$' . $marker . '}', $value, $content);
        }

        if (file_put_contents($target, $content)) {
            return true;
        }
    }


//------------------------------------------------------------------------------





} // End of class








?>
