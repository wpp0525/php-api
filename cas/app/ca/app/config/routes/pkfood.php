<?php

$router->addGet('/pkfood/getFoodCount/{dest_id:[0-9]+}[/]?'. $sysParam,array(
	'controller' => 'Pkfood',
	'action' => 'getFoodCount'
));

$router->addGet('/pkfood/getFood/{dest_id:[0-9]+}[/]?'. $sysParam,array(
	'controller' => 'Pkfood',
	'action' => 'getFood'
));

$router->addGet('/pkfood/getFood/{dest_id:[0-9]+}/{page:[0-9]+}[/]?'. $sysParam,array(
	'controller' => 'Pkfood',
	'action' => 'getFood'
));

$router->addGet('/pkfood/getFood/{dest_id:[0-9]+}/{page:[0-9]+}/{pageSize:[0-9]+}[/]?'. $sysParam,array(
	'controller' => 'Pkfood',
	'action' => 'getFood'
));