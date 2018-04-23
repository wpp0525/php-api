<?php

$router->addGet("/pkdest/getDestById/{id}[/]?". $sysParam, array(
		'controller' => 'Pkdest',
		'action' => 'getDestById'
));

$router->addGet("/pkdest/getImgCount/{dest_id}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getImgCount'
));

$router->addGet("/pkdest/getImg/{dest_id:[0-9]+}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getImg'
));
$router->addGet("/pkdest/getImg/{dest_id:[0-9]+}/{page:[0-9]+}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getImg'
));
$router->addGet("/pkdest/getImg/{dest_id:[0-9]+}/{page:[0-9]+}/{pageSize:[0-9]+}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getImg'
));
$router->addGet("/pkdest/getImg/{dest_id:[0-9]+}/{page:[0-9]+}/{pageSize:[0-9]+}/{uid}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getImg'
));
$router->addGet("/pkdest/getRecommendViewspot/{dest_id:[0-9]+}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getRecommendViewspot'
));
$router->addGet("/pkdest/getRecommendViewspot/{dest_id:[0-9]+}/{limit:[0-9]+}[/]?". $sysParam, array(
	'controller' => 'Pkdest',
	'action' => 'getRecommendViewspot'
));
