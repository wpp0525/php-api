<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 专题模板1.0 产品数据迁移至 模板3.0
 *
 * @author Qyl
 *
 */
class Subject1dataTask extends Task {

    /**
     *
     * @var \Subject1DataWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \Subject1DataWorkerService($dependencyInjector);
    }

    /**
     * @example php ts.php subject1data comment start|stop|restart
     */
    public function commentAction(array $params) {
//        $config = $this->getDI ()->get ( 'config' )->daemon->subject1data->toArray ();
        $default_daemon_options = array (
            'appName'               => 'subject1data',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS subject zt1 Content Worker Service',
            'logLocation'           => __DIR__ . '/../logs/subject1data/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/subject1data/daemon.pid',
            'sysMaxExecutionTime'   => 0,
            'sysMaxInputTime'       => 0,
            'sysMemoryLimit'        => '1024M',
//            'appRunAsUID' => 1000,
//            'appRunAsGID' => 1000,
            'appRunAsUID'           => function_exists('posix_geteuid') ? posix_geteuid() : 1000,
            'appRunAsGID'			    => function_exists('posix_getegid') ? posix_getegid() : 1000,
        );
//        $daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
        $daemon = new Daemon ($default_daemon_options);
        $block = array();
        if(isset($params[1])) $block[] =  $params[1];
        if(isset($params[2])) $block[] =  $params[2];
        $flag = isset($block) ? $block : null;
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