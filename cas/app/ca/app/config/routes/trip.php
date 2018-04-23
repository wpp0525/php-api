<?php

/**
 * 
 */
$router->addPost("/trip/info-update" . $sysParam, array(
	'controller' => 'trip',
	'action' => 'updateInfo',
));
$router->addGet("/trip/set-data", array(
	'controller' => 'trip',
	'action' => 'setTripData',
));
$router->addGet("/trip/dest-trip-data", array(
	'controller' => 'trip',
	'action' => 'getTripList',
));
$router->addGet("/trip/tag-trip-data", array(
	'controller' => 'trip',
	'action' => 'getTripTag',
));
$router->addGet("/trip/check-trip-data", array(
	'controller' => 'trip',
	'action' => 'checkTrip',
));