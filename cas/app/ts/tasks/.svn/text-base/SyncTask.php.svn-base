<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 同步数据 任务
 *
 * @author mac.zhao
 *        
 */
class SyncTask extends Task {

	/**
	 *
	 * @var \SyncWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \SyncWorkerService($dependencyInjector);
	}
	
	/**
	 * 从CM同步游记真实访问量到本地库
	 * 
	 * @example php ts.php sync travelsRealPvFromCoremetrics start|stop|restart
	 */
	public function travelsRealPvFromCoremetricsAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->sync->travelsRealPvFromCoremetrics->toArray ();
		$default_daemon_options = array (
				'appName'               => 'sync-travelsRealPvFromCoremetrics',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Sync TravelsRealPvFromCoremetrics Worker Service',
				'logLocation'           => __DIR__ . '/../logs/sync-travelsRealPvFromCoremetrics/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/sync-travelsRealPvFromCoremetrics/daemon.pid',
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
				$this->svc->processTravelsRealPvFromCoremetrics( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}