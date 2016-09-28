<?php
namespace Addon\SupportDesk\Libraries;

class TicketView
{

    /**
     * Process the ticket post body
     * Currently just nl2br and auto link any urls
     *
     * @param string post body to process
     * @return string the processed body string
     */
    public static function processBody($body)
    {
        $body = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $body);

        $body = nl2br($body);

        return $body;
    }
}
