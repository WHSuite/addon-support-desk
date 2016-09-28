<?php

App::get('hooks')->startListening(
    'automation-begin',
    'ticket-cleanup',
    '\Addon\SupportDesk\Libraries\Automation::cleanup'
);