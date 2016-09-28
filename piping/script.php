#!/usr/bin/php
<?php

if (! defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$dirname = dirname(__FILE__) . DS;

// Go!
if (file_exists($dirname . '../../../../system' . DS . 'cli_bootstrap.php')) {
    require_once($dirname . '../../../../system' . DS . 'cli_bootstrap.php');
} else {
    die("Fatal Error: System CLI bootstrap file not found!");
}

/**
 * get a raw email and process into a ticket
 */
$Parser = new \Addon\SupportDesk\Libraries\MailParser();

// get the email it was sent to so we can find if the department supports piping
$to_email = $Parser->getTo();

// we couldn't process the to email exit
if (! isset($to_email['email']) || empty($to_email['email'])) {
    exit;
}

$SupportDepartment = \SupportDepartment::where('piping', '=', 1)
    ->where('is_active', '=', 1)
    ->where('piping_settings', 'LIKE', '%' . $to_email['email'] . '%')
    ->first();

if (! empty($SupportDepartment) && is_object($SupportDepartment)) {
    $settings = json_decode($SupportDepartment->piping_settings);

    if (isset($settings->piping_email) && $settings->piping_email == $to_email['email']) {
        // get hte from address so we can check if it's a client
        $from_email = $Parser->getFrom();

        // we couldn't process the to email exit
        if (! isset($from_email['email']) || empty($from_email['email'])) {
            exit;
        }

        $Client = \Client::where('email', '=', $from_email['email'])
            ->first();

        if (empty($Client) || ! is_object($Client)) {
            // not a client, check if it's a staff member
            $Staff = \Staff::where('email', '=', $from_email['email'])
                ->first();

            if (empty($Staff) || ! is_object($Staff)) {
                // no client or staff member create a guest account

                // try to get the name from the email
                if (! empty($from_email['name'])) {
                    list($first_name, $last_name) = explode(' ', $from_email['name'], 2);
                } else {
                    $first_name = \App::get('translation')->get('guest');
                    $last_name = $from_email['email'];
                }

                \App\Libraries\ClientAuth::auth();
                $Client = new \Client;
                $Client->first_name = $first_name;
                $Client->last_name = $last_name;
                $Client->email = $from_email['email'];
                $Client->password = 'guest_password';
                $Client->guest_account = 1;
                $Client->html_emails = 1;
                $Client->save();
            }
        }

        // process subject, check for new ticket or reply to old
        $subject = $Parser->getHeader('subject');
        $subject_match = preg_match("/\[#[0-9]+\]/", $subject, $ticket_id);

        $body = $Parser->getMessageBody('text');
        $body_parts = explode('-----Reply above this line-----', $body);
        $body = rtrim($body_parts['0'], '> ');

        // check the subject for matches
        $tickedIdMatch = isset($ticket_id['0']) && ! empty($ticket_id['0']);

        if ($subject_match === 1 && $tickedIdMatch) {
            // we've found a pattern matching hash+ID num, get existing ticket
            $ticket_id['0'] = str_replace(array('[', ']', '#'), '', $ticket_id['0']); // remove the brackets and has to leave the ID
            $SupportTicket = \SupportTicket::where('id', '=', $ticket_id['0'])
                ->first();

        } else {
            // new ticket, set it up
            if (isset($Client) && $Client->id > 0) {
                // get the first active support priority
                $SupportPriority = \SupportTicketPriority::where('is_active', '=', 1)
                    ->orderBy('id', 'asc')
                    ->first();

                // check we have a priority and save
                if (isset($SupportPriority) && $SupportPriority->id > 0) {
                    $SupportTicket = new \SupportTicket;
                    $SupportTicket->subject = $subject;
                    $SupportTicket->client_id = $Client->id;
                    $SupportTicket->support_ticket_priority_id = $SupportPriority->id;
                    $SupportTicket->support_department_id = $SupportDepartment->id;
                    $SupportTicket->status = 0;
                    $SupportTicket->is_active = 1;
                    $SupportTicket->save();
                }
            }
        }

        // we have a ticket object, save the post
        if (! empty($SupportTicket) && is_object($SupportTicket)) {
            $SupportPost = new \SupportPost;
            $SupportPost->support_ticket_id = $SupportTicket->id;
            $SupportPost->body = $body;

            // check to see if the from user was a staff member or client
            if (! empty($Staff) && $Staff->id > 0) {
                $SupportPost->staff_id = $Staff->id;
            } else {
                $SupportPost->staff_id = 0;
            }

            // check the save was successfully so we can send out notification
            if ($SupportPost->save()) {
                // if post has saved send out the nofications

                if (isset($Staff) && $Staff->id > 0) {
                    // send notification to client
                    \Addon\SupportDesk\Libraries\Notifications::clientNewReply($SupportTicket->id);
                } else {
                    // send notification to staff
                    \Addon\SupportDesk\Libraries\Notifications::adminNewReply($SupportTicket->id);
                }

                // if it's not a new ticket change status
                if ($SupportTicket->status != 0) {
                    $SupportTicket->status = 0;
                    $SupportTicket->save();
                }
            }
        }
    }
}

exit;
