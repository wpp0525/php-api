<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 更新游记内容表中的章节内容中图片路径
 *
 * @author jianghu
 *
 */
class UpdatetravelcontentimagesrcTask extends Task {

    /**
     *
     * @var \TravelContentWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \UpdateTravelContentImageSrcWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php updatetravelcontentimagesrc comment start|stop|restart
     */
    public function commentAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->updatetravelcontentimagesrc->toArray ();
        $default_daemon_options = array (
            'appName'               => 'updateimagesrc',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Update Travel Content Image Src Worker Service',
            'logLocation'           => __DIR__ . '/../logs/updateimagesrc/updateimagesrc.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/updateimagesrc/updateimagesrc.pid',
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