<?php

$routes = array(

    /* SUPPORT DESK */
    'supportticket' => array(
        'path' => '/support-desk/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'index'
        )
    ),
    // just a fall back reference, to keep the router happy
    // for posting staff ticket replies
    'supportpost' => array(
        'path' => '/support-desk/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'index'
        )
    ),
    'supportpost-add' => array(
        'path' => '/support-desk/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'index'
        )
    ),


    'supportticket-paging' => array(
        'params' => array(
            'page' => '(\d+)'
        ),
        'path' => '/support-desk/{:page}/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'index'
        )
    ),
    'supportticket-add' => array(
        'path' => '/support-desk/create/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'form'
        )
    ),
    'supportticket-view' => array(
        'path' => '/support-desk/view/{:id}/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'view'
        )
    ),
    'supportticket-guest-view' => array(
        'params' => array(
            'hash' => '([A-Za-z0-9]+)'
        ),
        'path' => '/support-desk/guest-view/{:id}/{:hash}/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'guestView'
        )
    ),
    'supportticket-delete' => array(
        'path' => '/support-desk/delete/{:id}/',
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'delete'
        )
    ),

    'supportticket-reply' => array(
        'path' => '/support-desk/reply/',
        'values' => array(
            'controller' => 'SupportPostsController',
            'action' => 'reply'
        )
    ),

    'supportticket-guest-reply' => array(
        'params' => array(
            'hash' => '([A-Za-z0-9]+)'
        ),
        'path' => '/support-desk/guest-reply/{:hash}/',
        'values' => array(
            'controller' => 'SupportPostsController',
            'action' => 'guestReply'
        )
    )

);