<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-12-9
 * Time: 下午4:42
 */
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

class TravelformatTask extends Task {

    /**
     *
     * @var \CquestionWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \TravelformatWorkerService($dependencyInjector);
    }


    /**
     * @example /usr/local/php/bin/php ts.php travelformat formatContent start|stop|restart
     */
    public function formatContentAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->travelformat->formatcontent->toArray ();
        $default_daemon_options = array (
            'appName'               => 'format-content',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Format Content Worker Service',
            'logLocation'           => __DIR__ . '/../logs/format-content/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/format-content/daemon.pid',
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
            $this->svc->processFormatContent( time (), $flag );
            $this->svc->shutdown ( time (), $flag );
            $daemon->stop ();
        }
    }

}