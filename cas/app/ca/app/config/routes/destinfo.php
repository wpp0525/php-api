<?php

/**
 *
 */
$router->addGet("/destinfo/dest-all-info", array(
    'controller' => 'destinfo',
    'action' => 'destAllInfo',
));
$router->addGet("/destinfo/recom-dest", array(
    'controller' => 'destinfo',
    'action' => 'getRecomDest',
));
$router->addGet("/destinfo/recom-dest-mult", array(
    'controller' => 'destinfo',
    'action' => 'getRecomDestMult',
));
$router->addGet("/destinfo/dest-parents", array(
    'controller' => 'destinfo',
    'action' => 'destParents',
));
$router->addGet("/destinfo/dest-list", array(
    'controller' => 'destinfo',
    'action' => 'destListByIds',
));

$router->addGet("/destinfo/dest-static-num", array(
    'controller' => 'destinfo',
    'action' => 'getStaticNumByDestId',
));
$router->addGet("/destinfo/dest-add-count", array(
    'controller' => 'destinfo',
    'action' => 'addCount',
));
$router->addGet("/destinfo/dest-count-data", array(
    'controller' => 'destinfo',
    'action' => 'getDestCountData',
));
$router->addGet("/destinfo/dest-child-list", array(
    'controller' => 'destinfo',
    'action' => 'getDestChild',
));
$router->addGet("/destinfo/dest-child-list-mult", array(
    'controller' => 'destinfo',
    'action' => 'getDestChildMult',
));
$router->addGet("/destinfo/dest-image-list", array(
    'controller' => 'destinfo',
    'action' => 'destIndexImageList',
));
$router->addGet("/destinfo/dest-viewspot-subject", array(
    'controller' => 'destinfo',
    'action' => 'destViewspotGroupBySub',
));
$router->addGet("/destinfo/get-dest-sublist", array(
    'controller' => 'destinfo',
    'action' => 'destSubjectList',
));
$router->addGet("/destinfo/get-dest-district", array(
    'controller' => 'destinfo',
    'action' => 'destDistrict',
));
$router->addGet("/destinfo/get-dest-namelike", array(
    'controller' => 'destinfo',
    'action' => 'destViewListByPidAndName',
));
$router->addGet("/destinfo/get-view-tags", array(
    'controller' => 'destinfo',
    'action' => 'viewListByTag',
));
$router->addGet("/destinfo/get-rest-dis", array(
    'controller' => 'destinfo',
    'action' => 'restListByDis',
));