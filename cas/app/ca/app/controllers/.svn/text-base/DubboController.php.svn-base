<?php 
use Lvmama\Cas\Service\RedisDataService;
/**
* dubbo接口
*/
class DubboController extends ControllerBase
{
	private $dest_product_rel_service;
	private $tsrv_svc;
    protected $redis_svc;
    private $poiTypeName = array(
    	'ADULT' => '成人票',
    	'LOVER' => '情侣票',
    	'FAMILY' => '家庭票',
    	'CHILDREN' => '儿童票',
    	'PARENTAGE' => '亲子票',
    	'COUPE' => '双人票',
    	'OLDMAN' => '老人票',
    	'STUDENT' => '学生票',
    	'ACTIVITY' => '优待票',
    	'SOLDIER' => '军人票',
    	'WOMAN' => '女士票',
    	'TEACHER' => '教师票',
    	'DISABILITY' => '残疾票',
    	'GROUP' => '团体票',
    	'FREE' => '相关票'
    );

	public function initialize()
    {
		parent::initialize();
		$this->dest_product_rel_service = $this->di->get('cas')->get('dest_product_rel_service');
		$this->tsrv_svc = $this->di->get('tsrv');
        $this->redis_svc = $this->di->get('cas')->get('redis_data_service');
	}

	/**
	* 门票特卖相关
	 * @param dest_id 目的地ID
	 * @return array
	*/
	public function queryTeMaiProductsForTicketAction()
	{
		$res = array(
			'code' => '00000',
			'msg' => 'ok',
			'data' => array()
		);
		$data = array();
		//获取参数dest_id
		$dest_id = intval($this->request->get('dest_id'));
		if(empty($dest_id)){
			$res['code'] = '00500';
			$res['msg'] = 'dest_id不能为空!';
			$this->jsonResponse($res);
		}

		$redis_key = 'dubbo:teMaiProductsForTicket:dest_id:' . $dest_id;
		$data = $this->redis_svc->dataGet($redis_key);
		if(empty($data)){
			//通过dest_id获取product_id
			$product = $this->dest_product_rel_service->getProductByPoiId($dest_id);
			if(!empty($product)){
				//通过tirift访问dubbo接口获取数据
				$params = array('destId' => $dest_id, 'productId' => $product['productId']);
				$data = $this->tsrv_svc->exec('unityProduct/queryTeMaiProductsForTicket', array('params' => json_encode($params)));
				if(!empty($data)){
					$cur_data = array();
					foreach($data as $key => $item){
						if($item['cancelFlag'] == 'Y' && $item['onlineFlag'] == 'Y' && !empty($item['goodsId']) && !empty($item['sellPrice']) && (!empty($item['sId']) || !empty($item['tId'])) && !empty($item['buCode'])){
							$cur_data[] = $item;
						}
					}
					$data = json_encode($cur_data);
					$this->redis_svc->dataSet($redis_key, $data, 300);
				}else{
					$res['code'] = '00500';
					$res['msg'] = '门票数据不存在!';
				}
			}else{
				$res['code'] = '00500';
				$res['msg'] = '产品不存在!';
			}
		}
		
		if(!empty($data)){
			$res['data'] = json_decode($data, true);
		}

		$this->jsonResponse($res);
	}

