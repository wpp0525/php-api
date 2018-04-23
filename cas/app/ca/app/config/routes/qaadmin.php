<?php
/**
 * QA管理后台
 * User: sx
 * Date: 2016/6/20
 * Time: 18:02
 */
$router->addPost("/qaadmin/saveTag[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'saveTag'
));
$router->addPost("/qaadmin/saveTagCategory[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'saveTagCategory'
));
$router->addPost("/qaadmin/saveAdminAnswer[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'saveAdminAnswer'
));
$router->addPost("/qaadmin/auditQuestion[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'auditQuestion'
));
$router->addPost("/qaadmin/deleteQuestion[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'deleteQuestion'
));
$router->addPost("/qaadmin/resumeQuestion[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'resumeQuestion'
));
$router->addPost("/qaadmin/addQuestionTag[/]?". $sysParam, array(
    'controller' => 'Qaadmin',
    'action' => 'addQuestionTag'
));