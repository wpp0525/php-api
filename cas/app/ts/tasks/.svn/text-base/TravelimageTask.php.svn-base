<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记图片表及图片关联表数据迁移任务
 *
 * @author jianghu
 *
 */
class TravelimageTask extends Task {

    /**
     *
     * @var \TravelImageWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \TravelImageWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php travelimage comment start|stop|restart
     */
    public function commentAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->travelimage->toArray ();
        $default_daemon_options = array (
            'appName'               => 'travelimage',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Travel Image Worker Service',
            'logLocation'           => __DIR__ . '/../logs/travelimage/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/travelimage/daemon.pid',
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