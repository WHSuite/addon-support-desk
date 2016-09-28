<?php

$routes = array(

    /* create ticket - client search */

    'search-clients' => array(
        'path' => '/ajax/support-desk/find-clients/{:type}/{:search}',
        'params' => array(
            'type' => '([a-z-]+)',
            'search' => '([a-zA-Z0-9\.\+-_]+)',
        ),
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'ajaxFindClients'
        )
    ),

    'search-staff' => array(
        'path' => '/ajax/support-desk/find-staff/{:type}/{:search}',
        'params' => array(
            'type' => '([a-z-]+)',
            'search' => '([a-zA-Z0-9\.\+-_]+)',
        ),
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'ajaxFindStaff'
        )
    ),

    'search-products' => array(
        'path' => '/ajax/support-desk/find-products/{:clientid}',
        'params' => array(
            'clientid' => '([CLIENTID0-9/]+)'
        ),
        'values' => array(
            'controller' => 'SupportTicketsController',
            'action' => 'ajaxFindProducts'
        )
    )

);
