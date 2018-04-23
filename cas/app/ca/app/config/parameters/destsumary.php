<?php

/**
 * 目的地基础数据相关接口
 *
 * @author mac.zhao
 */
$parameter['destsumary'] = array(//controller
    'destSuggestTime' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'poiSummaryData' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'vstPoiSummaryData' => array(//action
        'dest_id' => array(//parameter 1
            'input' => 'dest_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'destScenerySum' => array(//action
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
);