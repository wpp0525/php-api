<?php
$router->addGet("/pktravel/getTravel/{dest_id}[/]?". $sysParam, array(
		'controller' => 'Pktravel',
		'action' => 'getTravel'
));

$router->addGet("/pktravel/getTravel/{dest_id}/{page}[/]?". $sysParam, array(
		'controller' => 'Pktravel',
		'action' => 'getTravel'
));

$router->addGet("/pktravel/getTravel/{dest_id}/{page}/{pageSize}[/]?". $sysParam, array(
		'controller' => 'Pktravel',
		'action' => 'getTravel'
));