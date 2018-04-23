<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-7-15
 * Time: 下午3:06
 */
$router->addGet("/qashow/product-qa-list/", array(
    'controller' => 'qashow',
    'action' => 'getProductQaList'
));

$router->addGet("/qashow/get-one-info/", array(
    'controller' => 'qashow',
    'action' => 'getOneQaContentByQid'
));