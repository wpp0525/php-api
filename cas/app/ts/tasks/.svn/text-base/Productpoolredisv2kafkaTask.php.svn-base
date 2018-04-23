<?php

use Lvmama\Cas\Component\Kafka\Consumer;
use Lvmama\Common\Components\Daemon;
use Phalcon\CLI\Task;

/**
 * canal消费kafka消息 进程（没有使用system-daemon，而是配合supervisor）
 *
 * @author libiying
 *
 */
class Productpoolredisv2kafkaTask extends Task
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
     * 处理队列中的产品和商品信息,进行重构
     * @param array $params
     * @throws Exception
     */
    public function updateAllAction(array $params)
    {

        $config   = $this->getDI()->get('config')->kafka->productpoolv2->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new ProductPoolV2Service($this->di));
        $consumer->run();
        return;
    }

    /**
     * 消费定时任务中的产品
     * @author lixiumeng
     * @datetime 2017-08-31T18:52:08+0800
     * @return   [type]                   [description]
     */
    public function updateProductCronAction(array $params)
    {
        $config = $this->getDI()->get('config')->kafka->productpoolv2cron->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new ProductPoolV2Service($this->di));
        $consumer->run();
        return;

    }

    /**
     * 消费定时任务中的商品
     * @author lixiumeng
     * @datetime 2017-08-31T18:52:08+0800
     * @return   [type]                   [description]
     */
    public function updateGoodsCronAction(array $params)
    {
        $config = $this->getDI()->get('config')->kafka->productpoolv2crongoods->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new ProductPoolV2Service($this->di));
        $consumer->run();
        return;

    }

    /**
     * 更新产品附加表(表中存储产品的附加信息)
     * @author lixiumeng
     * @datetime 2017-09-08T17:25:22+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function updateAdditionAction(array $params)
    {
        $config = $this->getDI()->get('config')->kafka->productpoolv2addition->toArray();

        $consumer = new Consumer($config);
        $consumer->setClient(new ProductPoolV2AdditionService($this->di));
        $consumer->run();
        return;
    }

}
