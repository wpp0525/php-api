<?php
$router->addPost("/apptrip/createInfo", array(
    'controller' => 'apptrip',
    'action' => 'createInfo',
));

$router->addPost("/apptrip/tripAct", array(
    'controller' => 'apptrip',
    'action' => 'tripAct',
));

$router->addPost("/apptrip/createImage", array(
    'controller' => 'apptrip',
    'action' => 'createImage',
));

$router->addPost("/apptrip/selectData", array(
    'controller' => 'apptrip',
    'action' => 'selectData',
));

$router->addPost("/apptrip/selectTripList", array(
    'controller' => 'apptrip',
    'action' => 'selectTripList',
));

$router->addPost("/apptrip/selectImgList", array(
    'controller' => 'apptrip',
    'action' => 'selectImgList',
));

$router->addPost("/apptrip/deleteTrip", array(
    'controller' => 'apptrip',
    'action' => 'deleteTrip',
));

$router->addPost("/apptrip/queryTrip", array(
    'controller' => 'apptrip',
    'action' => 'queryTrip',
));