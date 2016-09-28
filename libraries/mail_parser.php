<?php
namespace Addon\SupportDesk\Libraries;

use \PhpMimeMailParser\Parser;

class MailParser extends Parser
{
    /**
     * constructor, get the stream and assign to the parser
     *
     */
    public function __construct()
    {
        parent::__construct();

        $sock = fopen("php://stdin", 'r');
        $email = '';

        //Read e-mail into buffer
        while (! feof($sock)) {
            $email .= fread($sock, 1024);
        }

        //Close socket
        fclose($sock);

        $this->setText($email);
    }

    /**
     * shortcut function to get the 'from' address and process
     *
     * @return  array       2 element array of email and name (if exists)
     */
    public function getFrom()
    {
        $from = $this->getRawHeader('from');

        return $this->processEmailStr($from);
    }

    /**
     * shortcut function to get the 'to' address and process
     *
     * @return  array       2 element array of email and name (if exists)
     */
    public function getTo()
    {
        $from = $this->getRawHeader('to');

        return $this->processEmailStr($from);
    }


    /**
     * process the raw from / to headers like Mike Barlow <mike@whsuite.com>
     * into name / email, or just return email if not in that format
     *
     * @param   string      The email string to parse
     * @return  array       2 element array of email and name (if exists)
     */
    public function processEmailStr($email_str)
    {
        $email_str = str_replace('"', '', $email_str);
        if (strpos($email_str, '<') !== false) {
            list($name, $email) = explode(' <', trim($email_str, '> '), 2);
            $name = trim($name);
        } else {
            $name = null;
            $email = $email_str;
        }

        return array(
            'name' => $name,
            'email' => trim(filter_var($email, FILTER_SANITIZE_EMAIL))
        );
    }
}
