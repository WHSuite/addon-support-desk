<?php
namespace Addon\SupportDesk\Libraries;

class NotificationsHelper
{
    /**
     * the ticket we are helping with
     *
     * @var object    SupportTicket
     */
    protected $SupportTicket = null;

    /**
     * the email template object
     *
     * @var object  EmailTemplate
     */
    protected $EmailTemplate = null;

    /**
     * the email template translation object
     *
     * @var object  EmailTemplateTranslation
     */
    protected $EmailTemplateTranslation = null;

    /**
     * data to be returned and added to the templates
     *
     * @var  array
     */
    protected $data = array();

    /**
     * check all the objects, if they exist, we will have gotten all the data
     * so can proceed
     *
     * @param   string|array    Emails to send to
     * @return  bool
     */
    public function canSend($email)
    {
        if (
            is_object($this->SupportTicket) &&
            is_object($this->SupportTicket->Client) &&
            is_object($this->SupportTicket->SupportPost['0']) &&
            is_object($this->EmailTemplate) &&
            is_object($this->EmailTemplateTranslation) &&
            ! empty($this->data) &&
            ! empty($email)
        ) {
            return true;
        }

        return false;
    }

    /**
     * given the ticket id, get the ticket along with all the data we need
     *
     * @param    int             Ticket ID
     * @param    bool            Get the staff details?
     * @return   object|null     The object or null
     */
    public function getTicket($ticket_id, $get_staff = false)
    {
        $with_array = array(
            'Client',
            'SupportDepartment',
            'SupportPost' => function ($query) {

                $query->orderBy('id', 'desc')
                    ->limit(1);
            }
        );

        if ($get_staff) {
            $with_array[] = 'Staff';
        }

        $this->SupportTicket = \SupportTicket::where('id', '=', $ticket_id)
            ->with($with_array)
            ->first();

        // if it's an object setup some of the data we are going to need
        if (is_object($this->SupportTicket)) {
            $this->data['client'] = $this->SupportTicket->Client;
            $this->data['settings'] = \App::get('configs')->get('settings');
        }

        return $this->SupportTicket;
    }

    /**
     * setup subject for replies
     *
     * @return  string|null      the subject with the ID prepended for email piping
     */
    public function getSubject()
    {
        if (! is_object($this->SupportTicket)) {
            return false;
        }

        return '[#' . $this->SupportTicket->id . '] ' . $this->SupportTicket->subject;
    }

    /**
     * get the html body
     *
     * @param   string           admin or client
     * @return  string|null      the body of the email
     */
    public function getBody($type)
    {
        if (! is_object($this->SupportTicket) || ! is_object($this->EmailTemplateTranslation)) {
            return false;
        }

        $html = $this->getHtmlEmails($type);

        // Check if the email needs to be in HTML or Plaintext
        if ($html) {
            $body = \App::get('email')->parseData(
                htmlspecialchars_decode(
                    $this->EmailTemplateTranslation->html_body
                ),
                $this->data
            );
        } else {
            $body = \App::get('email')->parseData(
                $this->EmailTemplateTranslation->plaintext_body,
                $this->data
            );
        }

        // prepend the reply above this line string to the body
        if ($this->checkForPiping()) {
            $reply_line = '-----Reply above this line-----';

            if (! $html) {
                $reply_line .= "\n\n";
            }

            $body = $reply_line . $body;
        }

        return $body;
    }

    /**
     * get the main to email
     *
     * @param   string   admin or client
     * @return  array    emails to send to
     */
    public function getTo($type)
    {
        $email = false;

        if ($type == 'admin') {
            // build up the array of emails we need to notify

            if (is_object($this->SupportTicket->SupportDepartment)) {
                $email = $this->SupportTicket->SupportDepartment->notification_email;

                if (strpos($email, ',') !== false) {
                    $email = explode(',', $email);
                }
            }

            if (is_object($this->SupportTicket->Staff)) {
                if (! is_array($email)) {
                    $email = array($email);
                }

                $email[] = $this->SupportTicket->Staff->email;
            }

        } else {
            if (is_object($this->SupportTicket->Client)) {
                $email = $this->SupportTicket->Client->email;
            }
        }

        return $email;
    }

    /**
     * get the support department and piping details
     *
     * @return  array
     */
    public function getReplyTo()
    {
        if (! is_object($this->SupportTicket->SupportDepartment)) {
            return false;
        }

        $reply_to = false;

        if ($this->checkForPiping()) {
            $piping_settings = json_decode($this->SupportTicket->SupportDepartment->piping_settings);

            if (! empty($piping_settings->piping_email)) {
                $reply_to = array($piping_settings->piping_email);
            }
        }

        return $reply_to;
    }

    /**
     * get CC from email template
     *
     * @return  string|false
     */
    public function getCC()
    {
        if (! is_object($this->SupportTicket) || ! is_object($this->EmailTemplate)) {
            return false;
        }

        return (! empty($this->EmailTemplate->cc)) ? $this->EmailTemplate->cc : false;
    }

