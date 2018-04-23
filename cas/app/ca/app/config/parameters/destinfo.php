<?php

/**
 * 目的地基础数据相关接口
 *
 * @author mac.zhao
 */
$parameter['destinfo'] = array(//controller
    'destAllInfo' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'getRecomDest'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'recom_type'=>array(
            'input' => 'recom_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'pn'=>array(
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'ps'=>array(
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit'=>array(
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'getRecomDestMult'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'recom_type'=>array(
            'input' => 'recom_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'pn'=>array(
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'ps'=>array(
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit'=>array(
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        )
    ),
    'destParents'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'destListByIds'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'getStaticNumByDestId'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'addCount'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'type' => array(//parameter 1
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'getDestCountData'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dest_type' => array(//parameter 1
            'input' => 'dest_type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getDestChild'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'pn'=>array(
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'ps'=>array(
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit'=>array(
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'recom_type' => array(//parameter 1
            'input' => 'recom_type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dest_type' => array(//parameter 1
            'input' => 'dest_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),

    ),
    'getDestChildMult'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'pn'=>array(
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'ps'=>array(
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit'=>array(
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'forcedb' => array(//parameter 1
            'input' => 'forcedb',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'recom_type' => array(//parameter 1
            'input' => 'recom_type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dest_type' => array(//parameter 1
            'input' => 'dest_type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),

    ),
    'destIndexImageList'=>array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => 5,
            'rule' => '',
            'required' => true,
        ),
    ),
    'destViewspotGroupBySub'=>array(
        'base_id' => array(//parameter 1
            'input' => 'base_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'num' => array(//parameter 1
            'input' => 'num',
            'default' =>5,
            'rule' => '',
            'required' => true,
        ),

        'dest_num' => array(//parameter 1
            'input' => 'dest_num',
            'default' =>8,
            'rule' => '',
            'required' => true,
        ),
    ),
    'destSubjectList'=>array(
        'base_id' => array(//parameter 1
            'input' => 'base_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'destDistrict'=>array(
        'dis_pid' => array(//parameter 1
            'input' => 'dis_pid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dest_type' => array(//parameter 1
            'input' => 'dest_type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'destViewListByPidAndName'=>array(
        'base_id' => array(//parameter 1
            'input' => 'base_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dest_name' => array(//parameter 1
            'input' => 'dest_name',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'pn' => array(//parameter 1
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'ps' => array(//parameter 1
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'viewListByTag'=>array(
        'base_id' => array(//parameter 1
            'input' => 'base_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'tag_condition' => array(//parameter 1
            'input' => 'tag_condition',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'pn' => array(//parameter 1
            'input' => 'pn',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'ps' => array(//parameter 1
            'input' => 'ps',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'restListByDis'=>array(
        'base_id' => array(//parameter 1
            'input' => 'base_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'dis' => array(//parameter 1
            'input' => 'dis',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getRecomDestTop' => array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'find_type' => array(//parameter 1
            'input' => 'find_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'getRecomMainDestTop' => array(
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'find_type' => array(//parameter 1
            'input' => 'find_type',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
);