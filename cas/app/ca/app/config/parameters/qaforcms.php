<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-22
 * Time: 下午1:48
 */
$parameter['qaforcms'] = array(//controller
    'getCheckList' => array(
        'product_id' => array(//parameter 1
            'input' => 'product_id',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'main_status' => array(//parameter 2
            'input' => 'main_status',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'del_status' => array(//parameter 3
            'input' => 'del_status',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'tag_id' => array(//parameter 3
            'input' => 'tag_id',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'uid' => array(//parameter 3
            'input' => 'uid',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'username' => array(//parameter 3
            'input' => 'username',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'auditor_id' => array(//parameter 3
            'input' => 'a_id',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'begin' => array(//parameter 3
            'input' => 'begin',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'end' => array(//parameter 3
            'input' => 'end',
            'default' => 'NULL',
            'rule' => '',
            'required' => false,
        ),
        'sign' => array(//parameter 3
            'input' => 'sign',
            'default' => 'NULL',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 3
            'input' => 'code',
            'default' => 'NULL',
            'rule' => '',
            'required' => true,
        ),
    ),
    'setQuestionSensitiveWord' => array(
        'id' => array(//parameter 1
            'input' => 'id',
            'default' => '',
            'rule' => '^\d+$',
            'required' => true,
        ),
        'content' => array(//parameter 1
            'input' => 'content',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sensitiveWord' => array(//parameter 1
            'input' => 'sensitiveWord',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'username' => array(//parameter 1
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'uid' => array(//parameter 1
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'update_time' => array(//parameter 1
            'input' => 'update_time',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'main_status' => array(//parameter 1
            'input' => 'main_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'updateQuestionMainStatus' => array(
        'id' => array(//parameter 1
            'input' => 'id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'auditor_id' => array(//parameter 1
            'input' => 'auditor_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'audit_time' => array(//parameter 1
            'input' => 'audit_time',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'main_status' => array(//parameter 1
            'input' => 'main_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getQuestionAnswerInfo' => array(
        'qid' => array(//parameter 1
            'input' => 'qid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'aid' => array(//parameter 1
            'input' => 'aid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'replyAuditorAnswer' => array(

        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),

    'operOneCommonQuestion' => array(
        'question_id' => array(//parameter 1
            'input' => 'question_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'answer_id' => array(
            'input' => 'answer_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'question' => array(//parameter 1
            'input' => 'question',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'admin_answer' => array(
            'input' => 'admin_answer',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'old_bu' => array(//parameter 1
            'input' => 'old_bu',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'old_tag' => array(
            'input' => 'old_tag',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'new_bu' => array(//parameter 1
            'input' => 'new_bu',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'new_tag' => array(
            'input' => 'new_tag',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'delOneCommonQuestion' => array(
        'id' => array(//parameter 1
            'input' => 'id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'del_tag' => array(
            'input' => 'del_tag',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getQuestionInfo' => array(
        'id' => array(//parameter 1
            'input' => 'id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getCommonQuestion' => array(
        'page' => array(//parameter 1
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'limit' => array(//parameter 1
            'input' => 'limit',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'tag1' => array(//parameter 1
            'input' => 'tag1',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'tag2' => array(//parameter 1
            'input' => 'tag2',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getOneCommonQuestion' => array(
        'id' => array(//parameter 1
            'input' => 'id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'getQuestionAnswerList' => array(
        'page' => array(//parameter 1
            'input' => 'page',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'main_status' => array(//parameter 1
            'input' => 'main_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'del_status' => array(//parameter 1
            'input' => 'del_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'uid' => array(//parameter 1
            'input' => 'uid',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'username' => array(//parameter 1
            'input' => 'username',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'count_answer' => array(//parameter 1
            'input' => 'count_answer',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'product_id' => array(//parameter 1
            'input' => 'product_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'auditor_id' => array(//parameter 1
            'input' => 'auditor_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'bu' => array(//parameter 1
            'input' => 'bu',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'tag_id' => array(//parameter 1
            'input' => 'tag_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'begin' => array(//parameter 1
            'input' => 'begin',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'end' => array(//parameter 1
            'input' => 'end',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    ),
    'replyAuditorAnswer' => array(
        'question_id' => array(//parameter 1
            'input' => 'question_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'answer_id' => array(//parameter 1
            'input' => 'answer_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'admin_answer' => array(//parameter 1
            'input' => 'admin_answer',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'tag_id' => array(//parameter 1
            'input' => 'tag_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'old_tag' => array(//parameter 1
            'input' => 'old_tag',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'bu_id' => array(//parameter 1
            'input' => 'bu_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'is_hide' => array(//parameter 1
            'input' => 'is_hide',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'main_status' => array(//parameter 1
            'input' => 'main_status',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'update_time' => array(//parameter 1
            'input' => 'update_time',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'cate_id' => array(//parameter 1
            'input' => 'cate_id',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'sign' => array(//parameter 1
            'input' => 'sign',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
        'code' => array(//parameter 1
            'input' => 'code',
            'default' => '',
            'rule' => '',
            'required' => true,
        ),
    )
);