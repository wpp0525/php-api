<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 同步数据 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class SyncWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var TripStatisticsDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;
	
	private $config;

	public function __construct($di) {
		$this->datasvc = $di->get('cas')->get('trip-statistics-data-service');
		$this->datasvc->setReconnect(true);
		
		$this->travelsvc = $di->get('cas')->get('trip-data-service');
		$this->travelsvc->setReconnect(true);
		
		$this->travelBonusService = $di->get('cas')->get('travel-bonus-data-service');
		$this->travelBonusService->setReconnect(true);
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
	    
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::processTravelsRealPvFromCoremetrics()
	 */
	public function processTravelsRealPvFromCoremetrics($timestamp = null, $flag = null) {
	    
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
}