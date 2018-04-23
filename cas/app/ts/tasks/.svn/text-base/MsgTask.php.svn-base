<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 消息 任务
 *
 * @author mac.zhao
 *        
 */
class MsgTask extends Task {

	/**
	 *
	 * @var \MsgWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \MsgWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php msg sender start|stop|restart
	 */
	public function senderAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->msg->toArray ();
		$default_daemon_options = array (
				'appName'               => 'msg',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Msg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/msg/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/msg/daemon.pid',
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