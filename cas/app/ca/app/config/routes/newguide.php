<?php
$router->addPost("/newguide/info-update", array(
    'controller' => 'newguide',
    'action' => 'createInfo',
));

$router->addPost("/newguide/content-update", array(
    'controller' => 'newguide',
    'action' => 'createContent',
));

$router->addPost("/newguide/image-update", array(
    'controller' => 'newguide',
    'action' => 'createImage',
));


//=============================
$router->addPost("/newguide/trip-select", array(
    'controller' => 'newguide',
    'action' => 'selectTrip',
));

$router->addPost("/newguide/trip-delete", array(
    'controller' => 'newguide',
    'action' => 'deleteTrip',
));

$router->addPost("/newguide/delete-travel", array(
    'controller' => 'newguide',
    'action' => 'deleteTravel',
));

$router->addPost("/newguide/trip-query", array(
    'controller' => 'newguide',
    'action' => 'queryTrip',
));

$router->addPost("/newguide/get-dest-info-by-name", array(
    'controller' => 'newguide',
    'action' => 'getDestInfoByName',
));

$router->addPost("/newguide/get-dest-type-by-id", array(
    'controller' => 'newguide',
    'action' => 'getDestTypeById',
));

$router->addPost("/newguide/get-relation-city-by-guide_id", array(
    'controller' => 'newguide',
    'action' => 'getRelCityByGuideId',
));

//==================================
$router->addPost("/newguide/insert-content-dest-rel", array(
    'controller' => 'newguide',
    'action' => 'insertContentDestRel',
));

$router->addPost("/newguide/update-content-dest-rel", array(
    'controller' => 'newguide',
    'action' => 'updateContentDestRel',
));

$router->addPost("/newguide/update-image", array(
    'controller' => 'newguide',
    'action' => 'updateImage',
));

$router->addPost("/newguide/get-guide-list", array(
    'controller' => 'newguide',
    'action' => 'getGuideList',
));

$router->addPost("/newguide/get-redis-data", array(
    'controller' => 'newguide',
    'action' => 'getRedisData',
));

$router->addPost("/newguide/del-redis-data", array(
    'controller' => 'newguide',
    'action' => 'delRedisData',
));

$router->addPost("/newguide/save-configure-data", array(
    'controller' => 'newguide',
    'action' => 'saveConfigureData',
));

$router->addPost("/newguide/del-configure-data", array(
    'controller' => 'newguide',
    'action' => 'delConfigureData',
));

$router->addPost("/newguide/get-rel-guide-by-destid", array(
    'controller' => 'newguide',
    'action' => 'getRelationGuideByDestId',
));