<?php

/**
 *
 */
$router->addGet("/destsumary/suggest-time", array(
    'controller' => 'destsumary',
    'action' => 'destSuggestTime',
));
$router->addGet("/destsumary/poi-summary-data", array(
    'controller' => 'destsumary',
    'action' => 'poiSummaryData',
));
$router->addGet("/destsumary/dest-scenery-summary", array(
    'controller' => 'destsumary',
    'action' => 'destScenerySum',
));


$router->addGet("/vstdestsumary/poi-summary-data", array(
    'controller' => 'destsumary',
    'action' => 'vstPoiSummaryData',
));