<?php
 
/**
 * GentleScource Comment Script
 * 
 * @package		Download
 * @author      Ralf Stadtaus, <info@stadtaus.com>
 * @copyright   (C) Ralf Stadtaus , {@link http://www.stadtaus.com/}
 * 
 */


define('DH_FILE', 1);
define('DH_DATA', 2);
define('DH_RESOURCE', 3);




/**
 * Handle downloads
 */
class c5t_download
{




    /**
     * Send download to browser
     * 
     * @param string $file_name Download file name including complete path
     * @param string $new_file_name Public file name
     * @param string $type FILE|DATA|RESOURCE
     */
    function send($content, $name = null, $type = DH_FILE)
    {
        // Backward compatibilty for ob_list_handlers
        if (!function_exists('ob_list_handlers')) {
            function ob_list_handlers()
            {
                $res = array();
                if (ini_get('output_buffering')) {
                    $res[] = 'default output handler';
                }
                return $res;
            }
        }
      
      
        require_once 'Download.php';
        $dl = &new HTTP_Download();
   
        switch ($type) {
			case DH_FILE:
                if (is_file($content)) {
				    $dl->setFile($content);
                } else {
                    system_debug::add_message('Download File/Path Not Found', $content, 'error');
                    return false;
                }
				break;
		
			case DH_DATA:
                $dl->setData($content);
				break;
		
			case DH_RESOURCE:
                $dl->setResource($content);
				break;
		
			default:
                return false;
				break;
		}

        $dl->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT, $name);
        
        // Set content type        
        if (preg_match('#Opera(/| )([0-9].[0-9]{1,2})#', getenv('HTTP_USER_AGENT')) or 
            preg_match('#MSIE ([0-9].[0-9]{1,2})#', getenv('HTTP_USER_AGENT'))) {
                
            $content_type = 'application/octetstream';
        } else {
            $content_type = 'application/octet-stream';
        }
        $dl->setContentType($content_type);
        $res = $dl->send();
        
        if (PEAR::isError($res)) {
            system_debug::add_message('Send Download Failed', $res->message, 'error');
        } else {
            exit;
        }
    }
}









?>
