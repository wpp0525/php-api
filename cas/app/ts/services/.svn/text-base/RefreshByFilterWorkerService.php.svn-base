<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\Filelogger as Filelogger;
/**
 * 消息 Worker服务类
 *
 * @author win.sx
 *
 */
class RefreshByFilterWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var Lvmama\Cas\Service\ProductPoolDataService
	 */
	private $pp_svc;
	
	/**
	 * @var Lvmama\Cas\Service\SeoDestKeywordDataService
	 */
	private $dest_keyword_srv;
	
	private $soap;

	private $pageSize = 600;

	public function __construct($di) {
		$this->pp_svc = $di->get('cas')->get('product_pool_data');
		$this->dest_keyword_srv = $di->get('cas')->get('seo_dest_keyword_service');
		$this->pp_svc->setReconnect(true);
		$this->dest_keyword_srv->setReconnect(true);
		$this->logs = Filelogger::getInstance();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		//获取所有有效的长尾词
		$result = $this->dest_keyword_srv->query('SELECT keyword_id FROM seo_dest_keyword WHERE long_tail = 1 AND status = 1','All');
		foreach($result as $row){
			$this->pp_svc->refreshByFilter($row['keyword_id']);
			$this->logs->addLog('Refresh By Filter.keyword_id:['.$row['keyword_id'].']','INFO');
		}
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
}
