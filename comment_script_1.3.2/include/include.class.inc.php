<?php

/** 
 * GentleSource
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */





/**
 * 
 */
class c5t_include
{

    /**
     * 
     */
    protected $options;

    /**
     * File content
     */
    protected $content = '';

    /**
     * Start position
     */
    protected $start = null;

    /**
     * Total number of elements
     */
    protected $sum = 0;

    /**
     * Number of current element
     */
    protected $num = null;

    /**
     * HTML Elements
     */
    protected $elements = array(
        '<table',
        '<p',
        '</p>',
        '<div',
        '</div>',
        '<br />',
        '<br>',
        '<span',
        );

    /**
     * 
     */
    public function __construct($options = null)
    {
        $this->options = $options;
        
        $this->content = file_get_contents($options['path']);
        
        
        // Number of elements
        foreach ($this->elements AS $element)
        {
            $x = split($element, $this->content);
            $this->sum += count($x);
        }
        
        $this->element();
    }

    /**
     * 
     */
    public function element()
    {
        global $c5t;
        
        if ($this->num != null) {
            return $this->num;
        }
        
        $num = 0;
        
        if (isset($c5t['_post']['num'])) {
            $num = (int) $c5t['_post']['num'];
        }
        
        if (isset($c5t['_post']['up'])) {
            $num = $num - 1;
        }
        if (isset($c5t['_post']['down'])) {
            $num = $num + 1;    
        }
        if (isset($c5t['_post']['quickup'])) {
             $num = $num - $this->options['quick'];
        }
        if (isset($c5t['_post']['quickdown'])) {
            $num = $num + $this->options['quick'];    
        }
        
        // Jump to the middle
        if (isset($c5t['_post']['middle'])) {
                $num = -$this->sum / 2;
            
        }
        
        // Jump to top position
        if (isset($c5t['_post']['top'])) {
            $num = -$this->sum;
        }
        
        
        // Jump to bottom position
        if (isset($c5t['_post']['bottom'])) {
            $num = 0;
        }
        
        if ($num > 0) {
            $num = 0;
        }

        $this->num = $num;
        return $this->num;
    }

    /**
     * 
     */
    public function position()
    {
        if ($this->start != null) {
            return $this->start;
        }
        // Position end of file
        $start_position = strlen($this->content);
        
        
        // Position of closing body tag
        if ($this->num >= 0) {
            $position = strrpos($this->content, '</body>');
            if ($position !== false) {
                $start_position = $position;
            }
        }
        
        // Position of an existing comment form
        //if ($num >= 0) {
        //    $position = strrpos($file_content, $insert);
        //    if ($position !== false) {
        //        $file_content = substr_replace($file_content, '', $position, strlen($insert));
        //        
        //    }    
        //}
        
        
        // Changed position
        if ($this->num < 0) {
            $temp = $this->content;
            if ($this->sum < $this->num) {
                $this->num = $this->sum; 
            }
            for ($i = $this->num; $i < 0; $i++)
            {
                $temp_position = array();
                foreach ($this->elements AS $element)
                {
                    $position = strrpos($temp, $element);
                    if ($position !== false) {
                        $temp_position[] = $position;
                    }
                }
                if (count($temp_position) > 0) {
                    rsort($temp_position);
                    $start_position = $temp_position[0];            
                }
                $temp = substr($temp, 0, $start_position);
            }
        }
        
        $this->start = $start_position;
        return $this->start;
    }

    /**
     * 
     */
    public function preview()
    {
        $file_content = $this->content;
        if (c5t_gpc_vars('show_html') == 'yes') {     
            $insert = '<!-- Comment Form Position -->';            
            $file_content = substr_replace($file_content, "\n" . $insert, (int) c5t_gpc_vars('start_position'), 0);       
            $file_content = htmlentities($file_content);
            $file_content = str_replace(htmlentities($insert), '<a name="c5t_example_form" style="padding-top:200px;"></a><span style="color:red;font-weight:bold;">' . htmlentities($insert) . '</span>', $file_content);
            $file_content = '<pre>' . $file_content . '</pre>';
            return $file_content;
        }
        
        //$example = '<img src="http://scripts.local/comment_script/trunk/www/template/admin/image/example.png" style="border:0" />';
        $example = '<img src="http://comments.rstdev.selfip.com/template/admin/image/example.png" style="border:0" />';
    
        $insert = '<a name="c5t_example_form" style="padding-top:200px;"></a><div style="">' . $example . '</div>';
        
        $file_content = substr_replace($file_content, $insert, (int) c5t_gpc_vars('start_position'), 0);
        
        $file_content = str_replace('<head>', '<head><base href="' . $this->options['website'] . '" />', $file_content);
        //$file_content = str_replace(array('href="/', 'src="/', 'url("/'), array('href="', 'src="', 'url("'), $file_content);
        
        
        return $file_content;
    }

    /**
     * Add comment form code to file
     */
    public function finish()
    {
        global $c5t;
        $new_file = $this->content;
        $new_file = substr_replace($new_file, '<div id="c5t_comment_form"><?php echo $c5t_output; ?></div>', $this->position(), 0);
        
        $new_file = '<?php include \'include.php\'; ?>'. $new_file;
        $new_file = str_replace('</head>', '<link href="http://scripts.local/comment_script/trunk/www/template/default/style.css" rel="styleSheet" type="text/css">' . "\n\n</head>", $new_file);
        
        return $new_file;
    }

    /**
     * Write file back to file system
     */
    public function save()
    {
        global $c5t;

        $path = $this->options['path'];
        if (isset($c5t['_post']['saveas'])) {
            $path = dirname($path) . '/' . $c5t['_post']['file']; 
        }
        if (isset($c5t['_post']['backup'])) {
            $pos = strrpos($this->options['path'], '.');
            if ($pos !== false) {
                $backup = substr_replace($this->options['path'], '.bak', $pos, 0);
            }
            if (is_file($backup)) {
                $pos = strrpos($backup, '.');
                if ($pos !== false) {
                    $backup = substr_replace($backup, '.bak', $pos, 0);
                }
            }
            copy($this->options['path'], $backup); 
        }

        file_put_contents($path, $this->finish());
        copy('../include2.php', dirname($this->options['path']) . 'include.php');
    }

    /**
     * Allow file to be downloaded
     */
    public function download()
    {
        require_once 'download.class.inc.php';
        
        c5t_download::send($this->finish(), basename($this->options['path']), DH_DATA);
    }


} // End of class








?>
