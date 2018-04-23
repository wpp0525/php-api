<?php
/**
 * 问答相关接口参数
 *
 * @author win.sx
 */
$parameter['qaanswer'] = array(//controller
    'index' => array(
    ),
    'getList' => array(
        'product_id' => array(
            'input' => 'product_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'category_id' => array(
            'input' => 'category_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'tag_id' => array(
            'input' => 'tag_id',
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
    'getDetailByQuestionId' => array(
        'question_id' => array(
            'input' => 'question_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        )
    ),
    'getAnswerByQuestionId' => array(
        'question_id' => array(
            'input' => 'question_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true
        ),
        'answer_type' => array(
            'input' => 'answer_type',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false
        )
    ),
    'saveAnswer' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '',
            'required' => false
        ),
        'question_id' => array(
            'input' => 'question_id',
            'default' => '0',
            'rule' => '',
            'required' => false
        ),
        'uid' => array(
            'input' => 'uid',
            'default' => '0',
            'rule' => '',
            'required' => false
        ),
        'username' => array(
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => false
        ),
        'content' => array(
            'input' => 'content',
            'default' => '',
            'rule' => '',
            'required' => false
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '',
            'required' => false
        ),
        'del_status' => array(
            'input' => 'del_status',
            'default' => '0',
            'rule' => '',
            'required' => false
        )
    ),
    'saveQuestion' => array(
        'id' => array(
            'input' => 'id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'dest_id' => array(
            'input' => 'dest_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'product_id' => array(
            'input' => 'product_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'bu_id' => array(
            'input' => 'bu_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'tag_id' => array(
            'input' => 'tag_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'uid' => array(
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'username' => array(
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'title' => array(
            'input' => 'title',
            'default' => '',
            'rule' => '',
            'required' => false,
        ),
        'content' => array(
            'input' => 'content',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'auditor_id' => array(
            'input' => 'auditor_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'audit_time' => array(
            'input' => 'audit_time',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'status' => array(
            'input' => 'status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        ),
        'del_status' => array(
            'input' => 'del_status',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => false,
        )
    ),
    'getTagByProductId' => array(
        'product_id' => array(
            'input' => 'product_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        )
    ),
    'getProductByTagId' => array(
        'tag_id' => array(
            'input' => 'tag_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        )
    ),
    'getTagByCategoryId' => array(
        'category_id' => array(
            'input' => 'category_id',
            'default' => '0',
            'rule' => '^\d+$',
            'required' => true,
        )
    ),
);