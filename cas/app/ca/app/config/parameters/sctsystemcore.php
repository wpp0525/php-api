<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-5-4
 * Time: 上午11:49
 */
$parameter['sctsystemcore'] = array(
    'repaireFunction' => array(
        'controller' => array(
            'input' => 'controller',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'method' => array(
            'input' => 'method',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    ),
    'updateDelstatus' => array(
        'id' => array(
            'input' => 'id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'type' => array(
            'input' => 'type',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'del_status' => array(
            'input' => 'del_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        )
    ),
);