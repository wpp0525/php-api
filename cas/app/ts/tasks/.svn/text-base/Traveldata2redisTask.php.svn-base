<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 生成目的地游记redis任务
 *
 * @author zhta
 *
 */
class Traveldata2redisTask extends Task
{

    /**
     *
     * @var \TravelDestWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $this->svc = new \TravelData2RedisWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php traveldata2redis trip start|stop|restart
     */
    public function tripAction(array $params)
    {
        $config = $this->getDI()->get('config')->daemon->traveldata2redis->toArray();
        $default_daemon_options = array(
            'appName' => 'traveldata2redis',
            'appDir' => __DIR__ . '/../',
            'appDescription' => 'CAS Travel Data 2 Redis Worker Service',
            'logLocation' => __DIR__ . '/../logs/traveldata2redis/daemon.log',
            'authorName' => 'System Daemon',
            'authorEmail' => 'root@127.0.0.1',
            'appPidLocation' => __DIR__ . '/../run/traveldata2redis/daemon.pid',
            'sysMaxExecutionTime' => 0,
            'sysMaxInputTime' => 0,
            'sysMemoryLimit' => '1024M',
            'appRunAsUID' => 1000,
            'appRunAsGID' => 1000,
        );
        $daemon = new Daemon (array_merge($default_daemon_options, $config));
        $flag = isset($params[1]) ? $params[1] : null;
        switch ($action = strtolower($params[0])) {
            case 'start' :
                $daemon->start();
                break;
            case 'stop' :
                $daemon->stop();
                break;
            case 'restart' :
                $daemon->restart();
                break;
        }
        if (in_array($action, array('stop', 'restart'))) {
            $this->svc->shutdown(time(), $flag);
        }
        if (in_array($action, array('start', 'restart'))) {
            while ($daemon->isRunning()) {
                $this->svc->process(time(), $flag);
            }
            $this->svc->shutdown(time(), $flag);
        }
    }
}