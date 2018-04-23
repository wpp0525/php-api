<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 敏感词过滤 任务
 *
 * @author mac.zhao
 *        
 */
class SensitivewordTask extends Task {

	/**
	 *
	 * @var \SensitivewordWorkerService
	 */
	private $sensitivewordSVC;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->sensitivewordSVC = new \SensitivewordWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php sensitiveword qusetion start|stop|restart
	 */
	public function qusetionAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->sensitiveword->qusetion->toArray ();
		$default_daemon_options = array (
				'appName'               => 'sw-qusetion',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Sensitiveword Qusetion Worker Service',
				'logLocation'           => __DIR__ . '/../logs/sensitiveword-qusetion/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/sensitiveword-qusetion/daemon.pid',
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
			$this->sensitivewordSVC->shutdown ( time (), $flag );
		}
			
		if (in_array ( $action, array ('start', 'restart') )) {
			while ( $daemon->isRunning () ) {
				$this->sensitivewordSVC->processQuestion( time (), $flag );
			}
			$this->sensitivewordSVC->shutdown ( time (), $flag );
		}
	}
	
	/**
	 * @example php ts.php sensitiveword travel start|stop|restart
	 */
	public function travelAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->sensitiveword->travel->toArray ();
		$default_daemon_options = array (
				'appName'               => 'sw-travel',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Sensitiveword Travel Worker Service',
				'logLocation'           => __DIR__ . '/../logs/sensitiveword-travel/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/sensitiveword-travel/daemon.pid',
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
			$this->sensitivewordSVC->shutdown ( time (), $flag );
		}
			
		if (in_array ( $action, array ('start', 'restart') )) {
			while ( $daemon->isRunning () ) {
				$this->sensitivewordSVC->processTravel( time (), $flag );
			}
			$this->sensitivewordSVC->shutdown ( time (), $flag );
		}
	}
}