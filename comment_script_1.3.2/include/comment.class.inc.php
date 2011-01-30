<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */





/**
 * Handle comments
 */
class c5t_comment
{




    /**
     * Get comments
     *
     * @access public
     */
    function get_list()
    {
        if ($db = c5t_database::connection()) {
            $sql = "SELECT  tc.*,
                            ti.*

                    FROM    (" . C5T_COMMENT_TABLE . " AS tc,
                            " . C5T_IDENTIFIER_TABLE . " AS ti)

                    WHERE   (ti.identifier_hash = ?
                    AND     tc.comment_identifier_id = ti.identifier_id)
                    ORDER BY tc.comment_timestamp ASC";
            $identifier = md5($this->identifier());
            if ($res = c5t_database::query($sql, array($identifier))) {
                $list = array();
                $num_results = $res->numRows();
                while ($res->fetchInto($row))
                {
                    // Enhance user data
                    $enhance = array(
                                'comment_date'  => c5t_time::format_date($row['comment_timestamp']),
                                'comment_time'  => c5t_time::format_time($row['comment_timestamp']),
                                'comment_text'  => nl2br($row['comment_text'])
                                );

                    $list[] = array_merge(
                                        $row,
                                        $this->form_mapping($row),
                                        $enhance
                                        );
                }
                return $list;
            }
        }

    }

// -----------------------------------------------------------------------------




    /**
     * Get comment details
     *
     * @access public
     */
    function get($id)
    {
        global $c5t;

        $sql = "SELECT  c.*, i.*
                FROM    " . C5T_COMMENT_TABLE . " AS c
                LEFT JOIN " . C5T_IDENTIFIER_TABLE . " AS i ON c.comment_identifier_id = i.identifier_id
                WHERE   c.comment_id = ?";
        if ($db = c5t_database::query($sql, array($id))) {
            $res = $db->fetchRow();
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            }

            if (sizeof($res) > 0) {
                if ($res['identifier_name'] != '') {
                    $res['identifier_output'] = $res['identifier_name'];
                } else {
                    $res['identifier_output'] = $res['identifier_value'];
                }
                $res['comment_date']                = c5t_time::format_date($res['comment_timestamp']);
                $res['comment_time']                = c5t_time::format_time($res['comment_timestamp']);
                $res['comment_author_host']         = htmlentities($res['comment_author_host']);
                $res['comment_author_user_agent']   = htmlentities($res['comment_author_user_agent']);
                $res['hostname_output']             = (strlen($res['comment_author_host']) > $c5t['hostname_length']) ? substr($res['comment_author_host'], 0, $c5t['hostname_length']) . $c5t['cut_off_string'] : $res['comment_author_host'];
                $res['user_agent_output']           = (strlen($res['comment_author_user_agent']) > $c5t['user_agent_length']) ? substr($res['comment_author_user_agent'], 0, $c5t['user_agent_length']) . $c5t['cut_off_string'] : $res['comment_author_user_agent'];

                return $res;
            }
        }

    }

// -----------------------------------------------------------------------------





    /**
     * Write comment to database
     *
     * @access public
     */
    function put()
    {
        global $c5t;

        // Get (and set) identifier (id)
        if (!$identifier_id = $this->identifier_id()) {
            return false;
        }

        // Write into comment table
        if (!$comment_id = c5t_database::next_id('comment')) {
            return false;
        }

        $data = array_merge($c5t['_post'], $this->enhance());

        $data['comment_id']             = $comment_id;
        $data['comment_identifier_id']  = $identifier_id;
        $data['comment_timestamp']     = c5t_time::current_timestamp();

        require_once 'identifier.class.inc.php';
        $ident = new c5t_identifier;
        $page_data = $ident->get($identifier_id);


        c5t_module::call_module('frontend_save_content', $data, $page_data);

        // Comment blocked
        if ($page_data['page_allow_comment'] == 'N') {
            return false;
        }

        // Trigger moderation
        if ($c5t['enable_moderation'] == 'Y'
                and !isset($data['comment_status'])) {
            $data['comment_status'] = 100;
            $c5t['message'][] = $c5t['text']['txt_thanks_moderation'];
        }

        // Write comment
        if ($res = c5t_database::insert('comment', $data)) {
            $c5t['message'][] = $c5t['text']['txt_thanks'];
            $this->remember_user($data);
            return true;
        } else {
            $c5t['message'][] = $c5t['text']['txt_error_comment'];
            return false;
        }
    }

// -----------------------------------------------------------------------------

    /**
     * Remember user
     */
    function remember_user($data)
    {
        global $c5t;

        if ($c5t['remember_user'] == 'Y') {
            c5t_set_permanent_cookie('c5t_remember_name', stripslashes($data['name']));
            c5t_set_permanent_cookie('c5t_remember_email', stripslashes($data['email']));
            c5t_set_permanent_cookie('c5t_remember_homepage', stripslashes($data['homepage']));
        }
    }

