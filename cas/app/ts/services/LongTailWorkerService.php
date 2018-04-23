<?php
use Lvmama\Common\Utils\UCommon;
/**
 * kafka消息队列 Worker服务类
 *
 * @author shenxiang
 *
 */
class LongTailWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface {
	/**
	 * @var Lvmama\Cas\Service\SeoDestKeywordDataService
	 */
	private $seo_dest_keyword_srv;
	/**
	 * @var Lvmama\Cas\Service\SeoDestCategoryDataService
	 */
	private $seo_dest_category_svc;
	/**
	 * @var Lvmama\Cas\Service\SeoDestVariableDataService
	 */
	private $seo_dest_variable_svc;
	/**
	 * @var Lvmama\My\Services\CasDataService
	 */
	private $cas;
	/**
	 * @var Lvmama\My\Services\CasTwoDataService
	 */
	private $castwo;
	/**
	 * @param $md5
	 */
	private $md5;
	/**
	 * @param $filters
	 */
	private $filters;
	/**
	 * @var \Lvmama\Cas\Service\SeoVstRouteDataService
	 */
	private $route_service;
	/**
	 * @var \Lvmama\Cas\Service\SeoVstTicketDataService
	 */
	private $ticket_service;
	/**
	 * @var \Lvmama\Cas\Service\SeoVstHotelDataService
	 */
	private $hotel_service;
	/**
	 * @param $dest
	 * @var \Lvmama\Cas\Service\DestinationDataService
	 */
	private $dest_service;
	/**
	 * @param $keyword_info
	 */
	private $keyword_info;

	/**
	 * 根据筛选条件能获取到的最少产品数量,少于此值页面无效
	 * @param $filter_min_product_num
	 */
	private $filter_min_product_num = 2;

	public function __construct($di) {
		$this->cas = $di->get('ca');
		$this->castwo = $di->get('ca2');
		$this->seo_dest_category_svc = $di->get('cas')->get('seo_dest_category_service');
		$this->seo_dest_keyword_srv = $di->get('cas')->get('seo_dest_keyword_service');
		$this->seo_dest_variable_svc = $di->get('cas')->get('seo_dest_variable_service');
		$this->route_service = $di->get('cas')->get('seo_vst_route_service');
		$this->ticket_service = $di->get('cas')->get('seo_vst_ticket_service');
		$this->hotel_service = $di->get('cas')->get('seo_vst_hotel_service');
		$this->dest_service = $di->get('cas')->get('destination-data-service');
	}
	public function handle($data)
	{
		// TODO: Implement handle() method.
		if($data->payload){
			//开始去生成长尾页面,第一步获取默认数据
			$info = json_decode($data->payload,true);
			print_r($info);
			$keyword_id = is_numeric($info['keyword_id']) ? $info['keyword_id'] : 0;
			$template_id = is_numeric($info['template_id']) ? $info['template_id'] : 0;

			$this->md5 = $info['md5'] ? $info['md5'] : '';
			$this->filters = empty($info['filters']) ? '' : $info['filters'];
			$this->getDefaultData($keyword_id,$template_id);
		}
	}

