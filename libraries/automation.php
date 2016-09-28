<?php
namespace Addon\SupportDesk\Libraries;

class Automation
{
    /**
     * clean up any tickets that haven't had a reply
     * in the last X seconds
     *
     */
    public static function cleanup()
    {
        $settings = \App::get('configs')->get('settings.supportdesk');
        $timezone = \App::get('configs')->get('settings.localization.timezone');

        if (isset($settings['supportdesk_auto_close']) && $settings['supportdesk_auto_close'] == 1) {

            $SupportTickets = \SupportTicket::whereIn(
                'status',
                array(0, 1)
            )
            ->get();

            $automation_message = '<p>' . \App::get('translation')->get('automation_ticket_close') . '</p>';

            foreach ($SupportTickets as $ticket) {

                // add the no. inactivity seconds to the last modified date
                $date = \Carbon\Carbon::parse($ticket->updated_at, $timezone)
                    ->addSeconds($settings['supportdesk_auto_close_seconds']);

                // we have the inactivity period end, check if it's in the past
                if ($date->isPast()) {

                    // it is, let's close the ticket
                    $SupportPost = new \SupportPost;
                    $SupportPost->support_ticket_id = $ticket->id;
                    $SupportPost->staff_id = -1;
                    $SupportPost->body = $automation_message;
                    $SupportPost->save();

                    $ticket->status = 3;
                    $ticket->save();
                }
            }
        }
    }
}
