<?php
/**
 * Created by PhpStorm.
 * User: jack.dong
 * Date: 2017/4/17
 * Time: 15:21
 *
 */
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use Lvmama\Cas\Service\RedisDataService;

/**
 * Class DestmultirelationTask
 * @purpose 目的地多级关系优化任务
 */
class DestmultirelationTask extends Task {

    /**
     * @var Phalcon\DiInterface
     */
    private $di;

    /**
     * @var DestMultiRelationService
     */
    private $dist;

    /**
     * @var Lvmama\Cas\Service\DestinBaseMultiRelationDataService
     */
    private $destin_multi_relation_base;

    /**
     * @var \Lvmama\Cas\Service\RedisDataService
     */
    private $redis;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->dist = new \DestMultiRelationService($dependencyInjector);
        $this->di = $dependencyInjector;
    }

    /**
     * @example php ts.php destmultirelation updateGrandId
     */
    public function updateGrandIdAction(array $params) {
        $this->dist->process();

        $action = strtolower ($params[0]);
    }

    /**
     * 导入redis
     * @example php ts.php destmultirelation load2redis
     */
    public function load2redisAction($params){
        ini_set('memory_limit', '256M');

        $this->destin_multi_relation_base = $this->di->get('cas')->get('destin_multi_relation_base_service');
        $this->redis = $this->di->get('cas')->get('redis_data_service');;

        $count = $this->destin_multi_relation_base->getDestTotal(array());

        $batch = 1000;
        $page = isset($params[0]) ? $params[0] : 1;
        while($page * $batch <= $count){
            $list = $this->destin_multi_relation_base->getDefaultList(array(), array('page_num' => $page, 'page_size' => $batch));

            foreach ($list as $l){
                $key = str_replace('{dest_id}', $l['dest_id'], RedisDataService::REDIS_MULTI_RELATION_KEY);
                $this->redis->dataSet($key, json_encode($l), null);
            }

            echo $page * $batch . "\n";
            $page ++;
        }

    }



}