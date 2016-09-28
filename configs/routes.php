<?php

// ADMIN ROUTES
$admin_routes = App::get('configs')->getRoutes('admin');

App::get('router')->attach('/admin', array(
    'name_prefix' => 'admin-',
    'values' => array(
        'sub-folder' => 'admin',
        'addon' => 'support_desk'
    ),
    'params' => array(
        'id' => '(\d+)'
    ),

    'routes' => $admin_routes

));

// CLIENT ROUTES
$client_routes = App::get('configs')->getRoutes('client');

App::get('router')->attach('', array(
    'name_prefix' => 'client-',
    'values' => array(
        'sub-folder' => 'client',
        'addon' => 'support_desk'
    ),
    'params' => array(
        'id' => '(\d+)'
    ),

    'routes' => $client_routes

));
