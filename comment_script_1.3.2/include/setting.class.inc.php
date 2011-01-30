<?php

/** 
 * GentleSource Comment Script - setting.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//require_once 'database.class.inc.php';




/**
 * Handle comments
 */
class c5t_setting
{




// -----------------------------------------------------------------------------




    /**
     * Write setting to database
     * 
     * @access public
     */
    function write($name, $value)
    {
        global $c5t;
        
        if (c5t_setting::read($name)) {
            $data = array('setting_value' => $value);
            $where = "setting_name = ?";
            $where_data = array($name);
            c5t_database::update('setting', $data, $where, $where_data);
        } else {
            $data = array('setting_name' => $name, 'setting_value' => $value);
            c5t_database::insert('setting', $data);
        }
        $c5t[$name] = $value;
    }

// -----------------------------------------------------------------------------




    /**
     * Get setting from database
     * 
     * @access public
     */
    function read($name)
    {
        global $c5t;
        
        $sql = "SELECT setting_name, setting_value 
                FROM " . C5T_SETTING_TABLE . "
                WHERE setting_name = ?";
        if ($db = c5t_database::query($sql, array($name))) {
            $res = $db->fetchRow();
            if (sizeof($res) > 0) {
                return $res;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Get settings
     */
    function read_all()
    {
        global $c5t;
        $list = array();
        $sql = "SELECT      setting_name, setting_value 
                FROM        " .  C5T_SETTING_TABLE;
                
        if ($db = c5t_database::connection()) {            
            if ($res =& $db->query($sql)) {
                if (PEAR::isError($res)) {
                    system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                    system_debug::add_message('SQL Statement', $sql, 'error');
                    return false;
                }
                while ($row = $res->fetchRow()) 
                {
//                    if (!in_array($row['setting_name'], $c5t['setting_names'])) {
//                        continue;
//                    }
                    c5t_clean_output($row);                
                    $list[$row['setting_name']] = $row['setting_value'];
                }
            }
        }
        return $list;
    }

//------------------------------------------------------------------------------




} // End of class








?>
