<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('C5T_ROOT', '../');
define('C5T_ALTERNATIVE_TEMPLATE', 'admin');
define('C5T_LOGIN_LEVEL', 1);

$c5t_detail_template    = 'backup.tpl.html';

// -----------------------------------------------------------------------------




// Include
require C5T_ROOT . 'include/core.inc.php';

// Start output handling
$out = new c5t_output($c5t_detail_template);

// -----------------------------------------------------------------------------




require 'backup.class.inc.php';
$backup = new c5t_backup;


// Export database into file
if (c5t_gpc_vars('do') == 'ex') {
    if (false == $c5t['demo_mode']) {
        if ($backup->export()) {
            header('Location: ' . $c5t['server_protocol'] . $c5t['server_name'] . dirname($_SERVER['PHP_SELF']) . '/backup.php?e=s');
            exit;
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}
if (c5t_gpc_vars('e') == 's') {
    $c5t['message'][] = $c5t['text']['txt_export_successful'];
}

// -----------------------------------------------------------------------------




// Delete backup file
$delete_confirmation = array('dialogue' => 0);
if ($file = c5t_gpc_vars('f')
        and c5t_gpc_vars('do') == 'de') {
    $delete_confirmation = array(
                            'dialogue'      => 1,
                            'file' => $file
                            );
}
$out->assign('delete_confirmation', $delete_confirmation);
if ($identifier_id = c5t_gpc_vars('f')
        and c5t_gpc_vars('do') == 'dec') {   
    if (false == $c5t['demo_mode']) {
        if ($backup->delete($file)) {
            $c5t['message'][] = $c5t['text']['txt_delete_file_successful'];
        } else {        
            $c5t['message'][] = $c5t['text']['txt_delete_file_failed'];
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------




// Import backup file
$import_confirmation = array('dialogue' => 0);
if ($file = c5t_gpc_vars('f')
        and c5t_gpc_vars('do') == 'im') {
    $import_confirmation = array(
                            'dialogue'      => 1,
                            'file'          => $file
                            );
}
$out->assign('import_confirmation', $import_confirmation);
if ($identifier_id = c5t_gpc_vars('f')
        and c5t_gpc_vars('do') == 'imc') {   
    if (false == $c5t['demo_mode']) {
        if ($backup->import($file)) {
            header('Location: ' . $c5t['server_protocol'] . $c5t['server_name'] . dirname($_SERVER['PHP_SELF']) . '/backup.php?i=s');
            exit;
        } else {        
            $c5t['message'][] = $c5t['text']['txt_import_failed'];
        }
    } else {
        $c5t['message'][] = $c5t['text']['txt_disabled_in_demo_mode'];
    }
}
if (c5t_gpc_vars('i') == 's') {
    $c5t['message'][] = $c5t['text']['txt_import_successful'];
}

// -----------------------------------------------------------------------------




// Download backup file
if ($file = c5t_gpc_vars('f')
        and c5t_gpc_vars('do') == 'dl') {
    require_once 'download.class.inc.php';
    if (is_file(C5T_ROOT . $c5t['backup_directory'] . $file)){
        c5t_download::send(C5T_ROOT . $c5t['backup_directory'] . $file);
    }
}

// -----------------------------------------------------------------------------




// List available backup files
$out->assign('backup_files', $backup->file_list());

// -----------------------------------------------------------------------------




// Output
$out->finish();






?>
