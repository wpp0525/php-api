<?php
/**
 * Created by PhpStorm.
 * User: sx
 * Date: 2016/6/20
 * Time: 16:55
 */
$parameter['qausercenter'] = array(
    'getUserQuestion'=> array(
        'uid' => array(
            'input' => 'uid',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'return_type' => array(
            'input' => 'return_type',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        ),
        'page' => array(
            'input' => 'page',
            'default' => '1',
            'rule' => '^\d+$',
            'required' => false
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => '15',
            'rule' => '^\d+$',
            'required' => false
        )
    ),

    'getCQuestionUcenter' => array(
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
        'type' => array(
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => false
        ),
        'page' => array(
            'input' => 'page',
            'default' => '1',
            'rule' => '^\d+$',
            'required' => false
        ),
        'pageSize' => array(
            'input' => 'pageSize',
            'default' => '10',
            'rule' => '^\d+$',
            'required' => false
        )
    ),

    'deleteCQfollowUc' => array(
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
        'qid' => array(
            'input' => 'qid',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
    ),

    'deleteCQAnswerUc' => array(
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
        'aid' => array(
            'input' => 'aid',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
    ),

    'getCQUcenterMy' => array(

        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
    ),

);