<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记图片表及图片关联表数据迁移任务
 *
 * @author jianghu
 *
 */
class Travelsyncimage2picTask extends Task {

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
        $this->svc = new \TravelSyncImage2PicWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php travelsyncimage2pic comment start|stop|restart
     */
    public function commentAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->travelsyncimage2pic->toArray ();
        $default_daemon_options = array (
            'appName'               => 'travelimage2pic',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Travel Sync Image To Pic Worker Service',
            'logLocation'           => __DIR__ . '/../logs/travelsyncimage2pic/travelsyncimage2pic.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/travelimage2pic/travelimage2pic.pid',
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
