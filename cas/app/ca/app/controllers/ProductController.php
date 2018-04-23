<?php
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Utils\UCommon;

/**
 * 线路接口
 *
 * @author win.sx
 *
 */
class ProductController extends ControllerBase
{

    private $externalApi  = null;
    private $product_type = array('all', 'ticket', 'local', 'group', 'hotel', 'scenictour', 'freetour', 'youlun', 'freescenictour', 'ziyouxing');
    private $pp_startdistrict_addtional;

    public function initialize()
    {
        parent::initialize();
        $this->externalApi                = $this->di->get('cas')->get('external-api-data-server');
        $this->pp_startdistrict_addtional = $this->di->get('cas')->get('product_pool_startdistrict_addtional');
        $this->tsrv_svc                   = $this->di->get('tsrv');

    }

    /**
     * 取得目的地的友情链接
     * @param int $dest_id
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getData
     */
    public function getDataAction()
    {
        $dest_id = $this->request->get('dest_id');
        $num     = $this->request->get('num');
        $type    = $this->request->get('type');
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        if (!trim($num)) {
            $num = 4;
        }

        if (!trim($type)) {
            $type = 'all';
        }

        if (!is_numeric($num) || $num > 25) {
            $this->_errorResponse(10003, '请传入正确的数量或者数量不可大于25');
        }

        if (!in_array($type, $this->product_type)) {
            $this->_errorResponse(10004, '请传入正确的type');
        }

        $data = $this->externalApi->getResult('API_DESTINFO_SERVICE', array(
            'dest_id' => $dest_id,
            'forcedb' => 0,
        ));
        $result = $this->externalApi->getResult('API_API_ALL', array(
            'dest_name'   => $data['dest_name'],
            'type'        => $type,
            'num'         => $num,
            'dest_abroad' => $data['abroad'],
            'forcedb'     => 0,
        ));
        $this->_successResponse($result);
    }

    /**
     * 取得目的地特卖产品
     * @param int $dest_id
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getSpecialPro
     */
    public function getSpecialProAction()
    {
        $dest_id = $this->request->get('dest_id');
        $num     = $this->request->get('num');
        $type    = $this->request->get('type');
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        if (!trim($num)) {
            $num = 4;
        }

        if (!trim($type)) {
            $type = 'all';
        }

        if (!is_numeric($num) || $num > 25) {
            $this->_errorResponse(10003, '请传入正确的数量或者数量不可大于25');
        }

        if (!in_array($type, $this->type)) {
            $this->_errorResponse(10004, '请传入正确的type');
        }

        $result = $this->externalApi->getResult('API_SEARCH_ALL', array(
            'dest_id' => $dest_id,
            'num'     => $num,
            'sort'    => 3,
            'type'    => $type,
        ));
        $this->_successResponse($result);
    }
    /**
     * 根据产品ID和类型获取产品及其对应的目的地和商品等信息
     * @param int $productId
     * @param int $categoryId
     * @return array
     * example curl -i -X GET http://ca.lvmama.com/product/getProductGoods
     */
    public function getProductGoodsAction()
    {
        $productId  = $this->request->get('productId');
        $categoryId = $this->request->get('categoryId');
        if (!$productId || !is_numeric($productId)) {
            $this->_errorResponse(10001, '请传入正确产品ID且需为整数');
        }

        $vst_product = $this->di->get('cas')->get('product_pool_vst_product');
        $sql         = 'SELECT * FROM pp_vst_product WHERE productId = ' . $productId;
        if ($categoryId && is_numeric($categoryId)) {
            $sql .= ' AND categoryId = ' . $categoryId;
        }
        $prodProduct  = $vst_product->getRsBySql($sql, true);
        $prodDest     = $vst_product->getRsBySql('SELECT productId,dest_id FROM pp_vst_dest WHERE productId = ' . $productId, true);
        $prodDistrict = $vst_product->getRsBySql('SELECT productId,districtId,districtName FROM pp_vst_district WHERE productId = ' . $productId, true);
        $suppGoods    = $vst_product->getRsBySql('SELECT productId,categoryId,managerId,filiale,suppGoodsId FROM pp_vst_goods WHERE productId = ' . $productId);
        $data         = array(
            'prodProduct'  => $prodProduct ? $prodProduct : array(),
            'prodDest'     => $prodDest ? $prodDest : array(),
            'prodDistrict' => $prodDistrict ? $prodDistrict : array(),
            'suppGoods'    => $suppGoods,
        );
        $this->_successResponse($data);
    }
    /**
     * 根据产品ID获取产品的基本信息
     * @param string $ids
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getBaseByPid
     */
    public function getBaseByPidAction()
    {
        $ids     = $this->request->get('ids');
        $type_id = $this->request->get('type_id');
        if (!$ids) {
            $this->_errorResponse(10002, '请传入正确产品ID集合');
        }

        if (!$type_id || !is_numeric($type_id)) {
            $this->_errorResponse(10003, '请传入正确的type_id');
        }

        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $res          = array();
        foreach (explode(',', $ids) as $id) {
            if (is_numeric($id)) {
                $res[$id] = $product_info->inputProductPool(array('product_id' => $id, 'type_id' => $type_id));
            }
        }
        //还需要封装url,点评量,好评率,促销信息
        $this->_successResponse($res);
    }
    /**
     * 更新指定产品ID集合的基本信息
     * @param string $ids
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/updateProductByPid
     */
    public function updateProductByPidAction()
    {
        $ids     = $this->request->get('ids');
        $type_id = $this->request->get('type_id');
        if (!$ids) {
            $this->_errorResponse(10002, '请传入正确产品ID集合');
        }

        if (!$type_id || !is_numeric($type_id)) {
            $this->_errorResponse(10003, '请传入正确的type_id');
        }

        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $res          = array();
        foreach (explode(',', $ids) as $id) {
            if (is_numeric($id)) {
                $res[$id] = $product_info->inputProductPool(array(
                    'type_id'    => $type_id,
                    'product_id' => $id,
                ), true);
            }
        }
        $this->_successResponse($res);
    }
    /**
     * 修改当前产品池的刷新状态
     * @param string $k
     * @param string $status
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/updateRefreshStatus
     */
    public function updateRefreshStatusAction()
    {
        $key    = $this->request->get('k');
        $status = $this->request->get('status');
        if (!$key || $key != 'e382ccf8e59ecf2916f106b627437029') {
            die();
        }
        $product_info = $this->di->get('cas')->get('product-info-data-service');
        echo $product_info->updateRefreshStatus($status) ? 'ok' : 'error';
    }

