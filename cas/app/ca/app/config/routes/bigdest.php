<?php
$router->addGet("/bigdest/getNav" . $sysParam, array(
		'controller' => 'Bigdest',
		'action' => 'getNav',
));

$router->addGet("/bigdest/getNavigation" . $sysParam, array(
		'controller' => 'Bigdest',
		'action' => 'getNavigation',
));

$router->addGet("/bigdest/getCurrSeasonHot" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getCurrSeasonHot',
));
$router->addGet("/bigdest/getLuxuriousTrip" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getLuxuriousTrip',
));
$router->addGet("/bigdest/getLocalPlay" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getLocalPlay',
));
$router->addGet("/bigdest/getGoodHotel" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getGoodHotel',
));
$router->addGet("/bigdest/getTicket" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getTicket',
));
$router->addGet("/bigdest/getFreetour" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getFreetour',
));
$router->addGet("/bigdest/getGroup" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getGroup',
));
$router->addGet("/bigdest/getRomantic" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getNav',
));
$router->addGet("/bigdest/getHotCitys" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getHotCitys',
));

$router->addGet("/bigdest/getTrip" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getTrip',
));

$router->addGet("/bigdest/getTicketSubject" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getTicketSubject',
));

$router->addGet("/bigdest/getCommonHeader" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getCommonHeader',
));

$router->addGet("/bigdest/clearRedis" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'clearRedis',
));

$router->addGet("/bigdest/getTdk" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getTdk',
));

$router->addGet("/bigdest/getFilterContent" . $sysParam, array(
	'controller' => 'Bigdest',
	'action' => 'getFilterContent',
));
