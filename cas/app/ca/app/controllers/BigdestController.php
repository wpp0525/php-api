<?php
use Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Components\ApiClient;
/**
 * 大目的地&长尾词变量链接的内容
 * @author mac.shenxiang
 */
class BigdestController extends ControllerBase {

	/**
	 * @var \Lvmama\Cas\Service\SeoVstRouteDataService
	 */
	private $route;
	/**
	 * @var \Lvmama\Cas\Service\SeoVstTicketDataService
	 */
	private $ticket;
	/**
	 * @var \Lvmama\Cas\Service\SeoVstHotelDataService
	 */
	private $hotel;
	/**
	 * @var \Lvmama\Cas\Service\DestinationDataService
	 */
	private $dest;
	protected $baseUri = 'http://ca.lvmama.com/';

	public function initialize() {
		parent::initialize();
		$this->client = new ApiClient($this->baseUri);
		$this->route = $this->di->get('cas')->get('seo_vst_route_service');
		$this->ticket = $this->di->get('cas')->get('seo_vst_ticket_service');
		$this->hotel = $this->di->get('cas')->get('seo_vst_hotel_service');
		$this->dest = $this->di->get('cas')->get('destination-data-service');
	}
	/**
	 * 获取导航部分的数据
	 * example http://ca.lvmama.com/bigdest/getNav
	 */
	public function getNavAction(){
		$dest_id = $this->request->get('dest_id');
		$type = $this->request->get('type');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","aggr":true,"routeType":"ROUTE"}';
		$tour = $this->route->getData($params);
		switch($type){
			case 'subject':
				$data = $this->getNavSubect($tour, 7, $dest_id);
				break;
			case 'routeNum':
				$data = $this->getNavRouterNum($tour, 7, $dest_id);
				break;
			case 'viewPoint':
				$data = $this->getNavViewPoint($tour, 6, $dest_id);
				break;
			case 'hotRound':
				$data = $this->getNavHotRound($tour, '11', $dest_id);
				break;
			default:
				$data = array();
		}
		$this->_successResponse($data);
	}

	/**
	 * 获取一级导航数据
	 */
	public function getoneNavinfoAction(){
		if(!$dest_id = $this->request->get('dest_id'))
			return $this->_errorResponse(10002,'请传入正确的dest_id');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","aggr":true,"routeType":"ROUTE"}';
		$tour = $this->route->getData($params);
		$result = array(
			array(
				'name' => '热门天数',
				'limit' => 7,
				'sort' => 1,
				'links' => $this->getNavRouterNum($tour, 7, $dest_id),
			),
			array(
				'name' => '热门周边',
				'limit' => 7,
				'sort' => 2,
				'links' => $this->getNavHotRound($tour, '7', $dest_id),
			),
			array(
				'name' => '热门景点',
				'limit' => 7,
				'sort' => 3,
				'links' => $this->getNavViewPoint($tour, 7, $dest_id),
			),
			array(
				'name' => '热门主题',
				'limit' => 7,
				'sort' => 4,
				'links' => $this->getNavSubect($tour, 7, $dest_id),
			),
		);
		return $this->_successResponse($result);
	}

