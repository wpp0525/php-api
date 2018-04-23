<?php
$router->addPost("/qaanswer/index[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'index'
));
$router->addPost("/qaanswer/getList[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getList'
));
$router->addPost("/qaanswer/getDetailByQuestionId[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getDetailByQuestionId'
));

$router->addPost("/qaanswer/getAnswerByQuestionId[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getAnswerByQuestionId'
));

$router->addPost("/qaanswer/saveAnswer[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'saveAnswer'
));
$router->addPost("/qaanswer/saveQuestion[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'saveQuestion'
));
$router->addPost("/qaanswer/getTagByProductId[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getTagByProductId'
));
$router->addPost("/qaanswer/getProductByTagId[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getProductByTagId'
));
$router->addPost("/qaanswer/getTagByCategoryId[/]?". $sysParam, array(
    'controller' => 'Qaanswer',
    'action' => 'getTagByCategoryId'
));
$router->addPost("/qaanswer/delTagRel[/]?". $sysParam,array(
        "controller" => "Qaanswer",
        "action" => "delTagRel",
    )
);
$router->addPost("/qaanswer/saveTagRel[/]?". $sysParam,array(
        "controller" => "Qaanswer",
        "action" => "saveTagRel",
    )
);