// -----------------------------------------------------------------------------

    /**
     * Get user data from cookie
     */
    function remembered_user()
    {
        global $c5t;

        $data = array();
        if ($c5t['remember_user'] == 'Y') {
            if (isset($c5t['_cookie']['c5t_remember_name'])) {
                $data['name'] = stripslashes(strip_tags($c5t['_cookie']['c5t_remember_name']));
            }
            if (isset($c5t['_cookie']['c5t_remember_email'])) {
                $data['email'] = stripslashes(strip_tags($c5t['_cookie']['c5t_remember_email']));

            }
            if (isset($c5t['_cookie']['c5t_remember_homepage'])) {
                $data['homepage'] = stripslashes(strip_tags($c5t['_cookie']['c5t_remember_homepage']));

            }
        }
        return $data;
    }




    /**
     * Update comment
     *
     * @access public
     */
    function update($id)
    {
        global $c5t;

        $data = array_merge($c5t['_post'], $this->enhance());

        $where = "comment_id = ?";
        $where_data = array($id);
        c5t_database::update('comment', $data, $where, $where_data);
    }

// -----------------------------------------------------------------------------




    /**
     * Update comment status
     *
     * @access public
     */
    function status($id, $status)
    {
        global $c5t;

        $data = array('comment_status' => $status);

        $where = "comment_id = ?";
        $where_data = array($id);
        c5t_database::update('comment', $data, $where, $where_data);
    }

