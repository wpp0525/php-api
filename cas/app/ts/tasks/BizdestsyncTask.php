<?php

/**
 * Created by PhpStorm.
 * User: jackdong
 * Date: 17/8/15
 * Time: 下午3:10
 */

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use Lvmama\Cas\Component\Kafka\Consumer;

class BizdestsyncTask extends Task
{

    /**
     * @var \Phalcon\DiInterface
     */
    private $di;

    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService;
     */
    private $distin_base;

    /**
     * @see \Phanlcon\DI\Injectable::setDI()
     * @param \Phalcon\DiInterface $dependencyInjector
     */
    public function setDI( Phalcon\DiInterface $dependencyInjector )
    {

        parent::getDI( $dependencyInjector );
        $this->di = $dependencyInjector;

        $this->distin_base = new \BizDestTempService( $dependencyInjector );

    }

    public function updateAllAction( array $params )
    {

        $config = $this->getDI()->get('config')->kafka->bizDestInfosSync->toArray();

        /*
        $config1 = $this->getDI()->get('config')->kafka->toArray()['bizDestInfosSync'];

        $kafka = new \Lvmama\Cas\Component\Kafka\Producer(
            $this->getDI()->get('config')->kafka->toArray()['bizDestInfosSync']
        );
        */

        $consumer = new Consumer( $config );
        $consumer->setClient( new CanalBizDestService($this->di) );
        $consumer->run();
        return;



    }

    public function addNewAction( array $params )
    {
        $kafka = new \Lvmama\Cas\Component\Kafka\Producer(
            $this->di->get('config')->kafka->toArray()['bizDestInfosSyncProducer']
        );

        $this->distin_base->process($kafka);
    }




}