	/**
	 * 获取页面的默认数据
	 * @param $keyword_id
	 * @param $template_id
	 */
	private function getDefaultData($keyword_id,$template_id){
		//获取频道和路由
		$template_info = $this->cas->exec2('template/info',array('id' => $template_id,'columns' => 'channel_id,route_id'));
		if(empty($template_info['results'])){
			$this->writeLog(array('content' => '页面:['.$keyword_id.'],没有找到相应的模板信息'));
			return;
		}
		if(!empty($template_info['error'])){
			$this->writeLog(array('content' => '页面:['.$keyword_id.']模板异常['.$template_info['error_description'].']'));
			return;
		}
		$template_var_list = $this->cas->exec2('template/getVar',array('tid'=>$template_id),'post');
		if(empty($template_var_list['results'])){
			$this->writeLog(array('content' => '页面['.$keyword_id.']模板变量信息不存在'));
			return;
		}
		$keyword_info = $this->cas->exec2('keyword/info',array('id' => $keyword_id));
		if(empty($keyword_info)) $this->writeLog(array('content' => '页面['.$keyword_id.']基本信息不存在'));
		$dest_ids = isset($keyword_info['results']['dest_id']) ? $keyword_info['results']['dest_id'] : '';
		if(!$dest_ids){
			$this->writeLog(array('content' => '页面['.$keyword_id.'],未绑定目的地ID'));
			return;
		}
		$tmp = explode(',',$dest_ids);
		$dest_id = $tmp[0];
		$this->keyword_info = $keyword_info;
		$keyword_name = $keyword_info['results']['keyword_name'];
		$keyword_pinyin = $keyword_info['results']['keyword_pinyin'];
		//如果存在自定义筛选项的值,则进行格式转换
		$diy_filter_datas = array();
		if($this->filters){
			foreach(explode('|',$this->filters) as $tab_filter_str){
				$tmp = explode(':',trim($tab_filter_str));
				$filter_arr = array();
				//将筛选标签和筛选值设置为符合接口的形式
				foreach(explode(',',$tmp[1]) as $filter_str){
					$tmp2 = explode('=',$filter_str);
					$filterLabel = $this->cas->exec2('bigdest/getFilterTitle',array('name' => rawurlencode($tmp2[0])));
					if(!empty($filterLabel['result'])) $filter_arr[$filterLabel['result']] = $tmp2[1];
				}
				if(empty($filter_arr['routeType'])){//没有设置线路类型时默认为全部线路
					$filter_arr['routeType'] = '全部线路';
				}
				$productType = $this->cas->exec2('bigdest/getProductType',array('name' => rawurlencode($filter_arr['routeType'])));
				//因其他值均需要先确定产品类型
				$filter_arr['routeType'] = empty($productType['result']) ? 'ROUTE' : $productType['result'];
				foreach($filter_arr as $label => $val){
					if($label == 'routeType') continue;
					$filterValue = $this->cas->exec2('bigdest/getFilterValue',array(
						'productType' => $filter_arr['routeType'],
						'filterType' => $label,
						'filterName' => $val,
						'dest_id' => $dest_id
					));
					if(empty($filterValue['result'])){
						unset($filter_arr[$label]);
					}else{
						$filter_arr[$label] = $filterValue['result'];
					}
				}
				$diy_filter_datas[$tmp[0]] = $filter_arr;
			}
		}
		foreach($template_var_list['results'] as $key=>$row){
			$variable_url = trim($row['variable_url']);
			$group_type = $row['group_type'];
			$variable_name = $row['variable_name'];
			if($group_type == 'product') continue;
			$variable_content = $variable_url;
			if(preg_match('/^http[s]{0,1}:\/\/.*/' ,$variable_url)){
				$url = explode('?', $variable_url);
				$url = $url[0] . '?dest_id=' . $dest_id . '&keyword_id=' . $keyword_id . (isset($url[1]) ? '&'.$url[1] : '');
				$api_content = json_decode(UCommon::curl($url,'GET'),true);
				if($api_content && isset($api_content['error']) && !$api_content['error']){
					$variable_content = $api_content['result'];
				}
			}
			$variable_filter = array();
			//添加默认的筛选项内容
			if($group_type == 'tab'){
				$rs = $this->cas->exec2('bigdest/getTypeByUrl',array('url' => urlencode($variable_url)));
				if(!$rs['error']){
					$routeType = $rs['result']['routeType'];
					$type = $rs['result']['type'];
					foreach($variable_content as $k => $v){
						$filter = array();
						if($routeType) $filter['routeType'] = $routeType;
						if($type && $v['id']) $filter[$type] = $v['id'];
						//如果csv文件有设置自己的筛选项,把csv文件中的筛选条件加上去
						if(!empty($diy_filter_datas[$v['name']])){
							$filter = array_merge($filter,$diy_filter_datas[$v['name']]);
						}
						if($filter) $variable_filter[$k+1] = $filter;
						//拿tab项筛选项去获取下能取到的产品数量,产品数量少于可容忍的最小量以下的记录下来
						if($this->getProductNum($filter,$dest_id) < $this->filter_min_product_num){
							$this->writeLog(array('content' => '页面['.$keyword_id.'],标题:['.$keyword_name.']根据筛选项:['.json_encode($filter).']获取的产品数据小于'.$this->filter_min_product_num.'!'));
							return;
						}
					}
				}
			}
			if(!is_array($variable_content) && !is_array(json_decode($variable_content, true))){
				$variable_content = json_encode(array('value' => $variable_content), JSON_UNESCAPED_UNICODE);
			}else{
				if(is_array($variable_content)){
					$variable_content = json_encode($variable_content , JSON_UNESCAPED_UNICODE);
				}
				$variable_filter = $variable_filter ? json_encode($variable_filter,JSON_UNESCAPED_UNICODE) : '';
			}
			if(!$variable_content){
				$this->writeLog(array('content' => '页面['.$keyword_id.'],获取的变量内容为空!'));
				return;
			}
			//如果是导航数据，存储到对应数据库
			if(in_array($group_type, array('onenavigation', 'navigation'))){
				$this->cas->exec2('tvars/navgationimport',array(
					'var_content' => $variable_content,
					'var_name' => $variable_name,
					'keyword_id' => $keyword_id
				),'POST');
			}
			//跟据tab项加上去的筛选条件去获取一下产品数量,保存产品数据值来判断是的需要设置成有效的状态
			$this->seo_dest_variable_svc->insert(array(
				'variable_content' => $variable_content,
				'variable_filter' => $variable_filter ? $variable_filter : '',
				'create_time' => time(),
				'variable_name' => $variable_name,
				'keyword_id' => $keyword_id,
				'module_id' => $row['module_id'],
				'group_type' => $group_type,
				'max_count' => $row['max_count'],
			));
		}
		//生成文件
		$result = $this->castwo->exec2('template/buildPageTwig',array(
			'keyword_id' => $keyword_id,
			'template_id' => $template_id
		));
		if(empty($result['result'])){
			$this->writeLog(array('content' => '页面['.$keyword_id.'],标题['.$keyword_name.'],生成页面文件失败!'));
		}
		$this->seo_dest_keyword_srv->update($keyword_id,array('status' => 1));
		$this->cas->exec2('template/sendMsgGetDistrict',array(
			'template_id' => $template_id,
			'manualId' => $keyword_id,
			'dest_id' => $dest_id,
			'keyword_pinyin' => $keyword_pinyin
		));
		sleep(10);
		$this->cas->exec2('product/sendProductIdToPool',array('keyword_id' => $keyword_id));
		$this->writeLog(array('content' => '页面['.$keyword_id.'],标题['.$keyword_name.'],生成成功!'));
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
	private function getProductNum($filter,$dest_id){
		switch($filter['routeType']){
			case 'ROUTE':
			case 'LOCAL':
			case 'GROUP':
			case 'ZIYOUXING':
			case 'SCENICTOUR':
			case 'FREETOUR':
				$data = $this->getRouteByParams($filter,$dest_id);
				break;
			case 'TICKET':
				$data = $this->getTicketByParams($filter,$dest_id);
				break;
			case 'HOTEL':
				$dest = $this->dest_service->getDestById($dest_id);
				if(empty($dest['district_id'])){
					$this->writeLog(array('content' => '页面['.$this->keyword_info['keyword_id'].'],标题['.$this->keyword_info['keyword_name'].']获取'.$filter['routeType'].'数据时获取不到dest_id:'.$dest_id.'对应的district_id'));
					return;
				}
				$data = $this->getHotelByParams($filter,$dest['district_id']);
				break;
		}
		return empty($data['items']) ? 0 : $data['items'];
	}
	private function getRouteByParams($filter,$dest_id){
		$param = '{"destAll":"'.$dest_id.'",';
		foreach($filter as $filter_field => $filter_value){
			$field = $filter_field;
			switch($filter_field){
				case 'filter_days':
					$field = 'routeNum';
					break;
				case 'filter_theme':
					$field = 'subjectId';
					break;
				case 'filter_station':
					$field = 'districtId';
					break;
				case 'filter_dest':
					$field = 'destId';
					break;
			}
			$param .= '"'.$field.'":"'.$filter_value.'",';
		}
		$param .= '"pageSize":'.$this->filter_min_product_num.'}';
		return $this->route_service->getData($param);
	}
	private function getTicketByParams($filter,$dest_id){
		$param = '{"destAll":"'.$dest_id.'",';
		foreach($filter as $filter_field => $filter_value){
			$field = $filter_field;
			switch($filter_field){
				case 'filter_theme':
					$field = 'subjectId';
					break;
				case 'filter_dest':
					$field = 'COUNTY';
					break;
			}
			$param .= '"'.$field.'":"'.$filter_value.'",';
		}
		$param .= '"pageSize":'.$this->filter_min_product_num.'}';
		return $this->ticket_service->getData($param);
	}
	private function getHotelByParams($filter,$district_id){
		$param = '{"cityDistrictId":"'.$district_id.'",';
		foreach($filter as $filter_field => $filter_value){
			$field = $filter_field;
			switch($filter_field){
				case 'filter_theme':
					$field = 'subjectIds';
					break;
			}
			$param .= '"'.$field.'":"'.$filter_value.'",';
		}
		$param .= '"pageSize":'.$this->filter_min_product_num.'}';
		return $this->hotel_service->getData($param);
	}

	private function writeLog($data = array()){
		if(empty($data['content'])) return false;
		if(empty($data['type'])) $data['type'] = 1;
		$data['createtime'] = date('Y-m-d H:i:s');
		$data['md5'] = $this->md5;
		$keys = array_keys($data);
		$sql = 'INSERT INTO `seo_batch_log`(`'.implode('`,`',$keys).'`) VALUES('.implode(',',array_fill_keys($keys,'?')).')';
		$this->seo_dest_keyword_srv->execute($sql,array_values($data));
	}
}