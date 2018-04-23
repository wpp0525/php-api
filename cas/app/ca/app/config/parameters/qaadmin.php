<?php
/**
 * QA管理后台
 * User: sx
 * Date: 2016/6/20
 * Time: 18:01
 */
$parameter['qaadmin'] = array(//controller
    'saveTag' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        ),
        'category_id' => array(
            'input' => 'category_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'name' => array(
            'input' => 'name',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        )
    ),
    'saveTagCategory' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        ),
        'name' => array(
            'input' => 'name',
            'default' => '',
            'rule' => '',
            'required' => false
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        )
    ),
    'saveAdminAnswer' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        ),
        'question_id' => array(
            'input' => 'question_id',
            'default' => 0,
            'rule' => '^\d+$',
            'required' => true
        ),
        'admin_id' => array(
            'input' => 'admin_id',
            'default' => 0,
            'rule' => '^\d+$',
            'required' => true
        ),
        'content' => array(
            'input' => 'content',
            'default' => '',
            'rule' => '',
            'required' => true
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        )
    ),
    'auditQuestion' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'auditor_id' => array(
            'input' => 'auditor_id',
            'default' => 0,
            'rule' => '^\d+$',
            'required' => true
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        )
    ),
    'deleteQuestion' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        )
    ),
    'resumeQuestion' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        )
    ),
    'addQuestionTag' => array(
        'question_id' => array(
            'input' => 'question_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'tag_id' => array(
            'input' => 'tag_id',
            'default' => '',
            'rule' => '',
            'required' => true
        )
    )
);