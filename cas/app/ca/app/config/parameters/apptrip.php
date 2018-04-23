<?php

/**
 * app游记相关接口参数
 *
 * @author zhta
 */
$parameter['apptrip'] = array(//controller
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
        'memo' => array(
            'input' => 'memo',
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
        'version' => array(
            'input' => 'version',
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
        ),
        'dest_id' => array(
            'input' => 'dest_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'createImage' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'memo' => array(//parameter 2
            'input' => 'memo',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'imgurl' => array(//parameter 3
            'input' => 'imgurl',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'img_id' => array(//parameter 4
            'input' => 'img_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'trip_id' => array(//parameter 5
            'input' => 'trip_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'tripAct' => array(//action
        'parent_id' => array(//parameter 1
            'input' => 'parent_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'type' => array(//parameter 2
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'uid' => array(//parameter 3
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'username' => array(//parameter 4
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'trip_id'=>array(//parameter 5
            'input' => 'trip_id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        ),
        'memo'=>array(//parameter 6
            'input' => 'memo',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'id'=>array(//parameter 7
            'input' => 'id',
            'default' => '',
            'rule' => '\d+',
            'required' => false,
        )
    ),
    'selectData' => array(//action
        'type' => array(//parameter 1
            'input' => 'type',
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
    'selectTripList' => array(//action
        'where'=>array(//parameter 1
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'page'=>array(//parameter 2
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'selectImgList' => array(//action
        'where'=>array(//parameter 1
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'page'=>array(//parameter 2
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'deleteTrip' => array(//action
        'type' => array(//parameter 1
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'id'=>array(//parameter 2
            'input' => 'id',
            'default' => '',
            'rule' => '\d+',
            'required' => true,
        )
    ),
    'queryTrip' => array(//action
        'sql' => array(//parameter 1
            'input' => 'sql',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    )
);