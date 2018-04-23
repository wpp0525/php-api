<?php

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * 游记主表及扩展表数据迁移任务
 *
 * @author jianghu
 *
 */
class TravelTask extends Task {

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
        $this->svc = new \TravelWorkerService($dependencyInjector);
    }

    /**
     * 游记主表及扩展表数据迁移任务
     * 
     * @example php ts.php travel comment start|stop|restart
     */
    public function commentAction(array $params) {
        $config = $this->getDI ()->get ( 'config' )->daemon->travel->migration->toArray ();
        $default_daemon_options = array (
            'appName'               => 'travel',
            'appDir'                => __DIR__ . '/../',
            'appDescription'        => 'CAS Travel Worker Service',
            'logLocation'           => __DIR__ . '/../logs/travel/daemon.log',
            'authorName'            => 'System Daemon',
            'authorEmail'           => 'root@127.0.0.1',
            'appPidLocation'        => __DIR__. '/../run/travel/daemon.pid',
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
	 * 从游记内容中提取出目的地列表
	 * 
	 * @example php ts.php travel content2dest start|stop|restart
	 * 
	 * @author mac.zhao
	 */
	public function content2destAction(array $params) {
		$config = $this->getDI ()->get ( 'config' )->daemon->travel->content2dest->toArray ();
		$default_daemon_options = array (
				'appName'               => 'travel-c2d',
				'appDir'                => __DIR__ . '/../',
				'appDescription'        => 'CAS Travel Content2dest Worker Service',
				'logLocation'           => __DIR__ . '/../logs/travel-c2d/daemon.log',
				'authorName'            => 'System Daemon',
				'authorEmail'           => 'root@127.0.0.1',
				'appPidLocation'        => __DIR__. '/../run/travel-c2d/daemon.pid',
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
				$this->svc->processContent2dest( time (), $flag );
			}
			$this->svc->shutdown ( time (), $flag );
		}
	}
}