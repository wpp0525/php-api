<?php

use Lvmama\Cas\Component\Kafka\Consumer;
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Components\Daemon;
use Phalcon\CLI\Task;

/**
 * canal消费kafka消息 进程（没有使用system-daemon，而是配合supervisor）
 *
 * @author libiying
 *
 */
class CanalTask extends Task
{

    /**
     *
     * @var \Phalcon\DiInterface
     */
    private $di;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector)
    {
        parent::setDI($dependencyInjector);

        $this->di = $dependencyInjector;
    }

    /**
     * kafka es消费处理
     *
     * @param array $params
     * @throws Exception
     */
    public function esConsumeAction(array $params)
    {

        $config = $this->getDI()->get('config')->kafka->esConsume->toArray();
//        $config['groupId'] = 'myConsumerGroup1';
        //        $config['brokerList'] = '192.168.137.102:9092';
        //        $config['topics'] = ['canal-test'];

        $consumer = new Consumer($config);
        $consumer->setClient(new CanalEsWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * kafka redis消费处理
     *
     * @param array $params
     * @throws Exception
     */
    public function redisConsumeAction(array $params)
    {
        $config = $this->getDI()->get('config')->kafka->redisConsume->toArray();
//        $config['groupId'] = 'myConsumerGroup1';
        //        $config['brokerList'] = '192.168.137.102:9092';
        //        $config['topics'] = ['canal-test'];

        $consumer = new Consumer($config);
        $consumer->setClient(new CanalRedisWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * kafka vst消费处理
     * 表更新biz_dest、biz_district、biz_district_sign、biz_com_coordinate
     *
     * @param array $params
     * @throws Exception
     */
    public function vstConsumeAction(array $params)
    {
        $config   = $this->getDI()->get('config')->kafka->vstConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalVstWorkerService($this->di));
        $consumer->run();
        return;
    }
    public function userConsumeAction()
    {
        $config   = $this->getDI()->get('config')->kafka->userConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalUserWorkerService($this->di));
        $consumer->run();
        return;
    }
    /**
     * kafka product消费处理
     *
     * @param array $params
     * @throws Exception
     * example php ts.php canal inputproductConsume start
     */
    public function inputproductConsumeAction(array $params)
    {
        $config   = $this->getDI()->get('config')->kafka->inputproductConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new InputproductWorkerService($this->di));
        $consumer->run();
        return;
    }
    /**
     * kafka PROD_QUERY 消费处理
     *
     * @param array $params
     * @throws Exception
     * example php ts.php canal prodQueryConsume start
     */
    public function prodQueryConsumeAction(array $params)
    {
        $config   = $this->getDI()->get('config')->kafka->ppConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new ProQueryWorkerService($this->di));
        $consumer->run();
        return;
    }
    /**
     * kafka 大目的地页面保存 消费处理
     *
     * @param array $params
     * @throws Exception
     * example php ts.php canal templateSaveConsume start
     */
    public function templateSaveConsumeAction(array $params)
    {
        $config   = $this->getDI()->get('config')->kafka->templateConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new TemplateSaveWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * kafka 目的地与产品关系-产生消息
     *
     * @throws Exception
     * example php ts.php canal sendMsgProductDest start
     */
    public function sendMsgProductDestAction()
    {
        $rk = new Producer($this->getDI()->get('config')->kafka->productDestRelProducer->toArray());

        $pp_product_dest_rel = $this->di->get('cas')->get('pp_product_dest_rel');

        $pages     = $pp_product_dest_rel->getProductDestRelsTotal('11,12');
        $page_size = 1000;
        $total     = ceil($pages / $page_size);

        for ($i = 0; $i < $pages; $i++) {
            $data              = array();
            $product_dest_list = $pp_product_dest_rel->getProductDestRels($i * $page_size, $page_size, '11,12');
            if (!empty($product_dest_list)) {
                foreach ($product_dest_list as $key => $item) {
                    $data[$key]['product_id']  = $item['PRODUCT_ID'];
                    $data[$key]['dest_id']     = $item['DEST_IDS'];
                    $data[$key]['categroy_id'] = $item['CATEGORY_ID'];
                }
                var_dump(count($data));
                $rk->sendMsg(json_encode($data));
            }
        }

        return;
    }

    /**
     * kafka 目的地与产品关系-消费处理
     *
     * @param array $params
     * @throws Exception
     * example php ts.php canal productDestRel start
     */
    public function productDestRelAction()
    {
        $config = $this->getDI()->get('config')->kafka->productDestRelConsumer->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new ProductDestRelWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * kafka 目的地多级关系-消费处理
     *
     * @param array $params
     * @throws Exception
     * example php ts.php canal bizDestMultiRelation start
     */
    public function bizDestMultiRelationAction()
    {
        $config = $this->getDI()->get('config')->kafka->productDestRelConsumer->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new BizDestMultiRelationWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * Kafka 游记数据列表消费处理
     * @throws Exception
     * example php ts.php canal travelListConsume start
     */
    public function travelListConsumeAction()
    {
        $config   = $this->getDI()->get('config')->kafka->travelListConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalTravelListWorkerService($this->di));
        $consumer->run();
        return;
    }

    /**
     * 多出发地价格实时同步
     * @author lixiumeng
     * @datetime 2017-09-18T17:25:16+0800
     * @param    array                    $params [description]
     * @return   [type]                           [description]
     */
    public function startDistrictAction($params)
    {
        $config = $this->getDI()->get('config')->kafka->startDistrictSync->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new CanalProductPriceSyncService($this->di));
        $consumer->run();
        return;
    }

    /**
     * 产品价格实时同步
     */
    public function productPriceSyncAction($params)
    {
        $config   = $this->getDI()->get('config')->kafka->productpoolv2InfosSync->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalProductPriceSyncService($this->di));
        $consumer->run();
        return;
    }

    /*
     * 商品价格实时同步
     */
    public function goodsPriceSyncAction($params)
    {
        $config   = $this->getDI()->get('config')->kafka->goodspoolv2InfosSync->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalProductPriceSyncService($this->di));
        $consumer->run();
        return;
    }

    /**
     * 修复pp_place中的product_id
     * @author lixiumeng
     * @datetime 2017-09-06T18:04:41+0800
     * @return   [type]                   [description]
     */
    public function fixProductIdAction($params)
    {
        $config   = $this->getDI()->get('config')->kafka->fixproductid->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new CanalFixProductIdService($this->di));
        $consumer->run();
        return;
    }

    /**
     * 促销信息实时同步
     * @author lixiumeng
     * @datetime 2017-09-18T17:25:16+0800
     * @param    array                    $params [description]
     * @return   [type]                           [description]
     */
    public function promotionSyncAction($params)
    {
        $config = $this->getDI()->get('config')->kafka->promotionsync->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new CanalPromotionSyncService($this->di));
        $consumer->run();
        return;
    }

}
