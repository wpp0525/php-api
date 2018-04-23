<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-9-28
 * Time: 下午4:46
 */
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

class CquestionTask extends Task {

    /**
     *
     * @var \CquestionWorkerService
     */
    private $svc;

    /**
     * @var RedisAdapter
     */
    private $redis;

    /**
     * @var BeanstalkAdapter
     */
    private $beanstalk;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \CquestionWorkerService($dependencyInjector);
    }

    /**
     * @example /usr/local/php/bin/php ts.php cquestion cQuestionList start|stop|restart
     */
    public function cQuestionListAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->cquestion->cquestionlist->toArray ();
        $default_daemon_options = array (
            'appName'               => 'cq-list',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Cqlist Tag Worker Service',
            'logLocation'           => __DIR__ . '/../logs/cq-list/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/cq-list/daemon.pid',
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
                $this->svc->processCquestionList( time (), $flag );
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }

    /**
     * @example /usr/local/php/bin/php ts.php cquestion ckCQzeroList start|stop|restart
     */
    public function ckCQzeroListAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->cquestion->ckcqzerolist->toArray ();
        $default_daemon_options = array (
            'appName'               => 'ck-zero-list',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Check List Zero Worker Service',
            'logLocation'           => __DIR__ . '/../logs/ck-zero-list/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/ck-zero-list/daemon.pid',
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
                $this->svc->processCkCQzeroList( time (), $flag );
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }

    /**
     * @example /usr/local/php/bin/php ts.php cquestion newHotList start|stop|restart
     */
    public function newHotListAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->cquestion->newhotlist->toArray ();
        $default_daemon_options = array (
            'appName'               => 'new-hot-list',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS New Hot List Worker Service',
            'logLocation'           => __DIR__ . '/../logs/new-hot-list/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/new-hot-list/daemon.pid',
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
//            while ( $daemon->isRunning () ) {
//                $this->svc->processNewCqHotList( time (), $flag );
//            }
//            $this->svc->shutdown ( time (), $flag );
            $this->svc->processNewCqHotList( time (), $flag );
            $this->svc->shutdown ( time (), $flag );
            $daemon->stop ();
        }
    }




}