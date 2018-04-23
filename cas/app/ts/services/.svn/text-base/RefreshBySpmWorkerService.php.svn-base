<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\UCommon as UCommon;
use Lvmama\Common\Utils\Filelogger as Filelogger;
/**
 * 消息 Worker服务类
 *
 * @author win.sx
 *
 */
class RefreshBySpmWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var Lvmama\Cas\Service\ProductInfoDataService
	 */
	private $product_info;
	
	/**
	 * @var Lvmama\Cas\Service\SeoDestKeywordDataService
	 */
	private $dest_keyword_srv;

	/**
	 * @var Lvmama\Cas\Service\SeoTemplateBaseDataService
	 */
	private $template_srv;
	
	private $soap;

	private $pageSize = 600;

	public function __construct($di) {
		$this->product_info = $di->get('cas')->get('product-info-data-service');
		$this->dest_keyword_srv = $di->get('cas')->get('seo_dest_keyword_service');
		$this->template_srv = $di->get('cas')->get('seo_template_service');
		$this->product_info->setReconnect(true);
		$this->template_srv->setReconnect(true);
		$this->dest_keyword_srv->setReconnect(true);
		$this->logs = Filelogger::getInstance();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		//获取所有有效的长尾词
		$result = $this->dest_keyword_srv->query('SELECT keyword_id,template_id FROM seo_dest_keyword WHERE long_tail = 1 AND status = 1','All');
		foreach($result as $row){
			//获取频道路由信息
			$tpl_info = $this->template_srv->getOneTemplate(array('template_id =' => $row['template_id']),'template_id,channel_id,route_id');
			$data = array(
				'key_id' => $row['keyword_id'],
				'channel_id' => $tpl_info['channel_id'],
				'route_id' => $tpl_info['route_id'],
				'position' => 0,
				'place_order' => 0
			);
			$spm = UCommon::buildRule($data);
			$this->logs->addLog('Refresh By Spm.spm:['.$spm.']','INFO');
			$this->product_info->refreshBySpm($spm);
		}
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
}
