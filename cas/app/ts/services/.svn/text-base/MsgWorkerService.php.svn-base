<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

/**
 * 消息 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class MsgWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var MsgDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

	public function __construct($di) {
		$this->datasvc = $di->get('cas')->get('msg-data-service');
		$this->datasvc->setReconnect(true);
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {

		if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRIP_MSG)->ignore('default')->reserve()) {
			try {
				if ($job_data = json_decode($job->getData(), true)) {
				    $job_data['create_time'] = time();
				    $job_data['status'] = 0;
					$this->datasvc->insert($job_data);
				}
				unset($job_data);
			} catch (\Exception $ex) {
				echo $ex->getMessage() . ";" . $ex->getTraceAsString() . "\r\n";
			}
			$this->beanstalk->delete($job);
		}
		unset($job);
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
}