    /**
     * 将产品集合推送到消息队列中让产品池获取
     * @param product_ids 产品ID的集合,半角逗号隔开
     * @param type_id产品的类型
     * @author shenxiang
     * @example curl -X POST http://ca.lvmama.com/product/kafkaProduct
     */
    public function kafkaProductAction()
    {
        $product_ids = $this->request->getPost('product_ids');
        $type_id     = $this->request->getPost('type_id');
        if (!$product_ids || !$type_id) {
            $this->_errorResponse(10002, '请传入产品ID集合和类型ID');
        }
        if (!is_numeric($type_id)) {
            $this->_errorResponse(10003, '请传入正确的类型ID');
        }

        $tmp          = explode(',', $product_ids);
        $products     = array();
        $product_info = $this->di->get('cas')->get('product-info-data-service');
        foreach ($tmp as $v) {
            if (!is_numeric($v)) {
                $this->_errorResponse(10004, '请确保传入的产品ID为纯数字');
            }
            $products[] = $product_info->getProPoolIdByProductCategoryId($v, $type_id);
        }
        $rk = new Producer($this->config->kafka->msgProducer->toArray());
        $rk->sendMsg(implode(',', $products));
    }
    /**
     * 将大目的地&长尾词指定页面ID的产品ID发送到产品池消息去处理
     * @param $keyword_id 页面ID
     * @return void
     * @example curl -XGET 'http://ca.lvmama.com/product/sendProductIdToPool'
     */
    public function sendProductIdToPoolAction()
    {
        $keyword_id = $this->request->get('keyword_id');
        if (!is_numeric($keyword_id)) {
            $this->_errorResponse(10001, '请确保传入的页面ID为纯数字');
        }

        $pp_place_service = $this->di->get('cas')->get('product_pool_data');
        $rs               = $pp_place_service->query('SELECT product_id FROM pp_place WHERE channel_id = 1 AND key_id = ' . $keyword_id . ' AND del_status =1 AND lock_status = 1 AND product_id != 0 ', 'All');
        $productids       = array();
        foreach ($rs as $row) {
            $productids[] = $row['product_id'];
        }
        $rk = new Producer($this->config->kafka->msgProducer->toArray());
        $rk->sendMsg(implode(',', $productids));
    }
    /**
     * 根据门票产品获取相应的POI和城市及其以上的目的地
     * @param product_id
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getDestByProduct
     */
    public function getDestByProductAction()
    {
        $product_id = $this->request->get('product_id');
        if (!is_numeric($product_id)) {
            $this->_errorResponse(10003, '请传入正确的产品ID');
        }

        $dest_product_rel = $this->di->get('cas')->get('dest_product_rel_service');
        $rs               = $dest_product_rel->getOne(array('productId = ' => $product_id), 'dest_product_rel');
        $this->_successResponse($rs);
    }
    /**
     * 通过产品ID和分类ID取得产品池统一ID
     * @param $product_id
     * @param $category_id
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getProPoolIdByProductCategoryId
     */
    public function getProPoolIdByProductCategoryIdAction()
    {
        $product_id  = $this->request->get('product_id');
        $category_id = $this->request->get('category_id');
        if (!is_numeric($product_id) || strlen($product_id) > 10) {
            $this->_errorResponse(10001, '请传入正确的产品ID');
        }

        if (!is_numeric($category_id) || strlen($category_id) > 3) {
            $this->_errorResponse(10002, '请传入正确的类型ID');
        }

        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $this->_successResponse($product_info->getProPoolIdByProductCategoryId($product_id, $category_id));
    }

    /**
     * 获取产品分类ID所属的类型ID
     * @param $category_id
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getTypeIdByCategoryId
     */
    public function getTypeIdByCategoryIdAction()
    {
        $category_id = $this->request->get('category_id');
        if (!is_numeric($category_id) || strlen($category_id) > 3) {
            $this->_errorResponse(10001, '请传入正确的分类ID');
        }

        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $this->_successResponse($product_info->getTypeIdByCategoryId($category_id));
    }

