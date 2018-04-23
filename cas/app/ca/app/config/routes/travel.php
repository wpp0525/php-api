<?php

/**
 * 
 */
$router->addPost("/travel/info-create" . $sysParam, array(
	'controller' => 'travel',
	'action' => 'createInfo',
));

/**
 * 
 */
$router->addPost("/travel/info-update" . $sysParam, array(
	'controller' => 'travel',
	'action' => 'updateInfo',
));


/**
 *
 */
$router->addGet("/desttravel/travel-mult", array(
    'controller' => 'travel',
    'action' => 'destTravelMult',
));
$router->addGet("/desttravel/travel-view-num", array(
    'controller' => 'travel',
    'action' => 'destTravelViewNum',
));
$router->addGet("/desttravel/travel-single", array(
    'controller' => 'travel',
    'action' => 'destTravelSingle',
));
$router->addGet('/desttravel/travel-viewids',array(
    'controller'=>'travel',
    'action'=>"destTravelViewIds"
));