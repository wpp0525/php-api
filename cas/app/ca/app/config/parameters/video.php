<?php

/**
 * 视频游记相关接口参数
 *
 * @author jianghu
 */
$parameter['video'] = array(//controller
    'selectData' => array(//action
        'table' => array(
            'input' => 'table',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'select' => array(
            'input' => 'select',
            'default' => '*',
            'rule' => '',
            'required' => false,
        ),
        'where' => array(
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'order' => array(
            'input' => 'order',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'group' => array(
            'input' => 'group',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'limit' => array(
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'page' => array(
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
    ),
    'updateData' => array(//action
        'table' => array(//parameter 1
            'input' => 'table',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'where' => array(//parameter 1
            'input' => 'where',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'data' => array(//parameter 1
            'input' => 'data',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    ),
    'createData' => array(//action
        'table' => array(//parameter 1
            'input' => 'table',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'data' => array(//parameter 1
            'input' => 'data',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'executeSql' => array(//action
        'sql' => array(//parameter 1
            'input' => 'sql',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    ),
);