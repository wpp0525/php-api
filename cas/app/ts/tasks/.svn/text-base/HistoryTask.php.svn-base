<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 历史数据处理 任务
 *
 * @author mac.zhao
 *        
 */
class HistoryTask extends Task {

	/**
	 *
	 * @var \HistoryWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \HistoryWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php history msg start|stop|restart
	 */
	public function msgAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->history->msg->toArray ();
		$default_daemon_options = array (
				'appName'               => 'history-msg',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS History Msg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/history-msg/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/history-msg/daemon.pid',
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
			if ( $daemon->isRunning () ) {
				$this->svc->processMsg( time (), $flag );
				$daemon->stop ();
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}