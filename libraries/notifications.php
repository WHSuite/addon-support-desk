<?php
namespace Addon\SupportDesk\Libraries;

class Notifications
{
    /**
     * given the ticket id, send out a reply notification to the client
     *
     * @param   int     Ticket ID
     * @return  bool
     */
    public static function clientNewReply($ticket_id)
    {
        $return = false;

        $Helper = \App::factory('\Addon\SupportDesk\Libraries\NotificationsHelper');
        $SupportTicket = $Helper->getTicket($ticket_id);

        $Helper->setTicketUrl('client');
        $Helper->setStatus('client');
        $Helper->setTicketPost('client');
        $Helper->setEmailTemplate('client_support_ticket_reply', 'client');

        $subject = $Helper->getSubject();
        $body = $Helper->getBody('client');
        $to = $Helper->getTo('client');
        $cc = $Helper->getCC();
        $bcc = $Helper->getBCC();
        $reply_to = $Helper->getReplyTo();

        if ($Helper->canSend($to)) {
            // send the email
            $return = \App::get('email')->sendEmail(
                $to,
                $subject,
                $body,
                $Helper->getHtmlEmails('client'),
                $Helper->getData(),
                $cc,
                $bcc,
                null,
                array(),
                1,
                $reply_to
            );

            if ($return) {
                // log the email to the client
                $ClientEmail = new \ClientEmail;
                $ClientEmail->client_id = $Helper->getClientId();
                $ClientEmail->subject = $subject;
                $ClientEmail->body = $body;
                $ClientEmail->to = $to;
                $ClientEmail->cc = $cc;
                $ClientEmail->bcc = $bcc;
                $ClientEmail->save();
            }
        }

        return $return;
    }

    /**
     * given the ticket id, send out a reply notification to the admin
     *
     * @param   int     Ticket ID
     * @return  bool
     */
    public static function adminNewReply($ticket_id)
    {
        $return = false;

        $Helper = \App::factory('\Addon\SupportDesk\Libraries\NotificationsHelper');
        $SupportTicket = $Helper->getTicket($ticket_id);

        $Helper->setTicketUrl('admin');
        $Helper->setStatus('admin');
        $Helper->setTicketPost('admin');
        $Helper->setEmailTemplate('admin_support_ticket_reply', 'admin');

        $subject = $Helper->getSubject();
        $body = $Helper->getBody('admin');
        $to = $Helper->getTo('admin');
        $cc = $Helper->getCC();
        $bcc = $Helper->getBCC();
        $reply_to = $Helper->getReplyTo();

        if ($Helper->canSend($to)) {
            // send the email
            $return = \App::get('email')->sendEmail(
                $to,
                $subject,
                $body,
                $Helper->getHtmlEmails('admin'),
                $Helper->getData(),
                $cc,
                $bcc,
                null,
                array(),
                1,
                $reply_to
            );
        }

        return $return;
    }

    /**
     * notification to an admins assigned to a specific ticket
     *
     * @param   int     Ticket ID
     * @return  bool
     */
    public static function assignedAdmin($ticket_id)
    {
        $SupportTicket = \SupportTicket::where('id', '=', $ticket_id)
            ->with('Staff')
            ->first();

        if (is_object($SupportTicket) && ! empty($SupportTicket->Staff->id)) {
            $route = \App::get('router')->fullUrlGenerate(
                'admin-supportticket-view',
                array(
                    'id' => $SupportTicket->id
                )
            );

            $data = array(
                'ticket_url' => $route
            );

            return \App::get('email')->sendTemplateToStaff(
                $SupportTicket->Staff->id,
                'admin_support_ticket_assign',
                $data
            );

        } else {
            return false;
        }
    }
}
