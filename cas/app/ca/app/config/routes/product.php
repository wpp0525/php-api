<?php
/**
 * 获取java端产品相关接口数据
 * User: shenxiang
 * Date: 16-9-1
 */
$router->addGet("/product/getData", array(
    'controller' => 'product',
    'action' => 'getData',
));

$router->addGet("/product/getProductGoods", array(
    'controller' => 'product',
    'action' => 'getProductGoods',
));

$router->addGet("/product/getBaseByPid", array(
    'controller' => 'product',
    'action' => 'getBaseByPid',
));

$router->addGet("/product/updateProductByPid", array(
    'controller' => 'product',
    'action' => 'updateProductByPid',
));

$router->addGet("/product/updateRefreshStatus", array(
    'controller' => 'product',
    'action' => 'updateRefreshStatus',
));

$router->addGet("/product/kafkaProduct", array(
    'controller' => 'product',
    'action' => 'kafkaProduct',
));

$router->addGet('/product/getDestByProduct',array(
    'controller' => 'product',
    'action' => 'getDestByProduct'
));

$router->addGet('/product/getDistrictProductCounts',array(
    'controller' => 'product',
    'action' => 'getDistrictProductCounts'
));