    /**
     * 根据坑位取产品数据
     * @param $place
     * @return json
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getProductByPlace
     */
    public function getProductByPlaceAction()
    {
        $place = $this->request->get('place');
        $spm   = $this->request->get('spm');
        $plus  = $this->request->get('plus');
        if ($spm) {
            $place = $spm;
        }

        if (!$place) {
            $this->_errorResponse(10001, '请传入place');
        }

        $places = explode('.', $place);
        if (count($places) != 4) {
            $this->_errorResponse(10002, '请传入正确的place');
        }

        foreach ($places as $v) {
            if (!is_numeric($v)) {
                $this->_errorResponse(10002, '请传入正确的place');
            }

        }
        $spm                   = UCommon::spreadRule($place);
        $channel_id            = $spm['channel_id'];
        $route_id              = $spm['route_id'];
        $key_id                = $spm['key_id'];
        $position              = $spm['position'];
        $place_order           = $spm['place_order'];
        $product_pool_vst_dest = $this->di->get('cas')->get('product_pool_vst_dest');
        $tsrv_svc              = $this->di->get('tsrv');

        //表名
        $field      = $plus == 1 ? '' : '`product_price`,`product_url`,';
        $table_name = $plus == 1 ? 'pp_place_plus' : 'pp_place';
        $where      = $plus == 1 ? '1' : 'del_status = 1';

        $sql = 'SELECT `id`,`place_order`,`product_id`,`supp_goods_id`,`product_name`,`product_img`,`product_tips`,`product_district_id`,' . $field . '`product_commentCount`,`product_commentGood`,`product_promotionTitle` FROM ' . $table_name . ' WHERE ' . $where . ' AND channel_id = ' . $channel_id . ' AND route_id = ' . $route_id . ' AND key_id = ' . $key_id;
        if ($position) {
            $sql .= ' AND position = ' . $position;
        }
        if ($place_order) {
            $sql .= ' AND place_order = ' . $place_order;
        }
        $product_ids = $product_pool_vst_dest->getRsBySql($sql);

        $product_info = $this->di->get('cas')->get('product-info-data-service');

        $products = array();
        foreach ($product_ids as $k => $v) {
            $diy = array('id' => $v['id'], 'place_order' => $v['place_order'], 'productId' => intval(substr($v['product_id'], 3)));
            if ($v['product_id'] == 0) {
                $products[] = $diy;
                continue;
            }
            if (trim($v['product_name'])) {
                $diy['productName'] = $v['product_name'];
            }
            if (trim($v['product_img'])) {
                $diy['img_url'] = $v['product_img'];
            }
            if (trim($v['product_tips'])) {
                $diy['productTips'] = $v['product_tips'];
            }
            if (intval($v['product_price'])) {
                $diy['price'] = $v['product_price'];
            }
            if ($v['product_url']) {
                $diy['url'] = $v['product_url'];
            }
            if (trim($v['product_commentCount'])) {
                $diy['commentCount'] = $v['product_commentCount'];
            }
            if (trim($v['product_commentGood'])) {
                $diy['commentGood'] = $v['product_commentGood'];
            }
            if (trim($v['product_promotionTitle'])) {
                $diy['promotionTitle'] = $v['product_promotionTitle'];
            }
            if (intval($v['product_district_id'])) {
                $diy['product_district_id'] = $v['product_district_id'];
            }
            if (intval($v['supp_goods_id'])) {
                $pp_goods   = $this->di->get('cas')->get('product_pool_goods');
                $goods_info = $pp_goods->getAllByGoodsId(intval($v['supp_goods_id']));

                if ($goods_info) {
                    $diy['goodsId']   = intval($v['supp_goods_id']);
                    $diy['goodsName'] = $goods_info[0]['GOODS_NAME'];
                    $diy['price']     = $goods_info[0]['LOWEST_SALED_PRICE'] / 100;
                    $diy['saleFlag']  = $goods_info[0]['CANCEL_FLAG'];
                    //商品URL BRANCH商品 PROD产品
                    $url = $tsrv_svc->exec('unityProduct/getTMHUrl', array(
                        'params' => json_encode(array('objectId' => $v['supp_goods_id'], 'objectType' => 'BRANCH')),
                    ));
                    $diy['url'] = $url['returnContent'] ? $url['returnContent'] : $diy['url'];
                }
            }
            $products[] = array_merge(
                $product_info->getProductByKey($v['product_id']),
                $diy
            );
        }
        $this->_successResponse($products);
    }
    /**
     * 根据坑位取产品数据,使用redis管道获取
     * @param $spm
     * @param $ignore是否忽略产品内容为空的产品,默认不忽略
     * @param $tour是否根据坐标第三位把产品分组后返回,默认不分组
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getProductBySpm
     */
    public function getProductBySpmAction()
    {
        $spm    = $this->request->get('spm');
        $ignore = $this->request->get('ignore');
        $tour   = $this->request->get('tour');
        if (!$spm) {
            $this->_errorResponse(10001, '请传入spm码');
        }

        $spms = explode('.', $spm);
        if (count($spms) != 4) {
            $this->_errorResponse(10002, '请传入正确的spm码');
        }

        foreach ($spms as $v) {
            if (!is_numeric($v)) {
                $this->_errorResponse(10002, '请传入正确的spm码');
            }

        }
        $spmData               = UCommon::spreadRule($spm);
        $channel_id            = $spmData['channel_id'];
        $route_id              = $spmData['route_id'];
        $key_id                = $spmData['key_id'];
        $position              = $spmData['position'];
        $place_order           = $spmData['place_order'];
        $product_pool_vst_dest = $this->di->get('cas')->get('product_pool_vst_dest');
        $product_info          = $this->di->get('cas')->get('product-info-data-service');
        $tsrv_svc              = $this->di->get('tsrv');

        $sql = 'SELECT `id`,`position`,`place_order`,`product_id`,`supp_goods_id`,`product_name`,`product_img`,`product_tips`,`product_price`,`product_url`,`product_commentCount`,`product_commentGood`,`product_promotionTitle` FROM pp_place WHERE del_status = 1 AND channel_id = ' . $channel_id . ' AND route_id = ' . $route_id . ' AND key_id = ' . $key_id;
        if ($position) {
            $sql .= ' AND position = ' . $position;
        }
        if ($place_order) {
            $sql .= ' AND place_order = ' . $place_order;
        }
        $product_ids = $product_pool_vst_dest->getRsBySql($sql);
        $ids         = array();
        foreach ($product_ids as $v) {
            if ($v['product_id']) {
                $ids[] = $v['product_id'];
            }

        }
        $productData = $product_info->getProductsByKeys($ids, true);
        $products    = array();
        foreach ($product_ids as $v) {
            $diy = $tour ? array() : array('id' => $v['id'], 'place_order' => $v['place_order'], 'productId' => intval(substr($v['product_id'], 3)));
            if (!$v['product_id'] && !$ignore) {
                $products[] = $diy;
                continue;
            }
            if (trim($v['product_name'])) {
                $diy['productName'] = $v['product_name'];
            }
            if (trim($v['product_img'])) {
                $diy['img_url'] = $v['product_img'];
            }
            if (trim($v['product_tips'])) {
                $diy['productTips'] = $v['product_tips'];
            }
            if ($v['product_price'] > 0) {
                $diy['price'] = $v['product_price'];
            }
            if ($v['product_url']) {
                $diy['url'] = $v['product_url'];
            }
            if (trim($v['product_commentCount'])) {
                $diy['commentCount'] = $v['product_commentCount'];
            }
            if (trim($v['product_commentGood'])) {
                $diy['commentGood'] = $v['product_commentGood'];
            }
            if (trim($v['product_promotionTitle'])) {
                $diy['promotionTitle'] = $v['product_promotionTitle'];
            }
            if (intval($v['supp_goods_id'])) {
                $pp_goods   = $this->di->get('cas')->get('product_pool_goods');
                $goods_info = $pp_goods->getAllByGoodsId(intval($v['supp_goods_id']));
                if ($goods_info) {
                    $diy['goodsId']   = intval($v['supp_goods_id']);
                    $diy['goodsName'] = $goods_info[0]['GOODS_NAME'];
                    $diy['price']     = $goods_info[0]['LOWEST_SALED_PRICE'] / 100;
                    $diy['saleFlag']  = $goods_info[0]['CANCEL_FLAG'];
                    //商品URL BRANCH商品 PROD产品
                    $url = $tsrv_svc->exec('unityProduct/getTMHUrl', array(
                        'params' => json_encode(array('objectId' => $v['supp_goods_id'], 'objectType' => 'BRANCH')),
                    ));
                    $diy['url'] = $url['returnContent'] ? $url['returnContent'] : $diy['url'];
                }
            }
            if (isset($productData[$v['product_id']])) {
                if ($tour) {
                    if (!isset($products[$v['position']])) {
                        $products[$v['position']] = array();
                    }

                    $products[$v['position']][] = array_merge($productData[$v['product_id']], $diy);
                } else {
                    $product_img = !empty($diy['img_url']) ? $diy['img_url'] : $productData[$v['product_id']]['img_url'];
                    if (!empty($product_img)) {
                        $diy['img_url_max'] = UCommon::makePicSize2($product_img);
                        $diy['img_url_min'] = UCommon::makePicSize2($product_img, '_300_200');
                    }
                    $products[] = $productData[$v['product_id']] ? array_merge($productData[$v['product_id']], $diy) : $diy;
                }
            }
        }

        $this->_successResponse($products);
    }

