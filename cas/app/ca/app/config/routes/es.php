<?php
$router->addPost("/es/ajaxWaySearch", array(
    'controller' => 'es',
    'action' => 'ajaxWaySearch',
));

$router->addPost("/es/article", array(
    'controller' => 'es',
    'action' => 'article',
));
$router->addPost("/es/test", array(
    'controller' => 'es',
    'action' => 'test',
));
$router->addPost("/es/waySearch", array(
    'controller' => 'es',
    'action' => 'waySearch',
));
$router->addPost("/es/getTravelData", array(
    'controller' => 'es',
    'action' => 'getTravelData',
));
$router->addPost("/es/getDestIdByName", array(
    'controller' => 'es',
    'action' => 'getDestIdByName',
));
$router->addPost("/es/getDestIdsByNames", array(
    'controller' => 'es',
    'action' => 'getDestIdsByNames',
));
$router->addPost("/es/getQaQuestion", array(
    'controller' => 'es',
    'action' => 'getQaQuestion',
));
$router->addPost("/es/getVst", array(
    'controller' => 'es',
    'action' => 'getVst',
));