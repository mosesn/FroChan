<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//require_once 'database.class.inc.php';




/**
 * Handle backups
 */
class c5t_backup
{




    /**
     * 
     * 
     * @access public
     */
    function c5t_backup()
    {
    }

// -----------------------------------------------------------------------------




    /**
     * Export data
     * 
     * @access public
     */
    function export()
    {
        global $c5t;
        
        $path = C5T_ROOT . $c5t['backup_directory'];
        
        // Create directory and .htaccess
        if (!is_dir($path)) {
            mkdir($path);    
            $htcontent = "deny from all";
            file_put_contents($path . '.htaccess', $htcontent);
            
        }
        
        set_time_limit(600);
        ignore_user_abort(true);
        
        //Get database content
        $filename   = $path . $this->filename();          
        $source     = array("\x00", "\x0a", "\x0d", "\x1a");
        $target     = array('\0', '\n', '\r', '\Z');       
        $dump       = array();
        while(list($key, $val) = each($c5t['tables']))
        {
            if ($key == 'setting') {
                $sql = "SELECT * FROM " . $val . " WHERE setting_name != 'administration_login'";
            } else {
                $sql = "SELECT * FROM " . $val;
            }
            
            if ($res = c5t_database::query($sql)) {
                while ($row = $res->fetchRow())
                {
                    $tmp   = array();
                    $tmp[] = 'INSERT INTO `' . $val . '` ';
                    $tmp[] = '(`' . join('`, `', array_keys($row)) . '`)';
                    $tmp[] = ' VALUES ';
                    $tmp[] = "('" . str_replace($source, $target, join("', '", array_values($row))) . "');";
                    $dump[]= join('', $tmp);
                }                
            }
        }
        $content = join("\n", $dump);
        
        // Write file
        if (file_put_contents($filename, $content)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Create file name
     * 
     * @access public
     */
    function filename()
    {
        global $c5t;
        $filename  = $c5t['backup_file_prefix'];
        $filename .= date('Y-m-d_H-i-s', c5t_time::current_timestamp());
        $filename .= '.sql';
        return $filename;
    }

// -----------------------------------------------------------------------------




    /**
     * List available backup files
     * 
     * @access public
     */
    function file_list()
    {
        global $c5t;
        $list = array();
        if (!is_dir(C5T_ROOT . $c5t['backup_directory'])) {
//            return $list;
        }
        require_once 'Find.php';        
        if ($items = &File_Find::glob( '#' . $c5t['backup_file_prefix'] . '(.*?)\.sql#', C5T_ROOT . $c5t['backup_directory'], "perl" )) {
            if (PEAR::isError($items)) {
                system_debug::add_message($items->getMessage(), $items->getDebugInfo(), 'error', $items->getBacktrace());
                return $list;
            }
            arsort($items);
            while (list($key, $val) = each($items))
            {
                $date = $this->file_date($val);
                $time = str_replace('-', ':', substr($val, strrpos($val, '_') +1, 8));
                $list[] = array('file' => $val,
                                'path' => C5T_ROOT . $c5t['backup_directory'],
                                'date' => $date,
                                'time' => $time
                                );
            }
        }
        return $list;

    }

// -----------------------------------------------------------------------------




    /**
     * Create formatted date from file name
     * 
     * @access public
     */
    function file_date($val)
    {
        global $c5t;
        $date = substr($val, strpos($val, $c5t['backup_file_prefix']) + strlen($c5t['backup_file_prefix']), 10);
        $date = strtotime($date, c5t_time::current_timestamp());
        $date = c5t_time::format_date($date);
        return $date;

    }

// -----------------------------------------------------------------------------




    /**
     * Delete file
     * 
     * @access public
     */
    function delete($file)
    {
        global $c5t;
        if (is_file(C5T_ROOT . $c5t['backup_directory'] . trim($file))) {
            if (unlink(C5T_ROOT . $c5t['backup_directory'] . trim($file))) {
                return true;
            }
        }

    }

// -----------------------------------------------------------------------------




    /**
     * Import file
     * 
     * @access public
     */
    function import($file)
    {
        global $c5t;

        $file = C5T_ROOT . $c5t['backup_directory'] . trim($file);
        $error = false;
        set_time_limit(600);
        ignore_user_abort(true);
        if (is_file($file)) {
            if (!$sql = c5t_installation::parse_sql(file($file))) {
                return false;
            }
            reset($sql);
            
            // Truncate tables
            foreach ($c5t['tables'] AS $table => $name)
            {
                if ($table == 'setting') {
                    $del = 'DELETE FROM `' . $name . '` WHERE `setting_name` \!= ?';
                    $res = & c5t_database::query($del, array('administration_login'));                    
                } else {
                    c5t_database::query('TRUNCATE `' . $name . '`;');
                }
            }
            foreach ($sql AS $statement)
            {
                // Replace prefix
                if (!$res =& c5t_database::query($statement)) {
                    $error = true;
                } 
            }
        }
        if ($error == false) {
            return true;
        }

    }

// -----------------------------------------------------------------------------




} // End of class








?>
