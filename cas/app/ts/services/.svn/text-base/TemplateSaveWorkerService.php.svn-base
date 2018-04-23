<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Components\ApiClient;

/**
 * 大目的地&长尾词IP定位支持
 *
 * @author win.sx
 * 根据消息队列取从所有出发地出发的产品
 */
class TemplateSaveWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface {
	
	/**
	 * @var \Lvmama\Cas\Service\SeoDestVariableDataService
	 */
	private $seo_dest_variable_srv;

	private $host;

	private $port;

	private $client;
	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';
	public function __construct($di) {
		$this->seo_dest_variable_srv = $di->get('cas')->get('seo_dest_variable_service');
		$es = $di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}

	public function handle($data)
	{
		if(isset($data->err) && isset($data->payload)){
			
			$this->writeLog(array(
				'message' => $data->payload,
				'dbname' => 'TemplateSaveWorkerService',
				'table' => 'get all district product',
				'topic' => $data->topic_name
			));
			$tmp = json_decode($data->payload,true);
			$template_id = $tmp['template_id'];
			$manualId = $tmp['manualId'];
			$dest_id = $tmp['dest_id'];
			$keyword_pinyin = $tmp['keyword_pinyin'];
			$this->seo_dest_variable_srv->destSave($template_id,$manualId,$dest_id,$keyword_pinyin);
		}
	}
	public function error()
	{
		// TODO: Implement error() method.
	}

	public function timeOut()
	{
		// TODO: Implement timeOut() method.
		echo 'time out!';
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
	private function writeLog($data = array()){
		$data['message']	= isset($data['message']) ? $data['message'] : 'not input parama!';
		$data['createtime'] = date('Y-m-d H:i:s');
		$data['dbname']		= isset($data['dbname']) ? $data['dbname'] : 'null';
		$data['table']		= isset($data['table']) ? $data['table'] : 'null';
		$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->import_log.'/'.$this->log_type,json_encode($data,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),array(),'POST');
	}
}
