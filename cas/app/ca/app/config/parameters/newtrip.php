<?php

/**
 * 新游记相关接口参数
 *
 * @author zhta
 */
$parameter['newtrip'] = array(//controller
    'createInfo' => array(//action
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'username' => array(
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'title' => array(
            'input' => 'title',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'seo_title' => array(
            'input' => 'seo_title',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'summary' => array(
            'input' => 'summary',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'thumb' => array(
            'input' => 'thumb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'start_time' => array(
            'input' => 'start_time',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'publish_time' => array(
            'input' => 'publish_time',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'order_num' => array(
            'input' => 'order_num',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'losc_inner' => array(
            'input' => 'losc_inner',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'losc_outer' => array(
            'input' => 'losc_outer',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'status' => array(
            'input' => 'status',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'recommend_status' => array(
            'input' => 'recommend_status',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'trip_id' => array(
            'input' => 'trip_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'order_id' => array(
            'input' => 'order_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'product_id' => array(
            'input' => 'product_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'source' => array(
            'input' => 'source',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'platform' => array(
            'input' => 'platform',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'device_no' => array(
            'input' => 'device_no',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'port' => array(
            'input' => 'port',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'commit_time' => array(
            'input' => 'commit_time',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'main_status' => array(
            'input' => 'main_status',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'del_status' => array(
            'input' => 'del_status',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'fanli_status' => array(
            'input' => 'fanli_status',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'createContent' => array(//action
        'title' => array(//parameter 1
            'input' => 'title',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'content' => array(//parameter 2
            'input' => 'content',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'content_id' => array(//parameter 3
            'input' => 'content_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'order_num' => array(//parameter 4
            'input' => 'order_num',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'trip_id' => array(//parameter 5
            'input' => 'trip_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'dest_id' => array(//parameter 6
            'input' => 'dest_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'dest_type' => array(//parameter 7
            'input' => 'dest_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'travel_content_id' => array(//parameter 8
            'input' => 'travel_content_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'is_main' => array(//parameter 9
            'input' => 'is_main',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'sync_status' => array(//parameter 9
            'input' => 'sync_status',
            'default' => '0',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'createImage' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '\d+',
            'required' => true,
        ),
        'imgurl' => array(//parameter 2
            'input' => 'imgurl',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'width' => array(//parameter 3
            'input' => 'width',
            'default' => '0',
            'rule' => '',
            'required' => true,
        ),
        'trip_id'=>array(//parameter 4
            'input' => 'trip_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'selectTrip' => array(//action
        'table' => array(//parameter 1
            'input' => 'table',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'select' => array(//parameter 2
            'input' => 'select',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'where'=>array(//parameter 3
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'order'=>array(//parameter 4
            'input' => 'order',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'group'=>array(//parameter 5
            'input' => 'group',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit'=>array(//parameter 6
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'page'=>array(//parameter 7
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'deleteTrip' => array(//action
        'table' => array(//parameter 1
            'input' => 'table',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'where'=>array(//parameter 2
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'deleteTravel' => array(//action
        'trip_id' => array(//parameter 1
            'input' => 'trip_id',
            'default' => '0',
            'rule' => '\d+',
            'required' => true,
        ),
        'uid'=>array(//parameter 2
            'input' => 'uid',
            'default' => '0',
            'rule' => '',
            'required' => false,
        )
    ),
    'queryTrip' => array(//action
        'sql' => array(//parameter 1
            'input' => 'sql',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    ),
    'getTripDataForVideoByDestId' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '0',
            'rule' => '\d+',
            'required' => true,
        )
    ),
    'getRecommendTrip' => array(//action
        'trip_id' => array(//parameter 1
            'input' => 'trip_id',
            'default' => '0',
            'rule' => '\d+',
            'required' => true,
        )
    ),
    'getTripByDest' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '0',
            'rule' => '\d+',
            'required' => true,
        ),
        'page' => array(//parameter 2
            'input' => 'page',
            'default' => '1',
            'rule' => '\d+',
            'required' => false,
        ),
        'pageSize' => array(//parameter 3
            'input' => 'pageSize',
            'default' => '20',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'getDestInfo' => array(//action
        'destName'=>array(//parameter 1
            'input' => 'destName',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'destId'=>array(//parameter 2
            'input' => 'destId',
            'default' => '0',
            'rule' => '\d+',
            'required' => false,
        )
    )
);