<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记库中微攻略数据迁移到微攻略库
 *
 * @author jianghu
 *        
 */
class TraveltoguideTask extends Task {

	/**
	 *
	 * @var \TravelToGuideWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \TravelToGuideWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php traveltoguide comment start|stop|restart
	 */
	public function commentAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->traveltoguide->toArray ();
		$default_daemon_options = array (
				'appName'               => 'traveltoguide',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Travel To Guide Worker Service',
				'logLocation'           => __DIR__ . '/../logs/traveltoguide/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/traveltoguide/daemon.pid',
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
	 * @example php ts.php traveltoguide updateuserid start|stop|restart
	 */
	public function updateUserIdAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->traveltoguide->toArray ();
		$default_daemon_options = array (
			'appName'               => 'traveltoguide',
			'appDir'                => __DIR__ . '/../',
			'appDescription'        => 'CAS Travel To Guide Worker Service',
			'logLocation'           => __DIR__ . '/../logs/traveltoguide/updateuserid.log',
			'authorName'            => 'System Daemon',
			'authorEmail'           => 'root@127.0.0.1',
			'appPidLocation'        => __DIR__. '/../run/traveltoguide/updateuserid.pid',
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
				$this->svc->processUpdateUserId( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}