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
class TriprelationTask extends Task {

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
        $this->svc = new \TripRelationWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php triprelation destTripRelation start|stop|restart
     */
    public function destTripRelationAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->trip->triprelation->toArray ();
        $default_daemon_options = array (
            'appName'               => 'trip-dest',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Dest Trip Relation Worker Service',
            'logLocation'           => __DIR__ . '/../logs/trip-dest/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/trip-dest/daemon.pid',
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
    /**
     * @example php ts.php triprelation destTraceRelation start|stop|restart
     */
    public function destTraceRelationAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->trip->tracerelation->toArray ();
        $default_daemon_options = array (
            'appName'               => 'trace-dest',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Dest trace Relation Worker Service',
            'logLocation'           => __DIR__ . '/../logs/trace-dest/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/trace-dest/daemon.pid',
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

                $this->svc->processTrace( time (), $flag );
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }
}