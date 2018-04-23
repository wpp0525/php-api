<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Components\ApiClient;

/**
 * 大目的地线路接口
 *
 * @author shenxiang
 *
 */
class SeoVstRouteDataService extends DataServiceBase {
	private $ttl = 600;
	protected $baseUri = 'http://ca.lvmama.com/';
	public function __construct($di, $adapter, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->redis = $redis;
		$this->tsrv = $this->di->get('tsrv');
		$this->ttl = rand(1800,7200);
	}
	public function getData($params){
		$redis_key = str_replace('{params}',md5($params),RedisDataService::REDIS_SEO_ROUTER_DATA);
		$data = json_decode($this->redis->get($redis_key),true);
		if(!$data){
			try{
				$rs = $this->tsrv->exec('search/getVstRoute',array('params' => $params));
			}catch (\Exception $e){
				//var_dump($e->getMessage());
			}
			$this->redis->setex($redis_key,$this->ttl,json_encode($rs,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE));
		}else{
			$rs = $data;
		}
		return $rs;
	}
	public function getTrip($dest_id,$page,$pageSize){
		$redis_key = str_replace('{params}',$dest_id.'_'.$page.'_'.$pageSize,RedisDataService::REDIS_SEO_TRIP);
		$trip_string = $this->redis->get($redis_key);
		if(!$trip_string){
			$this->client = new ApiClient($this->baseUri);
			$trip_info = $this->client->exec('newtrip/getTripByDest',array(
				'dest_id' => $dest_id,
				'page'	=> $page,
				'pageSize' => $pageSize
			),'post');
			$trips = isset($trip_info['result']['list']) ? $trip_info['result']['list'] : array();
			$this->redis->setex($redis_key,$this->ttl,json_encode($trips,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE));
		}else{
			$trips = json_decode($trip_string,true);
		}
		return $trips;
	}
	public function getHotDest($dest){
		$group = $dest['abroad'] == 'N' ? 'home' : 'outside';
		$redis_key = str_replace('{params}',$group,RedisDataService::REDIS_SEO_HOT_DEST);
		$hot_dest_string = $this->redis->get($redis_key);
		if(!$hot_dest_string){
			$hot_dest_string = UCommon::curl(
				'http://www.lvmama.com/seo_api/hotOutDest/getHotList.do','get',array('group' => $group)
			);
			$this->redis->setex($redis_key,$this->ttl,$hot_dest_string);
		}
		$hot_citys = json_decode($hot_dest_string,true);
		$citys = array('hot' => array(),'province' => array());
		foreach($hot_citys as $v){
			if(isset($v['secondIsRecomm']) && $v['secondIsRecomm'] == 'Y'){
				$citys['hot'][] = $v;
			}
			if(isset($v['destId']) && $v['destId'] == 0){
				$citys['province'][$v['hotDestId']] = $v;
			}
		}
		foreach($citys['province'] as $k=>$v){
			if(!isset($citys['province'][$k]['list'])){
				$citys['province'][$k]['list'] = array();
			}
			foreach($hot_citys as $city){
				if(isset($city['hotDestId']) && $city['hotDestId'] == $k){
					$citys['province'][$k]['list'][] = $city;
				}
			}
		}
		return $citys;
	}
	public function getUrl($productId,$categoryId){
		$redis_key = str_replace('{params}',$productId.'_'.$categoryId,RedisDataService::REDIS_SEO_PRODUCT_URL);
		$return = $this->redis->get($redis_key);
		if(!$return){
			$url = $this->tsrv->exec('product/findProductUrl',array(
				'params' => '{"productId":"'.$productId.'","categoryId":"'.$categoryId.'"}'
			));
			$return = '';
			if(isset($url['success']) && $url['success'] == 1){
				$return = isset($url['content']) ? $url['content'] : '';
			}
			$this->redis->setex($redis_key,$this->ttl,$return);
		}
		return $return;
	}
	//只能清理大目的地项目的(紧急情况下使用)
	public function clearRedis($key){
		if(!$key){
			return false;
		}
		if($key == 'all_seo'){
			$keys = $this->redis->keys('seo:hotel:*');
			$this->redis->del($keys);
			$keys = $this->redis->keys('seo:router:*');
			$this->redis->del($keys);
			$keys = $this->redis->keys('seo:ticket:*');
			$this->redis->del($keys);
			$keys = $this->redis->keys('seo_dest:variable_url:*');
			$this->redis->del($keys);
			$keys = $this->redis->keys('seo_dest:product:*');
			$this->redis->del($keys);
			$keys = $this->redis->keys('seo_dest:template:info:*');
			$this->redis->del($keys);
			return true;
		}
		$tmp = explode(':',$key);
		if($tmp[0] == 'seo' || $tmp[0] == 'seo_dest'){
			$this->redis->del($key);
			return true;
		}
	}
	public function getFilterContent($var_name,$dest,$param){
		$rs = array();
		//访问的接口类型
		if(strstr($var_name,'gHotel') || $var_name == 'HOTEL'){//酒店类
			$hotel = $this->di->get('cas')->get('seo_vst_hotel_service');
			$district_id = $dest['district_id'];//行政区ID
			$params = '{"currentPage":1,"pageSize":1,"cityDistrictId":"'.$district_id.'","aggr":true}';
			$data = $hotel->getData($params);
			if($param == 'filter_dest'){//目的地
				//...
			}elseif($param == 'filter_station'){//出发地
				//...
			}elseif($param == 'filter_theme'){//主题
				$rs = isset($data['resultMap']['resultMap']['hotelSubject']) ? $data['resultMap']['resultMap']['hotelSubject'] : array();
			}elseif($param == 'filter_days'){//游玩天数
				//...
			}
			$rs = $this->structureUpdate($rs,'hotel');
		}elseif(stristr($var_name,'ticket')){//门票类
			$ticket = $this->di->get('cas')->get('seo_vst_ticket_service');
			$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest['dest_id'].'","keyword":"'.$dest['dest_name'].'","aggr":true}';
			$data = $ticket->getData($params);
			if($param == 'filter_dest'){//目的地
				$rs = isset($data['selectMap']['dest']) ? $data['selectMap']['dest'] : array();
			}elseif($param == 'filter_station'){//出发地
				$rs = isset($data['selectMap']['county']) ? $data['selectMap']['county'] : array();
			}elseif($param == 'filter_theme'){//主题
				$rs = isset($data['selectMap']['subject']) ? $data['selectMap']['subject'] : array();
			}elseif($param == 'filter_days'){//游玩天数
				//...
			}
			$rs = $this->structureUpdate($rs,'ticket');
		}else{//线路类
			if(stristr($var_name,'localplay') || stristr($var_name,'local')){//当地游,目的地跟团游
				$routeType = 'LOCAL';
			}elseif(stristr($var_name,'freetour') || stristr($var_name,'ziyouxing')){//自由行
				$routeType = 'ZIYOUXING';
			}elseif(stristr($var_name,'group')){//出发地跟团游
				$routeType = 'GROUP';
			}elseif(stristr($var_name,'around')){//周边跟团游
				$routeType = 'AROUND';
			}elseif(stristr($var_name,'romantic') || stristr($var_name,'scenictour')){//景+酒
				$routeType = 'SCENICTOUR';
			}elseif(stristr($var_name,'planeHotel') || stristr($var_name,'freetour')){//机+酒
				$routeType = 'FREETOUR';
			}else{
				$routeType = 'ROUTE';
			}
			$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest['dest_id'].'","routeType":"'.$routeType.'","aggr":true}';
			$data = $this->getData($params);
			if($param == 'filter_dest'){//目的地
				$rs = isset($data['selectMap']['destId']) ? $data['selectMap']['destId'] : array();
			}elseif($param == 'filter_station'){//出发地
				$rs = isset($data['selectMap']['districtId']) ? $data['selectMap']['districtId'] : array();
			}elseif($param == 'filter_theme'){//主题
				$rs = isset($data['selectMap']['subjectId']) ? $data['selectMap']['subjectId'] : array();
			}elseif($param == 'filter_days'){//游玩天数
				$rs = isset($data['selectMap']['routeNum']) ? $data['selectMap']['routeNum'] : array();
			}
			$rs = $this->structureUpdate($rs,'route');
		}
		return $rs;
	}
	public function getFilterValue($filter_name,$filter_type,$dest_id = 0){
		//先从redis中获取,找不到则使用dest_id去搜索接口获取
		//filter_theme->subjectId,filter_station->districtId,filter_dest->destId,filter_days->routeNum
		//兼容正在使用的筛选类型别名
		if($filter_type == 'filter_theme') $filter_type = 'subjectId';
		if($filter_type == 'filter_station') $filter_type = 'districtId';
		if($filter_type == 'filter_dest') $filter_type = 'destId';
		if($filter_type == 'filter_days') $filter_type = 'routeNum';
		$filter_value = $this->getFilter($filter_type,$filter_name);
		if(!$filter_value){//调接口获取,保存
			$data = $this->getData('{"pageSize":1,"destAll":"'.$dest_id.'","aggr":true,"routeType":"ROUTE"}');
			//主题
			$this->saveFilters('subjectId',$data);
			//出发地
			$this->saveFilters('districtId',$data);
			//目的地
			$this->saveFilters('destId',$data);
			//游玩景点
			$this->saveFilters('viewPiont',$data);
			//线路玩法
			$this->saveFilters('playMethod',$data);
			//游玩天数
			$this->saveFilters('routeNum',$data);
			$filter_value = $this->getFilter($filter_type,$filter_name);
		}
		return $filter_value;
	}
	private function saveFilters($type,$data){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_ROUTER_FILTER);
		$filters = empty($data['selectMap'][$type]) ? array() : $data['selectMap'][$type];
		foreach($filters as $k => $v){
			if(!$this->redis->zscore($redis_key,$k)) $this->redis->zadd($redis_key,$v['name'],$k);
		}
		return true;
	}
	private function getFilter($type,$filter_name){
		$redis_key = str_replace('{filter_type}',$type,RedisDataService::REDIS_SEO_ROUTER_FILTER);
		return $this->redis->zscore($redis_key,$filter_name);
	}
	/**
	 * 将结构返回的筛选项内容结构调整为语义更清晰的方式
	 * @param $data 需要调整的数组
	 * @return array
	 */
	private function structureUpdate($data,$type = 'route'){
		$rs = array();
		switch($type){
			case 'route':
			case 'ticket':
				foreach($data as $k => $v){
					if($v['name'] == 0) continue;
					$rs[] = array('id' => intval($v['name']), 'num' => $v['num'], 'name' => $k,);
				}
				break;
			case 'hotel':
				foreach($data as $k=>$v){
					if($v['id'] == 0) continue;
					$rs[] = array('id' => $v['id'],'name' => $v['value']);
				}
				break;
		}
		return $rs;
	}
}