    /**
     * 根据坑位取产品数据,使用redis管道获取
     * @param $spm
     * @param $ignore是否忽略产品内容为空的产品,默认不忽略
     * @param $tour是否根据坐标第三位把产品分组后返回,默认不分组
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/product/getProductBySpmNew
     */
    public function getProductBySpmNewAction()
    {
        header("Content-Type: text/html; charset=utf-8");
        $spm    = $this->request->get('spm');
        $ignore = $this->request->get('ignore');
        $tour   = $this->request->get('tour');
        if (!$spm) {
            $this->_errorResponse(10001, '请传入spm码');
        }

        $spms = explode('.', $spm);
        if (count($spms) != 4) {
            $this->_errorResponse(10002, '请传入正确的spm码');
        }

        foreach ($spms as $v) {
            if (!is_numeric($v)) {
                $this->_errorResponse(10002, '请传入正确的spm码');
            }

        }
        $spmData               = UCommon::spreadRule($spm);
        $channel_id            = $spmData['channel_id'];
        $route_id              = $spmData['route_id'];
        $key_id                = $spmData['key_id'];
        $position              = $spmData['position'];
        $place_order           = $spmData['place_order'];
        $product_pool_vst_dest = $this->di->get('cas')->get('product_pool_vst_dest');

        $tsrv_svc = $this->di->get('tsrv');

        $sql = 'SELECT `id`,`position`,`place_order`,`product_id`,`supp_goods_id`,`product_name`,`product_img`,`product_tips`,`product_price`,`product_url`,`product_commentCount`,`product_commentGood`,`product_promotionTitle` FROM pp_place WHERE del_status = 1 AND channel_id = ' . $channel_id . ' AND route_id = ' . $route_id . ' AND key_id = ' . $key_id;
        if ($position) {
            $sql .= ' AND position = ' . $position;
        }
        if ($place_order) {
            $sql .= ' AND place_order = ' . $place_order;
        }
        $product_ids = $product_pool_vst_dest->getRsBySql($sql);
        $ids         = array();
        $ids_new     = array();
        foreach ($product_ids as $v_tmp) {
            if ($v_tmp['product_id']) {
                $ids[]     = $v_tmp['product_id'];
                $ids_new[] = $this->formatLongProductId($v_tmp['product_id']);
            }
        }

        $from_code      = 'product_pool_v2_spm';
        $ids_new        = implode(',', $ids_new);
        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $productData    = $this->pprd_svs->getProductInfoByProductId($ids_new, $from_code);

        $products = array();
        foreach ($product_ids as $v) {

            $format_product_id = $this->formatLongProductId($v['product_id']);
            $diy               = $tour ? array() : array('id' => $v['id'], 'placeOrder' => $v['place_order'], 'productId' => $format_product_id);

//            if( !$v['product_id'] && !$ignore ){
            //                $products[] = $diy;
            //                continue;
            //            }

            $diy['productName'] = isset($v['product_name']) ? $v['product_name'] : '';

            $diy['imgUrl'] = isset($v['product_img']) ? $v['product_img'] : '';

            $diy['productTips'] = isset($v['product_tips']) ? $v['product_tips'] : '';

            $diy['price'] = isset($v['product_price']) ? $v['product_price'] : '';

            $diy['url'] = isset($v['product_url']) ? $v['product_url'] : '';

            $diy['commentCount'] = isset($v['product_commentCount']) ? $v['product_commentCount'] : '';

            $diy['commentGood'] = isset($v['product_commentGood']) ? $v['product_commentGood'] : '';

            $diy['promotionTitle'] = isset($v['product_promotionTitle']) ? $v['product_promotionTitle'] : '';

            // 冗余字段
            $diy['urlId'] = isset($v['urlId']) ? $v['urlId'] : '';

            $diy['districtId'] = isset($v['districtId']) ? $v['districtId'] : '';

            $diy['subCategoryId'] = isset($v['subCategoryId']) ? $v['subCategoryId'] : '';

            $diy['categoryId'] = isset($v['categoryId']) ? $v['categoryId'] : '';

            $diy['saleFlag'] = isset($v['saleFlag']) ? $v['saleFlag'] : '';

            $diy['categoryName'] = isset($v['categoryName']) ? $v['categoryName'] : '';

            $diy['suppGoodsId'] = isset($v['supp_goods_id']) ? $v['supp_goods_id'] : '';

            $tmp_product_name = '';

            if (!empty($productData[$format_product_id])) {

                if (intval($diy['price']) == 0) {
                    $diy['price'] = $productData[$format_product_id]['lowestSaledPrice'];
                }

                // 处理董宏亚的bug
                $temp_price = $productData[$format_product_id]['lowestSaledPrice'];

                $productData[$format_product_id]['price'] = $productData[$format_product_id]['lowestSaledPrice'];
                unset($productData[$format_product_id]['lowestSaledPrice']);
                unset($productData[$format_product_id]['lowestMarketPrice']);

                $tmp_product_name = $productData[$format_product_id]['productName'];

                if ($tour) {
                    if (!isset($products[$v['position']])) {
                        $products[$v['position']] = array();
                    }

                    $products[$v['position']][] = array_merge($productData[$format_product_id], $diy);
                    // 恢复价格以便下次使用
                    $productData[$format_product_id]['lowestSaledPrice'] = $temp_price;
                } else {
                    $productData[$format_product_id]['lowestSaledPrice'] = $temp_price;
                    $product_img                                         = !empty($diy['imgUrl']) ? $diy['imgUrl'] : $productData[$format_product_id]['imgUrl'];
                    if (!empty($product_img)) {
                        $diy['imgUrl']    = $product_img;
                        $diy['imgUrlMax'] = UCommon::makePicSize2($product_img);
                        $diy['imgUrlMin'] = UCommon::makePicSize2($product_img, '_300_200');
                    } else {
                        $diy['imgUrlMax'] = '';
                        $diy['imgUrlMin'] = '';
                    }

                    if (empty($diy['productName'])) {
                        $diy['productName'] = $productData[$format_product_id]['productName'];
                    }

                    if (empty($diy['imgUrl'])) {
                        $diy['imgUrl'] = $productData[$format_product_id]['imgUrl'];
                    }

                    if (empty($diy['url'])) {
                        $diy['url'] = $productData[$format_product_id]['url'];
                    }

                    $diy['saleFlag'] = empty($diy['saleFlag']) ? $productData[$format_product_id]['saleFlag'] : '';

                    $diy['categoryName'] = empty($diy['categoryName']) ? $productData[$format_product_id]['categoryName'] : '';

                    $diy['categoryId'] = empty($diy['categoryId']) ? $productData[$format_product_id]['categoryId'] : '';

                    $diy['subCategoryId'] = empty($diy['subCategoryId']) ? $productData[$format_product_id]['subCategoryId'] : '';

                    $diy['districtId'] = empty($diy['districtId']) ? $productData[$format_product_id]['districtId'] : '';

                    $diy['urlId']        = empty($diy['urlId']) ? $productData[$format_product_id]['urlId'] : '';
                    $diy['commentCount'] = empty($diy['commentCount']) ? $productData[$format_product_id]['commentCount'] : '';
                    $diy['commentGood']  = empty($diy['commentGood']) ? $productData[$format_product_id]['commentGood'] : '';

//                    $products[] =  $productData[$format_product_id]?array_merge($productData[$format_product_id], $diy):$diy;

                }

            } else {

                $diy['imgUrlMax'] = UCommon::makePicSize2($diy['imgUrl']);
                $diy['imgUrlMin'] = UCommon::makePicSize2($diy['imgUrl'], '_300_200');

            }

            $format_supp_goods_id = intval($v['supp_goods_id']);

            if ($format_supp_goods_id) {
                $goods_info = $this->pprd_svs->getGoodsInfoByGoodsId($format_supp_goods_id, $from_code);

                if ($goods_info) {

                    if (!empty($goods_info[$format_supp_goods_id]['lowestSaledPrice'])) {
                        $diy['price'] = $goods_info[$format_supp_goods_id]['lowestSaledPrice'];
                    }

                    if (!empty($goods_info[$format_supp_goods_id]['url'])) {
                        $diy['url'] = $goods_info[$format_supp_goods_id]['url'];
                    }

                    $diy['suppGoodsId'] = $goods_info[$format_supp_goods_id]['suppGoodsId'];
                    $diy['productName'] = !empty($tmp_product_name) ? $tmp_product_name . $goods_info[$format_supp_goods_id]['goodsName'] : $goods_info[$format_supp_goods_id]['goodsName'];

                }

            }

            $products[] = $diy;

        }

        $this->_successResponse($products);
    }

