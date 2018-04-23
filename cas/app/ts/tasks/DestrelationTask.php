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
class DestrelationTask extends Task {

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
        $this->svc = new \DestRelationWorkerService($dependencyInjector);
    }

    /**
     * @param array $params
     * php ts.php destrelation destRelationData dest_type start
     */
    public function destRelationDataAction($params=array()){
        $dest_type=$params[0];
        $config = $this->getDI ()->get ( 'config' )->daemon->destrel->$dest_type->toArray ();
        $default_daemon_options = array (
            'appName'               => 'destrel-'.$dest_type,
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Dest Detail-country Worker Service',
            'logLocation'           => __DIR__ . '/../logs/destrel-'.$dest_type.'/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/destrel-'.$dest_type.'/daemon.pid',
            'sysMaxExecutionTime'   => 0,
            'sysMaxInputTime'       => 0,
            'sysMemoryLimit'        => '1024M',
            'appRunAsUID' => 1000,
            'appRunAsGID' => 1000,
        );
        $daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
        $flag = isset($params[2]) ? $params[2] : null;
        switch ($action = strtolower ($params[1])) {
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
                $this->svc->process( time (), $flag, $dest_type);
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }
}