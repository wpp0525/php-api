<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 根据spm重新产品
 *
 * @author win.sx
 *        
 */
class RefreshbyspmTask extends Task {

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
		$this->svc = new \RefreshBySpmWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php refreshbyspm refresh start
	 */
	public function refreshAction(array $params) {
		$config = $this->getDI()->get( 'config' )->daemon->refreshbyspm->toArray();
		$default_daemon_options = array (
				'appName'               => 'refreshbyspm',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'refreshbyspm Worker Service',
				'logLocation'           => __DIR__ . '/../logs/refreshbyspm/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/refreshbyspm/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'			=> 1000,
				'appRunAsGID'			=> 1000,
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
			$this->svc->process( time (), $flag );
			$this->svc->shutdown ( time (), $flag );
			$daemon->stop ();
		}
	}
}