    /**
     * 删除坑位中的产品
     */
    public function deletePlaceProductAction()
    {
        $id   = $this->request->get('id');
        $plus = $this->request->get('plus');
        if (!$id || !is_numeric($id)) {
            $this->_errorResponse(10001, '请传入正确的id');
        }
        $place = $this->di->get('cas')->get('product_pool_data');
        $flag  = $place->update($id, array(
            'product_id'             => 0,
            'supp_goods_id'          => 0,
            'product_name'           => '',
            'product_tips'           => '',
            'product_price'          => 0,
            'product_url'            => '',
            'product_img'            => '',
            'product_commentCount'   => '',
            'product_commentGood'    => '',
            'product_promotionTitle' => '',
            'product_district_id'    => '',
        ));
        if ($flag) {
            $this->_successResponse('执行成功');
        } else {
            $this->_errorResponse(10002, '操作失败');
        }
    }
    /**
     * 保存坑位上的自定义产品信息
     */
    public function savePlaceProductAction()
    {
        $id                  = $this->request->get('id');
        $position            = $this->request->get('position');
        $product_id          = $this->request->get('product_id');
        $productName         = $this->request->get('productName');
        $img_url             = $this->request->get('img_url');
        $tips                = $this->request->get('tips');
        $product_price       = $this->request->get('product_price');
        $product_url         = $this->request->get('product_url');
        $commentCount        = $this->request->get('commentCount');
        $commentGood         = $this->request->get('commentGood');
        $promotionTitle      = $this->request->get('promotionTitle');
        $product_district_id = $this->request->get('product_district_id');
        if (!$id || !is_numeric($id)) {
            $this->_errorResponse(10001, '请传入正确的id');
        }
        $data = array();
        if ($position) {
            $data['position'] = $position;
        }

        $data['product_id']   = $product_id;
        $data['product_name'] = $productName;
        $data['product_img']  = $img_url;
        $data['product_tips'] = $tips;
        if ($product_price) {
            $data['product_price'] = $product_price;
        }

        $data['product_url']            = $product_url;
        $data['product_commentCount']   = $commentCount;
        $data['product_commentGood']    = $commentGood;
        $data['product_promotionTitle'] = $promotionTitle;
        if ($product_district_id) {
            $data['product_district_id'] = $product_district_id;
        }

        $place = $this->di->get('cas')->get('product_pool_data');
        $flag  = $place->update($id, $data);
        if ($flag) {
            $this->_successResponse('执行成功');
        } else {
            $this->_errorResponse(10002, '操作失败');
        }
    }