    /**
     * get BCC from email template
     *
     * @return  string|false
     */
    public function getBCC()
    {
        if (! is_object($this->SupportTicket) || ! is_object($this->EmailTemplate)) {
            return false;
        }

        return (! empty($this->EmailTemplate->bcc)) ? $this->EmailTemplate->bcc : false;
    }

    /**
     * given the type return the language
     *
     * @param   string      admin or client
     * @return  int         language id to use
     */
    public function getLanguageId($type)
    {
        if (! is_object($this->SupportTicket->Client)) {
            return false;
        }

        if ($type == 'client') {
            return intval($this->SupportTicket->Client->language_id);
        } else {
            // TODO: Review this to see if we can work out admin language
            return 1;
        }
    }

    /**
     * given the type, return html or not
     *
     * @param   string      admin or client
     * @return  bool        html emails
     */
    public function getHtmlEmails($type)
    {
        if (! is_object($this->SupportTicket->Client)) {
            return false;
        }

        if ($type == 'client') {
            $html = false;

            if ($this->SupportTicket->Client->html_emails == '1') {
                $html = true;
            }

        } else {
            // TODO: Review once we implement email type selection for admins
            $html = true;
        }

        return $html;
    }

    /**
     * return all the data we've collected
     *
     * @return  array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * get the client id
     *
     * @return  int|false    client ID
     */
    public function getClientId()
    {
        if (! is_object($this->SupportTicket->Client)) {
            return false;
        }

        return $this->SupportTicket->Client->id;
    }

    /**
     * prepare the route url and set to the data
     *
     * @param   string  given the route name, set the route url
     * @param   array   the array of params to set
     */
    public function setTicketUrl($type)
    {
        if (! is_object($this->SupportTicket->Client)) {
            return false;
        }

        if ($type == 'client') {
            // generate the link based on whether its a guest client or registered

            if ($this->SupportTicket->Client->guest_account == 0) {
                $route_name = 'client-supportticket-view';
                $params = array(
                    'id' => $this->SupportTicket->id
                );

            } else {
                $route_name = 'client-supportticket-guest-view';
                $params = array(
                    'id' => $this->SupportTicket->id,
                    'hash' => $this->SupportTicket->unique_hash
                );
            }
        } else {
            $route_name = 'admin-supportticket-view';
            $params = array(
                'id' => $this->SupportTicket->id
            );
        }

        $route = \App::get('router')->fullUrlGenerate(
            $route_name,
            $params
        );

        $this->data['ticket_url'] = $route;
    }

    /**
     * given the type, 'client' / 'admin' get the status for the ticket
     *
     * @param   string      admin or client
     */
    public function setStatus($type)
    {
        if (! is_object($this->SupportTicket)) {
            return false;
        }

        $status = \SupportTicket::$status[$this->SupportTicket->status];

        if (isset($status[$type]['label'])) {
            $status = $status[$type]['label'];
        } else {
            $status = $status['label'];
        }

        // TODO: Review this block when we add translation method: issue 0000034
        // get the status language string
        $language_id = $this->getLanguageId($type);

        $LangPhrase = \LanguagePhrase::where('language_id', '=', $language_id)
            ->where('slug', '=', $status)
            ->first();

        if (is_object($LangPhrase)) {
            $status = $LangPhrase->text;
        }

        $this->data['status'] = $status;
    }

    /**
     * set the ticket post
     *
     * @param   string      admin or client
     */
    public function setTicketPost($type)
    {
        if (! is_object($this->SupportTicket->SupportPost['0'])) {
            return false;
        }

        $html = $this->getHtmlEmails($type);

        // set the post
        $this->data['ticket_post'] = $this->SupportTicket->SupportPost['0']->body;

        // nl2br if we are sending html email
        if ($html) {
            $this->data['ticket_post'] = nl2br($this->data['ticket_post']);
        }
    }

    /**
     * set the email template and email template data
     *
     * @param   string      email template to load
     * @param   string      admin or client
     */
    public function setEmailTemplate($template_name, $type)
    {
        if (! is_object($this->SupportTicket)) {
            return false;
        }

        // get the template
        $this->EmailTemplate = \EmailTemplate::where('slug', '=', $template_name)->first();

        // set the return so we can just return this variable
        if (is_object($this->EmailTemplate)) {
            $language_id = $this->getLanguageId($type);

            // Attempt to load the template data in the selected language
            $this->EmailTemplateTranslation = \EmailTemplateTranslation::where(
                'email_template_id',
                '=',
                $this->EmailTemplate->id
            )->where('language_id', '=', $language_id)
                ->first();

            // If the selected language doesnt have a translation for this template, fall back to language id 1 (english/default)
            if (! is_object($this->EmailTemplateTranslation)) {
                $this->EmailTemplateTranslation = \EmailTemplateTranslation::where(
                    'email_template_id',
                    '=',
                    $this->EmailTemplate->id
                )->where('language_id', '=', 1)
                    ->first();
            }
        }
    }

    /**
     * check for piping
     *
     * @return  bool
     */
    public function checkForPiping()
    {
        if (! is_object($this->SupportTicket->SupportDepartment)) {
            return false;
        }

        if ($this->SupportTicket->SupportDepartment->piping == 1) {
            return true;
        } else {
            return false;
        }
    }
}
