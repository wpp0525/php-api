<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\UCommon as UCommon;

/**
 * 度假内链 Worker服务类
 *
 * @author win.sx
 *        
 */
class SeoWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var EsDataService
	 */
	private $datasvc;

	private $host;

	private $port;

	private $client;

	private $pageSize = 1000;

	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';

	private $cache1 = 'http://php-api.lvmama.com/seo/getUrlRelateLinks';

	private $cache2 = 'http://php-api.lvmama.com/seo/getUrlRelateKeywordLinks';

	public function __construct($di) {
		//关键词数据库
		$this->manual_url = $di->get('cas')->get('seo_manual_url_service');
		$this->manual_url->setReconnect(true);

		$es = $di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		$this->startExec();
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
		$this->writeLog(array(
			'dbname' => 'lmm_seo',
			'table' => 'seo_manual_url',
			'topic_name' => '',
			'message' => 'update seo cache shutdown'
		));
	}
	//开始处理
	private function startExec(){
		$this->writeLog(array(
			'dbname' => 'lmm_seo',
			'table' => 'seo_manual_url',
			'topic_name' => '',
			'message' => 'start update seo cache'
		));
		//读取关键词表中的Url
		$sql = 'SELECT COUNT(id) AS c FROM seo_manual_url WHERE `status` = 1';
		$tmp  = $this->manual_url->getRsBySql($sql,true);
		$total = isset($tmp['c']) ? $tmp['c'] : 0;
		$totalPage = ceil($total / $this->pageSize);
		for($p = 1; $p <= $totalPage;$p++){
			$sql = 'SELECT url FROM seo_manual_url WHERE `status` = 1 LIMIT '.(($p - 1) * $this->pageSize).','.$this->pageSize;
			$rs = $this->manual_url->getRsBySql($sql);
			foreach($rs as $v){
				file_get_contents($this->cache1.'?url='.urlencode($v['url']));
				file_get_contents($this->cache2.'?url='.urlencode($v['url']));
				sleep(1);
			}
		}
		$this->writeLog(array(
			'dbname' => 'lmm_seo',
			'table' => 'seo_manual_url',
			'topic_name' => '',
			'message' => 'seo cache end'
		));
	}
	/**
	 * 获取与需要导入的索引名相似的索引
	 * @param $index_name 索引名称
	 * @return array
	 */
	private function getIndexs($index_name = ''){
		$tmp = array();
		$index_names = array();
		if(!$index_name) return $index_names;
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_cat/indices/'.$index_name.'*');
		foreach(explode("\n",$res) as $v){
			if(trim($v)){
				$tmp = explode(' ',$v);
				$index_names[] = $tmp[2];
			}
		}
		return $index_names;
	}
	/**
	 * 创建索引和映射信息
	 * @param $index_name 索引名称
	 * @param $mappings 映射配置
	 * @return bool
	 */
	private function createIndex($index_name = '',$mappings  = ''){
		if(!$index_name) return false;
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name,$mappings,array(),'POST');
		return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
	}
	/**
	 * 删除指定索引
	 * @param $index_name 索引名称
	 * @return bool
	 */
	private function deleteIndex($index_name = ''){
		if(!$index_name) return false;
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name,array(),array(),'DELETE');
		return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
	}
	/**
	 * 获取索引的映射
	 * @param $index_name 索引名称
	 * @return string
	 */
	private function getMappings($index_name = ''){
		if(!$index_name) return array();
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$index_name.'/_mappings');
		return $res;
	}
	/**
	 * 给指定索引创建别名
	 * @param string $index_name 索引名称
	 * @param array $alias 别名数组
	 * @return bool
	 */
	private function addAliases($index_name = '',$alias = array()){
		if(!$index_name || !$alias || !is_array($alias)) return false;
		$tmp = array();
		foreach($alias as $alias_name){
			$tmp[] = '{"add": {"index": "'.$index_name.'","alias": "'.$alias_name.'"}}';
		}

		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_aliases','{"actions":['.implode(',',$tmp).']}',array(),'POST');
		return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
	}
	/**
	 * 给指定索引删除别名
	 * @param string $index_name 索引名称
	 * @param array $alias 别名数组
	 * @return bool
	 */
	private function deleteAliases($index_name = '',$alias = array()){
		if(!$index_name || !$alias || !is_array($alias)) return false;
		$tmp = array();
		foreach($alias as $alias_name){
			$tmp[] = '{"remove": {"index": "'.$index_name.'","alias": "'.$alias_name.'"}}';
		}

		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_aliases','{"actions":['.implode(',',$tmp).']}',array(),'POST');
		return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
	}
	/**
	 * 给指定索引修改别名
	 * @param string $index_name 索引名称
	 * @param array $alias 别名数组 array('old' => 'new','no' => 'yes')
	 * @return bool
	 */
	private function updateAliases($index_name = '',$alias = array()){
		if(!$index_name || !$alias || !is_array($alias)) return false;
		$tmp = array();
		foreach($alias as $old => $new){
			$tmp[] = '{"remove": {"index": "'.$index_name.'","alias": "'.$old.'"}}';
			$tmp[] = '{"add": {"index": "'.$index_name.'","alias": "'.$new.'"}}';
		}
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_aliases','{"actions":['.implode(',',$tmp).']}',array(),'POST');
		return isset($res['acknowledged']) && $res['acknowledged'] == 1 ? true : false;
	}

	//批量导入数据
	private function batchImport($index_name = '',$data = array()){
		
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