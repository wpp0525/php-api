<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 大目的地门票接口
 *
 * @author shenxiang
 *
 */
class SeoVstTicketDataService extends DataServiceBase {
	private $ttl = 600;
	public function __construct($di, $adapter, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->redis = $redis;
		$this->tsrv = $this->di->get('tsrv');
		$this->ttl = rand(1800,7200);
	}
	public function getData($params){
		$redis_key = str_replace('{params}',md5($params),RedisDataService::REDIS_SEO_TICKET_DATA);
		$data = $this->redis->get($redis_key);
		if(!$data){
			try{
				$rs = $this->tsrv->exec('search/getVstTicket',array('params' => $params));
			}catch (\Exception $e){
				//var_dump($e);
			}
			$this->redis->setex($redis_key,$this->ttl,json_encode($rs,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE));
		}else{
			$rs = json_decode($data,true);
		}
		return $rs;
	}
	public function getFilterValue($filter_name,$filter_type,$dest_id = 0){
		//先从redis中获取,找不到则使用dest_id去搜索接口获取
		//filter_theme->subjectId,filter_station->districtId,filter_dest->destId,filter_days->routeNum
		//兼容正在使用的筛选类型别名
		if($filter_type == 'filter_theme') $filter_type = 'subject';
		$filter_value = $this->getFilter($filter_type,$filter_name);
		if(!$filter_value){//调接口获取,保存
			$data = $this->getData('{"pageSize":1,"destAll":"'.$dest_id.'","aggr":true}');
			//county
			$this->saveFilters('county',$data);
			//subject
			$this->saveFilters('subject',$data);
			$filter_value = $this->getFilter($filter_type,$filter_name);
		}
		return $filter_value;
	}
	private function saveFilters($type,$data){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_TICKET_FILTER);
		$filters = empty($data['selectMap'][$type]) ? array() : $data['selectMap'][$type];
		foreach($filters as $k => $v){
			if(!$this->redis->zscore($redis_key,$k)) $this->redis->zadd($redis_key,$v['name'],$k);
		}
		return true;
	}
	private function getFilter($type,$filter_name){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_TICKET_FILTER);
		return $this->redis->zscore($redis_key,$filter_name);
	}
}