<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 同步阿里云
 *
 * @author win.sx
 *        
 */
class AliyunsyncTask extends Task {

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
		$this->svc = new \AliyunSyncWorkerService($dependencyInjector);
	}
	
	/**
	 * @example php ts.php aliyunSync sync start
	 */
	public function syncAction(array $params) {
		$config = $this->getDI()->get( 'config' )->daemon->aliyunsync->toArray();
		$default_daemon_options = array (
				'appName'               => 'aliyunsync',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'aliyunsyncg Worker Service',
				'logLocation'           => __DIR__ . '/../logs/aliyunsync/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/aliyunsync/daemon.pid',
				'sysMaxExecutionTime'   => 0,
				'sysMaxInputTime'       => 0,
				'sysMemoryLimit'        => '1024M',
				'appRunAsUID'			=> 1000,
				'appRunAsGID'			=> 1000,
		);
		$daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
		$flag = isset($params[1]) ? $params[1] : null;
		if(!$flag) die('please input flag param![ baike | seo ]'."\n");
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