	//热门周边
	private function getNavHotRound($tour,$num = 11, $dest_id = 1){
		$info = $this->destInfomation($dest_id);
		$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
		$url = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/route-I";
		$hotRound = array();
		$tmp_round = isset($tour['selectMap']['destId']) ? $tour['selectMap']['destId'] : array();
		$i = 1;
		foreach($tmp_round as $k=>$v){
			if($i >= $num){ break;}
			$hotRound[$k]['id'] = $v['name'];
			$hotRound[$k]['name'] = $k;
			$hotRound[$k]['url'] = $url . $v['name'];
			$i++;
		}
		return $hotRound;
	}
	//热门景点
	private function getNavViewPoint($tour, $num = 6, $dest_id = 1){
		$info = $this->destInfomation($dest_id);
		$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
		$url = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/route-V";
		$hotSpot = array();
		$viewPiont = isset($tour['selectMap']['viewPiont']) ? $tour['selectMap']['viewPiont'] : array();
		$i = 1;
		foreach($viewPiont as $k=>$v){
			if($i >= $num){ break;}
			$hotSpot[$k] = array('id' => $v['name'],'name' => $k);
			$hotSpot[$k]['url'] = $url . $v['name'];
			$i++;
		}
		return $hotSpot;
	}
	//行程天数
	private function getNavRouterNum($tour, $num = 7, $dest_id = 1){
		$info = $this->destInfomation($dest_id);
		$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
		$url = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/route-N";
		$routenum = isset($tour['selectMap']['routeNum']) ? $tour['selectMap']['routeNum'] : array();
		ksort($routenum);
		$data = array();
		$i = 1;
		foreach($routenum as $k=>$v){
			if($i > $num) break;
			$data[$k]['name'] = $k;
			$data[$k]['num'] = $v['num'];
			$data[$k]['url'] = $url . $v['name'];
			$i++;
		}
		return $data;
	}
	//导航主题
	private function getNavSubect($tour, $num = 7, $dest_id){
		$info = $this->destInfomation($dest_id);
		$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
		$url = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/route-J";
		$subject = array();
		//主题,最多显示7个
		$tmp_subject = isset($tour['selectMap']['subjectId']) ? $tour['selectMap']['subjectId'] : array();
		$i = 1;
		foreach($tmp_subject as $k=>$v) {
			if ($i >= $num) {break;}
			$subject[$k] = array('id' => $v['name'], 'name' => $k);
			$subject[$k]['url'] = $url . $v['name'];
			$i++;
		}
		return $subject;
	}
	/**
	 * 获取当季热卖
	 * 产品数据调用景点活动和促销活动中门票产品，景点活动优先于促销活动的门票产品，显示5条，
	 * 如果景点活动门票不足5条，则用促销活动中的门票产品来补
	 * example curl -XGET http://ca.lvmama.com/bigdest/getCurrSeasonHot?dest_id=1
	 */
	public function getCurrSeasonHotAction(){
		$dest_id = $this->request->get('dest_id');
		$num = $this->request->get('num');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$num = is_numeric($num) ? $num : 5;
		$params = '{"currentPage":1,"pageSize":'.$num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","activeFlag":"1"}';
		$viewSpotAct = $this->ticket->getData($params);
		$res = $rs = array();
		$rs = is_array($viewSpotAct['items']) ? $viewSpotAct['items'] : array();
		$len = count($rs);
		if($len < $num){
			$params = '{"currentPage":1,"pageSize":'.$num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","promotionFlag":"1"}';
			$promotionAct = $this->ticket->getData($params);
			if(isset($promotionAct['items'])){
				$rs = array_merge($rs,$promotionAct['items']);
			}
		}
		//如果数量还不够的话去掉上面的两个筛选条件
		$len = count($rs);
		if($len < $num){
			$params = '{"currentPage":1,"pageSize":'.$num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'"}';
			$tmpData = $this->ticket->getData($params);
			if(isset($tmpData['items'])){
				$rs = array_merge($rs,$tmpData['items']);
			}
		}
		$in_id = array();
		foreach($rs as $k=>$v){
			if(in_array($v['productId'],$in_id) || count($in_id) >= $num) continue;
			$in_id[] = $v['productId'];
			$res[$v['productId']] = array(
				'productId' => $v['productId'],
				'url'		=> $this->getUrl(
					$v['subCategoryId'] ? $v['subCategoryId'] : $v['categoryId'],
					$v['urlId'] ? $v['urlId'] : $v['productId']
				),
				'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$v['photoUrl'],'_300_200'),
				'CategoryName' => $v['categoryName'],
				'bizCategoryId' => $v['subCategoryId'] ? $v['subCategoryId'] : $v['categoryId'],
				'productName' => $v['productName'],
				'price'	=> $v['sellPrice'],
				'promotionTitle' => $v['promotionTitle'],
				'commentGood' => $v['commentGood']
			);
		}
		$this->_successResponse($res);
	}
	/**
	 * 奢华品质游
	 * 产品数据调用特卖会和促销活动中的度假线路产品，特卖会优先于促销活动的线路产品，显示5条，
	 * 如果特卖会线路不足5条，则用促销活动中的线路产品来补
	 * example curl -XGET http://ca.lvmama.com/bigdest/getLuxuriousTrip?dest_id=1
	 */
	public function getLuxuriousTripAction(){
		$dest_id = $this->request->get('dest_id');
		$num = $this->request->get('num');
		$filter_days = $this->request->get('filter_days');
		$filter_theme = $this->request->get('filter_theme');
		$filter_station = $this->request->get('filter_station');
		$filter_dest = $this->request->get('filter_dest');
		$routeType = $this->request->get('routeType');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$num = is_numeric($num) ? $num : 5;
		$filter_string = '';
		if($filter_days) $filter_string .= ',"routeNum":"'.$filter_days.'"';
		if($filter_theme) $filter_string .= ',"subjectId":"'.$filter_theme.'"';
		if($filter_station) $filter_string .= ',"districtId":"'.$filter_station.'"';
		if($filter_dest) $filter_string .= ',"destId":"'.$filter_dest.'"';
		$params = '{"currentPage":1,"pageSize":'.$num.',"distribution":"TEMAIDIS","destAll":"'.$dest_id.'","routeType":"'.($routeType ? $routeType : 'ROUTE').'"'.$filter_string.'}';
		$data = $this->route->getData($params);
		$res = $rs = array();
		$rs = isset($data['items']) ? $data['items'] : array();
		$len = count($rs);
		if($len < $num){
			$params = '{"currentPage":1,"pageSize":'.$num.',"destAll":"'.$dest_id.'","promotionFlag":"1","routeType":"'.($routeType ? $routeType : 'ROUTE').'"'.$filter_string.'}';
			$promotionAct = $this->route->getData($params);
			if(isset($promotionAct['items'])){
				$rs = array_merge($rs,$promotionAct['items']);
			}
		}
		//如果使用促销条件查询到的产品数量不够则去掉促销条件查询
		$len = count($rs);
		if($len < $num){
			$params = '{"currentPage":1,"pageSize":'.$num.',"destAll":"'.$dest_id.'","routeType":"'.($routeType ? $routeType : 'ROUTE').'"'.$filter_string.'}';
			$tmpData = $this->route->getData($params);
			if(isset($tmpData['items'])){
				$rs = array_merge($rs,$tmpData['items']);
			}
		}
		$in_id = array();
		foreach($rs as $k=>$v){
			if(in_array($v['productId'],$in_id) || count($in_id) >= $num) continue;
			$in_id[] = $v['productId'];
			$res[$v['productId']] = array(
				'productId' => $v['productId'],
				'url'		=> $this->getUrl(
					$v['subCategoryId'] ? $v['subCategoryId'] : $v['categoryId'],
					$v['urlId'] ? $v['urlId'] : $v['productId']
				),
				'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$v['photoUrl'],'_300_200'),
				'CategoryName' => $v['categoryName'],
				'bizCategoryId' => $v['subCategoryId'] ? $v['subCategoryId'] : $v['categoryId'],
				'productName' => $v['productName'],
				'price'	=> $v['sellPrice'],
				'promotionTitle' => $v['promotionTitle'],
				'commentGood' => $v['commentGood']
			);
		}
		$this->_successResponse($res);
	}
	/**
	 * 当地风情
	 * 二级栏目：默认调用游玩的天数，显示2条，优先调用产品数多的天数，再天数从小到大的逻辑调用
	 * 产品调用逻辑：相应栏目下产品调用其搜索列表的当地游产品，先取有促销活动的产品，再取好评数从高到低的产品，显示5条产品数，
	 * 如不够5条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example curl -XGET http://ca.lvmama.com/bigdest/getLocalPlay?dest_id=79&day_num=2&type=day
	 */
	public function getLocalPlayAction(){
		$dest_id = $this->request->get('dest_id');
		$day_num = $this->request->get('day_num');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$placeholder = $this->request->get('placeholder');//获取tab项的占位标记
		$type = $this->request->get('type');
		$filter_days = $this->request->get('filter_days');//筛选项中自定义的天数
		$filter_theme = $this->request->get('filter_theme');
		$filter_station = $this->request->get('filter_station');
		$filter_dest = $this->request->get('filter_dest');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10004,'请传入需要查询的type');
		$day_num = is_numeric($day_num) ? $day_num : 2;
		$subject_num = is_numeric($subject_num) ? $subject_num : 2;
		$list_num = is_numeric($list_num) ? $list_num : 4;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_days = is_numeric($filter_days) ? $filter_days : 0;
		$res = array();
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		switch($type){
			case 'day':
				$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","aggr":true,"routeType":"LOCAL"}';
				$tour = $this->route->getData($params);
				$tmp_days = isset($tour['selectMap']['routeNum']) ? $tour['selectMap']['routeNum'] : array();
				$tmp_days = $this->num_sort($tmp_days);
				$i = 0;
				$info = $this->destInfomation($dest_id);
				$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
				foreach($tmp_days as $k=>$v) {
					if ($i >= $day_num) {
						break;
					}
					$v['name'] = UCommon::changeNumToCn($v['name']) . '日游';
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/local-N{$v['id']}";
					$res[] = $v;
					$i++;
				}
				$this->_successResponse($res);
				break;
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","aggr":true,"routeType":"LOCAL"}';
				$tour = $this->route->getData($params);
				$tmp_subject = isset($tour['selectMap']['subjectId']) ? $tour['selectMap']['subjectId'] : array();
				//按照产品数降序排序
				$tmp = $this->num_sort($tmp_subject);
				$i = 1;
				$info = $this->destInfomation($dest_id);
				$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
				foreach($tmp as $k=>$v){
					if($i > $subject_num) break;
					$res[] = array(
						'id' => $v['id'],
						'name' => $v['name'],
						'url' => "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/local-J{$v['id']}",
					);
					$i++;
				}
				$this->_successResponse($res);
				break;
			case 'list':
				if($filter_days){//用户自定的数据优先
					$routeNum = $filter_days;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","aggr":true,"routeType":"LOCAL"}';
					$tour = $this->route->getData($params);
					$tmp_days = isset($tour['selectMap']['routeNum']) ? $tour['selectMap']['routeNum'] : array();
					$tmp_days = $this->num_sort($tmp_days);
					if(!isset($tmp_days[$placeholder - 1]['id'])){
						$this->_errorResponse(10005,'未找到与placeholder相符的tab项');
					}
					$routeNum = $tmp_days[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_theme){
					$filter_param .= ',"subjectId":"'.$filter_theme.'"';
				}
				if($filter_dest){
					$filter_param .= ',"destId":"'.$filter_dest.'"';
				}
				if($filter_station){
					$filter_param .= ',"districtId":"'.$filter_station.'"';
				}
				//促销活动的产品
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","routeNum":"'.$routeNum.'","routeType":"LOCAL","promotionFlag":"1"'.$filter_param.'}';
				$items = $this->route->getData($params);
				$list_items = array();
				if(isset($items['items'])){
					$list_items = $items['items'];
				}
				$items_num = count($list_items);
				//好评数降序的产品
				if($items_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num .',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","routeNum":"'.$routeNum.'","routeType":"LOCAL","sort":"cmtGoodDown"'.$filter_param.'}';
					$items = $this->route->getData($params);
					if(isset($items['items'])){
						$list_items = array_merge($list_items,$items['items']);
					}
				}
				$items_num = count($list_items);
				//数量不够,去掉筛选条件搜索
				if($items_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","routeNum":"'.$routeNum.'","routeType":"LOCAL"'.$filter_param.'}';
					$items = $this->route->getData($params);
					if(isset($items['items'])){
						$list_items = array_merge($list_items,$items['items']);
					}
				}
				//数量还不够,取该城市的省级目的地产品促销进行补齐
				$items_num = count($list_items);
				if($items_num < $list_num){
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$prov_dest['dest_id'].'","keyword":"'.$prov_dest['dest_name'].'","routeNum":"'.$routeNum.'","routeType":"LOCAL","promotionFlag":"1"'.$filter_param.'}';
					$items = $this->route->getData($params);
					if(isset($items['items'])){
						$list_items = array_merge($list_items,$items['items']);
					}
				}
				//数量还不够,取该城市的省级目的地产品好评率倒序进行补齐
				$items_num = count($list_items);
				if($items_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$prov_dest['dest_id'].'","keyword":"'.$prov_dest['dest_name'].'","routeNum":"'.$routeNum.'","routeType":"LOCAL","sort":"cmtGoodDown"'.$filter_param.'}';
					$items = $this->route->getData($params);
					if(isset($items['items'])){
						$list_items = array_merge($list_items,$items['items']);
					}
				}
				$in_id = array();//避免出现重复的产品
				foreach($list_items as $v){
					if(in_array($v['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $v['productId'];
					$res[] = array(
						'productId' => $v['productId'],
						'url'		=> $this->getUrl(
							$v['categoryId'],
							$v['urlId'] ? $v['urlId'] : $v['productId']
						),
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$v['photoUrl'],'_300_200'),
						'CategoryName' => $v['categoryName'],
						'bizCategoryId' => $v['categoryId'],
						'productName' => $v['productName'],
						'price'	=> $v['sellPrice'],
						'promotionTitle' => $v['promotionTitle'],
						'commentGood' => $v['commentGood']
					);
				}
				$this->_successResponse($res);
				break;
			default:
				$this->_errorResponse(10005,'此类型暂不支持');
				break;
		}
	}
	/**
	 * 酒店精品
	 * 调用度假酒店类别筛选中的参数，优先调用产品数多的类别参数，抽取其中2个，默认排序按产品数从大到小。
	 * 相应栏目下产品调用其搜索列表的酒店产品，先取有促销活动的产品，再取好评数从高到低的产品，显示5条产品数
	 * 如不够5条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * district_id
	 * example curl -XGET http://ca.lvmama.com/bigdest/getGoodHotel?dest_id=1
	 */
	public function getGoodHotelAction(){
		$dest_id = $this->request->get('dest_id');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');//获取tab项的占位标记
		$filter_theme = $this->request->get('filter_theme');//获取自定义主题的ID
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10004,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$district_id = $dest['district_id'];//行政区ID
		if(!$district_id) $this->_errorResponse(10005,'行政区ID不存在');
		$subject_num = is_numeric($subject_num) ? $subject_num : 2;
		$list_num = is_numeric($list_num) ? $list_num : 4;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_theme = is_numeric($filter_theme) ? $filter_theme : 0;
		$res = $tmp = array();
		$info = $this->destInfomation($dest_id);
		switch($type){
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"cityDistrictId":"'.$district_id.'","aggr":true}';
				$hotels = $this->hotel->getData($params);
				//酒店主题
				$tmp_subject = isset($hotels['resultMap']['resultMap']['hotelSubject']) ? $hotels['resultMap']['resultMap']['hotelSubject'] : array();
				$info = $this->destInfomation($dest_id);
				$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
				$district_id = isset($info['district_id']) ? $info['district_id'] : '';
				$dest_name =  isset($info['dest_name']) ? $info['dest_name'] : '';
				foreach($tmp_subject as $k => $v) {
					if ($k > $subject_num - 1) break;
					$v['name'] = $v['value'];
					$v['url'] = "http://s.lvmama.com/hotel/T{$v['id']}U{$district_id}?keyword={$dest_name}";
					$res[] = $v;
				}
				break;
			case 'list':
				if($filter_theme){
					$subject_id = $filter_theme;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"cityDistrictId":"'.$district_id.'","aggr":true}';
					$hotels = $this->hotel->getData($params);
					//酒店主题
					$tmp_subject = isset($hotels['resultMap']['resultMap']['hotelSubject']) ? $hotels['resultMap']['resultMap']['hotelSubject'] : array();
					foreach($tmp_subject as $k => $v) {
						$v['name'] = $v['value'];
						$tmp[] = $v;
					}
					if(!isset($tmp[$placeholder - 1]['id'])){
						$this->_errorResponse(10007,'未找到与placeholder相符的tab项');
					}
					$subject_id = $tmp[$placeholder - 1]['id'];
				}
				$list_item = array();
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"cityDistrictId":"'.$district_id.'","subjectIds":"'.$subject_id.'","promotionFlag":"1"}';
				$hotels = $this->hotel->getData($params);
				if(is_array($hotels['items'])){
					$list_item = $hotels['items'];
				}
				//数量不够,取好评率降序补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"cityDistrictId":"'.$district_id.'","subjectIds":"'.$subject_id.'","sort":"cmtGoodDown"}';
					$hotels = $this->hotel->getData($params);
					if(is_array($hotels['items'])){
						$list_item = array_merge($list_item,$hotels['items']);
					}
				}
				//数量还不够,去掉筛选条件补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"cityDistrictId":"'.$district_id.'","subjectIds":"'.$subject_id.'"}';
					$hotels = $this->hotel->getData($params);
					if(is_array($hotels['items'])){
						$list_item = array_merge($list_item,$hotels['items']);
					}
				}
				//如果城市本身取出来的数量不够,用省级促销进行补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"cityDistrictId":"'.$prov_dest['district_id'].'","subjectIds":"'.$subject_id.'","promotionFlag":"1"}';
					$hotels = $this->hotel->getData($params);
					if(is_array($hotels['items'])){
						$list_item = array_merge($list_item,$hotels['items']);
					}
				}
				//还不够,用省级好评率倒序补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"cityDistrictId":"'.$prov_dest['district_id'].'","subjectIds":"'.$subject_id.'","sort":"cmtGoodDown"}';
					$hotels = $this->hotel->getData($params);
					if(is_array($hotels['items'])){
						$list_item = array_merge($list_item,$hotels['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if($val['hotelProduct']){
						if(in_array($val['hotelProduct']['productId'],$in_id) || count($in_id) >= $list_num) continue;
						$in_id[] = $val['hotelProduct']['productId'];
						$res[] = array(
							'productId' => $val['hotelProduct']['productId'],
							'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['hotelProduct']['photoUrl'],'_300_200'),
							'CategoryName' => '酒店',
							'bizCategoryId' => 1,
							'url' => $this->getUrl(1,$val['hotelProduct']['productId']),
							'productName' => $val['hotelProduct']['productName'],
							'saleFlag'	=> $val['hotelProduct']['saleFlag'],
							'price'	=> $val['hotelProduct']['sellPrice'],
							'promotionTitle' => $val['promotionTitle'],
							'commentGood' => $val['commentGood']
						);
					}
				}
				break;
			default:
				$this->_errorResponse(10006,'暂不支持该类型');
		}
		$this->_successResponse($res);
	}
	/**
	 * 景点门票
	 * 默认调用门票筛选项中的主题，优先调用产品数多的主题参数，显示2条，默认排序按产品数从大到小
	 * 相应栏目下产品调用其搜索列表的门票产品，先取有促销活动的产品，再取好评数从高到低的产品，显示5条产品数
	 * 如不够5条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example curl -XGET http://ca.lvmama.com/bigdest/getTicket?dest_id=1&type_num=2&list_num=4
	 */
	public function getTicketAction(){
		$dest_id = $this->request->get('dest_id');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_theme = $this->request->get('filter_theme');//自定义主题ID
		$filter_dest = $this->request->get('filter_dest');
		$filter_station = $this->request->get('filter_station');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10004,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$subject_num = is_numeric($subject_num) ? $subject_num : 2;
		$list_num = is_numeric($list_num) ? $list_num : 4;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_theme = is_numeric($filter_theme) ? $filter_theme : 0;
		$res = $list_item = array();
		switch($type){
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true}';
				$data = $this->ticket->getData($params);
				//主题
				$tmp_subject = isset($data['selectMap']['subject']) ? $data['selectMap']['subject'] : array();
				$i = 0;
				$tmp_subject = $this->num_sort($tmp_subject);
				$info = $this->destInfomation($dest_id);
				$dest_pinyin = isset($info['pinyin']) ? $info['pinyin'] : '';
				foreach($tmp_subject as $v) {
					if ($i >= $subject_num) break;
					$v['url'] = "http://ticket.lvmama.com/a-{$dest_pinyin}{$dest_id}/tf-T{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($filter_theme){
					$subject_id = $filter_theme;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true}';
					$data = $this->ticket->getData($params);
					//主题
					$tmp_subject = isset($data['selectMap']['subject']) ? $data['selectMap']['subject'] : array();
					$tmp_subject = $this->num_sort($tmp_subject);
					if(!isset($tmp_subject[$placeholder - 1]['id'])){
						$this->_errorResponse(10005,'未找到与placeholder相符的tab项');
					}
					$subject_id = $tmp_subject[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_dest){
					$filter_param .= ',"COUNTY":"'.$filter_dest.'"';
				}
				//有景点活动的产品
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","keyword":"'.$dest['dest_name'].'","subjectId":"'.$subject_id.'","activeFlag":"1"'.$filter_param.'}';
				$data = $this->ticket->getData($params);
				if(is_array($data['items'])){
					$list_item = $data['items'];
				}
				//数量不够,取好评率倒序补全
				$item_num = count($list_item);
				if($item_num < $list_num) {
					$params = '{"currentPage":1,"pageSize":' . $list_num . ',"destAll":"' . $dest_id . '","keyword":"' . $dest['dest_name'] . '","subjectId":"' . $subject_id . '","sort":"cmtGoodDown"'.$filter_param.'}';
					$data = $this->ticket->getData($params);
					if (is_array($data['items'])) {
						$list_item = array_merge($list_item,$data['items']);
					}
				}
				//数量还不够,去掉筛选条件补全
				$item_num = count($list_item);
				if($item_num < $list_num) {
					$params = '{"currentPage":1,"pageSize":' . $list_num . ',"destAll":"' . $dest_id . '","keyword":"' . $dest['dest_name'] . '","subjectId":"' . $subject_id . '"'.$filter_param.'}';
					$data = $this->ticket->getData($params);
					if (is_array($data['items'])) {
						$list_item = array_merge($list_item,$data['items']);
					}
				}
				//数量还不够,取省级景点活动补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					//取省级产品
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$prov_dest['dest_id'].'","keyword":"'.$prov_dest['dest_name'].'","subjectId":"'.$subject_id.'","activeFlag":"1"'.$filter_param.'}';
					$data = $this->ticket->getData($params);
					if(is_array($data['items'])){
						$list_item = array_merge($list_item,$data['items']);
					}
				}
				//数量还不够,取省级景点好评率倒序补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$prov_dest['dest_id'].'","keyword":"'.$prov_dest['dest_name'].'","subjectId":"'.$subject_id.'","sort":"cmtGoodDown"'.$filter_param.'}';
					$data = $this->ticket->getData($params);
					if(is_array($data['items'])){
						$list_item = array_merge($list_item,$data['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if(in_array($val['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $val['productId'];
					$res[] = array(
						'productId' => $val['productId'],
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
						'url' => $this->getUrl($val['categoryId'],$val['urlId'] ? $val['urlId'] : $val['productId']),
						'productName' => $val['productName'],
						'saleFlag'	=> $val['saleFlag'],
						'CategoryName'	=> $val['categoryName'],
						'bizCategoryId'	=> $val['categoryId'],
						'price'	=> $val['sellPrice'],
						'commentGood' => $val['commentGood'],
						'promotionTitle' => $val['promotionTitle']
					);
				}

				break;
			default:
				$this->_errorResponse(10005,'暂不支持该类型');
		}
		$this->_successResponse($res);
	}
	/**
	 * 快乐自由行
	 * 第一个默认显示如果当前ip定位的出发城市在所录入的热门城市中，则显示该ip定位出来的城市，其他3条调用筛选列表上其他产品数多的出发城市，默认排序按产品数从大到小，如果当前ip定位城市没有存在热门城市库中，则4条都用筛选列表上的出发城市，按产品数从大到小排序
	 * 产品调用逻辑：相应栏目下的产品调用其搜索列表下的产品，先取有促销活动的产品，再取好评数从高到低的产品，显示8条产品数
	 * 如果不够8条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example curl -XGET http://ca.lvmama.com/bigdest/getFreetour?dest_id=1&type_num=4&subject_num=10&list_num=8
	 */
	public function getFreetourAction(){
		$dest_id = $this->request->get('dest_id');
		$dest_num = $this->request->get('dest_num');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_station = $this->request->get('filter_station');
		$filter_dest = $this->request->get('filter_dest');
		$filter_theme = $this->request->get('filter_theme');
		$filter_days = $this->request->get('filter_days');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$dest_num = is_numeric($dest_num) ? $dest_num : 4;
		$subject_num = is_numeric($subject_num) ? $subject_num : 10;
		$list_num = is_numeric($list_num) ? $list_num : 8;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_station = is_numeric($filter_station) ? $filter_station : 0;
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10004,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$res = array();
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : '';
		switch($type){
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"ZIYOUXING"}';
				$tour = $this->route->getData($params);
				$tmp_subject = isset($tour['selectMap']['subjectId']) ? $tour['selectMap']['subjectId'] : array();
				$tmp_subject = $this->num_sort($tmp_subject);
				$i = 0;
				foreach($tmp_subject as $v){
					if($i >= $subject_num) break;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/ziyouxing-J{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'dest':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"ZIYOUXING"}';
				$tour = $this->route->getData($params);
				$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
				$tmp_dest = $this->num_sort($tmp_dest);
				$i = 0;
				foreach($tmp_dest as $v){
					if($i >= $dest_num) break;
					if($v['id'] == 0) continue;//去掉不限出发地
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/ziyouxing-D{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($filter_station){
					$districtId = $filter_station;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"ZIYOUXING"}';
					$tour = $this->route->getData($params);
					$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
					$tmp_dest = $this->num_sort($tmp_dest);
					if(!isset($tmp_dest[$placeholder - 1]['id'])){
						$this->_errorResponse(10006,'未找到与placeholder相符的tab项');
					}
					$districtId = $tmp_dest[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_theme){
					$filter_param .= ',"subjectId":"'.$filter_theme.'"';
				}
				if($filter_dest){
					$filter_param .= ',"destId":"'.$filter_dest.'"';
				}
				if($filter_days){
					$filter_param .= ',"routeNum":"'.$filter_days.'"';
				}
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"ZIYOUXING","promotionFlag":"1","districtId":"'.$districtId.'"'.$filter_param.'}';
				$tour = $this->route->getData($params);
				$list_item = array();
				if(is_array($tour['items'])){
					$list_item = $tour['items'];
				}
				$item_num = count($list_item);
				//数量不够,按照好评率降序补全
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"ZIYOUXING","sort":"cmtGoodDown","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//还不够,去掉筛选条件补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"ZIYOUXING","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//还不够,用省级促销补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$prov_dest['dest_name'].'","destAll":"'.$prov_dest['dest_id'].'","routeType":"ZIYOUXING","promotionFlag":"1","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//还不够,用省级好评率倒序补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$prov_dest['dest_name'].'","destAll":"'.$prov_dest['dest_id'].'","routeType":"ZIYOUXING","sort":"cmtGoodDown","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if(in_array($val['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $val['productId'];
					$res[] = array(
						'productId' => $val['productId'],
						'url' => $this->getUrl(
							$val['categoryId'],
							$val['urlId'] ? $val['urlId'] : $val['productId']
						).'-D'.$districtId,
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
						'productName' => $val['productName'],
						'saleFlag'	=> $val['saleFlag'],
						'commentGood' => $val['commentGood'],
						'promotionTitle' => $val['promotionTitle'],
						'CategoryName'	=> $val['categoryName'],
						'bizCategoryId'	=> $val['categoryId'],
						'price'	=> $val['sellPrice']
					);
				}
				break;
			default:
				$this->_errorResponse(10005,'暂不支持该类型的查询');
		}
		$this->_successResponse($res);
	}
	/**
	 * 舒适跟团游
	 * 第一个默认显示如果当前ip定位的出发城市在所录入的热门城市中，则显示该ip定位出来的城市，其他3条调用筛选列表上其他产品数多的出发城市，默认排序按产品数从大到小，如果当前ip定位城市没有存在热门城市库中，则4条都用筛选列表上的出发城市，按产品数从大到小排序
	 * 相应栏目下的产品调用其搜索列表下的产品，先取有促销活动的产品，再取好评数从高到低的产品，显示8条产品数，如果不够8条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example curl -XGET http://ca.lvmama.com/bigdest/getGroup?dest_id=1
	 */
	public function getGroupAction(){
		$dest_id = $this->request->get('dest_id');
		$dest_num = $this->request->get('dest_num');
		$view_num = $this->request->get('view_num');
		$list_num = $this->request->get('list_num');
		$type 	  = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_station = $this->request->get('filter_station');//自定义出发城市
		$filter_theme = $this->request->get('filter_theme');
		$filter_dest = $this->request->get('filter_dest');
		$filter_days = $this->request->get('filter_days');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$dest_num = is_numeric($dest_num) ? $dest_num : 5;
		$view_num = is_numeric($view_num) ? $view_num : 5;
		$list_num = is_numeric($list_num) ? $list_num : 8;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_station = is_numeric($filter_station) ? $filter_station : 0;
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])) $this->_errorResponse(10003,'没有找到dest_id为'.$dest_id.'的基本信息');
		$res = array();
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : '';
		switch($type){
			case 'playMethod':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"GROUP"}';
				$tour = $this->route->getData($params);
				$tmp_view = isset($tour['selectMap']['playMethod']) ? $tour['selectMap']['playMethod'] : array();
				$tmp_view = $this->num_sort($tmp_view);
				$i = 0;
				foreach($tmp_view as $v){
					if($i >= $view_num) break;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/group-C{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'dest':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"GROUP"}';
				$tour = $this->route->getData($params);
				$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
				$tmp_dest = $this->num_sort($tmp_dest);
				$i = 0;
				foreach($tmp_dest as $v){
					if($i >= $dest_num) break;
					if($v['id'] == 0) continue;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/group-D{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($filter_station){
					$districtId = $filter_station;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"GROUP"}';
					$tour = $this->route->getData($params);
					$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
					$tmp_dest = $this->num_sort($tmp_dest);
					if(!isset($tmp_dest[$placeholder - 1]['id'])) $this->_errorResponse(10003,'没有找到与placeholder相符的TAB产品');
					$districtId = $tmp_dest[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_theme){
					$filter_param .= ',"subjectId":"'.$filter_theme.'"';
				}
				if($filter_dest){
					$filter_param .= ',"destId":"'.$filter_dest.'"';
				}
				if($filter_days){
					$filter_param .= ',"routeNum":"'.$filter_days.'"';
				}
				//促销
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"GROUP","promotionFlag":"1","districtId":"'.$districtId.'"'.$filter_param.'}';
				$tour = $this->route->getData($params);
				$list_item = is_array($tour['items']) ? $tour['items'] : array();
				//数量不够,改用好评率降序排序
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"GROUP","sort":"cmtGoodDown","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//数量不够,去掉条件进行补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"GROUP","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//数量不够,取省级促销进行补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$prov_dest = $this->getProvDest($dest);
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$prov_dest['dest_name'].'","destAll":"'.$prov_dest['dest_id'].'","routeType":"GROUP","promotionFlag":"1","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//数量不够,取省级好评率降序进行补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$prov_dest['dest_name'].'","destAll":"'.$prov_dest['dest_id'].'","routeType":"GROUP","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if(in_array($val['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $val['productId'];
					$res[] = array(
						'productId' => $val['productId'],
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
						'productName' => $val['productName'],
						'saleFlag'	=> $val['saleFlag'],
						'CategoryName'	=> $val['categoryName'],
						'bizCategoryId'	=> $val['categoryId'],
						'price'	=> $val['sellPrice'],
						'url' => $this->getUrl(
							$val['categoryId'],
							$val['urlId'] ? $val['urlId'] : $val['productId']
						).'-D'.$districtId,
						'commentGood' => $val['commentGood'],
						'promotionTitle' => $val['promotionTitle'],
						'fromDest' => $val['districtName'] ? $val['districtName'] : '全国'
					);
				}
				break;
			default:
				$this->_errorResponse(10005,'暂不支持该类型的查询');
		}
		$this->_successResponse($res);
	}
	/**
	 * 浪漫景酒
	 * 默认调用筛选列表中的目的地数据，显示4条，按目的地相应的产品数大小排序
	 * 左侧图片位置数据：调用筛选列表上的线路主题数据，显示5条，按线路主题相应的产品数从大到小排序
	 * 产品调用逻辑：相应栏目下的产品调用其搜索列表下的产品，先取有促销活动的产品，再取好评数从高到低的产品，显示8条产品数，如果不够8条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example curl -XGET http://ca.lvmama.com/bigdest/getRomantic?dest_id=1
	 */
	public function getRomanticAction(){
		$dest_id = $this->request->get('dest_id');
		$dest_num = $this->request->get('dest_num');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_dest = $this->request->get('filter_dest');
		$filter_theme = $this->request->get('filter_theme');
		$filter_days = $this->request->get('filter_days');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传type');
		$dest_num = is_numeric($dest_num) ? $dest_num : 5;
		$subject_num = is_numeric($subject_num) ? $subject_num : 5;
		$list_num = is_numeric($list_num) ? $list_num : 8;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_dest = is_numeric($filter_dest) ? $filter_dest : 0;
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])) $this->_errorResponse(10004,'没有找到dest_id为'.$dest_id.'的基本信息');
		$res = array();
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : "";
		switch($type){
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"SCENICTOUR"}';
				$tour = $this->route->getData($params);
				$tmp_subject = isset($tour['selectMap']['subjectId']) ? $tour['selectMap']['subjectId'] : array();
				$tmp_subject = $this->num_sort($tmp_subject);
				$i = 0;
				foreach($tmp_subject as $v){
					if($i >= $subject_num) break;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/scenictour-J{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'dest':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"SCENICTOUR"}';
				$tour = $this->route->getData($params);
				$tmp_dest = isset($tour['selectMap']['destId']) ? $tour['selectMap']['destId'] : array();
				$tmp_dest = $this->num_sort($tmp_dest);
				$i = 0;
				foreach($tmp_dest as $v){
					if($i >= $dest_num) break;
					if($v['id'] == 0) continue;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/scenictour-I{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($filter_dest){
					$destId = $filter_dest;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"SCENICTOUR"}';
					$tour = $this->route->getData($params);
					$tmp_dest = isset($tour['selectMap']['destId']) ? $tour['selectMap']['destId'] : array();
					$tmp_dest = $this->num_sort($tmp_dest);
					if(!isset($tmp_dest[$placeholder - 1]['id'])) $this->_errorResponse(10005,'没有与placeholder相符的TAB');
					$destId = $tmp_dest[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_theme){
					$filter_param .= ',"subjectId":"'.$filter_theme.'"';
				}
				if($filter_days){
					$filter_param .= ',"routeNum":"'.$filter_days.'"';
				}
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"SCENICTOUR","promotionFlag":"1","destId":"'.$destId.'"'.$filter_param.'}';
				$tour = $this->route->getData($params);
				$list_item = is_array($tour['items']) ? $tour['items'] : array();
				//促销产品的量不够
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"SCENICTOUR","sort":"cmtGoodDown","destId":"'.$destId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//如果量还不够,去掉筛选条件进行获取
				$item_num = count($list_item);
				if($item_num < $list_num){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"SCENICTOUR","destId":"'.$destId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if(in_array($val['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $val['productId'];
					$res[] = array(
						'productId' => $val['productId'],
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
						'productName' => $val['productName'],
						'saleFlag'	=> $val['saleFlag'],
						'CategoryName'	=> $val['categoryName'],
						'bizCategoryId'	=> $val['subCategoryId'] ? $val['subCategoryId'] : $val['categoryId'],
						'price'	=> $val['sellPrice'],
						'url' => $this->getUrl(
							$val['categoryId'],
							$val['urlId'] ? $val['urlId'] : $val['productId']
						),
						'commentGood' => $val['commentGood'],
						'promotionTitle' => $val['promotionTitle']
					);
				}
				break;
			default:
				$this->_errorResponse(10005,'该类型暂不支持');
		}
		$this->_successResponse($res);
	}
	/**
	 * 约惠机酒
	 * 二级栏目：第一个默认显示如果当前ip定位的出发城市在所录入的热门城市中，则显示该ip定位出来的城市，其他3条调用筛选列表上其他产品数多的出发城市，默认排序按产品数从大到小，如果当前ip定位城市没有存在热门城市库中，则4条都用筛选列表上的出发城市，按产品数从大到小排序
	 * 左侧图片位置数据：默认调用筛选列表上的线路玩法数据，显示其中5个，按线路玩法相应的产品数大小排序，
	 * 产品调用逻辑：相应栏目下的产品调用其搜索列表下的产品，先取有促销活动的产品，再取好评数从高到低的产品，显示8条产品数，如果不够8条，则进行省级补全，在后台产品库中显示补标签，区别自动调用的产品
	 * example -XGET http://ca.lvmama.com/bigdest/getPlaneHotel?dest_id=1
	 */
	public function getPlaneHotelAction(){
		$dest_id = $this->request->get('dest_id');
		$dest_num = $this->request->get('dest_num');
		$view_num = $this->request->get('view_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_station = $this->request->get('filter_station');
		$filter_theme = $this->request->get('filter_theme');
		$filter_dest = $this->request->get('filter_dest');
		$filter_days = $this->request->get('filter_days');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$dest_num = is_numeric($dest_num) ? $dest_num : 4;
		$view_num = is_numeric($view_num) ? $view_num : 5;
		$list_num = is_numeric($list_num) ? $list_num : 8;
		$placeholder = is_numeric($placeholder) ? $placeholder : 1;
		$filter_station = is_numeric($filter_station) ? $filter_station : 0;
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])) $this->_errorResponse(10004,'没有找到dest_id为'.$dest_id.'的基本信息');
		$res = array();
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : "";
		switch($type){
			case 'playMethod':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"FREETOUR"}';
				$tour = $this->route->getData($params);
				//线路玩法
				$tmp_view = isset($tour['selectMap']['playMethod']) ? $tour['selectMap']['playMethod'] : array();
				$tmp_view = $this->num_sort($tmp_view);
				$i = 0;
				foreach($tmp_view as $v){
					if($i >= $view_num) break;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/freetour-C{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'dest':
				$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"FREETOUR"}';
				$tour = $this->route->getData($params);
				$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
				$tmp_dest = $this->num_sort($tmp_dest);
				$i = 0;
				foreach($tmp_dest as $v){
					if($i >= $dest_num) break;
					if($v['id'] == 0) continue;
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/freetour-D{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($filter_station){
					$districtId = $filter_station;
				}else{
					$params = '{"currentPage":1,"pageSize":1,"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","aggr":true,"routeType":"FREETOUR"}';
					$tour = $this->route->getData($params);
					$tmp_dest = isset($tour['selectMap']['districtId']) ? $tour['selectMap']['districtId'] : array();
					$tmp_dest = $this->num_sort($tmp_dest);
					if(!isset($tmp_dest[$placeholder - 1]['id'])) $this->_errorResponse(10005,'没有与该placeholder相应的TAB');
					$districtId = $tmp_dest[$placeholder - 1]['id'];
				}
				$filter_param = '';
				if($filter_theme){
					$filter_param .= ',"subjectId":"'.$filter_theme.'"';
				}
				if($filter_dest){
					$filter_param .= ',"destId":"'.$filter_dest.'"';
				}
				if($filter_days){
					$filter_param .= ',"routeNum":"'.$filter_days.'"';
				}
				//促销
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"FREETOUR","promotionFlag":"1","districtId":"'.$districtId.'"'.$filter_param.'}';
				$tour = $this->route->getData($params);
				$list_item = is_array($tour['items']) ? $tour['items'] : array();
				//不够,好评率倒序补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					//好评降序
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"FREETOUR","sort":"cmtGoodDown","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				//还不够,去掉筛选条件补全
				$item_num = count($list_item);
				if($item_num < $list_num){
					//好评降序
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"keyword":"'.$dest['dest_name'].'","destAll":"'.$dest_id.'","routeType":"FREETOUR","districtId":"'.$districtId.'"'.$filter_param.'}';
					$tour = $this->route->getData($params);
					if(is_array($tour['items'])){
						$list_item = array_merge($list_item,$tour['items']);
					}
				}
				$in_id = array();
				foreach($list_item as $val){
					if(in_array($val['productId'],$in_id) || count($in_id) >= $list_num) continue;
					$in_id[] = $val['productId'];
					$res[] = array(
						'productId' => $val['productId'],
						'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
						'productName' => $val['productName'],
						'saleFlag'	=> $val['saleFlag'],
						'url' => $this->getUrl(
							$val['categoryId'],
							$val['urlId'] ? $val['urlId'] : $val['productId']
						).'-D'.$districtId,
						'commentGood' => $val['commentGood'],
						'CategoryName'	=> $val['categoryName'],
						'bizCategoryId'	=> $val['subCategoryId'] ? $val['subCategoryId'] : $val['categoryId'],
						'promotionTitle' => $val['promotionTitle'],
						'price'	=> $val['sellPrice']
					);
				}
				break;
			default:
				$this->_errorResponse(10005,'暂不支持该type');
		}
		$this->_successResponse($res);
	}
	/**
	 * 热门目的地
	 * example http://ca.lvmama.com/bigdest/getHotCitys?dest_id=79
	 */
	public function getHotCitysAction(){
		$dest_id = $this->request->get('dest_id');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(1003,'请传入正确的dest_id');
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(1003,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$citys = $this->route->getHotDest($dest);
		$this->_successResponse($citys);
	}
	/**
	 * 游记攻略
	 * example curl -XGET http://ca.lvmama.com/bigdest/getTrip?dest_id=1
	 */
	public function getTripAction(){
		$dest_id = $this->request->get('dest_id');
		$page 	 = $this->request->get('page');
		$pageSize = $this->request->get('pageSize');
		if(!$dest_id || !is_numeric($dest_id)){
			$this->_errorResponse(10002,'请传入正确的dest_id');
		}
		$page = is_numeric($page) ? $page : 1;
		$pageSize = is_numeric($pageSize) ? $pageSize : 8;
		$trips = $this->route->getTrip($dest_id,$page,$pageSize);
		$this->_successResponse($trips);
	}
	/**
	 * 长尾词门票主题模块
	 * example curl http://ca.lvmama.com/bigdest/getTicketSubject?dest_id=1
	 */
	public function getTicketSubjectAction(){
		$dest_id = $this->request->get('dest_id');
		$subject_num = $this->request->get('subject_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_theme = $this->request->get('filter_theme');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$subject_num = is_numeric($subject_num) ? $subject_num : 5;
		$list_num = is_numeric($list_num) ? $list_num : 7;
		$placeholder = is_numeric($placeholder) ? $placeholder : 0;
		$filter_theme = is_numeric($filter_theme) ? $filter_theme : 0;
		$res = array();
		$dest = $this->dest->getDestById($dest_id);
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : '';
		switch($type){
			case 'subject':
				$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","aggr":true}';
				$data = $this->ticket->getData($params);
				//主题
				$tmp_subject = isset($data['selectMap']['subject']) ? $data['selectMap']['subject'] : array();
				$i = 0;
				//加载默认第一项
				foreach($tmp_subject as $k => $v) {
					if ($i >= $subject_num) break;
					$v['id'] = $v['name'];
					$v['name'] = $k;
					$v['url'] = "http://ticket.lvmama.com/a-{$dest_pinyin}{$dest_id}/tf-T{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				if($placeholder){//占位
					$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","aggr":true}';
					$data = $this->ticket->getData($params);
					//主题
					$tmp_subject = isset($data['selectMap']['subject']) ? $data['selectMap']['subject'] : array();
					$tmp = array();
					foreach($tmp_subject as $k => $v) {
						$v['id'] = $v['name'];
						$v['name'] = $k;
						$tmp[] = $v;
					}
					if(!isset($tmp[$placeholder - 1]['id'])) $this->_errorResponse(10004,'无placeholder对应的id');
					$subjectId = $tmp[$placeholder - 1]['id'];
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","subjectId":"'.$subjectId.'"}';
				} elseif($filter_theme){//自定义筛选项
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","subjectId":"'.$filter_theme.'"}';
				} else {//不限条件
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'"}';
				}
				$data = $this->ticket->getData($params);
				if($data['items']){
					foreach($data['items'] as $val){
						$res[] = array(
							'productId' => $val['productId'],
							'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
							'productName' => $val['productName'],
							'saleFlag'	=> $val['saleFlag'],
							'CategoryName'	=> $val['categoryName'],
							'bizCategoryId'	=> $val['subCategoryId'] ? $val['subCategoryId'] : $val['categoryId'],
							'commentGood' => $val['commentGood'],
							'url' => $this->getUrl(
								$val['categoryId'],
								$val['urlId'] ? $val['urlId'] : $val['productId']
							),
							'price'	=> $val['sellPrice']
						);
					}
				}
				break;
			default:
				$this->_errorResponse(10005,'暂不支持此类型');
		}
		$this->_successResponse($res);
	}
	/**
	 * 长尾词线路出游天数
	 * example curl -XGET http://ca.lvmama.com/bigdest/getRouteDays?dest_id=1
	 */
	public function getRouteDaysAction(){
		$dest_id = $this->request->get('dest_id');
		$day_num = $this->request->get('day_num');
		$list_num = $this->request->get('list_num');
		$type = $this->request->get('type');
		$placeholder = $this->request->get('placeholder');
		$filter_days = $this->request->get('filter_days');
		$filter_dest = $this->request->get('filter_dest');
		$filter_station = $this->request->get('filter_station');
		$filter_theme = $this->request->get('filter_theme');
		$routeType = $this->request->get('routeType');
		if(!$dest_id) $this->_errorResponse(10002,'请传入dest_id');
		$tmp = explode(',',$dest_id);
		$dest_id = $tmp[0];
		if(!$type) $this->_errorResponse(10003,'请传入type');
		$day_num = is_numeric($day_num) ? $day_num : 6;
		$list_num = is_numeric($list_num) ? $list_num : 4;
		$placeholder = is_numeric($placeholder) ? $placeholder : 0;
		$filter_days = is_numeric($filter_days) ? $filter_days : 0;
		$filter_dest = is_numeric($filter_dest) ? $filter_dest : 0;
		$filter_station = is_numeric($filter_station) ? $filter_station : 0;
		$filter_theme = is_numeric($filter_theme) ? $filter_theme : 0;
		$res = array();
		$dest = $this->dest->getDestById($dest_id);
		$dest_pinyin = isset($dest['pinyin']) ? $dest['pinyin'] : '';
		switch($type){
			case 'day':
				$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","aggr":true,"routeType":"'.($routeType ? $routeType : 'ROUTE').'"}';
				$data = $this->route->getData($params);
				//行程天数
				$tmp_days = isset($data['selectMap']['routeNum']) ? $data['selectMap']['routeNum'] : array();
				$tmp_days = $this->num_sort($tmp_days);
				$i = 0;
				$res[] = array('name' => '推荐线路','num' => '','id' => '','url' => '');
				foreach($tmp_days as $k=>$v) {
					if ($i >= $day_num) break;
					$v['name'] = UCommon::changeNumToCn($v['name']) . '日游';
					$v['url'] = "http://dujia.lvmama.com/tour/{$dest_pinyin}{$dest_id}/".($routeType ? strtolower($routeType) : 'route')."-N{$v['id']}";
					$res[] = $v;
					$i++;
				}
				break;
			case 'list':
				$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","routeType":"'.($routeType ? $routeType : 'ALL').'"}';
				if(!$filter_days && $placeholder){
					$params = '{"currentPage":1,"pageSize":1,"destAll":"'.$dest_id.'","aggr":true,"routeType":"'.($routeType ? $routeType : 'ALL').'"}';
					$data = $this->route->getData($params);
					//行程天数
					$tmp_days = isset($data['selectMap']['routeNum']) ? $data['selectMap']['routeNum'] : array();
					$tmp_days = $this->num_sort($tmp_days);
					if(!isset($tmp_days[$placeholder - 1]['id'])) $this->_errorResponse(10005,'无placeholder相应的筛选项');
					$routeNum = $tmp_days[$placeholder-1]['id'];
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","routeNum":"'.$routeNum.'","routeType":"'.($routeType ? $routeType : 'ALL').'"}';
				}
				if($filter_days || $filter_theme || $filter_dest || $filter_station){
					$params = '{"currentPage":1,"pageSize":'.$list_num.',"destAll":"'.$dest_id.'","routeType":"'.($routeType ? $routeType : 'ALL').'"';
					if($filter_days) $params .= ',"routeNum":"'.$filter_days.'"';
					if($filter_theme) $params .= ',"subjectId":"'.$filter_theme.'"';
					if($filter_dest) $params .= ',"destId":"'.$filter_dest.'"';
					if($filter_station) $params .= ',"districtId":"'.$filter_station.'"';
					$params .= '}';
				}
				$data = $this->route->getData($params);
				if($data['items']){
					foreach($data['items'] as $val){
						$res[] = array(
							'productId' => $val['productId'],
							'img_url'	=> UCommon::makePicSize2('http://pic.lvmama.com'.$val['photoUrl'],'_300_200'),
							'productName' => $val['productName'],
							'CategoryName'	=> $val['categoryName'],
							'bizCategoryId'	=> $val['subCategoryId'] ? $val['subCategoryId'] : $val['categoryId'],
							'url' => $this->getUrl(
								$val['categoryId'],
								$val['urlId'] ? $val['urlId'] : $val['productId']
							).($filter_station ? '-D'.$filter_station : ''),
							'commentGood' => $val['commentGood'],
							'promotionTitle' => $val['promotionTitle'],
							'price'	=> $val['sellPrice']
						);
					}
				}
				break;
			default:
				$this->_errorResponse(10005,'暂不支持此类型');
		}
		$this->_successResponse($res);
	}

	/**
	 * 获取长尾词的TDK
	 * example curl -XGET http://ca.lvmama.com/bigdest/getTdk?type=lvyou
	 */
	public function getTdkAction(){
		$category_id = $this->request->get('category_id');
		$dest_id = $this->request->get('dest_id');
		$dest_name = $this->request->get('dest_name');
		$keyword = $this->request->get('keyword');
		$word_root = $this->request->get('word_root');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$category_id || !is_numeric($category_id)) $this->_errorResponse(10003,'请传入正确的category_id');
		//找到分类对应的tdk的key值
		$seo_dest_category = $this->di->get('cas')->get('seo_dest_category_service');
		$data = $seo_dest_category->getTdkByCategory($category_id,$dest_id,$dest_name,$keyword,$word_root);
		if(!$data){
			$this->_errorResponse(10004,'获取TDK接口的返回值为空');
		}
		$this->_successResponse($data);
	}

	/**
	 * 公共头部
	 * example curl -XGET http://ca.lvmama.com/bigdest/getCommonHeader
	 */
	public function getCommonHeaderAction(){
		$seo_dest_category = $this->di->get('cas')->get('seo_dest_category_service');
		$data = $seo_dest_category->getCommonHeader();
		$this->_successResponse($data);
	}

	/**
	 * 清除redis中的指定数据(当紧急情况下使用)
	 */
	public function clearRedisAction(){
		$token = $this->request->get('token');
		$key = $this->request->get('key');
		if($token == 'c271bfe201116ece85d3e397f7e4eb69'){
			echo $this->route->clearRedis($key) ? 'ok' : 'no';
		}
	}
	/**
	 * 根据请求URL返回产品的分类及筛选参数名称
	 * @param url
	 * @return json
	 * @example curl -XGET 'http://ca.lvmama.com/bigdest/getTypeByUrl'
	 */
	public function getTypeByUrlAction(){
		$url = urldecode($this->request->get('url'));
		if(!$url) $this->_errorResponse(10001,'请传入url');
		if(!filter_var($url, FILTER_VALIDATE_URL)) $this->_errorResponse(10002,'请传入正确的url');
		$routeType = '';
		if(stripos($url,'getRouteDays') !== false || stripos($url,'getLuxuriousTrip') !== false){
			$routeType = 'ROUTE';
		}else if(stripos($url,'getTicketSubject') !== false || stripos($url,'getTicket') !== false || stripos($url,'getCurrSeasonHot') !== false){
			$routeType = 'TICKET';
		}else if(stripos($url,'getGoodHotel') !== false){
			$routeType = 'HOTEL';
		}else if(stripos($url,'getLocalPlay') !== false){
			$routeType = 'LOCAL';
		}else if(stripos($url,'getGroup') !== false){
			$routeType = 'GROUP';
		}else if(stripos($url,'getFreetour') !== false){
			$routeType = 'ZIYOUXING';
		}else if(stripos($url,'getRomantic') !== false){
			$routeType = 'SCENICTOUR';
		}else if(stripos($url,'getPlaneHotel') !== false){
			$routeType ='FREETOUR';
		}
		$type = '';
		$query_string = parse_url($url,PHP_URL_QUERY);
		parse_str($query_string,$output);
		if(isset($output['type'])){
			switch($output['type']){
				case 'dest':
					$type = $routeType == 'SCENICTOUR' ? 'filter_dest' : 'filter_station';
					break;
				case 'day':
				case 'days':
					$type = 'filter_days';
					break;
				case 'subject':
					$type = 'filter_theme';
					break;
				case 'playMethod':
					$type = 'playMethod';
					break;
			}
		}
		$return = array('routeType' => $routeType,'type' => $type);
		$this->_successResponse($return);
	}

	/**
	 * 后台关键字筛选项内容获取
	 * example curl -XGET http://ca.lvmama.com/bigdest/getFilterContent
	 */
	public function getFilterContentAction(){
		$var_name = $this->request->get('var_name');
		$dest_id = $this->request->get('dest_id');
		$param = $this->request->get('param');
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		if(!$var_name) $this->_errorResponse(10003,'请传入var_name');
		if(!$param) $this->_errorResponse(10004,'请传入param');
		//先取得城市的区ID
		$dest = $this->dest->getDestById($dest_id);
		if(!isset($dest['dest_id'])){
			$this->_errorResponse(10005,'没有找到dest_id为'.$dest_id.'的基本信息');
		}
		$rs = $this->route->getFilterContent($var_name,$dest,$param);
		$this->_successResponse($rs);
	}
	/**
	 * 获取长尾词页面的热门精选
	 * @param dest_id 目的地ID
     * @param keyword_id 关键词ID
	 * @return json
	 * @example curl -XGET  'http://ca.lvmama.com/bigdest/getHotSelect'
	 */
    public function getHotSelectAction(){
        $dest_id = $this->request->get('dest_id');
        $keyword_id = $this->request->get('keyword_id');
        if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10001,'请传入正确的dest_id');
        if(!$keyword_id || !is_numeric($keyword_id)) $this->_errorResponse(10002,'请传入正确的keyword_id');

        $conditions = array();
        $seo_dest_keyword_svc = $this->di->get('cas')->get('seo_dest_keyword_service');
        !empty($keyword_id) && $conditions['keyword_id'] = "=" . $keyword_id;
        !empty($conditions) && $keyword_info = $seo_dest_keyword_svc->getOneKeyword($conditions);
        if(empty($keyword_info)) {
            $this->_errorResponse(10003,'大目的地关键词信息不存在');
        }

        $seo_dest_category_service = $this->di->get('cas')->get('seo_dest_category_service');
        $conditions = array();
        !empty($keyword_info['category_id']) && $conditions['category_id'] = "=" . $keyword_info['category_id'];
        $categoryInfo = $seo_dest_category_service->getOneCate($conditions);
        if (empty($categoryInfo)) {
            $this->_errorResponse(10004, '大目的地关键词分类不存在');
        }
        $url = $categoryInfo['category_url'] . '/' . $keyword_info['keyword_pinyin'];
        if ($keyword_info['long_tail']) {
            $url .= $keyword_info['keyword_id'];
        } else {
            $url .= $keyword_info['dest_id'];
        }
        $keyword_url_related = $this->di->get('cas')->get('seo_keyword_url_related_service');
        $return = $keyword_url_related->getUrlRelateKeywordLinks($url);
        $this->_successResponse($return);
    }

	/**
	 * 根据筛选项Label的中文名转为对应的筛选项英文Label
	 * @param $name
	 * @return string
	 */
	public function getFilterTitleAction(){
		$name = rawurldecode($this->request->get('name'));
		$return = '';
		switch($name){
			case '产品类型':
				$return = 'routeType';
				break;
			case 'TAB选项':
				$return = 'Tab';
				break;
			case '关键词':
				$return = 'Keyword';
				break;
			case '主题':
				$return = 'filter_theme';
				break;
			case '出发地':
				$return = 'filter_station';
				break;
			case '目的地':
				$return = 'filter_dest';
				break;
			case '游玩天数':
				$return = 'filter_days';
				break;
			case '展现数量':
				$return = 'num';
				break;
		}
		$this->_successResponse($return);
	}

	/**
	 * 根据不同线路类型的筛选项名称获取其对应的值
	 * @param $productType 产品类型
	 * @param $filterName 筛选项名称
	 * @return string
	 */
	public function getFilterValueAction(){
		$product_type = $this->request->get('productType');
		$filter_type = $this->request->get('filterType');
		$filter_name = $this->request->get('filterName');
		$dest_id = $this->request->get('dest_id');
		if(
			!$product_type ||
			!$filter_type ||
			!$filter_name
		) $this->_errorResponse(10001,'productType、filterType和filterName均不能为空');
		$return = '';
		if(!$dest_id) $dest_id = 0;
		//根据不同类型的产品调用不同类型的接口
		switch(strtoupper($product_type)){
			case 'ROUTE':
			case 'FREETOUR':
			case 'SCENICTOUR':
			case 'GROUP':
			case 'AROUND':
			case 'LOCAL':
			case 'ZIYOUXING':
				$return = $this->route->getFilterValue($filter_name,$filter_type,$dest_id);
				break;
			case 'TICKET':
				$return = $this->ticket->getFilterValue($filter_name,$filter_type,$dest_id);
				break;
			case 'HOTEL':
				$return = $this->hotel->getFilterValue($filter_name,$filter_type,$dest_id);
				break;
		}
		$this->_successResponse($return);
	}

	/**
	 * 根据产品类型中文名转为对应的英文参数
	 * @param $name
	 * @return string
	 */
	public function getProductTypeAction(){
		$name = rawurldecode($this->request->get('name'));
		$return = '';
		switch($name){
			case '全部线路':
				$return = 'ROUTE';
				break;
			case '门票':
				$return = 'TICKET';
				break;
			case '酒店':
				$return = 'HOTEL';
				break;
			case '机+酒':
				$return = 'FREETOUR';
				break;
			case '景+酒':
				$return = 'SCENICTOUR';
				break;
			case '出发地跟团游':
				$return = 'GROUP';
				break;
			case '周边跟团游':
				$return = 'AROUND';
				break;
			case '目的地跟团游':
				$return = 'LOCAL';
				break;
			case '自由行':
				$return = 'ZIYOUXING';
				break;
		}
		$this->_successResponse($return);
	}
	/**
	 * 根据页面的筛选项规则重新获取产品
	 */
	public function getNewProductByFilterAction(){
		$kid = $this->request->get('keyword_id');
		if(!$kid || !is_numeric($kid)) $this->_errorResponse(10001,'keyword_id不合法');
		$pp_srv = $this->di->get('cas')->get('product_pool_data');
		$pp_srv->refreshByFilter($kid);
		echo 'success';
	}
	/**
	 * 刷新指定页面的产品数据
	 */
	public function refreshProductBySpmAction(){
		$spm = $this->request->get('spm');
		$product_info = $this->di->get('cas')->get('product-info-data-service');
		$product_info->refreshBySpm($spm);
		echo 'success';
	}

	/**
	 * 根据产品ID和所属类型获取URL
	 * @param $categoryId
	 * @param $productId
	 * @return string
	 */
	private function getUrl($categoryId,$productId){
		return $this->route->getUrl($productId,$categoryId);
	}

	/**
	 * 获取目的地的省级目的地基础信息
	 * @param $data
	 */
	private function getProvDest($data){
		if(!$data || !$data['parent_id']) return array();
		//省以上直接返回自己
		if(in_array($data['dest_type'],array('CONTINENT','SPAN_COUNTRY','COUNTRY'))) return $data;
		//直辖市,特别行政区返回自己
		if(in_array(intval($data['dest_id']),array(1,2,79,277,398,400))) return $data;
		while($data['dest_type'] != 'PROVINCE' && $data['dest_type'] != 'SPAN_PROVINCE'){
			$data = $this->dest->getDestById($data['parent_id']);
		}
		return $data;
	}
	/**
	 * 将目的地或者出发地的筛选项按照产品数进行倒序排序
	 * @param $arr 需要排序的筛选项数据
	 * @return array
	 */
	private function num_sort($arr){
		$tmp = array();
		foreach($arr as $k=>$v){
			if($v['name'] == '0') continue;//过滤掉不限出发地
			$tmp[] = array('name' => $k,'num' => $v['num'],'id' => $v['name']);
		}
		$len = count($tmp);
		for($i = 0;$i < $len;$i++){
			for($j = $i+1;$j < $len;$j++){
				if($tmp[$i]['num'] < $tmp[$j]['num']){
					$tmp_row = $tmp[$j];
					$tmp[$j] = $tmp[$i];
					$tmp[$i] = $tmp_row;
				}
			}
		}
		return $tmp;
	}

	/**
	 * get dest_id infomation
	 * @param	$dest_id int 目的地 id
	 * @return ini dest_id infomation
	 */
	private function destInfomation($dest_id){
		return $this->di->get('cas')->get('destin_base_service')->getOneDest(array(
			'dest_id' => '='.$dest_id,
		));
	}
}
