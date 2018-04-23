<?php

use Lvmama\Cas\Component\Kafka\Consumer;
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Components\Daemon;
use Phalcon\CLI\Task;

/**
 * 长尾词批量导入后生成页面
 *
 * @author shenxiang
 *
 */
class LongtailTask extends Task
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
     * kafka long tail 消费处理
     *
     * @param array $params
     * @throws Exception
     * @example php ts.php Longtail longtailConsume start
     */
    public function longtailConsumeAction(array $params)
    {
        $config = $this->getDI()->get('config')->kafka->longtailConsume->toArray();
        $consumer = new Consumer($config);
        $consumer->setClient(new LongTailWorkerService($this->di));
        $consumer->run();
        return;
    }
}
