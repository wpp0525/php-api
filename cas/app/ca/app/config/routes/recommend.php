<?php

/**
 *
 */
$router->addPost("/module/recommend", array(
    'controller' => 'recommend',
    'action' => 'travelRecommend',
));

/**
 *
 */
$router->addPost("/module/select", array(
    'controller' => 'recommend',
    'action' => 'select',
));

/**
 *
 */
$router->addPost("/module/query", array(
    'controller' => 'recommend',
    'action' => 'query',
));
