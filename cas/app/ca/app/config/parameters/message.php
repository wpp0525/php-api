<?php
$parameter['message'] = array(//controller
    'getMsgByUid' => array(//action
        'uid' => array(//parameter 1
            'input' => 'uid',
            'default' => '',
            'rule' => '^[a-zA-Z0-9]+$',
            'required' => true,
        ),
        'type' => array(//parameter 1
            'input' => 'type',
            'default' => '',
            'rule' => '^[a-zA-Z]+$',
            'required' => false,
        ),
        'unread' => array(//parameter 1
            'input' => 'unread',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => false,
        ),
        'page' => array(//parameter 1
            'input' => 'page',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => false,
        ),
        'pageSize' => array(//parameter 1
            'input' => 'pageSize',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => false,
        ),
    ),
    'getUnreadCount'=> array(//action
        'uid' => array(//parameter 1
            'input' => 'uid',
            'default' => '',
            'rule' => '^[a-zA-Z0-9]+$',
            'required' => true,
        ),
    ),
    'getTypeUnreadCount'=> array(//action
        'uid' => array(//parameter 1
            'input' => 'uid',
            'default' => '',
            'rule' => '^[a-zA-Z0-9]+$',
            'required' => true,
        ),
        'type' => array(//parameter 1
            'input' => 'type',
            'default' => '',
            'rule' => '^[a-zA-Z]+$',
            'required' => true,
        ),
    ),
    'getMsgDetail'=> array(//action
        'mid' => array(//parameter 1
            'input' => 'mid',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => true,
        ),
    ),
    'msgDelete'=> array(//action
        'mid' => array(//parameter 1
            'input' => 'mid',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => true,
        ),
    ),
    'msgRead'=> array(//action
        'mid' => array(//parameter 1
            'input' => 'mid',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => true,
        ),
    ),
    'getAllMsgByUid' => array(//action
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '^[a-zA-Z0-9]+$',
            'required' => true,
        ),
        'page' => array(
            'input' => 'page',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => false,
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => '',
            'rule' => '^[0-9]+$',
            'required' => false,
        ),
    ),
);