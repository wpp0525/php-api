<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记机器人 任务
 *
 * @author mac.zhao
 *        
 */
class RobotTask extends Task {

	/**
	 *
	 * @var \RobotWorkerService
	 */
	private $svc;
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		$this->svc = new \RobotWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php robot pv start|stop|restart
	 */
	public function pvAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->robot->pv->toArray ();
		$default_daemon_options = array (
				'appName'               => 'robot-pv',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot PV Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-pv/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-pv/daemon.pid',
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
				$this->svc->processPV( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
	
	/**
	 * @example php ts.php robot like start|stop|restart
	 */
	public function likeAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->robot->like->toArray ();
		$default_daemon_options = array (
				'appName'               => 'robot-like',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Like Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-like/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-like/daemon.pid',
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
				$this->svc->processLike( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
	
	/**
	 * @example php ts.php robot comment start|stop|restart
	 */
	public function commentAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->robot->comment->toArray ();
		$default_daemon_options = array (
				'appName'               => 'robot-comment',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Comment Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-comment/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-comment/daemon.pid',
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
	 * @example php ts.php robot msg start|stop|restart
	 */
	public function msgAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->robot->msg->toArray ();
		$default_daemon_options = array (
				'appName'               => 'robot-msg',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Robot Msg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/robot-msg/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/robot-msg/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID' => 1000,
				'appRunAsGID' => 1000,
		);
		$daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
// 		$daemon->setInterval(30);
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
				$this->svc->processMsg( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}