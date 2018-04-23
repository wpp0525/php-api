<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记数据统计 任务
 *
 * @author mac.zhao
 *        
 */
class TripstatisticsTask extends Task {

	/**
	 *
	 * @var \TripstatisticsWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \TripstatisticsWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php tripstatistics statistics start|stop|restart
	 */
	public function statisticsAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->tripstatistics->toArray ();
		$default_daemon_options = array (
				'appName'               => 'tripstatistics',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Trip Statistics Worker Service',
				'logLocation'           => __DIR__ . '/../logs/tripstatistics/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/tripstatistics/daemon.pid',
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