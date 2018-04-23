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
class DesttripTask extends Task {

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
        $this->svc = new \DesttripWorkerService($dependencyInjector);
    }

    /**
     * 目的地关联游记相关数据  php ts.php desttrip destTripRel  start
     * @param array $params
     */
    public function destTripRelAction($params=array()){
        $config = $this->getDI ()->get ( 'config' )->daemon->desttriprel->toArray ();
        $default_daemon_options = array (
            'appName'               => 'desttriprel',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Dest Detail-country Worker Service',
            'logLocation'           => __DIR__ . '/../logs/desttriprel/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/desttriprel/daemon.pid',
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
                $this->svc->process( time (), $flag);
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }
}