	/**
     * 常规门票接口
     * @param dest_id 产品ID
     * @return array
     */
	public function findTicketSuppGoodsByProductIdAction()
	{
		$res = array(
			'code' => '00000',
			'msg' => 'ok',
			'data' => array()
		);
		$data = array();
		//获取参数dest_id
		$dest_id = intval($this->request->get('dest_id'));
		if(empty($dest_id)){
			$res['code'] = '00500';
			$res['msg'] = 'dest_id不能为空!';
			$this->jsonResponse($res);
		}
		//filter只为显示java返回的全部结果
		$filter = $this->request->get('filter');
		if(!empty($filter)){
			$product = $this->dest_product_rel_service->getProductByPoiId($dest_id);
			if(!empty($product)){
				$params = array('productId' => $product['productId']);
				$ticket = $this->tsrv_svc->exec('scenic/findTicketSuppGoodsByProductId', array('params' => json_encode($params)));
				$res['data'] = $ticket['returnContent'];
				$this->jsonResponse($res);
			}
		}

		$redis_key = 'dubbo:ticketSuppGoods:dest_id:' . $dest_id;
		$data = $this->redis_svc->dataGet($redis_key);
		if(empty($data)){
			//通过dest_id获取product_id
			$product = $this->dest_product_rel_service->getProductByPoiId($dest_id);
			if(!empty($product)){
				//通过tirift访问dubbo接口获取数据
				$params = array('productId' => $product['productId']);
				$ticket = $this->tsrv_svc->exec('scenic/findTicketSuppGoodsByProductId', array('params' => json_encode($params)));
				if(!empty($ticket) && !empty($ticket['success']) && !empty($ticket['returnContent'])){
					$item = array();
					$poiTypeNameTemp = $this->poiTypeName;
					//过滤数据，返回只需要的数据
					foreach($ticket['returnContent'] as $key => $val){
						if($val['cancelFlag'] == 'Y'){
							//获取成人票
							foreach($val['suppGoodsList'] as $v){
								$type = $v['goodsSpec'];
								if($v['cancelFlag'] == 'Y' && $type == 'ADULT' && !array_key_exists('ADULT', $item) && !empty($poiTypeNameTemp) && array_key_exists($type, $poiTypeNameTemp)){
                                    $dataParam = array('goodsId' => $v['suppGoodsId'] );
                                    $goodsIdInfo = $this->tsrv_svc->exec('product/findSuppGoodsById', array('params' => json_encode($dataParam)));

                                    if(!empty($goodsIdInfo) && !empty($goodsIdInfo['success']) && !empty($goodsIdInfo['returnContent'])) {

                                        $item[$type]['suppGoodsId'] = $v['suppGoodsId'];
                                        $item[$type]['poiTypeName'] = $this->poiTypeName[$type];
                                        $item[$type]['goodsName'] = $v['goodsName'];
                                        $item[$type]['lowestSaledPrice'] = ceil($v['suppGoodsAddition']['lowestSaledPrice'] / 100);
                                        $item[$type]['lowestMarketPrice'] = ceil($v['suppGoodsAddition']['lowestMarketPrice'] / 100);
                                        unset($poiTypeNameTemp[$type]);
                                    }
								}
							}
							//取成人票以外的四张不同类型的门票
							foreach($val['suppGoodsList'] as $v){
								$type = $v['goodsSpec'];
								//只需要成人，情侣，儿童，家庭票 ADULT:成人票, CHILDREN:儿童票, PARENTAGE:亲子票, FAMILY:家庭票, LOVER:情侣票, COUPE:双人票, OLDMAN:老人票, STUDENT:学生票, ACTIVITY:优待票, SOLDIER:军人票, WOMAN:女士票, TEACHER:教师票, DISABILITY:残疾票, GROUP:团体票, FREE:相关票
								// if($v['cancelFlag'] == 'Y' && ((empty($item['ADULT']) && $type == 'ADULT') || (empty($item['LOVER']) && $type == 'LOVER') || (empty($item['FAMILY']) && $type == 'FAMILY') || (empty($item['CHILDREN']) && $type == 'CHILDREN'))){
								// 	$item[$type]['suppGoodsId'] = $v['suppGoodsId'];
								// 	$item[$type]['poiTypeName'] = $this->poiTypeName[$type];
								// 	$item[$type]['goodsName'] = $v['goodsName'];
								// 	$item[$type]['lowestSaledPrice'] = ceil($v['suppGoodsAddition']['lowestSaledPrice'] / 100);
								// 	$item[$type]['lowestMarketPrice'] = ceil($v['suppGoodsAddition']['lowestMarketPrice'] / 100);
								// }
								if($v['cancelFlag'] == 'Y' && (count($item) < 5) && !empty($poiTypeNameTemp) && array_key_exists($type, $poiTypeNameTemp)){
                                    $dataParam = array('goodsId' => $v['suppGoodsId'] );
                                    $goodsIdInfo = $this->tsrv_svc->exec('product/findSuppGoodsById', array('params' => json_encode($dataParam)));

                                    if(!empty($goodsIdInfo) && !empty($goodsIdInfo['success']) && !empty($goodsIdInfo['returnContent'])) {

                                        $item[$type]['suppGoodsId'] = $v['suppGoodsId'];
                                        $item[$type]['poiTypeName'] = $this->poiTypeName[$type];
                                        $item[$type]['goodsName'] = $v['goodsName'];
                                        $item[$type]['lowestSaledPrice'] = ceil($v['suppGoodsAddition']['lowestSaledPrice'] / 100);
                                        $item[$type]['lowestMarketPrice'] = ceil($v['suppGoodsAddition']['lowestMarketPrice'] / 100);
                                        unset($poiTypeNameTemp[$type]);
                                    }
								}
							}
						}
					}
					$data = json_encode($item);
					$this->redis_svc->dataSet($redis_key, $data, 300);
				}else{
					$res['code'] = '00500';
					$res['msg'] = '门票数据不存在!';
				}
			}else{
				$res['code'] = '00500';
				$res['msg'] = '产品不存在!';
			}
		}
		
		if(!empty($data)){
			$res['data'] = json_decode($data, true);
		}

		$this->jsonResponse($res);
	}

