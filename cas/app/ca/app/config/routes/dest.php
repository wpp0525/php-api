<?php

$router->addPost("/dest/getAppDestDetail" . $sysParam, array(
		'controller' => 'dest',
		'action' => 'getAppDestDetail',
));

$router->addPost("/dest/getAppDestListDetail" . $sysParam, array(
		'controller' => 'dest',
		'action' => 'getAppDestListDetail',
));

$router->addPost("/dest/wapGetPoiDataById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'wapGetPoiDataById',
));

$router->addPost("/dest/getPoiThem" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getPoiThem',
));

$router->addPost("/dest/getSubDestIdByDestId" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getSubDestIdByDestId',
));

$router->addPost("/dest/getAddress" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getAddress',
));

$router->addPost("/dest/getContactById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getContactById',
));

$router->addPost("/dest/getTimeById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getTimeById',
));

$router->addPost("/dest/getSuggestTimeById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getSuggestTimeById',
));

$router->addPost("/dest/getTicketById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getTicketById',
));

$router->addPost("/dest/getNewPicsByDest" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getNewPicsByDest',
));

$router->addPost("/dest/getPicsByDest" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getPicsByDest',
));

$router->addPost("/dest/getTransportByDest" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getTransportByDest',
));

$router->addPost("/dest/getDestByType" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestByType',
));
$router->addPost("/dest/getSummaryById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getSummaryById',
));
$router->addPost("/dest/getTripList" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getTripList',
));
$router->addPost("/dest/getBaseIdByDestId" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getBaseIdByDestId',
));
$router->addPost("/dest/getDestById" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestById',
));
$router->addPost("/dest/getDestParentsByIds" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestParentsByIds',
));
$router->addPost("/dest/getDestsByIds" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestsByIds',
	'action' => 'getDestsByIds',
));
$router->addPost("/dest/getNearestDests" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getNearestDests',
));
$router->addPost("/dest/getDestsBySubjectIds" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestsBySubjectIds',
));
$router->addPost("/dest/getRecommendDests" . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getRecommendDests',
));

$router->addGet('/dest/getDestNav' . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestNav',
));

$router->addGet('/dest/getDistrictIdByDestId' . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDistrictIdByDestId',
));

$router->addGet('/dest/getDestDistrictNav' . $sysParam, array(
	'controller' => 'dest',
	'action' => 'getDestDistrictNav',
));

$router->addGet('/dest/getHotChildren'. $sysParam, array(
    'controller' => 'dest',
    'action' => 'getHotChildren',
));

$router->addGet('/dest/getDestChildList'. $sysParam, array(
    'controller' => 'dest',
    'action' => 'getDestChildList',
));

$router->addGet('/dest/getTempCode'. $sysParam, array(
	'controller' => 'dest',
	'action' => 'getTempCode',
));

$router->addGet('/dest/setCancelFlag'. $sysParam, array(
	'controller' => 'dest',
	'action' => 'setCancelFlag',
));

$router->addGet('/dest/setShowed'. $sysParam, array(
	'controller' => 'dest',
	'action' => 'setShowed',
));

$router->addGet('/dest/setTempCode'. $sysParam, array(
	'controller' => 'dest',
	'action' => 'setTempCode',
));
$router->addPost('/dest/getInfoByCondition'. $sysParam, array(
	'controller' => 'dest',
	'action' => 'getInfoByCondition',
));

$router->addPost('/dest/saveDestination'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'saveDestination',
));

$router->addPost('/dest/saveSeqs'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'saveSeqs',
));

$router->addPost('/dest/saveLyData'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'saveLyData',
));

$router->add('/dest/featureDel'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'featureDel',
));

$router->add('/dest/featureSeqs'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'featureSeqs',
));

$router->addPost('/dest/featureSave'. $sysParam,array(
	'controller' => 'dest',
	'action' => 'featureSave',
));

$router->add('/dest/saveSuggestTime',array(
	'controller' => 'dest',
	'action' => 'saveSuggestTime',
))->setName('suggest_time_save');

$router->addPost('/dest/saveTicket',array(
	'controller' => 'dest',
	'action' => 'saveTicket',
))->setName('ticket_save');

$router->addPost('/dest/mustSave',array(
	'controller' => 'dest',
	'action' => 'mustSave',
))->setName('ly_must_save');

$router->addPost('/dest/recommendSearch',array(
	'controller' => 'dest',
	'action' => 'recommendSearch',
))->setName('ly_recommend_search');

$router->addPost('/consulate/save',array(
	'controller' => 'consulate',
	'action' => 'save',
))->setName('ly_consulate_save');

$router->addPost('/consulate/infoSave',array(
	'controller' => 'consulate',
	'action' => 'infoSave',
))->setName('ly_consulate_info_save');

$router->addPost('/dest/saveStay',array(
	'controller' => 'dest',
	'action' => 'saveStay',
))->setName('ly_stay_save');

$router->addPost('/dest/saveTravel',array(
	'controller' => 'dest',
	'action' => 'saveTravel'
))->setName('ly_travel_save');

$router->addPost('/dest/saveTravelDay',array(
	'controller' => 'dest',
	'action' => 'saveTravelDay'
))->setName('ly_travel_day_save');