    /**
     * 批量保存坑位上的产品信息
     */
    public function blukSaveProductAction()
    {
        $data = urldecode($this->request->getPost('data'));
        if (!$data) {
            $this->_errorResponse(10001, '请传入正确的参数内容');
        }

        $tmp = json_decode($data, true);
        if (!$tmp || !is_array($tmp)) {
            $this->_errorResponse(10002, '请传入正确的格式化内容');
        }

        $place = $this->di->get('cas')->get('product_pool_data');
        $error = array();
        foreach ($tmp as $k => $v) {
            $id = $v['id'];
            unset($v['id']);
            $flag = $place->update($id, $v);
            if (!$flag) {
                $error[] = $id;
            }

        }
        $error ? $this->_errorResponse(10003, '有下列ID操作失败:' . implode(',', $error)) : $this->_successResponse('执行成功');
    }
    /**
     * 根据条件清理pp_place表中的产品数据
     */
    public function cleanPlaceByWhereAction()
    {
        $channel_id = $this->request->get('channel_id');
        $route_id   = $this->request->get('route_id');
        $key_id     = $this->request->get('key_id');
        $position   = $this->request->get('position');
        if (!$channel_id || !$route_id || !$key_id) {
            $this->_errorResponse(10001, '请传入指定的参数');
        }

        if (!is_numeric($channel_id) || !is_numeric($route_id) || !is_numeric($key_id)) {
            $this->_errorResponse(10002, '请传入合法的参数');
        }

        $place = $this->di->get('cas')->get('product_pool_data');
        $where = 'channel_id = ' . $channel_id . ' AND route_id = ' . $route_id . ' AND key_id = ' . $key_id;
        if ($position && is_numeric($position)) {
            $where .= ' AND position = ' . $position;
        }

        $data = array(
            'product_id'             => 0,
            'product_name'           => '',
            'product_img'            => '',
            'product_tips'           => '',
            'product_price'          => 0,
            'product_url'            => '',
            'product_commentCount'   => 0,
            'product_commentGood'    => 0,
            'product_promotionTitle' => '',
            'product_district_id'    => 0,
            'update_time'            => time(),
        );
        $flag = $place->updateByWhere($where, $data);
        $flag ? $this->_successResponse('执行成功') : $this->_errorResponse(10002, '执行失败');
    }
    /**
     * 获取坑位上的产品基本信息
     * @param channel_id 频道ID
     * @param route_id 路由ID
     * @param key_id 页面ID
     * @param position 变量ID
     * @param fields 需要查询的字段名称
     * @param ignore 是否忽略产品ID为0
     * @return json
     * @author shenxiang
     * @example curl -XGET 'http://ca.lvmama.com/product/getPlaceInfo'
     */
    public function getPlaceInfoAction()
    {
        $channel_id = $this->request->get('channel_id');
        $route_id   = $this->request->get('route_id');
        $key_id     = $this->request->get('key_id');
        $position   = $this->request->get('position');
        $fields     = $this->request->get('fields');
        $ignore     = $this->request->get('ignore');
        $place      = $this->di->get('cas')->get('product_pool_data');
        if (!$channel_id || !is_numeric($channel_id)) {
            $this->_errorResponse(10001, '请传入正确的channel_id');
        }
        if (!$route_id || !is_numeric($route_id)) {
            $this->_errorResponse(10002, '请传入正确的route_id');
        }
        if (!$key_id || !is_numeric($key_id)) {
            $this->_errorResponse(10003, '请传入正确的key_id');
        }
        $sql = 'SELECT ' . ($fields ? $fields : '*') . ' FROM pp_place WHERE channel_id = ' . $channel_id . ' AND route_id=' . $route_id . ' AND key_id = ' . $key_id;
        if ($position && is_numeric($position)) {
            $sql .= ' AND position = ' . $position;
        }
        if ($ignore) {
            $sql .= ' AND product_id != \'0\'';
        }
        $sql .= ' AND del_status = 1 AND lock_status = 1';
        $rs = $place->query($sql, 'All');
        if ($rs) {
            $this->_successResponse($rs);
        } else {
            $this->_errorResponse(10004, '获取失败');
        }
    }

    /***
     * 根据 PRODUCT_ID 和 START_DISTRICT_ID 取产品价格
     */
    public function getPriceByPidDidAction()
    {
        $product_id                                            = $this->request->get('product_id');
        $district_id                                           = intval($this->request->get('district_id'));
        !empty($product_id) && !empty($start_dest_id) && $info = $this->pp_startdistrict_addtional->getDataOne(" `product_id`=" . $product_id . " AND `start_district_id`=" . $district_id);

        if (empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据不存在');
            return;
        }
        $this->jsonResponse(array('result' => $info, 'error' => 0));
    }

