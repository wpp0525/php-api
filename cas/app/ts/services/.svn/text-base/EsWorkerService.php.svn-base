<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\Filelogger;

/**
 * 消息 Worker服务类
 *
 * @author win.sx
 *        
 */
class EsWorkerService implements DaemonServiceInterface {

	private $db_srv;

	/**
	 * @var Lvmama\Cas\Service\DestinationDataService
	 */
	private $dest_svc;

	/**
	 * @var Lvmama\Cas\Service\QaQuestionDataService
	 */
	private $qa_svc;

	/**
	 * @var Lvmama\Cas\Service\TravelDataServiceBase
	 */
	private $travel_svc;

	/**
	 * @var Lvmama\Cas\Service\BaiKeDataService
	 */
	private $baike_svc;

	/**
	 * @var Lvmama\Cas\Service\DestinBaseDataService
	 */
	private $vst_svc;

	/**
	 * @var Lvmama\Cas\Service\SeoKeywordUrlRelatedDataService
	 */
	private $seo_svc;
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

	private $host;

	private $port;

	private $client;

	private $pageSize = 1000;
	//存储导入日志的索引名
	private $import_log = 'es_import_log';
	//日志type
	private $log_type = 'import_db_data';
	//数据库名
	private $db_name = '';
	//新索引名
	private $newIndex;
	//老索引名
	private $oldIndex;

	private $include_index = array(
		'lmm_lvyou',
		'lmm_qa',
		'lmm_travels',
		'lmm_baike',
		'lmm_vst_destination',
		'lmm_seo',
		'hotresult'
	);