	/**
     * 搜索接口
     * @param dest_id 产品ID
     * @param type 类型
     * @param page_size 返回条数
     * @return array
     */
	public function getSearchByDestIdAction()
	{
		$res = array(
			'code' => '00000',
			'msg' => 'ok',
			'data' => array()
		);
		$data = array();
		//获取参数dest_id
		$dest_id = intval($this->request->get('dest_id'));
		if(empty($dest_id)){
			$res['code'] = '00500';
			$res['msg'] = 'dest_id不能为空!';
			$this->jsonResponse($res);
		}

		$type = trim($this->request->get('type'));

		$page_size = intval($this->request->get('page_size'));

		$order = trim($this->request->get('order'));

		$params = array(
			'pageSize' => $page_size,
			'currentPage' => 1,
			'filters' => array('DEST_ID' => $dest_id)
		);

		if(!empty($order)){
			$params['sort'] = array($order => true);
		}

		$redis_key = 'dubbo:getSearch:dest_id:' . $dest_id . '_' . $type . '_' . $page_size;
		$data = $this->redis_svc->dataGet($redis_key);
		if(empty($data)){
			$exec_action = '';
			switch ($type) {
				case 'TICKET'://门票搜索接口
					$exec_action = 'getSimpleTicket';
					break;
				case 'FREFTOUR'://机+酒 路线搜索接口 categoryId：18  subcategoryId 182
					$exec_action = 'getSimpleRoute';
					$params['filters']['CATEGORY_ID'] = 18;
					$params['filters']['SUB_CATEGORY_ID'] = 182;
					break;
				case 'SCENICTOUR'://景+酒 路线搜索接口 categoryId: 18  subcategoryId 181
					$exec_action = 'getSimpleRoute';
					$params['filters']['CATEGORY_ID'] = 18;
					$params['filters']['SUB_CATEGORY_ID'] = 181;
					break;
				case 'GROUP'://跟团游 categoryId：15
					$exec_action = 'getSimpleRoute';
					$params['filters']['CATEGORY_ID'] = 15;
					break;
				case 'HOTEL'://酒店搜索接口
					$exec_action = 'getSimpleHotel';
					break;
				case 'LOCAL'://当地游 路线搜索接口 (categoryId:15 AND productType:innershortline)  OR categoryId:16 目前接口不支持OR条件查询 暂取categoryId:16的数据
					$exec_action = 'getSimpleRoute';
					$params['filters']['CATEGORY_ID'] = 16;
					break;
				default:
					$res['code'] = '00500';
					$res['msg'] = '没有该类型'.$type;
					$this->jsonResponse($res);
			}

			$search = $this->tsrv_svc->exec('search/' . $exec_action, array('params' => json_encode($params)));

			if(!empty($search) && !empty($search['items'])){
				$data = json_encode($search['items']);
				$this->redis_svc->dataSet($redis_key, $data, 300);
				$res['totalResultSize'] = $search['totalResultSize'];
				$res['pageSize'] = $page_size;
				$res['currentPage'] = 1;
			}
		}

		if(!empty($data)){
			$res['data'] = json_decode($data, true);
		}

		$this->jsonResponse($res);
	}

	/**
	* 根据商品或产品ID获取图片
	* @param object_id 商品或产品ID
	* @param object_type 类型
	* @return array
	*/
	public function findImageListAction()
	{
		$res = array(
			'code' => '00000',
			'msg' => 'ok',
			'data' => array()
		);
		$data = array();
		//获取参数objectId
		$object_id = intval($this->request->get('object_id'));
		if(empty($object_id)){
			$res['code'] = '00500';
			$res['msg'] = 'objectId不能为空!';
			$this->jsonResponse($res);
		}

		$object_type = trim($this->request->get('object_type'));

		$redis_key = 'dubbo:findImageList:object_id:' . $object_id . ',object_type:' . $object_type;
		$data = $this->redis_svc->dataGet($redis_key);
		if(empty($data)){
			//通过tirift访问dubbo接口获取数据
			$params = array('objectId' => $object_id, 'objectType' => $object_type);
			$product = $this->tsrv_svc->exec('product/findImageList', array('params' => json_encode($params)));
			$cur_data = array();
			if(!empty($product) && !empty($product['success']) && !empty($product['returnContent'])){
				$column = array_column($product['returnContent'], 'photoSeq');
				if(empty($cur_data) && array_search('1', $column) !== false){
					$key = array_search('1', $column);
					$cur_data = $product['returnContent'][$key];
				}

				if(empty($cur_data)){
					$column = array_column($product['returnContent'], 'photoType');
				
					if(empty($cur_data) && array_search('5', $column) !== false){
						$key = array_search('5', $column);
						$cur_data = $product['returnContent'][$key];
					}elseif(empty($cur_data) && array_search('0', $column) !== false){
						$key = array_search('0', $column);
						$cur_data = $product['returnContent'][$key];
					}elseif(empty($cur_data) && array_search('9', $column) !== false){
						$key = array_search('9', $column);
						$cur_data = $product['returnContent'][$key];
					}
				}
				if(empty($cur_data)){
					$cur_data = $product['returnContent'][0];
				}
			}
			if(!empty($cur_data)){
				$data = json_encode($cur_data);
				$this->redis_svc->dataSet($redis_key, $data, 300);
			}else{
				$res['code'] = '00500';
				$res['msg'] = '图片不存在!';
			}
		}
		
		if(!empty($data)){
			$res['data'] = json_decode($data, true);
		}

		$this->jsonResponse($res);
	}
}