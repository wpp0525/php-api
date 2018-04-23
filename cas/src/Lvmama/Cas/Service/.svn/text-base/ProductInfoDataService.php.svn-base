<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\UCommon as UCommon;

/**
 * 主站用户 服务类
 *
 * @author sx
 *
 */
class ProductInfoDataService extends DataServiceBase
{
    protected $baseUri = 'http://www.lvmama.com/';
    protected $key     = 'product:';
    public function __construct($di, $redis = null, $beanstalk = null)
    {
        $this->di            = $di;
        $this->redis         = $redis;
        $this->beanstalk     = $beanstalk;
        $this->client        = new ApiClient($this->baseUri);
        $this->elasticsearch = $this->di->get('config')->toArray()['elasticsearch'];
    }
    public function getProductInfo($product_id)
    {
        if (!is_numeric($product_id)) {
            return array();
        }

        $product = $this->redis->hGetAll($this->key . $product_id);
        if (!$product) {
            $params = array('productId' => $product_id);
            $res    = $this->client->exec('ajax/PhpQueryUntityProductById.do', $params, '', 'GET');
            if (isset($res[0])) {
                $product = $res[0];
                foreach ($product as $k => $v) {
                    if (is_array($v)) {
                        $this->saveSubArray($product_id, $k, $v);
                    }
                    $this->redis->hset($this->key . $product_id, $k, $v);
                }
            }
        } else {
            foreach ($product as $k => $v) {
                if ($v == 'Array') {
                    $product[$k] = $this->redis->hGetAll($this->key . $product_id . ':' . $k);
                }
            }
        }
        return $product;
    }
    private function saveSubArray($product_id, $key, $data)
    {
        foreach ($data as $k => $v) {
            $this->redis->hset($this->key . $product_id . ':' . $key, $k, $v);
        }
    }
    public function getProductBaseInfo($params = array())
    {
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return array();
        }

