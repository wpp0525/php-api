<?php
/**
 * 产品池优化
 * 将数据库中对应的数据批量缓存到redis中来
 */

use Phalcon\CLI\Task;

/**
 *
 * Class Productpoolv2Task
 */
class Goodspoolv2Task extends Task
{

    /**
     * @var DestMultiRelationService
     */
    private $dist;

    private $dist_delete;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $this->dist        = new \GoodsPoolRedisV2Service($dependencyInjector);
        $this->dist_delete = new \GoodsPoolRedisV2DelService($dependencyInjector);
    }

    /**
     * @example php ts.php productpoolv2 updateAll
     */
    public function updateAllAction(array $params)
    {
        $this->dist->process();

        $action = strtolower($params[0]);
//        var_dump($action);
        //        die('333');

    }

    public function deleteAllAction(array $params)
    {
        $this->dist_delete->process();

        $action = strtolower($params[0]);
//        var_dump($action);
        //        die('333');

    }

    /**
     * 定时脚本, 将商品放入缓存进行处理
     * @author lixiumeng
     * @datetime 2017-09-01T10:05:46+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function updateCronAction(array $params)
    {
        $this->dist->putGoodsInKafka();
    }

}
