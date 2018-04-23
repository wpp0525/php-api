<?php

/**
 * 目的地基础数据相关接口
 *
 * @author mac.zhao
 */
$parameter['apidata'] = array(//controller
    'destSearchPrdAll' => array(//action
        'dest_name' => array(//parameter 1
            'input' => 'dest_name',
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
        'type' => array(//parameter 1
            'input' => 'type',
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
        'dest_abroad' => array(//parameter 1
            'input' => 'dest_abroad',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),

    ),
    'destTicketsMult' => array(//action
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
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),

    ),
    'destLineMult' => array(//action
        'dest_name' => array(//parameter 1
            'input' => 'dest_name',
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
        'num' => array(//parameter 1
            'input' => 'num',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),

    ),
);