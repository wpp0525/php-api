<?php

use \Lvmama\Common\Utils\UCommon;
/**
 * 目的地周边信息
 *
 * @author win.sx
 *
 */
class DestnearController extends ControllerBase {
    private $dest_image_svc;
    /**
     * @var \Lvmama\Cas\Service\DestinationDataService
     */
    private $dest;

    public function initialize() {
        parent::initialize();
		$this->dest = $this->di->get('cas')->get('destination-data-service');
        $this->es = $this->di->get('cas')->get('es-data-service');
    }

    /**
     * 根据目的地ID获取其周边指定类型的目的地
     * @param dest_id 目的地ID
     * @param dest_type 需要获取的目的地类型
     * @param distance 查询的范围,单位为km
     * @param limit 查询的最大条数
     * @return json
     * @author shenxiang
     * @example curl -XGET 'http://ca.lvmama.com/destnear/index?dest_id=103279&dest_type=RESTAURANT'
     */
	public function indexAction(){
        $dest_id = $this->request->get('dest_id');
        $dest_type = $this->request->get('dest_type');
        $distance = $this->request->get('distance');
        $limit = $this->request->get('limit');
        if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10001,'请传入正确的dest_id');
        if(!$dest_type) $this->_errorResponse(10002,'请传入正确的dest_type');
        if($distance && !is_numeric($distance)) $this->_errorResponse(10003,'请传入正确的范围参数distance');
        if($limit && !is_numeric($limit)) $this->_errorResponse(10004,'请传入正确的查询数量参数limit');
        if(!$distance) $distance = 5;
        if(!$limit) $limit = 15;
        $this->_successResponse($this->dest->getNearDest($dest_id,$dest_type,$distance,$limit));
	}
    public function seoAction(){
        $dest_id = $this->request->get('dest_id');
        $this->_successResponse($this->dest->getSeo($dest_id));
    }


    public function getNearCityByProductAction(){

        $dest_id = $this->request->get('dest_id');
        $distance = $this->request->get('distance');
        $limit = $this->request->get('limit');
        if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10001,'请传入正确的dest_id');
        if(!$distance) $distance = 500;
        if(!$limit) $limit = 5;

        // 目前按想去去过取前五
        $dests = $this->dest->getNearDestByWant($dest_id,'CITY', $distance,$limit);
//        $this->dest->getNearDest($dest_id,'CITY',$distance,$limit);

        if($dests && is_array($dests)){
            $dests_all = $product_ids = array();

            $dest_image_svc = $this->di->get('cas')->get('dest_image_service');

            foreach($dests as $dest){
                $destps = $this->es->getPoiRecommendHotProductByCategoryId($dest['dest_id'],18 ,0 ,1);

                $this->poiDataFormat($destps,$product_id);
                unset($destps);
                $dest['product'] = $product_id;

                // 7.12 add no img_url use `lmm_lvyou`.`ly_elite_image` seq min limit 1
                if($dest['img_url'] == ''){
                    $dest['img_url'] = $dest_image_svc->getCoverByDestId($dest['dest_id']);
                }

                $dests_all['dest'][] = $dest;

                $product_ids[] = $product_id;
            }
        }


        if ( $product_ids) {
            // 是否增加缓存  -- 待定  -- 命中较低
            $product_ids_str = implode(',',$product_ids);

            $product_pool_product_service = $this->di->get('cas')->get('product_pool_product');
            $product_info_simple = $product_pool_product_service->getByProductId($product_ids_str);

            // 调取产品详细数据
            $return = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            foreach ( $product_info_simple as $product_info_simple_value ) {
                if ( $product_info_simple_value['SUB_CATEGORY_ID'] ) {
                    if ( count($return[$product_info_simple_value['SUB_CATEGORY_ID']]) > 3 ) {
                        continue;
                    }
                    $dests_all['product'][$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['SUB_CATEGORY_ID']));
                } else {
                    if ( count($return[$product_info_simple_value['CATEGORY_ID']]) > 3 ) {
                        continue;
                    }
                    $dests_all['product'][$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['CATEGORY_ID']));
                }
            }

        }

        $this->_successResponse($dests_all);

    }

    /**
     * poi数据转换product_ids
     * @param $dests
     * @param $product_ids
     */
    private function poiDataFormat($dests,&$product_ids)
    {
        $product_ids = 0;
        if ( $dests['hits']['total'] != 0 ) {
            if (isset($dests['hits']) && isset($dests['hits']['hits'])) {
                $tmp_array = $dests['hits']['hits'];
                foreach ($tmp_array as $tmp_array_value) {

                    $product_ids = $tmp_array_value['_source']['product_id'];

                }
            }
        }
    }

}
