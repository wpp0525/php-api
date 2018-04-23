<?php

/**
 *
 */
$router->addPost("/video/select", array(
    'controller' => 'video',
    'action' => 'selectData',
));

/**
 *
 */
$router->addPost("/video/update", array(
    'controller' => 'video',
    'action' => 'updateData',
));

/**
 *
 */
$router->addPost("/video/create", array(
    'controller' => 'video',
    'action' => 'createData',
));

/**
 *
 */
$router->addPost("/video/execute-sql", array(
    'controller' => 'video',
    'action' => 'executeSql',
));
