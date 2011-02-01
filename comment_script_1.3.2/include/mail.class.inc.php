<?php

/**
 * GentleSource Comment Script
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


include 'htmlMimeMail.php';




/**
 * Send mails
 */
class c5t_mail
{




    /**
     * Send mails
     */
    function send($to, $subject, $body, $from)
    {
        global $c5t;

        $mail = new htmlMimeMail();

        if ($c5t['mail_type'] == 'smtp') {
            $type = 'smtp';
            $smtp = $c5t['smtp'];
            $mail->setSMTPParams($smtp['host'], $smtp['port'], $smtp['helo'], $smtp['auth'], $smtp['user'], $smtp['pass']);
        } else {
            $type = 'mail';
        }

        $mail->setFrom($from);
        $mail->setReturnPath($from);
        $mail->setSubject($subject);
        $mail->setText($body);
        $result = $mail->send(array($to), $type);
        if ($result) {
            return true;
        } else {
            system_debug::add_message('Sending Mail Failed', @join('<br />', @$mail->errors), 'system');
        }
    }


//------------------------------------------------------------------------------





}








?>
