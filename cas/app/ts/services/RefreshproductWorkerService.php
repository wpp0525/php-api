<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Components\ApiClient;

/**
 * 产品 Worker服务类
 *
 * @author win.sx
 * 刷新产品信息
 */
class RefreshproductWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var EsDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

	private $host;

	private $port;

	private $client;
	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';

	public function __construct($di) {
		$this->product_info = $di->get('cas')->get('product-info-data-service');
		$this->beanstalk = $di->get('cas')->getBeanstalk();
		$es = $di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}

	public function process($timestamp = null, $flag = null) {
		$this->writeLog(array(
			'dbname' => 'refresh product info',
			'table' => 'product',
			'topic_name' => '',
			'message' => 'refresh product start'
		));
		$this->product_info->refreshProductPool();
		$this->writeLog(array(
			'dbname' => 'refresh product info',
			'table' => 'product',
			'topic_name' => '',
			'message' => ' refresh product complete'
		));
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
		$this->writeLog(array(
			'dbname' => 'refresh product info',
			'table' => 'product',
			'topic_name' => '',
			'message' => 'refresh product shutdown'
		));
	}
	//记录日志抛出异常
	private function printException($data = array()){
		$data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
		$this->writeLog($data);
		throw new \Exception($data['message']);
	}
	private function writeLog($data = array()){
		$data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
		$data['createtime'] = date('Y-m-d H:i:s');
		$data['dbname']		= isset($data['dbname']) ? $data['dbname'] : 'null';
		$data['table']		= isset($data['table']) ? $data['table'] : 'null';
		$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->import_log.'/'.$this->log_type,json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),array(),'POST');
	}
}
