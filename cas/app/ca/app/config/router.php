<?php
$router = new \Phalcon\Mvc\Router ();
$sysParam = "/{format:(json|xml)}/{token:[\w\d]+}/{timestamp:\d+}/{sign:[\w\d]+}";

include __DIR__ . '/routes/api.php';

include __DIR__ . '/routes/social.php';

include __DIR__ . '/routes/travel.php';

include __DIR__ . '/routes/newtrip.php';

include __DIR__ . '/routes/newguide.php';

include __DIR__ . '/routes/apptrip.php';

include __DIR__ . '/routes/trip.php';

include __DIR__ . '/routes/destinfo.php';

// 新目的地信息
include __DIR__ . '/routes/destinfonew.php';

include __DIR__ . '/routes/destsumary.php';

include __DIR__ . '/routes/video.php';

include __DIR__ . '/routes/apidata.php';

include __DIR__ . '/routes/pkdest.php';

include __DIR__ . '/routes/pkviewspot.php';

include __DIR__ . '/routes/pktravel.php';

include __DIR__ . '/routes/pkfood.php';

include __DIR__ . '/routes/pkhot.php';

include __DIR__ . '/routes/auth.php';

// FOR USER
include __DIR__ . '/routes/qashow.php';
include __DIR__ . '/routes/qacomshow.php';

// FOR ADMIN
include __DIR__ . '/routes/qaforcms.php';
include __DIR__ . '/routes/qacomforcms.php';

include __DIR__ . '/routes/qaanswer.php';

include __DIR__ . '/routes/qausercenter.php';

include __DIR__ . '/routes/qaadmin.php';

include __DIR__ . '/routes/subject.php';

include __DIR__ . '/routes/es.php';

include __DIR__ . '/routes/dest.php';

include __DIR__ . '/routes/recommend.php';

include __DIR__ . '/routes/seo.php';

include __DIR__ . '/routes/product.php';

include __DIR__ . '/routes/productpool.php';

include __DIR__ . '/routes/food.php';

include __DIR__ . '/routes/vstdest.php';

include __DIR__ . '/routes/ads.php';

include __DIR__ . '/routes/bigdest.php';

include __DIR__ . '/routes/message.php';

include __DIR__ . '/routes/distip.php';

include __DIR__ . '/routes/tvars.php';

include __DIR__ . '/routes/envconfig.php';

include __DIR__ . '/routes/dubbo.php';

include __DIR__ . '/routes/sctsystemcore.php';

include __DIR__ . '/routes/workflow.php';

include __DIR__ . '/routes/file_logger.php';

include __DIR__ . '/routes/visa.php';

include __DIR__ . '/routes/eliteimage.php';

$router->add ( "/", array (
    'controller' => 'index',
    'action' => 'index'
) );

$router->notFound ( array (
    'controller' => 'index',
    'action' => 'missing'
) );
return $router;
