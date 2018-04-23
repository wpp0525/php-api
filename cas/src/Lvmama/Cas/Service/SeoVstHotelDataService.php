<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;

/**
 * 大目的地酒店接口
 *
 * @author shenxiang
 *
 */
class SeoVstHotelDataService extends DataServiceBase {
	private $ttl = 600;
	public function __construct($di, $adapter, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->redis = $redis;
		$this->tsrv = $this->di->get('tsrv');
		$this->ttl = rand(1800,7200);
	}
	public function getData($params){
		$redis_key = str_replace('{params}',md5($params),RedisDataService::REDIS_SEO_HOTEL_DATA);
		$data = $this->redis->get($redis_key);
		if(!$data){
			try{
				$rs = $this->tsrv->exec('search/getVstHotel',array('params' => $params));
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
		if($filter_type == 'filter_theme') $filter_type = 'hotelSubject';
		if($filter_type == 'filter_station') $filter_type = 'districtId';
		if($filter_type == 'filter_dest') $filter_type = 'destId';
		if($filter_type == 'filter_days') $filter_type = 'routeNum';
		$filter_value = $this->getFilter($filter_type,$filter_name);
		if(!$filter_value){//调接口获取,保存
			$dest = json_decode(
				UCommon::curl('http://ca.lvmama.com/dest/getAppDestDetail','GET',array('dest_id' => $dest_id)),
				true
			);
			$district_id = empty($dest['result']['district_id']) ? 0 : $dest['result']['district_id'];
			$data = $this->getData('{"pageSize":1,"cityDistrictId":"'.$district_id.'","aggr":true}');
			//moreHotelBrand
			$this->saveFilters('moreHotelBrand',$data);
			//defalutHotelBrand
			$this->saveFilters('defalutHotelBrand',$data);
			//landmarkMap
			$this->saveFilters('landmarkMap',$data);
			//hotelBrand
			$this->saveFilters('hotelBrand',$data);
			//hotelTag
			$this->saveFilters('hotelTag',$data);
			//hotelSubject
			$this->saveFilters('hotelSubject',$data);
			$filter_value = $this->getFilter($filter_type,$filter_name);
		}
		return $filter_value;
	}
	private function saveFilters($type,$data){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_HOTEL_FILTER);
		$filters = empty($data['resultMap']['resultMap'][$type]) ? array() : $data['resultMap']['resultMap'][$type];
		foreach($filters as $k => $v){
			if(!$this->redis->zscore($redis_key,$v['value'])) $this->redis->zadd($redis_key,$v['id'],$v['value']);
		}
		return true;
	}
	private function getFilter($type,$filter_name){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_HOTEL_FILTER);
		return $this->redis->zscore($redis_key,$filter_name);
	}
}