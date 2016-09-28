<?php

use \Illuminate\Support\Str;

class SupportShortcutsController extends \WidgetsController
{
    /**
     * dashboard shortcut to show how many new replies there are for the given user
     *
     */
    public function shortcutActiveTickets()
    {
        $Http = new \Whsuite\Http\Http;

        $Response = $Http->newResponse();
        $Response->setHeaders(
            array(
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Type' => 'text/plain'
            )
        );
        $Response->setContent(
            SupportTicket::countTickets($this->admin_user, 0)
        );

        $Http->send($Response);
   }

}
