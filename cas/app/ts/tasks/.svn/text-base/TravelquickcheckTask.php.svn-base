<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记快速审核
 *
 * @author jianghu
 *
 */
class TravelquickcheckTask extends Task {

    /**
     *
     * @var \TravelWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \TravelQuickCheckWorkerService($dependencyInjector);
    }

    /**
     * 游记快速审核（从未审核游记中查找符合快速审核条件的游记）
     * 
     * @example php ts.php travelquickcheck searchTravelFromDatabase start|stop|restart
     */
    public function searchTravelFromDatabaseAction(array $params) {
        $config = $this->getDI()->get ( 'config' )->daemon->travelquickcheck->toArray ();
        $default_daemon_options = array (
            'appName'               => 'travel',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Search Travel From Database Worker Service',
            'logLocation'           => __DIR__ . '/../logs/searchTravelFromDatabase/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/searchTravelFromDatabase/daemon.pid',
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
			$from = 'database';
            while ( $daemon->isRunning () ) {
                $this->svc->process( time (), $flag, $from );
            }
            $this->svc->shutdown ( time (), $flag );
        }
    }
	
	/**
	 * 游记快速审核（从 Beanstalk 中查找符合快速审核条件的游记）
	 * 
	 * @example php ts.php travelquickcheck searchTravelFromBeanstalk start|stop|restart
	 * 
	 */
	public function searchTravelFromBeanstalkAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->travelquickcheck->toArray ();
		$default_daemon_options = array (
				'appName'               => 'travel-c2d',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Search Travel From Beanstalk Worker Service',
				'logLocation'           => __DIR__ . '/../logs/searchTravelFromBeanstalk/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/searchTravelFromBeanstalk/daemon.pid',
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
			$from = 'beanstalk';
			while ( $daemon->isRunning () ) {
				$this->svc->process( time (), $flag, $from );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}