    /***
     * 根据 产品ids取产品类型
     */
    public function getCategoryByPidAction()
    {
        $pid = $this->request->get('pid');
        if (empty($pid)) {
            $this->_errorResponse(PARAMS_ERROR, '参数不正确');
            return;
        }
        $pids = !is_array($pid) ? $pid : implode(',', $pid);

        $product_cas = $this->di->get('cas')->get('product_pool_product');
        $info        = $product_cas->getAllByProductId($pids);
        if (empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据不存在');
            return;
        }
        $this->jsonResponse(array('result' => $info, 'error' => 0));
    }

    /***
     * 根据 spm和出发地id更新价格
     */
    public function upPiceBySpmDidAction()
    {
        $spm = $this->request->get('spm');
//        $district_id = $this->request->get('district_id');
        if (empty($spm)) {
            $this->_errorResponse(PARAMS_ERROR, '参数不正确');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);
        if (empty($spm_str['channel_id']) || empty($spm_str['route_id']) || empty($spm_str['key_id'])) {
            $this->_errorResponse(PARAMS_ERROR, 'spm参数精度错误');
            return;
        }
        $where['channel_id'] = $spm_str['channel_id'];
        $where['route_id']   = $spm_str['route_id'];
        $where['key_id']     = $spm_str['key_id'];
        if ($spm_str['position']) {
            $where['position'] = $spm_str['position'];
        }

        $pp_svc       = $this->di->get('cas')->get('product_pool_data');
        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $redis_svc    = $this->di->get('cas')->get('redis_data_service');

        $pids = array();
        //坑位产品数据
        $place = $pp_svc->getByCondition('id,product_id,product_district_id', $where);
        //多出发地价格
        foreach ($place as $v) {
            if ($v['product_id'] > 0 && $v['product_district_id'] > 0) {
                $pid   = intval(substr($v['product_id'], 3));
                $where = " `product_id`=" . $pid . " AND `start_district_id`=" . $v['product_district_id'];

                $district_res = $this->pp_startdistrict_addtional->getDataOne($where);
                $pp_svc->update($v['id'], array('product_price' => $district_res['LOWEST_SALED_PRICE'] / 100));
            }
            if (intval(substr($v['product_id'], 0, 3)) == 6) {
                $pids[] = $v['product_id'];
            }

        }
        //游轮产品价格为空则刷新到redis
        if ($pids) {
            $productData = $product_info->getProductsByKeys($pids, true);
        }

        foreach ($productData as $k => $v) {
            if (empty($v['price'])) {
                $product_res = $pp_svc->getOneByCondition('pp_product_addtional', 'LOWEST_SALED_PRICE', $v['productId'], 'PRODUCT_ID');
                if (!empty($product_res['LOWEST_SALED_PRICE'])) {
                    $v['price'] = $product_res['LOWEST_SALED_PRICE'] / 100;

                    $redis_svc->dataHmset('productpool:' . $k, $v);
                }
            }
        }
        if (empty($place)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据不存在');
            return;
        }
        $this->jsonResponse(array('result' => 200, 'error' => 0));
    }

    /***
     * 根据 导入的excel数据 spm和出发地id 更新表
     */
    public function upProductBySpmDidAction()
    {
        $spm         = $this->request->getPost('spm');
        $district_id = intval($this->request->getPost('district_id'));
        $ex_data     = json_decode($this->request->getPost('ex_data'), true);

        if (empty($spm) || empty($ex_data) || !is_array($ex_data)) {
            $this->_errorResponse(PARAMS_ERROR, '参数不正确');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);
        if ($spm_str == false) {
            $this->_errorResponse(PARAMS_ERROR, '参数不正确');
            return;
        }
        $where['channel_id'] = $spm_str['channel_id'];
        $where['route_id']   = $spm_str['route_id'];
        $where['key_id']     = $spm_str['key_id'];
//        $where['position']   = $spm_str['position'];
        //        $other_where = 'product_id != 0';
        //取出
        $position_id_arr = array_column($ex_data, 9);
        if (!empty($position_id_arr)) {
            $position_id_arr = array_unique($position_id_arr);
            $position_ids    = implode(',', $position_id_arr);
            $other_where     = ' position IN (' . $position_ids . ') ';
        } else {
            $this->_errorResponse(PARAMS_ERROR, '参数不正确');
            return;
        }

        $pp_svc = $this->di->get('cas')->get('product_pool_data');

        //坑位数据
        $place = $pp_svc->getByCondition('id,product_id,position', $where, $other_where);
        //更新
        foreach ($place as $item) {
            if ($item['product_id'] > 0) {
                foreach ($ex_data as $key => $value) {
                    $data = array();
                    if ($value[9] == $item['position'] && $value[0] == intval(substr($item['product_id'], 3))) {
                        if (!$value[4] || !$value[5]) {
                            $data = $this->getPromoById($value[0]);
                        }

                        if ($value[1]) {
                            $data['product_name'] = trim($value[1]);
                        }

                        if ($value[2]) {
                            $data['product_url'] = trim($value[2]);
                        }

                        if ($value[3]) {
                            $data['product_img'] = trim($value[3]);
                        }

                        if ($value[4]) {
                            $data['product_tips'] = trim($value[4]);
                        }

                        if ($value[5]) {
                            $data['product_promotionTitle'] = trim($value[5]);
                        }

                        if ($value[6]) {
                            $data['product_commentCount'] = intval($value[6]);
                        }

                        if ($value[7]) {
                            $data['product_commentGood'] = intval($value[7]);
                        }

                        if ($district_id || intval($value[8]) > 0) {
                            $product_district_id = $value[8] ? intval($value[8]) : $district_id;
                            $district_product    = $this->pp_startdistrict_addtional->getListByPidDid($product_district_id, $value[0]);
                            if ($district_product) {
                                $data['product_district_id'] = $product_district_id;
                                $data['product_price']       = $district_product[$item['product_id']]['product_price'];
                                $data['product_url']         = $value[2] ? $value[2] : $district_product[$item['product_id']]['product_url'];
                            }
                        }
                        if ($data) {
                            $pp_svc->update($item['id'], $data);
                        }

                        unset($ex_data[$key]);
                        break;
                    }
                }
            }
        }
        //新增
        $product_cas = $this->di->get('cas')->get('product_pool_product');

        foreach ($place as $item) {
            if ($item['product_id'] == 0) {
                foreach ($ex_data as $key => $value) {
                    if ($value[9] == $item['position']) {
                        $data        = array();
                        $info        = $product_cas->getAllByProductId($value[0]);
                        $category_id = $info[0]['CATEGORY_ID'];
                        if ($category_id) {
                            if (!$value[4] && !$value[5]) {
                                $data = $this->getPromoById($value[0]);
                            }

                            $typeF              = UCommon::productIdMap($category_id);
                            $data['product_id'] = str_pad($typeF, 3, '0', STR_PAD_LEFT) . str_pad($value[0], 10, '0', STR_PAD_LEFT);
                            if ($value[1]) {
                                $data['product_name'] = trim($value[1]);
                            }

                            if ($value[2]) {
                                $data['product_url'] = trim($value[2]);
                            }

                            if ($value[3]) {
                                $data['product_img'] = trim($value[3]);
                            }

                            if ($value[4]) {
                                $data['product_tips'] = trim($value[4]);
                            }

                            if ($value[5]) {
                                $data['product_promotionTitle'] = trim($value[5]);
                            }

                            if ($value[6]) {
                                $data['product_commentCount'] = intval($value[6]);
                            }

                            if ($value[7]) {
                                $data['product_commentGood'] = intval($value[7]);
                            }

                            if ($district_id || intval($value[8]) > 0) {
                                $product_district_id = $value[8] ? intval($value[8]) : $district_id;
                                $district_product    = $this->pp_startdistrict_addtional->getListByPidDid($product_district_id, $value[0]);
                                if ($district_product) {
                                    $data['product_district_id'] = $product_district_id;
                                    $data['product_price']       = $district_product[$data['product_id']]['product_price'];
                                    $data['product_url']         = $value[2] ? $value[2] : $district_product[$data['product_id']]['product_url'];
                                }
                            }
                            $pp_svc->update($item['id'], $data);
                            unset($ex_data[$key]);
                            break;
                        }
                    }
                }
            }
        }

        $this->jsonResponse(array('result' => 1, 'error' => 0));
    }

