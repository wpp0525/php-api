<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Components\ApiClient;

/**
 * 产品 Worker服务类
 *
 * @author win.sx
 * 根据消息队列取产品
 */
class ProQueryWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface {
	
	/**
	 * @var \Lvmama\Cas\Service\ProductPoolVstProductDataService
	 */
	private $vst_product;
	/**
	 * @var \Lvmama\Cas\Service\ProductPoolVstDestDataService
	 */
	private $vst_dest;
	/**
	 * @var \Lvmama\Cas\Service\ProductPoolVstDistrictDataService
	 */
	private $vst_district;
	/**
	 * @var \Lvmama\Cas\Service\ProductPoolVstGoodsDataService
	 */
	private $vst_goods;
	/**
	 * @var \Lvmama\Cas\Service\DestinProductRelDataService
	 */
	private $dest_product_rel;
	/**
	 * @var \Lvmama\Cas\Service\SourceProductRelDataService
	 */
	private $source_product_dest_service;

	private $host;

	private $port;

	private $client;
	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';
	public function __construct($di) {
		$this->vst_product = $di->get('cas')->get('product_pool_vst_product');
		$this->vst_dest = $di->get('cas')->get('product_pool_vst_dest');
		$this->vst_district = $di->get('cas')->get('product_pool_vst_district');
		$this->vst_goods = $di->get('cas')->get('product_pool_vst_goods');
		$this->dest_product_rel = $di->get('cas')->get('dest_product_rel_service');
		$this->source_product_dest_service = $di->get('cas')->get('source_product_dest_service');
		$es = $di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}

	public function handle($data)
	{
		if(isset($data->err) && isset($data->payload)){
			$tmp = $data->payload;
			$start = strpos($tmp,'{');
			$end = strrpos($tmp,'}');
			$json_str = substr($tmp,$start,$end-$start+1);
			$this->writeLog(array(
				'dbname' => 'vst back Kafka ProQuery',
				'table' => 'product',
				'topic_name' => $data->topic_name,
				'message' => $json_str
			));
			$res = json_decode($json_str,true);
			if(isset($res['prodProduct']['productId']) && $res['prodProduct']['productId']){
				$this->vst_product->save($res['prodProduct']);
				$dest_ids = array();
				foreach($res['prodDest'] as $v){
					$dest_ids[] = $v['destId'];
				}
				$this->vst_dest->batchSave($res['prodProduct']['productId'],$dest_ids);
				$this->dest_product_rel->batchSave(
					$res['prodProduct']['productId'],
					$dest_ids,
					isset($res['prodProduct']['categoryId']) ? $res['prodProduct']['categoryId'] : 0
				);
				$this->source_product_dest_service->batchSave(
					$res['prodProduct']['productId'],
					$dest_ids,
					isset($res['prodProduct']['categoryId']) ? $res['prodProduct']['categoryId'] : 0
				);
				$this->vst_goods->batchSave($res['suppGoods']);
				$this->vst_district->batchSave($res['prodProduct']['productId'],$res['prodDistrict']);
			}
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
