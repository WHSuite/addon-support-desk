<?php

$routes = array(

    /* DEPARTMENTS */
    'supportdepartment' => array(
        'path' => '/support-departments/',
        'values' => array(
            'controller' => 'SupportDepartmentsController',
            'action' => 'index'
        )
    ),
    'supportdepartment-paging' => array(
        'params' => array(
            'page' => '(\d+)'
        ),
        'path' => '/support-departments/{:page}/',
        'values' => array(
            'controller' => 'SupportDepartmentsController',
            'action' => 'index'
        )
    ),
    'supportdepartment-edit' => array(
        'path' => '/support-departments/edit/{:id}/',
        'values' => array(
            'controller' => 'SupportDepartmentsController',
            'action' => 'form'
        )
    ),
    'supportdepartment-add' => array(
        'path' => '/support-departments/add/',
        'values' => array(
            'controller' => 'SupportDepartmentsController',
            'action' => 'form'
        )
    ),
    'supportdepartment-delete' => array(
        'path' => '/support-departments/delete/{:id}/',
        'values' => array(
            'controller' => 'SupportDepartmentsController',
            'action' => 'delete'
        )
    )
);