    /**
     * 根据SPM码刷新产品数据
     */
    public function refreshProductBySpmAction()
    {
        $spm          = $this->request->get('spm');
        $product_info = $this->di->get('cas')->get('product-info-data-service');
        $product_info->refreshBySpm($spm);
        $this->_successResponse('执行完成');
    }

    /**
     * 格式化长ID---待定  需要抽象到lib中
     * @param $product_id
     * @return int
     */
    private function formatLongProductId($product_id)
    {
        $result = intval(substr($product_id, 3, 10));
        return $result;
    }

    /**
     *  取促销信息
     * @param $objectId 产品id或商品id
     * @param string $type 产品"GOODS"商品"PRODUCT"
     * @return array
     */
    private function getPromoById($objectId, $type = 'PRODUCT')
    {
        $tsrv_svc = $this->di->get('tsrv');

        $where = $up_data = array();

        $where['objectId']   = $objectId;
        $where['objectType'] = $type;

        $data  = $tsrv_svc->exec('product/getPromotions', array('params' => json_encode($where)));
        $title = $data['returnContent']['items'][0]['title'];
        if ($data['success'] == true && !empty($title)) {
            $title_pos = strpos($title, '(') ? strpos($title, '(') : strpos($title, '（');
            if ($title_pos === false) {
                $title1 = $title;
            } else {
                $title1 = substr($title, 0, $title_pos);

                preg_match_all("/(?:\(|\（)(.*)(?:\)|\）)/i", $title, $result);
                $title2 = $result[1][0];
            }
        }
        if ($title1) {
            $up_data['product_tips'] = $title1;
        }

        if ($title2) {
            $up_data['product_promotionTitle'] = $title2;
        }

        return $up_data ? $up_data : array();
    }

    /**
     *    一键刷新一个专题的促销标签
     */
    public function refreshSubPromotionsAction()
    {

        $spm = $this->request->get('spm');

        if (empty($spm)) {
            $this->_errorResponse(PARAMS_ERROR, 'spm参数为空');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);
        if ($spm_str == false) {
            $this->_errorResponse(PARAMS_ERROR, 'spm参数不正确');
            return;
        }
        $where['channel_id'] = $spm_str['channel_id'];
        $where['route_id']   = $spm_str['route_id'];
        $where['key_id']     = $spm_str['key_id'];
        $other_where         = 'product_id != 0';

        $pp_svc = $this->di->get('cas')->get('product_pool_data');
        //坑位数据
        $place = $pp_svc->getByCondition('id,product_id,supp_goods_id', $where, $other_where);
        //更新
        foreach ($place as $item) {
            $data = array();
            if ($item['supp_goods_id'] > 0) {
                $data = $this->getPromoById($item['supp_goods_id'], 'GOODS');
            } else {
                $data = $this->getPromoById(intval(substr($item['product_id'], 3)));
            }
            if ($data) {
                $pp_svc->update($item['id'], $data);
            }

        }

        $this->jsonResponse(array('result' => 200, 'error' => 0));
    }

    /**
     * 获取出发地的产品数
     * @param size 获取条数
     * @param isDesc 排序 true：倒叙 false：升序
     * @return array
     */
    public function getDistrictProductCountsAction()
    {
        $size = $this->request->get('size');
        if (empty($size)) {
            $size = 200;
        }

        $isDesc = $this->request->get('isDesc');
        if (!isset($isDesc)) {
            $isDesc = true;
        }

        $params = array('size' => $size, 'isDesc' => $isDesc);

        $data = $this->tsrv_svc->exec('destDistrict/getDistrictProductCounts', array('params' => json_encode($params)));
        $res  = array();
        if (!empty($data) && !empty($data['success']) && !empty($data['returnContent'])) {
            $res = $data['returnContent'];
        }

        $this->jsonResponse(array('result' => $res, 'error' => 0));
    }
}