	public function __construct($di) {
		//目的地数据库
		$this->dest_svc = $di->get('cas')->get('destination-data-service');
		//问答数据库
		$this->qa_svc = $di->get('cas')->get('qaquestion-data-service');
		//游记数据库
		$this->travel_svc = $di->get('cas')->get('travel_data_service');
		//百科数据库
		$this->baike_svc = $di->get('cas')->get('baike-data-service');
		//目的地基础数据
		$this->vst_svc = $di->get('cas')->get('destin_base_service');
		$this->seo_svc = $di->get('cas')->get('seo_keyword_url_related_service');
		$this->dest_svc->setReconnect(true);
		$this->qa_svc->setReconnect(true);
		$this->travel_svc->setReconnect(true);
		$this->baike_svc->setReconnect(true);
		$this->vst_svc->setReconnect(true);
		$this->seo_svc->setReconnect(true);
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
		$es = $di->get('config')->get('elasticsearch');
		$this->host = $es->host;
		$this->port = $es->port;
		$this->client = new ApiClient('http://'.$this->host.':'.$this->port);
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		if($flag){
			$this->startExec($flag);
		}else{
			foreach($this->include_index as $index_name){
				$this->startExec($index_name);
			}
		}
	}
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		//关闭时收尾任务
	}
	//开始处理
	private function startExec($index_name = ''){
		//先看看存储日志的索引是否存在
		$log_index = $this->getIndexs($this->import_log);
		//没有存储日志的索引,创建它
		if(!$log_index){
			$log_mappings = array(
				'mappings' =>array(
					$this->log_type => array(
						'properties' => array(
							'createtime'	=> array('type' => 'date','format'	=> 'yyyy-MM-dd HH:mm:ss'),
							'dbname'		=> array('type' => 'string','index' => 'not_analyzed'),
							'table'			=> array('type' => 'string','index' => 'not_analyzed'),
							'message'		=> array('type' => 'string')
						)
					)
				)
			);
			//创建日志索引
			$log_index_name = $this->import_log.date('Ymd');
			if(!$this->createIndex($log_index_name,json_encode($log_mappings,JSON_FORCE_OBJECT))){
				$this->printException(array('message' => 'Log Index ['.$this->import_log.'] create fail.'));
			}
			$this->addAliases($log_index_name,array($this->import_log));
		}
		$this->db_name = $index_name;
		$this->newIndex = $index_name.date('Ymd');
		$custom_mappings = '';
		if(file_exists(APPLICATION_PATH.'/config/mappings/'.$index_name.'.mappings')){
			$custom_mappings = file_get_contents(APPLICATION_PATH.'/config/mappings/'.$index_name.'.mappings');
		}
		//查看目前使用别名的索引名
		$current_index_name = $this->getIndexs($index_name,'');
		//看看目前存在的索引名
		$index_names	= $this->getIndexs($index_name);
		if(count($index_names)){
			$this->oldIndex		= $current_index_name[0];
			if($this->oldIndex == $this->newIndex){
				$this->writeLog(array('message' => 'online old index and new index name same.','dbname' => $index_name));
				return;
			}
			//获取映射
			$_mappings		= $this->getMappings($this->oldIndex);
			$mapping		= $_mappings[$this->oldIndex];
			//给非$indx_name创建别名
			foreach($index_names as $v){
				//保留一个创建别名,其他的都删除
				if($v != $this->oldIndex){
					$this->deleteIndex($v);
				}
			}
			//创建新索引
			if(!$this->createIndex($this->newIndex,$custom_mappings ? $custom_mappings : json_encode($mapping,JSON_FORCE_OBJECT))){
				$this->printException(array('message' => 'new Index ['.$this->newIndex.'] create fail.','dbname' => $index_name));
			}
		}else {
			if(!$custom_mappings) $this->printException(array('message' => 'online not found [' . $index_name . '] same index.', 'dbname' => $index_name));
			if(!$this->createIndex($this->newIndex,$custom_mappings)) $this->printException(array('message' => 'new Index ['.$this->newIndex.'] create fail.','dbname' => $index_name));
		}
		//开始导数据
		$method_name = 'import'.$index_name;
		$this->$method_name();
		//在新索引中添加别名为需要的索引名
		if(!$this->addAliases($this->newIndex,array($index_name == 'hotresult' ? 'HotResult' : $index_name))){
			$this->printException(array('message' => 'new Index ['.$this->newIndex.'] set Alias fail.','dbname' => $index_name));
		}
		//删除老索引别名
		$this->deleteAliases($this->oldIndex,array($index_name == 'hotresult' ? 'HotResult' : $index_name));
	}
	//导入旅游库的数据
	private function importlmm_lvyou(){
		if(!$this->newIndex) $this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));

		$this->pageSize = 5000;
		$table_name = 'ly_destination';
		//统计数据总量
		$tmp_count	= $this->dest_svc->getRsBySql("SELECT COUNT(dest_id) AS c FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y'",true);
		$count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
		$totalPage	= ceil($count / $this->pageSize);
		
		$this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => $this->db_name,'table' => $table_name));
		//逐页读取存入es
		for($p = 1;$p <= $totalPage;$p++){
			$list = array();
			$start = ($p - 1) * $this->pageSize;
			$sql = "SELECT dest_id,dest_name,dest_type,abroad,en_name,coord_type,parent_id,district_id,img_url,count_been,count_want,ent_sight,heritage,letter,protected_area,`range`,star,local_lang,district_name,stage,pinyin,short_pinyin,dest_alias,parents,parent_name,parent_names,intro,longitude,latitude,g_latitude,g_longitude FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y' LIMIT {$start} , {$this->pageSize}";
			foreach($this->dest_svc->getRsBySql($sql) as $dest){
				$dest['address'] = '';
				$dest['location'] = $dest['latitude'] ? $dest['latitude'].','.$dest['longitude'] : $dest['g_latitude'].','.$dest['g_longitude'];
				foreach($dest as $k => $v){ if(is_numeric($v)){ $dest[$k] = floatval($v); } }
				$list[] = '{ "index" : { "_index" : "'.$this->newIndex.'", "_type" : "'.$table_name.'", "_id" : "'.$dest['dest_id'].'" } }'."\n".json_encode($dest,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
			}
			$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->newIndex.'/'.$table_name.'/_bulk',implode("\n",$list)."\n",array(),'POST');
		}
		$this->writeLog(array('message' => 'import data done!','dbname' => $this->db_name,'table' => $table_name));
		$table_name = 'ly_district_sign';
		//统计数据总量
		$tmp_count	= $this->dest_svc->getRsBySql("SELECT COUNT(dest_id) AS c FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y'",true);
		$count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
		$totalPage	= ceil($count / $this->pageSize);
		$this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => $this->db_name,'table' => $table_name));

		//逐页读取存入es
		for($p = 1;$p <= $totalPage;$p++){
			$list = array();
			$start = ($p - 1) * $this->pageSize;
			$sql = "SELECT dest_id,dest_name,dest_type,abroad,en_name,coord_type,parent_id,district_id,img_url,count_been,count_want,ent_sight,heritage,letter,protected_area,`range`,star,local_lang,district_name,stage,pinyin,short_pinyin,dest_alias,parents,parent_name,parent_names,intro,longitude,latitude,g_latitude,g_longitude FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y' LIMIT {$start} , {$this->pageSize}";
			foreach($this->dest_svc->getRsBySql($sql) as $dest){
				$dest['location'] = $dest['latitude'] ? $dest['latitude'].','.$dest['longitude'] : $dest['g_latitude'].','.$dest['g_longitude'];
				$dest['address'] = '';
				foreach($dest as $k => $v){ if(is_numeric($v)){ $dest[$k] = floatval($v); } }
				$list[] = '{ "index" : { "_index" : "'.$this->newIndex.'", "_type" : "'.$table_name.'", "_id" : "'.$dest['dest_id'].'" } }'."\n".json_encode($dest,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
			}
			$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->newIndex.'/'.$table_name.'/_bulk',implode("\n",$list)."\n",array(),'POST');
		}
		$this->writeLog(array('message' => 'import data done!','dbname' => $this->db_name,'table' => $table_name));
		$table_name = 'ly_address';
		//统计数据总量
		$tmp_count	= $this->dest_svc->getRsBySql("SELECT COUNT(address_id) AS c FROM {$table_name} WHERE `status` = 99",true);
		$count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
		$totalPage	= ceil($count / $this->pageSize);
		$this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => $this->db_name,'table' => $table_name));

		//逐页读取存入es
		for($p = 1;$p <= $totalPage;$p++){
			$list = array();
			$start = ($p - 1) * $this->pageSize;
			$sql = "SELECT * FROM {$table_name} WHERE `status` = 99 LIMIT {$start} , {$this->pageSize}";
			foreach($this->dest_svc->getRsBySql($sql) as $dest){
				foreach($dest as $k => $v){ if(is_numeric($v)){ $dest[$k] = floatval($v); } }
				$list[] = '{ "index" : { "_index" : "'.$this->newIndex.'", "_type" : "'.$table_name.'", "_id" : "'.$dest['address_id'].'" } }'."\n".json_encode($dest,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
			}
			$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->newIndex.'/'.$table_name.'/_bulk',implode("\n",$list)."\n",array(),'POST');
		}
		$this->writeLog(array('message' => 'import data done!','dbname' => $this->db_name,'table' => $table_name));
		$this->pageSize = 1000;
	}
	//导入问答库的数据
	private function importlmm_qa(){
		if(!$this->newIndex){
			$this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));
		}
		$this->db_srv = $this->qa_svc;
		$this->commonImport('qa_admin_answer','id','*');
		$this->commonImport('qa_answer','id','*');
		$this->commonImport('qa_question','id','*');
		$this->commonImport('qa_answer_comment','id','*');
		$this->commonImport('qa_tag','id','*');
		$this->commonImport('qa_tag_category','id','*');
	}
	//导入游记库的数据
	private function importlmm_travels(){
		if(!$this->newIndex){
			$this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));
		}
		$this->db_srv = $this->travel_svc;
		$this->commonImport('tr_travel','id','*');
		$this->commonImport('tr_travel_content','id','*');
		$this->commonImport('tr_video','id','*');
		$this->commonImport('tr_video_brand','id','*');
		$this->commonImport('tr_video_relation','id','*');
		$this->commonImport('ly_s_text','segment_id','*');
		$this->commonImport('ly_tag','tag_id','*');
		$this->commonImport('ly_trip','trip_id','*');
		$this->commonImport('tr_app_travel','id','*');
	}
	//导入百科
	private function importlmm_baike(){
		if(!$this->newIndex) $this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));

		$this->db_srv = $this->baike_svc;
		$this->commonImport('productbase','id');
		$this->pageSize = 2;
		$this->commonImport('productcontent','productId');
		$this->pageSize = 1000;
		$this->commonImport('productsegment','id');
		$this->commonImport('segment','id');
		$this->commonImport('segmentcontent','pid');
	}

	//导入目的地基础信息数据
	private function importlmm_vst_destination(){
		if(!$this->newIndex) $this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));
		$this->db_srv = $this->vst_svc;
		$this->commonImport('biz_dest','dest_id');
		$this->commonImport('biz_district','district_id');
		$this->commonImport('biz_district_sign','sign_id');
		$this->commonImport('biz_com_coordinate','coord_id');
		$this->commonImport('ly_destination','dest_id','dest_id,dest_name,dest_type,dest_type_name,abroad,showed,cancel_flag,en_name,coord_type,parent_id,district_id,img_url,count_been,count_want,ent_sight,heritage,letter,protected_area,`range`,star,local_lang,district_name,stage,pinyin,short_pinyin,dest_alias,parents,parent_name,parent_names,intro,longitude,latitude,g_latitude,g_longitude');
		$this->commonImport('ly_district_sign','dest_id','sign_id,dest_id,dest_name,dest_type,dest_type_name,showed,cancel_flag,abroad,en_name,coord_type,parent_id,district_id,img_url,count_been,count_want,ent_sight,heritage,letter,protected_area,`range`,star,local_lang,district_name,stage,pinyin,short_pinyin,dest_alias,parents,parent_name,parent_names,intro,longitude,latitude,g_latitude,g_longitude');
		$this->commonImport('ly_address','address_id');
		$this->commonImport('dest_com_city','city_id');
	}
	private function importlmm_seo(){
		if(!$this->newIndex) $this->printException(array('message' => 'new Index must have.','dbname' => $this->db_name));
		$this->db_srv = $this->seo_svc;
		$this->commonImport('seo_category','id');
		$this->commonImport('seo_crawler_url','id');
		$this->commonImport('seo_keyword_url','id');
		$this->commonImport('seo_keyword_url_related','id');
		$this->commonImport('seo_manual_crawler','id');
		$this->commonImport('seo_manual_url','id');
	}
	private function commonImport($table_name,$primary_field,$select = '*',$not_analyzed = array()){
		try{
			//统计数据总量
			$tmp_count	= $this->db_srv->query("SELECT COUNT({$primary_field}) AS c FROM {$table_name}");
			$count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
			$totalPage	= ceil($count / $this->pageSize);
			$this->writeLog(array('message' => 'itemCount:'.$count.' totalPage:'.$totalPage.' pageSize:'.$this->pageSize,'dbname' => $this->db_name,'table' => $table_name));
			//逐页读取存入es
			for($p = 1;$p <= $totalPage;$p++){
				$list = array();
				$start = ($p - 1) * $this->pageSize;
				$sql = "SELECT {$select} FROM {$table_name} LIMIT {$start} , {$this->pageSize}";
				foreach($this->db_srv->query($sql,'All') as $dest){
					foreach($not_analyzed as $field){
						$dest[$field.'_not_analyzed'] = isset($dest[$field]) ? $dest[$field] : '';
					}
					foreach($dest as $k => $v){
						if(is_numeric($v)){
							$dest[$k] = floatval($v);
						}
						if($k == 'updated_time'){//如果是初始化时间格式存在问题,需特殊处理
							if($v == '0000-00-00 00:00:00') $dest[$k] = '1970-01-01 00:00:00';
						}
					}
					$list[] = '{ "index" : { "_index" : "'.$this->newIndex.'", "_type" : "'.$table_name.'", "_id" : "'.$dest[$primary_field].'" } }'."\n".json_encode($dest,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
				}
				Filelogger::getInstance()->addLog($this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->newIndex.'/'.$table_name.'/_bulk',implode("\n",$list)."\n",array(),'POST'),'INFO');
			}
			$this->writeLog(array('message' => 'import data done!','dbname' => $this->db_name,'table' => $table_name));
		}catch (\Exception $e){
			var_dump($e);
			Filelogger::getInstance()->addLog($e->getMessage(),'ERROR');
		}
	}
	//导入目的地热门景点数据及排序
	private function importhotresult(){
		//查出CITY
		$table_name = 'ly_destination';
		//统计数据总量
		$tmp_count	= $this->dest_svc->getRsBySql("SELECT COUNT(dest_id) AS c FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y' AND `dest_type` = 'CITY'",true);
		$count		= isset($tmp_count['c']) ? intval($tmp_count['c']) : 0;
		$totalPage	= ceil($count / $this->pageSize);
		for($p = 1;$p <= $totalPage;$p++){
			$start = ($p - 1) * $this->pageSize;
			$sql = "SELECT dest_id,dest_name FROM {$table_name} WHERE `showed` = 'Y' AND `cancel_flag` = 'Y' AND `dest_type` = 'CITY' LIMIT {$start} , {$this->pageSize}";
			foreach($this->dest_svc->getRsBySql($sql) as $row){
				$viewspots = $this->client->external_exec('http://ca.lvmama.com/destinfo/dest-child-list',array(
					'dest_id' => $row['dest_id'],
					'pn' => 1,
					'ps' => 20,
					'limit' => 20,
					'forcedb' => 0,
					'recom_type' => 'VIEWSPOT',
					'dest_type' => 'VIEWSPOT',
				));
				$list = array();
				foreach($viewspots as $i => $viewspot){
					$dest = array(
						'dest_id' => $viewspot['dest_id'],
						'dest_name' => $row['dest_name'],
						'name' => $viewspot['dest_name'],
						'recommand' => $i+1
					);
					$list[] = '{ "index" : { "_index" : "'.$this->newIndex.'", "_type" : "hot_spot", "_id" : "'.$viewspot['dest_id'].'" } }'."\n".json_encode($dest,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
				}
				//把内容写到新索引
				$this->client->external_exec('http://'.$this->host.':'.$this->port.'/'.$this->newIndex.'/'.$table_name.'/_bulk',implode("\n",$list)."\n",array(),'POST');
			}
		}
	}
	/**
	 * 获取与需要导入的索引名相似的索引
	 * @param $index_name 索引名称
	 * @return array
	 */
	private function getIndexs($index_name = '',$extend = '*'){
		$tmp = array();
		$index_names = array();
		if(!$index_name) return $index_names;
		$res = $this->client->external_exec('http://'.$this->host.':'.$this->port.'/_cat/indices/'.$index_name.$extend);
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