        $interfaceAddr = array(
            'product' => 'product/findRouteByProductId',
            'search'  => 'search/getVstRoute',
        );
        $productTypeId                   = $this->getTypeIdByCategoryId(intval($params['type_id']));
        $interfaceAddr['product_params'] = array('productId' => $params['id']);
        $interfaceAddr['search_params']  = array('params' => '{"productIds":"' . $params['id'] . '"}');
        switch ($productTypeId) {
            case 1:
                $interfaceAddr['product'] = 'product/findHotelByProductId';
                $interfaceAddr['search']  = 'search/getVstHotel';
                break;
            case 2:
                $interfaceAddr['product']        = 'product/findShipProductDetail';
                $interfaceAddr['product_params'] = array('params' => json_encode(array('productId' => $params['id'])));
                $interfaceAddr['search']         = 'search/getSimpleShip';
                $interfaceAddr['search_params']  = array('params' => json_encode(array('fields' => '', 'filters' => array('PRODUCT_ID' => $params['id']), 'pageSize' => 1, 'currentPage' => 1)));
                break;
            case 5:
                $interfaceAddr['product'] = 'product/findTicketByProductId';
                $interfaceAddr['search']  = 'search/getVstTicket';
                break;
            case 6:
                $interfaceAddr['product']        = 'product/findShipProductDetail';
                $interfaceAddr['search']         = 'search/getSimpleShip';
                $interfaceAddr['product_params'] = array('params' => json_encode(array('productId' => $params['id'])));
                $interfaceAddr['search_params']  = array('params' => json_encode(array('fields' => '', 'filters' => array('PRODUCT_ID' => $params['id']), 'pageSize' => 1, 'currentPage' => 1)));
                break;
            case 14:
                $interfaceAddr['product'] = 'product/findRouteByProductId';
                $interfaceAddr['search']  = 'search/getVstRoute';
                break;
            default:
                //暂时不支持的类型
        }
        $res        = array();
        $start_time = UCommon::get_mic_time();
        try {
            $data = $this->di->get('tsrv')->exec($interfaceAddr['product'], $interfaceAddr['product_params']);
            if ($data['success']) {
                $returnContent = $data['returnContent'];
                $price         = empty($returnContent['prodProductAddtionalVo']['lowestSaledPrice']) ? (empty($returnContent['lowestSaledPrice']) ? '' : $returnContent['lowestSaledPrice'] / 100) : $returnContent['prodProductAddtionalVo']['lowestSaledPrice'] / 100;
                $img_url       = empty($returnContent['imageList'][0]['photoUrl']) ? $this->getProductPhoto($returnContent, intval($returnContent['bizCategoryId'])) : $returnContent['imageList'][0]['photoUrl'];
                $res           = array(
                    'productId'      => $returnContent['productId'],
                    'urlId'          => $returnContent['urlId'],
                    'productName'    => $returnContent['productName'],
                    'cancelFlag'     => $returnContent['cancelFlag'],
                    'saleFlag'       => $returnContent['saleFlag'],
                    'bizCategoryId'  => $returnContent['bizCategoryId'],
                    'CategoryName'   => UCommon::categoryNameById($returnContent['bizCategoryId']),
                    'img_url'        => $img_url,
                    'price'          => $price,
                    'fromDest'       => isset($returnContent['districtVo']['districtName']) ? $returnContent['districtVo']['districtName'] : '',
                    'promotionTitle' => isset($returnContent['promotionTitle']) ? $returnContent['promotionTitle'] : '',
                );
            } else {
                //获取失败,记录日志
                $end_time = UCommon::get_mic_time();
                $this->writeLog(array(
                    'message' => 'productId:[' . $params['id'] . '] get tsrv[' . $interfaceAddr['product'] . '] success is false,cost ' . ($end_time - $start_time) . 's',
                    'dbname'  => 'ProductInfoDataService',
                    'table'   => 'getProductBaseInfo',
                ));
                $data = $this->di->get('tsrv')->exec(
                    $interfaceAddr['search'],
                    $interfaceAddr['search_params']
                );
                $item = isset($data['items'][0]) ? $data['items'][0] : array();
                if ($item) {
                    $category_id = empty($item['subCategoryId']) ? $item['categoryId'] : $item['subCategoryId'];
                    $img_url     = empty($item['photoUrl']) ? (empty($item['imageUrl']) ? '' : 'http://pic.lvmama.com' . $item['imageUrl']) : 'http://pic.lvmama.com' . $item['photoUrl'];
                    $res         = array(
                        'productId'      => $item['productId'],
                        'urlId'          => empty($item['urlId']) ? '' : $item['urlId'],
                        'productName'    => $item['productName'],
                        'cancelFlag'     => empty($item['cancelFlag']) ? 'Y' : $item['cancelFlag'],
                        'saleFlag'       => empty($item['saleFlag']) ? 'Y' : $item['saleFlag'],
                        'bizCategoryId'  => $category_id,
                        'CategoryName'   => UCommon::categoryNameById($category_id),
                        'img_url'        => $img_url,
                        'price'          => empty($item['sellPrice']) ? (empty($item['price']) ? '' : $item['price']) : $item['sellPrice'] / 100,
                        'fromDest'       => isset($item['districtName']) ? $item['districtName'] : '',
                        'promotionTitle' => isset($item['promotionTitle']) ? $item['promotionTitle'] : '',
                    );
                }
            }
        } catch (\Exception $e) {
            //异常,记录日志到es中
            $end_time = UCommon::get_mic_time();
            $this->writeLog(array(
                'dbname'  => 'ProductInfoDataService',
                'table'   => 'getProductBaseInfo',
                'message' => 'productId:[' . $params['id'] . '] get tsrv[product/findRouteByProductId] time out Exception,cost ' . ($end_time - $start_time) . 's',
            ));
        }
        $res['commentCount'] = '';
        $res['commentGood']  = '';
        $res['url']          = '';
        //点评数据
        $comment = $this->di->get('tsrv')->exec('product/getVstCmtTitleStatisticsByProductId', array('productId' => $params['id']));
        $this->writeLog(array(
            'message' => 'productId:[' . $params['id'] . '] get tsrv[product/getVstCmtTitleStatisticsByProductId] data [' . json_encode($comment, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE) . ']',
            'dbname'  => 'ProductInfoDataService',
            'table'   => 'getProductBaseInfo',
        ));
        if (isset($comment['commentCount'])) {
            $res['commentCount'] = isset($comment['commentCount']) ? $comment['commentCount'] : '';
            $res['commentGood']  = isset($comment['formatAvgScore']) ? $comment['formatAvgScore'] : '';
        }
        //URL
        $url = $this->di->get('tsrv')->exec('product/findProductUrl', array(
            'params' => '{"productId":"' . (isset($res['urlId']) ? $res['urlId'] : $params['id']) . '","categoryId":"' . (isset($res['bizCategoryId']) ? $res['bizCategoryId'] : $params['type_id']) . '"}',
        ));
        $this->writeLog(array(
            'message' => 'productId:[' . $params['id'] . '] get tsrv[product/findProductUrl] data [' . json_encode($url, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE) . ']',
            'dbname'  => 'ProductInfoDataService',
            'table'   => 'getProductBaseInfo',
        ));
        if (isset($url['success']) && $url['success'] == 1) {
            $res['url'] = isset($url['content']) ? $url['content'] : UCommon::getDoMainUrl($res['bizCategoryId']) . $res['productId'];
        }
        return $res;
    }

    /**
     * 门票产品根据UrlId取得其产品ID
     */
    public function getProductIdFromUrlId($urlId)
    {
        $data = $this->di->get('tsrv')->exec('product/findProductIdByUrlId', array(
            'urlId' => $urlId,
        ));
        $return = 0;
        if ($data['success']) {
            $return = $data['returnContent'] ? $data['returnContent'] : $urlId;
        }
        return $return;
    }
    public function inputProductPool($data = array(), $update = false)
    {
        if (!isset($data['product_id']) ||
            !isset($data['type_id']) ||
            !is_numeric($data['product_id']
            )) {
            return array();
        }

        $typeF     = $this->productIdMap($data['type_id']);
        $productId = str_pad($typeF, 3, '0', STR_PAD_LEFT) . str_pad($data['product_id'], 10, '0', STR_PAD_LEFT);
        $product   = $this->redis->hGetAll('productpool:' . $productId);
        if (!$product || $update) {
            $product = $this->getProductBaseInfo(array(
                'id'      => $data['product_id'],
                'type_id' => $data['type_id'],
            ));
            if (isset($product['productId']) && $product['productId']) {
                $this->redis->hmset('productpool:' . $productId, $product);
                /********* 同时更新产品池v2的数据  ***/
                $arr = [
                    'lowest_saled_price' => 'price', // 更新v2的lowest_saled_price 用 v1的price代替
                    //'url'                => 'url',
                    'comment_count'      => 'commentCount',
                    'comment_good'       => 'commentGood',
                ];
                $info = [];

                foreach ($arr as $m => $n) {
                    if (isset($product[$n])) {
                        $info[$m] = ($n == 'price' && empty($product['price'])) ? 0 : $product[$n];
                    }
                }
                $redisv2_key = 'productpoolv2:' . $data['product_id'];

                $redis_info = $this->redis->hGetAll($redisv2_key);

                if (!empty($info) && !empty($redis_info)) {
                    foreach ($info as $k => $v) {
                        $this->redis->hset($redisv2_key, $k, $v);
                    }
                }
                /********* 同时更新产品池v2的数据  ***/
            }
        }
        return $product;
    }
    public function productIdMap($id)
    {
        $map = array(
            '11'  => '5',
            '12'  => '5',
            '13'  => '5',
            '8'   => '6',
            '9'   => '7',
            '10'  => '7',
            '15'  => '14',
            '16'  => '14',
            '17'  => '14',
            '18'  => '14',
            '29'  => '14',
            '32'  => '14',
            '42'  => '14',
            '181' => '14',
            '182' => '14',
            '183' => '14',
        );
        return isset($map[$id]) ? $map[$id] : $id;
    }
    //刷新产品池数据
    public function refreshProductPool()
    {
        //先检查上一次刷新是否已经完成
        $status = $this->redis->zCard('refresh:product:keys');
        if ($status) {
            return;
        }

        $this->saveRefreshKey();
        $this->updateProductPool();
    }
    //手动将刷新产品状态改为已完成(防止程序中断导致以后都无法更新)
    public function updateRefreshStatus($status = 0)
    {
        if (!$status) {
            $this->redis->del('refresh:product:keys');
        }
        return $this->redis->set('refresh:product:status', $status);
    }

    /**
     * 根据产品池ID获取产品信息
     * @param $product_id
     * return array
     */
    public function getProductByKey($product_id)
    {
        return $this->redis->hgetall('productpool:' . $product_id);
    }
    /**
     * 根据产品ID集合批量获取产品信息
     * @param $product_ids
     * @param $idIsKey 是否将传入的产品ID值做为返回值的key,如果为true有重复产品ID时返回值的数量会少于传入的量
     * @param array
     */
    public function getProductsByKeys($product_ids, $idIsKey = false)
    {
        $keys = array();
        $ids  = array_unique($product_ids);
        foreach ($ids as $id) {
            $keys[] = 'productpool:' . $id;
        }
        $nodes = array();
        foreach ($keys as $k => $value) {
            $nodes[UCommon::calRedisNode($value)][] = $value;
        }
        $products = array();
        // 3 个 redis node
        for ($i = 1; $i <= 3; $i++) {
            if (empty($nodes[$i])) {
                continue;
            }

            $this->di->get('cas')->getRedis($i)->pipeline();
            foreach ($nodes[$i] as $value) {
                $this->di->get('cas')->getRedis($i)->hGetAll($value);
            }
            $rs = $this->di->get('cas')->getRedis($i)->exec();
            foreach ($rs as $k => $v) {
                $products[$nodes[$i][$k]] = $v;
            }
        }
        $data = array();
        if ($idIsKey) {
            foreach ($product_ids as $v) {
                $data[$v] = $products['productpool:' . $v];
            }
        } else {
            foreach ($product_ids as $v) {
                $data[] = $products['productpool:' . $v];
            }
        }
        return $data;
    }
    /**
     * 通过产品ID和分类ID取得产品池统一ID
     * @param $product_id
     * @param $category_id
     * @return int
     */
    public function getProPoolIdByProductCategoryId($product_id, $category_id)
    {
        if (!is_numeric($product_id) || !is_numeric($category_id)) {
            return 0;
        }

        if (strlen($product_id) > 10 || strlen($category_id) > 3) {
            return 0;
        }

        $typeId = $this->getTypeIdByCategoryId($category_id);
        return str_pad($typeId, 3, '0', STR_PAD_LEFT) . str_pad($product_id, 10, '0', STR_PAD_LEFT);
    }
    /**
     * 根据产品类行ID取得所属分类ID
     * @param $category_id
     * @return int
     */
    public function getTypeIdByCategoryId($category_id)
    {
        switch (intval($category_id)) {
            case 1: //酒店
                return 1;
            case 2: //邮轮
                return 2;
            case 3: //保险
                return 3;
            case 4: //签证
                return 4;
            case 5: //门票
            case 11: //景点门票
            case 12: //其他票
            case 13: //组合套餐票
                return 5;
            case 6: //组合产品
            case 8: //邮轮组合产品
                return 6;
            case 7: //附加项目
            case 9: //岸上观光
            case 10: //邮轮附加项
                return 7;
            case 14: //线路
            case 15: //跟团游
            case 16: //当地游
            case 17: //酒店套餐
            case 18: //自由行
            case 29: //交通+X
            case 32: //酒套餐
            case 42: //定制游
            case 181: //景+酒
            case 182: //机+酒
            case 183: //交通+服务
                return 14;
            default: //其他先不考虑
                return 0;
        }
    }
    public function refreshBySpm($spm)
    {
        $pp_svc  = $this->di->get('cas')->get('product_pool_data');
        $spm_arr = UCommon::spreadRule($spm);
        $where   = ' channel_id = ' . $spm_arr['channel_id'] . ' AND route_id = ' . $spm_arr['route_id'] . ' AND key_id = ' . $spm_arr['key_id'];
        if ($spm_arr['position']) {
            $where .= ' AND position = ' . $spm_arr['position'];
        }

        if ($spm_arr['place_order']) {
            $where .= ' AND place_order = ' . $spm_arr['place_order'];
        }

        //pp_place
        $result = $pp_svc->query('SELECT product_id FROM pp_place WHERE ' . $where . ' AND del_status = 1', 'All');
        foreach ($result as $row) {
            if ($row['product_id'] == '0') {
                continue;
            }

            $type_id    = intval(substr($row['product_id'], 0, 3));
            $product_id = intval(substr($row['product_id'], 3));
            $this->inputProductPool(array(
                'type_id'    => $type_id,
                'product_id' => $product_id,
            ), true);
        }
        //pp_place_plus
        $result = $pp_svc->query('SELECT product_id FROM pp_place_plus WHERE ' . $where, 'All');
        foreach ($result as $row) {
            if ($row['product_id'] == '0') {
                continue;
            }

            $type_id    = intval(substr($row['product_id'], 0, 3));
            $product_id = intval(substr($row['product_id'], 3));
            $this->inputProductPool(array(
                'type_id'    => $type_id,
                'product_id' => $product_id,
            ), true);
        }
    }
    private function updateProductPool()
    {
        while (true) {
            if (!$this->redis->llen('refresh:product:keys')) {
                break;
            }

            $key = $this->redis->lpop('refresh:product:keys');
            $tmp = explode(':', $key);
            $this->writeLog(array(
                'message' => 'redis product key ' . $key,
                'dbname'  => 'RefreshproductTash',
                'table'   => 'updateProductPool',
            ));
            if (isset($tmp[1]) && is_numeric($tmp[1])) {
                $product_id  = intval(substr($tmp[1], 3));
                $old_product = $this->redis->hGetAll($key);
                $this->writeLog(array(
                    'message' => '[' . $product_id . '] old product:' . json_encode($old_product, JSON_UNESCAPED_UNICODE),
                    'dbname'  => 'RefreshproductTash',
                    'table'   => 'updateProductPool',
                ));
                if (empty($old_product['bizCategoryId'])) {
                    $this->writeLog(array(
                        'message' => '[' . $product_id . '] bizCategoryId Undefined index product:' . json_encode($old_product, JSON_UNESCAPED_UNICODE),
                        'dbname'  => 'RefreshproductTash',
                        'table'   => 'bizCategoryId Undefined',
                    ));
                }
                $new_product = $this->getProductBaseInfo(
                    array('id' => $product_id, 'type_id' => intval(substr($tmp[1], 0, 3)))
                );
                $this->writeLog(array(
                    'message' => '[' . $product_id . '] new product:' . json_encode($new_product, JSON_UNESCAPED_UNICODE),
                    'dbname'  => 'RefreshproductTash',
                    'table'   => 'updateProductPool',
                ));
                if (isset($new_product['saleFlag'])) {
                    foreach ($new_product as $k => $v) {
                        $old_product[$k] = $v;
                    }
                    $this->redis->hmset($key, $old_product);
                }
                $this->redis->zrem('refresh:product:keys', $key);
            }
        }
        $this->writeLog(array(
            'message' => 'refresh product done!',
            'dbname'  => 'RefreshproductTash',
            'table'   => 'updateProductPool',
        ));
        return true;
    }

    /**
     * 获取产品的图片地址
     * @param $returnContent 产品接口返回值
     * @param $category_id 类型ID
     * @return string
     */
    private function getProductPhoto($returnContent, $category_id = 0)
    {
        switch ($category_id) {
            case 1:
                $imageListKey    = 'hotelImageList';
                $imageContentKey = 'hotelImageURl';
                break;
            case 2:
            case 6:
            case 8:
                $imageListKey    = 'shipImageList';
                $imageContentKey = 'photoUrl';
                break;
            case 5:
            case 14:
            default:
                $imageListKey    = 'imageList';
                $imageContentKey = 'photoUrl';
        }
        return empty($returnContent[$imageListKey][0][$imageContentKey]) ? '' : $returnContent[$imageListKey][0][$imageContentKey];
    }
    private function saveRefreshKey()
    {
        $keys = $this->redis->keys('productpool:001*');
        foreach ($keys as $k => $v) {
            $this->redis->lpush('refresh:product:keys', $v);
        }
        $keys = $this->redis->keys('productpool:002*');
        foreach ($keys as $k => $v) {
            $this->redis->lpush('refresh:product:keys', $v);
        }
        $keys = $this->redis->keys('productpool:005*');
        foreach ($keys as $k => $v) {
            $this->redis->lpush('refresh:product:keys', $v);
        }
        $keys = $this->redis->keys('productpool:006*');
        foreach ($keys as $k => $v) {
            $this->redis->lpush('refresh:product:keys', $v);
        }
        $keys = $this->redis->keys('productpool:014*');
        foreach ($keys as $k => $v) {
            $this->redis->lpush('refresh:product:keys', $v);
        }
        $this->writeLog(array(
            'message' => 'keys save to refresh:product:keys success!',
            'dbname'  => 'RefreshproductTash',
            'table'   => 'saveRefreshKey',
        ));
    }
    private function writeLog($data = array())
    {
        $data['message']    = isset($data['message']) ? $data['message'] : 'not input parama!';
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['dbname']     = isset($data['dbname']) ? $data['dbname'] : 'null';
        $data['table']      = isset($data['table']) ? $data['table'] : 'null';
        $this->client->external_exec('http://' . $this->elasticsearch['host'] . ':' . $this->elasticsearch['port'] . '/es_import_log/import_db_data', json_encode($data, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE), array(), 'POST');
    }
}
