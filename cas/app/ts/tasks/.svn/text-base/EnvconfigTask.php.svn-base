<?php
/**
 * User: dirc.wang
 */

use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;
use Lvmama\Cas\Service\RedisDataService;

class EnvconfigTask extends Task {


  private $svc;

  /**
   *
   * @see \Phalcon\DI\Injectable::setDI()
   */
  public function setDI(Phalcon\DiInterface $dependencyInjector) {
    parent::setDI ( $dependencyInjector );
    $this->svc = new \EnvconfigWorkerService($dependencyInjector);
  }

  /**
   * @example php ts.php Envconfig renewinfo start|stop|restart
   */
  public function renewinfoAction(array $params) {
    $config = array();
    $diconfig = $this->getDI ()->get ( 'config' );
    if(isset($diconfig->daemon) && isset($diconfig->daemon->envserverinfo)){
      $config = $diconfig->daemon->envserverinfo->toArray ();
    }
    $default_daemon_options = array (
        'appName'               => 'server_info',
        'appDir'                => __DIR__ . '/../',
        'appDescription'        => 'CAS History Envconfig Worker Service',
        'logLocation'           => __DIR__ . '/../logs/server_info/daemon.log',
        'authorName'            => 'System Daemon',
        'authorEmail'           => 'root@127.0.0.1',
        'appPidLocation'        => __DIR__. '/../run/server_info/daemon.pid',
        'sysMaxExecutionTime'   => 0,
        'sysMaxInputTime'       => 0,
        'sysMemoryLimit'        => '1024M',
        'appRunAsUID' => 1000,
        'appRunAsGID' => 1000,
    );

    $daemon = new Daemon ( array_merge ( $default_daemon_options, $config) );
    $flag = isset($params[1]) ? $params[1] : null;
    switch ($action = strtolower ($params[0])) {
      // case 'start' :
      //   $daemon->start ();
      //   break;
      // case 'stop' :
      //   $daemon->stop ();
      //   break;
      // case 'restart' :
      //   $daemon->restart ();
      //   break;
    }

    // if (in_array ( $action, array ('stop', 'restart') )) {
    //   $this->svc->shutdown ( time (), $flag );
    // }

    if (in_array ( $action, array ('start', 'restart') )) {
        // while ( $daemon->isRunning () ) {
            $this->svc->process( time (), $flag );
        // }
        // $this->svc->shutdown ( time (), $flag );
    }
  }

/**
 * 复位任务游标
 * @return [type] [description]
 */
  public function noniousAction() {
    $redis = $this->getDI ()->get('cas')->getRedis();
    $renewid = RedisDataService::REDIS_ENV_LIST . 'task:' . 'renew';
    $redis->set($renewid, 0);
    echo "********************任务开始*************************************";
  }

}
