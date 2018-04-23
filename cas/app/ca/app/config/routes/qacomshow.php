<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-8-31
 * Time: 上午11:57
 */

$router->addGet("/qacomshow/index-ad-list/", array(
    'controller' => 'qacomshow',
    'action' => 'getIndexAdList'
));

$router->addGet("/qacomshow/dest-cq-list/", array(
    'controller' => 'qacomshow',
    'action' => 'getDestCQuestionList'
));

$router->addGet("/qacomshow/tag-cq-list/", array(
    'controller' => 'qacomshow',
    'action' => 'getTagCQuestionList'
));

$router->addGet("/qacomshow/all-cq-list/", array(
    'controller' => 'qacomshow',
    'action' => 'getAllQuestionList'
));

$router->addGet("/qacomshow/cq-info/", array(
    'controller' => 'qacomshow',
    'action' => 'getCQuestionInfo'
));

$router->addGet("/qacomshow/cq-info-show/", array(
    'controller' => 'qacomshow',
    'action' => 'getCQuestionShow'
));

$router->addGet("/qacomshow/follow/", array(
    'controller' => 'qacomshow',
    'action' => 'setFollow'
));

$router->addPost("/qacomshow/update-question/", array(
    'controller' => 'qacomshow',
    'action' => 'updateQuestion'
));
$router->addGet("/qacomshow/update-question/", array(
    'controller' => 'qacomshow',
    'action' => 'updateQuestion'
));


$router->addPost("/qacomshow/update-answer/", array(
    'controller' => 'qacomshow',
    'action' => 'updateAnswer'
));
$router->addGet("/qacomshow/update-answer/", array(
    'controller' => 'qacomshow',
    'action' => 'updateAnswer'
));
$router->addPost("/qacomshow/update-answer-comment/", array(
    'controller' => 'qacomshow',
    'action' => 'updateAnswerComment'
));
$router->addGet("/qacomshow/update-answer-comment/", array(
    'controller' => 'qacomshow',
    'action' => 'updateAnswerComment'
));

$router->addGet("/qacomshow/user-data/", array(
    'controller' => 'qacomshow',
    'action' => 'getUserData'
));

$router->addGet("/qacomshow/get-list-by-ids/", array(
    'controller' => 'qacomshow',
    'action' => 'getCQuestionByIds'
));

$router->addGet("/qacomshow/get-dest-info/", array(
    'controller' => 'qacomshow',
    'action' => 'getDestInfoById'
));

$router->addGet("/qacomshow/get-tag-info/", array(
    'controller' => 'qacomshow',
    'action' => 'getOneTagInfo'
));


$router->addGet("/qacomshow/get-tag-list/", array(
    'controller' => 'qacomshow',
    'action' => 'getOneTagList'
));

$router->addGet('/qacomshow/get-cq/', array(
    'controller' => 'qacomshow',
    'action' => 'getOneByQId'
));

$router->addGet('/qacomshow/get-answer-by-id/', array(
    'controller' => 'qacomshow',
    'action' => 'getCQAnswerBase'
));
$router->addGet('/qacomshow/get-comment-list-page/', array(
    'controller' => 'qacomshow',
    'action' => 'getCQCommentPage'
));

$router->addGet('/qacomshow/del-comment/', array(
    'controller' => 'qacomshow',
    'action' => 'delCQComment'
));

$router->addGet('/qacomshow/user-top/', array(
    'controller' => 'qacomshow',
    'action' => 'getUserTop'
));

$router->addGet('/qacomshow/total-question-and-user/', array(
    'controller' => 'qacomshow',
    'action' => 'getTotalQandU'
));

$router->addGet('/qacomshow/get-answer-list/', array(
    'controller' => 'qacomshow',
    'action' => 'getAnswerListByType'
));
