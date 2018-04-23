<?php

$router->addPost("/productpool/buildBlackRule" . $sysParam, array(
		'controller' => 'Productpool',
		'action' => 'buildBlackRule',
));

$router->addPost("/productpool/buildPlace" . $sysParam, array(
		'controller' => 'Productpool',
		'action' => 'buildPlace',
));