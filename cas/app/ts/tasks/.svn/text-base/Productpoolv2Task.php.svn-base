<?php
/**
 * 产品池优化
 * 将数据库中对应的数据批量缓存到redis中来
 */

use Phalcon\CLI\Task;
use Lvmama\Common\Utils\Filelogger;

/**
 *
 * Class Productpoolv2Task
 */
class Productpoolv2Task extends Task
{

    /**
     * @var \ProductPoolRedisV2Service
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
        $this->dist = new \ProductPoolRedisV2Service($dependencyInjector);
        //$this->dist_delete = new \ProductPoolRedisV2DelService($dependencyInjector);
    }

    /**
     * @example php ts.php productpoolv2 updateAll
     */
    public function updateAllAction(array $params)
    {
        $this->dist->process($params);

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
     * 将产品信息放入队列
     * @author lixiumeng
     * @datetime 2017-09-01T09:41:30+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function updateCronAction($params)
    {
        $this->dist->putProductInKafka();
    }

    /**
     * 修复product_id
     * @author lixiumeng
     * @datetime 2017-09-06T18:06:24+0800
     * @param    array                    $params [description]
     * @return   [type]                           [description]
     */
    public function fixProductIdAction($params)
    {
        $this->dist->fixProductId();
    }

    /**
     * 构建行政区信息
     * @author lixiumeng
     * @datetime 2017-10-13T15:25:40+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function buildDistrictAction($params)
    {
        $this->dist->buildDistrict();
    }
    /**
     * 把门票类所属城市及以上级别的行政区ID补全到产品池的行政区ID字段里面去
     */
    public function ticketDistrictAction($params){
        Filelogger::getInstance()->addLog('开始处理门票类型所属城市行政区ID的任务','INFO');
        $this->dist->complementTicketDistrictId();
        Filelogger::getInstance()->addLog('完成门票类型所属城市行政区ID处理的任务','INFO');
    }
}
