<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/4/8
 * Time: 15:21
 *
 */
use Phalcon\CLI\Task;
use Lvmama\Common\Components\Daemon;

/**
 * Class DestDataTask
 * @purpose 目的地数据处理任务
 */
class DestdetailTask extends Task {

    /**
     *
     * @var \DestDataWorkerService
     */
    private $svc;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector) {
        parent::setDI ( $dependencyInjector );
        $this->svc = new \DestDetailWorkerService($dependencyInjector);
    }

    /**
     * 目的地详细数据处理    php ts.php destDetail destDetailData dest_type start
     * @param array $params
     */
    public function destDetailDataAction($params=array()){
          $dest_type=$params[0];
          if(!$dest_type || $dest_type=='start' || $dest_type=='stop') {
              die('别忘了dest_type参数');
          }
          $config = $this->getDI ()->get ( 'config' )->daemon->destdetail->$dest_type->toArray ();
          $default_daemon_options = array (
              'appName'               => 'detail-'.$dest_type,
              'appDir'                => __DIR__ . '/../',
              'appDescription'        => 'CAS Dest Detail-country Worker Service',
              'logLocation'           => __DIR__ . '/../logs/detail-'.$dest_type.'/daemon.log',
              'authorName'            => 'System Daemon',
              'authorEmail'           => 'root@127.0.0.1',
              'appPidLocation'        => __DIR__. '/../run/detail-'.$dest_type.'/daemon.pid',
              'sysMaxExecutionTime'   => 0,
              'sysMaxInputTime'       => 0,
              'sysMemoryLimit'        => '1024M',
              'appRunAsUID' => 1000,
              'appRunAsGID' => 1000,
          );
          $daemon = new Daemon ( array_merge ( $default_daemon_options, $config ) );
          $flag = isset($params[2]) ? $params[2] : null;
          switch ($action = strtolower ($params[1])) {
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
                  $this->svc->process( time (), $flag, $dest_type);
              }
              $this->svc->shutdown ( time (), $flag );
          }
      }
}
