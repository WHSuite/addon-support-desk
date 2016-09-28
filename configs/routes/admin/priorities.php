<?php

$routes = array(

    /* PRIORITIES */
    'supportticketpriority' => array(
        'path' => '/support-priorities/',
        'values' => array(
            'controller' => 'SupportTicketPrioritiesController',
            'action' => 'index'
        )
    ),
    'supportticketpriority-paging' => array(
        'params' => array(
            'page' => '(\d+)'
        ),
        'path' => '/support-priorities/{:page}/',
        'values' => array(
            'controller' => 'SupportTicketPrioritiesController',
            'action' => 'index'
        )
    ),
    'supportticketpriority-edit' => array(
        'path' => '/support-priorities/edit/{:id}/',
        'values' => array(
            'controller' => 'SupportTicketPrioritiesController',
            'action' => 'form'
        )
    ),
    'supportticketpriority-add' => array(
        'path' => '/support-priorities/add/',
        'values' => array(
            'controller' => 'SupportTicketPrioritiesController',
            'action' => 'form'
        )
    ),
    'supportticketpriority-delete' => array(
        'path' => '/support-priorities/delete/{:id}/',
        'values' => array(
            'controller' => 'SupportTicketPrioritiesController',
            'action' => 'delete'
        )
    )
);