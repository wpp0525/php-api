<?php
use Phalcon\CLI\Task;
use Lvmama\Common\Components\ApiClient;

/**
 * 维护产品数据
 *
 * @author win.sx
 * 
 */
class RefreshproductTask extends Task {

	/**
	 *
	 * @var \MsgWorkerService
	 */
	private $svc;

	private $host;

	private $port;

	private $client;
	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';
	
	/**
	 *
	 * @see \Phalcon\DI\Injectable::setDI()
	 */
	public function setDI(Phalcon\DiInterface $dependencyInjector) {
		parent::setDI ( $dependencyInjector );
		//$this->svc = new \RefreshproductWorkerService($dependencyInjector);
		$this->di = $dependencyInjector;
		$es = $this->di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}
	
	/**
	 * @example php ts.php refreshproduct refresh start
	 */
	public function refreshAction(array $params) {
		$this->writeLog(array(
			'dbname' => 'refresh product info',
			'table' => 'product',
			'topic_name' => '',
			'message' => 'refresh product start'
		));
		$this->di->get('cas')->get('product-info-data-service')->refreshProductPool();
		$this->writeLog(array(
			'dbname' => 'refresh product info',
			'table' => 'product',
			'topic_name' => '',
			'message' => ' refresh product complete'
		));
	}
	private function writeLog($data = array()){
		$data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
		$data['createtime'] = date('Y-m-d H:i:s');
		$data['dbname']		= isset($data['dbname']) ? $data['dbname'] : 'null';
		$data['table']		= isset($data['table']) ? $data['table'] : 'null';
		$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->import_log.'/'.$this->log_type,json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),array(),'POST');
	}
}
