<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/4/8
 * Time: 15:21
 *
 */
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * Class DestDataTask
 * @purpose 目的地数据处理任务
 */
class DestdatarepairTask extends Task {

    /**
     *
     * @var \DestDataWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \DestdatarepairWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php destdatarepair destRepair start|stop|restart
     */
    public function destRepairAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->destrepair->toArray ();
        $default_daemon_options = array (
            'appName'               => 'dest-repair',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS dest repair Worker Service',
            'logLocation'           => __DIR__ . '/../logs/dest-repair/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/dest-repair/daemon.pid',
            'sysMaxExecutionTime'   => 0,
            'sysMaxInputTime'       => 0,
            'sysMemoryLimit'        => '1024M',
            'appRunAsUID' => 1000,
            'appRunAsGID' => 1000,
        );
        $daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
        $flag = isset($params[1]) ? $params[1] : null;
        switch ($action = strtolower ($params[0])) {
            case 'start' :
                $daemon->start ();
                break;
            case 'stop' :
                $daemon->stop ();
                break;
            case 'restart' :
                $daemon->restart ();
                break;
        }
        if (in_array ( $action, array ('stop', 'restart') )) {
            $this->svc->shutdown ( time (), $flag );
        }

        if (in_array ( $action, array ('start', 'restart') )) {
            while ( $daemon->isRunning () ) {
                $this->svc->process( time (), $flag );
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }
}