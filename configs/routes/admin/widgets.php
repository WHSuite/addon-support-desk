<?php

$routes = array(
    /**
     * support desk widgets
     */
    'widget-support_desk-active-tickets' => array(
        'path' => '/widgets/support-desk/active-tickets/*',
        'values' => array(
            'controller' => 'SupportWidgetsController',
            'action' => 'activeTickets'
        )
    ),

    'shortcut-support_desk-label' => array(
        'path' => '/shortcuts/support-desk/active-tickets/',
        'values' => array(
            'controller' => 'SupportShortcutsController',
            'action' => 'shortcutActiveTickets'
        )
    )
);