// -----------------------------------------------------------------------------





    /**
     * Delete comment
     *
     * @access public
     */
    function delete($id)
    {
        global $c5t;

        $where = " comment_id = ?";
        $data = array($id);
        if ($res = c5t_database::delete(C5T_COMMENT_TABLE, $where, $data)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Delete comment list
     *
     * @access public
     */
    function delete_list($arr)
    {
        global $c5t;

        if (!is_array($arr)) {
            return false;
        }
        $data = array();
        $qm   = array();
        foreach ($arr AS $id)
        {
            if (!is_numeric($id)) {
                continue;
            }
            $data[] = (int) $id;
            $qm[]   = '';
        }

        $where = ' comment_id = ? ' . join(' OR comment_id = ? ', $qm);
        if ($res = c5t_database::delete(C5T_COMMENT_TABLE, $where, $data)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Enhance comment data
     * @access private
     */
    function enhance()
    {
        $identifier = $this->identifier();
        $data = array(
                    'comment_identifier'        => $identifier,
                    'comment_identifier_hash'   => md5($identifier),
                    'comment_author_ip'         => getenv('REMOTE_ADDR'),
                    'comment_author_host'       => @gethostbyaddr(getenv('REMOTE_ADDR')),
                    'comment_author_user_agent' => getenv('HTTP_USER_AGENT'),
                    );

        return $data;
    }

// -----------------------------------------------------------------------------




    /**
     * Translate database fields to form fields
     *
     */
    function form_mapping($data)
    {
        global $c5t;
        if (isset($c5t['mapping']['comment'])) {
            reset($c5t['mapping']['comment']);
            while (list($key, $val) = each($c5t['mapping']['comment']))
            {
                $data[$val] = $data[$key];
            }
        }

        return $data;
    }






    /**
     * Manage identifier
     *
     * Takes either REQUEST_URI or provided key as identifier
     *
     *
     */
    function identifier()
    {
        global $c5t;
        // $identifier = getenv('REQUEST_URI');
        $identifier = c5t_request_uri();
        if ($redirect = c5t_gpc_vars('c5t_ssi_redirect')) {
            $identifier = $redirect;
        }

        $data = array('identifier' => $identifier);
        c5t_module::call_module('frontend_uri', $data, $c5t['module_additional']);
        $identifier = $data['identifier'];

        if ($c5t['automatic_identifier'] == false) {
            if (isset($c5t['_get'][$c5t['identifier_key']])
                    and $c5t['_get'][$c5t['identifier_key']] != '') {
                $identifier = $c5t['_get'][$c5t['identifier_key']];
            }
            if (isset($c5t['_post'][$c5t['identifier_key']])
                    and $c5t['_post'][$c5t['identifier_key']] != '') {
                $identifier = $c5t['_post'][$c5t['identifier_key']];
            }
            if (isset($GLOBALS['c5t_identifier_key'])
                    and $GLOBALS['c5t_identifier_key'] != '') {
                $identifier = $GLOBALS['c5t_identifier_key'];
            }
        }
        return $identifier;
    }






    /**
     * Manage identifier id
     *
     * Takes either an exiting id from database or creates one
     */
    function identifier_id()
    {
        global $c5t;

        // Get identifier
        $identifier = $this->identifier();

        // Check if page identifier already exists
        if ($identifier_data = $this->select_identifier($identifier)) {
            return $identifier_data['identifier_id'];
        }


        // Prevent automatic page registration if configuration says so
        if ($c5t['page_registration'] != 'Y') {
            $c5t['message'][] = $c5t['text']['txt_page_not_registered'];
            return false;
        }


        // Create new page identifier
        if ($id = c5t_database::next_id('identifier')) {
            //$request_uri = getenv('REQUEST_URI');
            $request_uri = c5t_request_uri();
            if ($redirect = c5t_gpc_vars('c5t_ssi_redirect')) {
                $request_uri = $redirect;
            }
            $identifier_url = $c5t['server_protocol'] . $c5t['server_name'] . $request_uri;
            $data = array(  'identifier_id'     => $id,
                            'identifier_value'  => $identifier,
                            'identifier_hash'   => md5($identifier),
                            'identifier_url'    => $identifier_url
                            );


            if ($res = c5t_database::insert('identifier', $data)) {
                return $id;
            }
        }

    }






    /**
     * Select identifier data
     *
     */
    function select_identifier($identifier)
    {
        $identifier_hash = md5($identifier);

        $sql = "SELECT  *,
                        identifier_name AS page_title,
                        identifier_url AS page_url
                FROM    "  . C5T_IDENTIFIER_TABLE . "
                WHERE   identifier_hash = ?";
        if ($db = c5t_database::query($sql, array($identifier_hash))) {
        $res = $db->fetchRow();
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            }

            if (sizeof($res) > 0) {
                return $res;
            }
        }
    }




    /**
     * Cache list of pages
     */
    function cache_list()
    {
        global $c5t;

        $file = C5T_ROOT . $c5t['cache_directory'] . md5($c5t['dsn']['password']) . '_page_list.txt';

        if ((filemtime($file) + $c5t['comment_info_cache']) > c5t_time::current_timestamp()) {
            return unserialize(join('', file($file)));
        }

        // Get comment number
        $sql = "SELECT      tc.comment_identifier, COUNT(tc.comment_id) AS comment_number
                FROM        " . C5T_COMMENT_TABLE . " AS tc
                GROUP BY    tc.comment_identifier_id
                ";
        $comment_number_list = array();
        $comments_total = 0;
        if ($res = c5t_database::query($sql)) {
            while ($row = $res->fetchRow())
            {
                $comments_total += $row['comment_number'];
                $comment_number[$row['comment_identifier']]= $row['comment_number'];
            }
        }

        // Get last comment
        $sql = "SELECT      tc.*, COUNT(tc.comment_id) AS comment_number, tc.comment_text AS frontend_text
                FROM        " . C5T_COMMENT_TABLE . " AS tc
                LEFT JOIN   " . C5T_COMMENT_TABLE . " AS tc2 ON (tc.comment_identifier_id = tc2.comment_identifier_id AND tc.comment_id < tc2.comment_id)
                WHERE tc2.comment_identifier_id IS NULL
                GROUP BY tc.comment_identifier_id
                ";


        $list = array();
        if ($res = c5t_database::query($sql)) {
            while ($row = $res->fetchRow())
            {
                $row['comment_author_name'] = ($row['comment_author_name'] == '' ? $c5t['text']['txt_anonymous'] : $row['comment_author_name']);

                $enhance = array(
                    'comment_date'          => c5t_time::format_date($row['comment_timestamp']),
                    'comment_time'          => c5t_time::format_time($row['comment_timestamp']),
                    'comment_text'          => nl2br($row['comment_text']),
                    'comment_number'        => isset($comment_number[$row['comment_identifier']]) ? $comment_number[$row['comment_identifier']] : 0,
                    'comment_number_total'  => $comments_total,
                    );

                $final  = array_merge($row, $enhance);

                c5t_module::call_module('frontend_content', $final, $c5t['module_additional']);


                $list[$row['comment_identifier']] = $final;
            }
        }
        file_put_contents($file, serialize($list));
        return $list;
    }

    /**
     * Display comment number
     */
    function number_of_comments($url = null)
    {
        global $c5t;

        $result = c5t_comment::cache_list();
        if ($url == null) {
            $result = array_values($result);
            if (isset($result[0])) {
                return (int)$result[0]['comment_number_total'];
            }
        }
        if (isset($result[$url])) {
            return (int)$result[$url]['comment_number'];
        }
    }

    /**
     * Display comment data
     */
    function latest_comment($field, $url)
    {
        global $c5t;

        $fields = array(
            'comment'   => 'frontend_text',
            'author'    => 'comment_author_name',
            'email'     => 'comment_author_email',
            'homepage'  => 'comment_author_homepage',
            'title'     => 'comment_title',
            'date'      => 'comment_date',
            'time'      => 'comment_time',
            );
        $result = c5t_comment::cache_list();
        if (isset($result[$url])
                and isset($result[$url][$fields[$field]])) {
            return $result[$url][$fields[$field]];
        }
        return '';
    }




} // End of class








?>
