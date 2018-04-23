<?php

use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Utils\Filelogger;
use Lvmama\Common\Utils\UCommon;

/**
 * 重新构建kafka消息队列中的产品和商品数据
 *
 * @author libiying
 *
 * 操作产品池中的数据形式
 * 1. 全量更新  productpoolv2:12344
 * 2. 部分更新  productpoolv2:12344:url
 * 3. 直接指定更新的值 productpooolv2:12344:update:url_id-17,dest_id-15
 *
 */
class ProductPoolV2Service implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
    const TYPE_PRODUCT = 1;

    const TYPE_GOODS = 2;

    // 大目的地base url
    public $destBaseUri = 'http://ca.lvmama.com/';

    public $di;

    /**
     * 不合法的产品id
     * @var string
     */
    public $lost_product = 'unavaiable:productpoolv2:';

    /**
     * 不合法的商品池
     * @var string
     */
    public $lost_goods = 'unavaiable:goodspoolv2:';

    /**
     * 产品池新的key值
     * @var string
     */
    private $product_pool_key_v2 = 'productpoolv2:';

    /**
     * @var Lvmama\Cas\Service\DestBaseDataService
     */
    private $dest_base;

    /**
     * 产品池新的key值
     * @var string
     */
    private $goods_pool_key_v2 = 'goodspoolv2:';

    private $baseUrl = 'http://ca.lvmama.com/';

    private $addition_kafka_config = 'productpoolv2additionproducer'; // kafka配置信息的topic


    public $product_part = ['base', 'url', 'comment', 'img', 'promotion', 'addition', 'dest']; // 产品可以指定更新的部分

    public $goods_part = ['base', 'url', 'promotion']; // 商品可以指定更新的部分

    private $ticket_category = array(11);

    public function __construct($di)
    {
        $this->di         = $di;
        $this->tsrv       = $this->di->get('tsrv');
        $this->db_hotel   = $this->di->get('cas')->getDbServer('dbhotel');
        $this->db_ship    = $this->di->get('cas')->getDbServer('dbship');
        $this->db_sem     = $this->di->get('cas')->getDbServer('dbsem');
        $this->db_propool = $this->di->get('cas')->getDbServer('dbpropool');
        $this->db_vst     = $this->di->get('cas')->getDbServer('dbvst');
        $this->redis      = $this->di->get('cas')->getRedis();
        $this->dest_base  = $this->di->get('cas')->get('dest_base_service');
        // kafka 生产者, 通知生成产品附加信息
        $this->kafka = new Producer($this->di->get('config')->kafka->toArray()[$this->addition_kafka_config]);
    }

    /**
     * 主处理函数
     * @author lixiumeng
     * @datetime 2017-09-21T19:30:16+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    public function handle($data)
    {
        var_dump($data);
        echo "\n";
        // 校验数据
        if (!empty($data) && $data->err == 0 && !empty($data->payload)) {
            $this->tmp  = []; // 临时数据 没个产品的临时信息存储于此
            $this->meta = [
                'id'       => 0, // 产品id
                'all'      => 1, // 是否全量更新
                'key'      => '', // 消息队列中的key
                'rediskey' => '', // 产品在redis中的key
                'type'     => 1, // 产品类型
                'run'      => 1, // 是否有基本的产品信息,无基本信息标记为不可获取
            ]; // 元信息
            $this->proccess($data->payload);
        } else {
            echo 'data is invalid!';
        }
    }

    /**
     * 根据key处理数据
     * @author lixiumeng
     * @datetime 2017-09-21T14:52:49+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function proccess($key)
    {
        $str = explode(':', $key);

        $this->meta['id']       = $str[1];
        $this->meta['key']      = $key; // key
        $this->meta['rediskey'] = $str[0] . ":" . $str[1]; // productpoolv2:111

        if ($str[0] == 'productpoolv2') {
            $this->meta['type'] = self::TYPE_PRODUCT;
            $parts              = $this->product_part;
        } elseif ($str[0] == 'goodspoolv2') {
            $this->meta['type'] = self::TYPE_GOODS;
            $parts              = $this->goods_part;
        }

        if (!empty($str[2])) {
            // 如果是直接更新redis
            if (strtolower($str[2]) == 'update' && !empty($str[3])) {
                $info = [];
                foreach (explode(',', $str[3]) as $k_v) {
                    $tmp_map = explode('-', $k_v);

                    $info[$tmp_map[0]] = $tmp_map[1];
                }
                $this->updateRedis($this->meta['rediskey'], $info);
                return false;
            } else {
                // 处理部分
                $parts = explode(',', $str[2]);

                $this->meta['all'] = 0;
            }
        }

        foreach ($parts as $part) {
            // 更新
            switch ($part) {
                case "base":
                    if ($this->meta['type'] == 1) {
                        $this->getProductBaseInfo();
                    } else {
                        $this->getGoodsBaseInfo();
                    }
                    if (!$this->meta['run']) {
                        return false;
                    }
                    break;
                case "url":
                    $this->getUrl();
                    break;
                case "comment":
                    $this->getComment();
                    break;
                case "img":
                    $this->getImg();
                    break;
                case "promotion":
                    $this->getPromotion();
                    break;
                case "dest":
                    $this->getDest();
                    break;
                case "addition":
                    $this->notificationAddition();
                    break;
                case "start":
                    $this->getStart();
                    break;
            }
        }
        $this->saveRedis();
    }
    /**
     * 获取图片
     * @author lixiumeng
     * @datetime 2017-09-21T18:55:31+0800
     * @return   [type]                   [description]
     */
    public function getUrl()
    {
        $id               = $this->meta['id'];
        $this->tmp['url'] = '';
        switch ($this->meta['type']) {
            // 产品
            case self::TYPE_PRODUCT:
                // 获取产品对应的前端页面url地址
                try {
                    if (!empty($this->tmp['product_id'])) {
                        $url_id       = $this->tmp['url_id'];
                        $product_id   = $this->tmp['product_id'];
                        $category_id  = $this->tmp['category_id'];
                        $product_type = $this->tmp['product_type'];
                    } else {
                        $sql = "select URL_ID,CATEGORY_ID,PRODUCT_TYPE from pp_product where PRODUCT_ID = {$id}";
                        $rs  = $this->db_propool->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);
                        if (!empty($rs)) {
                            $product_id   = $id;
                            $url_id       = $rs['URL_ID'];
                            $category_id  = $rs['CATEGORY_ID'];
                            $product_type = $rs['product_type'];
                        } else {
                            return false;
                        }
                    }
                    $pid = !empty($url_id) ? $url_id : $product_id;
                    // 处理部分接口没处理的url
                    if ($category_id == 28) {
                        if ($product_type == 'PHONE') {
                            // 电话卡
                            $this->tmp['url'] = "http://dujia.lvmama.com/phcard/" . $pid;
                        } elseif ($product_type == 'WIFI') {
                            // wifi
                            $this->tmp['url'] = "http://dujia.lvmama.com/wifi/" . $pid;
                        } else {
                            $this->tmp['url'] = '';
                        }
                    } else {
                        //兼容门票,门票使用urlid
                        $url = $this->tsrv->exec('product/findProductUrl', array(
                            'params' => '{"productId":"' . $pid . '","categoryId":"' . $category_id . '"}',
                        ));
                        if (isset($url['success']) && $url['success'] == 1) {
                            $this->tmp['url'] = isset($url['content']) ? $url['content'] : '';
                        }
                    }

                } catch (\Exception $e) {
                    var_dump($e);
                    die;
                }
                break;

            case self::TYPE_GOODS:
                try {
                    $url = $this->tsrv->exec('unityProduct/getTMHUrl', array(
                        'params' => json_encode(array('objectId' => $id, 'objectType' => 'BRANCH')),
                    ));
                    if (!empty($url['returnContent'])) {
                        $this->tmp['url'] = $url['returnContent'];
                    }
                } catch (\Exception $e) {
                    var_dump($e);
                    die;
                }
                break;
        }
    }

    /**
     * 获取点评
     * @author lixiumeng
     * @datetime 2017-09-21T18:56:22+0800
     * @return   [type]                   [description]
     */
    public function getComment()
    {
        $this->tmp['comment_good'] = $this->tmp['comment_count'] = '';
        $id                        = $this->meta['id'];
        //点评数据
        try {
            $comment = $this->tsrv->exec('product/getVstCmtTitleStatisticsByProductId', array('productId' => $id));

            $this->tmp['comment_count'] = !empty($comment['commentCount']) ? $comment['commentCount'] : '';
            $this->tmp['comment_good']  = !empty($comment['formatAvgScore']) ? $comment['formatAvgScore'] : '';

        } catch (\Exception $e) {
            var_dump($e);
            die;
        }
    }

    /**
     * 获取图片
     * @author lixiumeng
     * @datetime 2017-09-21T18:56:30+0800
     * @return   [type]                   [description]
     */
    public function getImg()
    {
        $this->tmp['img_url'] = '';
        $id                   = $this->meta['id'];
        try {
            $params = array('objectId' => $id, 'objectType' => 'PRODUCT_ID');
            $rs     = $this->tsrv->exec('product/findImageList', array('params' => json_encode($params)));
            if (!empty($rs['returnContent']) && !empty($rs['returnContent']['photoUrl'])) {
                $this->tmp['img_url'] = $rs['returnContent']['photoUrl'];
            }
        } catch (\Exception $e) {
            var_dump($e);die;
        }
    }

    /**
     * 获取促销信息
     * @author lixiumeng
     * @datetime 2017-09-21T18:56:40+0800
     * @return   [type]                   [description]
     */
    public function getPromotion()
    {
        $this->tmp['promotion'] = '';
        $id                     = $this->meta['id'];

        if ($this->meta['type'] == self::TYPE_PRODUCT) {
            $object_type = 'PRODUCT';
        } elseif ($this->meta['type'] == self::TYPE_GOODS) {
            $object_type = 'GOODS';
        } else {
            return false;
        }
        $info = [];

        $sql = "select PROM_PROMOTION_ID from lmm_sem.sem_prom_goods where `SUPP_GOODS_ID` = {$id} and `OBJECT_TYPE` = '{$object_type}'";

        $rs = $this->db_sem->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);

        if (!empty($rs)) {
            $ids = implode(',', array_column($rs, 'PROM_PROMOTION_ID'));

            $sql_promotion = "select TITLE,BEGIN_TIME,END_TIME from lmm_sem.sem_prom_promotion where `VALID` = 'Y' and `PROM_PROMOTION_ID` in ($ids) and `END_TIME` > '" . date('Y-m-d H:i:s') . "'";

            $info = $this->db_sem->fetchAll($sql_promotion, Phalcon\Db::FETCH_ASSOC);
        }

        $this->tmp['promotion'] = !empty($info) ? json_encode($info, JSON_UNESCAPED_UNICODE) : '';
    }

    /**
     * 获取产品信息
     * @author lixiumeng
     * @datetime 2017-09-21T17:32:22+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function getProductBaseInfo()
    {
        // 从缓存中获取该产品的分类
        $category_id = $this->redis->hget($this->meta['rediskey'], 'category_id');
        $id          = $this->meta['id'];

        // 邮轮
        $this->cates_ship  = [2, 8, 9, 10];
        $this->cates_hotel = [1];
        // $cates_line  = [];
        if (!empty($category_id) && in_array($category_id, $this->cates_ship)) {
            $this->getShipInfo();
            // 酒店
        } elseif (!empty($category_id) && in_array($category_id, $this->cates_hotel)) {
            $this->getHotelInfo();
        } else {
            // 其他分类
            $sql = "SELECT `pp_product`.PRODUCT_ID,`pp_product`.PRODUCT_TYPE,`pp_product`.CATEGORY_ID,`pp_product`.PRODUCT_NAME,`pp_product`.SALE_FLAG,`pp_product`.DISTRICT_ID,`pp_product`.URL_ID,`pp_product`.SUB_CATEGORY_ID,`pp_product_addtional`.LOWEST_MARKET_PRICE,`pp_product_addtional`.LOWEST_SALED_PRICE FROM
                `pp_product` LEFT JOIN `pp_product_addtional` ON `pp_product`.PRODUCT_ID = `pp_product_addtional`.PRODUCT_ID WHERE
                `pp_product`.PRODUCT_ID = {$id}";

            $this->rs = $this->db_propool->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);
        }

        if (!empty($this->rs)) {
            $v        = $this->rs;
            //门票类产品,行政区ID为空
            if(in_array($v['CATEGORY_ID'],$this->ticket_category) && empty($v['DISTRICT_ID'] && is_numeric($v['URL_ID']))){
                $district_id = $this->dest_base->getDistrictIdByProductIdAndUrlId($v['PRODUCT_ID'],$v['URL_ID']);
                if($district_id) $v['DISTRICT_ID'] = $district_id;
            }
            $this->rs = '';
            $this->tmp['product_id']          = $v['PRODUCT_ID'];
            $this->tmp['category_id']         = $v['CATEGORY_ID'];
            $this->tmp['product_name']        = $v['PRODUCT_NAME'];
            $this->tmp['product_type']        = $v['PRODUCT_TYPE'];
            $this->tmp['lowest_saled_price']  = !empty($v['LOWEST_SALED_PRICE']) ? $v['LOWEST_SALED_PRICE'] / 100 : 0;
            $this->tmp['lowest_market_price'] = !empty($v['LOWEST_MARKET_PRICE']) ? $v['LOWEST_MARKET_PRICE'] / 100 : $this->tmp['lowest_saled_price'];
            $this->tmp['sale_flag']           = $v['SALE_FLAG'];
            $this->tmp['district_id']         = $v['DISTRICT_ID'];
            $this->tmp['url_id']              = $v['URL_ID'];
            $this->tmp['sub_category_id']     = $v['SUB_CATEGORY_ID'];
            $this->tmp['category_name']       = Ucommon::categoryNameById($v['CATEGORY_ID']);
            // 特殊处理酒店价钱
            // if (in_array($v['CATEGORY_ID'], [1])) {
            //     $this->getHotelPrice($id);
            // }
            // 特殊处理邮轮
            // if (in_array($v['CATEGORY_ID'], [2, 8, 9, 10])) {
            //     $this->getShipPrice($id);
            // }
            $this->meta['run'] = 1;
        } else {
            $this->meta['run'] = 0;
            $this->_setUnavaiableKey($id, self::TYPE_PRODUCT);
        }
    }

    /**
     * 获取出发地信息
     * @author lixiumeng
     * @datetime 2017-09-22T15:59:19+0800
     * @return   [type]                   [description]
     */
    public function getStart()
    {
        $id = $this->meta['id'];

        $info = [];

        $sql = "select ID,PRODUCT_ID,START_DISTRICT_ID from lmm_pp.pp_startdistrict_addtional where PRODUCT_ID = " . $id;

        $rs = $this->db_propool->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);

        if (!empty($rs)) {

            // $ids = implode(',', array_column($rs, 'START_DISTRICT_ID'));

            // // 添加地区名称
            // $sql_district = "select district_id,district_name from biz_district where district_id in ($ids)";
            // $rs_district  = $this->db_vst->fetchAll($sql_district, Phalcon\Db::FETCH_ASSOC);

            // $dis_info = [];
            // if (!empty($rs_district)) {
            //     foreach ($rs_district as $n) {
            //         $dis_info[$n['district_id']] = $n['district_name'];
            //     }
            // }

            foreach ($rs as $v) {
                $district_key  = 'district:' . $v['START_DISTRICT_ID'];
                $district_name = $this->redis->hGet($district_key, 'district_name');

                $info[] = [
                    'DISTRICT_NAME'     => !empty($district_name) ? $district_name : '',
                    'ID'                => $v['ID'],
                    'PRODUCT_ID'        => $v['PRODUCT_ID'],
                    'START_DISTRICT_ID' => $v['START_DISTRICT_ID'],
                ];
            }
        }

        $this->tmp['start_district'] = json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取商品基本信息
     * @author lixiumeng
     * @datetime 2017-09-21T17:32:30+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function getGoodsBaseInfo()
    {
        $id  = $this->meta['id'];
        $sql = "SELECT `pp_product_goods`.SUPP_GOODS_ID,`pp_product_goods`.SEQ,`pp_product_goods`.SUPPLIER_ID,`pp_product_goods`.PRODUCT_ID,`pp_product_goods`.PRODUCT_BRANCH_ID,`pp_product_goods`.GOODS_NAME,`pp_product_goods`.GOODS_TYPE,`pp_product_goods`.CANCEL_FLAG,`pp_product_goods`.ONLINE_FLAG,`pp_product_goods`.GOODS_DESC,`pp_product_goods`.GOODS_SPEC,`pp_product_goods`.TODAY_ONLINE_FLAG,`pp_product_goods`.CATEGORY_ID,
        `pp_product_goods_addition`.LOWEST_SALED_PRICE,`pp_product_goods_addition`.LOWEST_MARKET_PRICE,`pp_product_goods_addition`.SETTLEMENT_PRICE FROM `pp_product_goods` LEFT JOIN `pp_product_goods_addition` ON `pp_product_goods`.SUPP_GOODS_ID = `pp_product_goods_addition`.SUPP_GOODS_ID WHERE `pp_product_goods`.SUPP_GOODS_ID = {$id}";
        $v = $this->db_propool->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);
        if (!empty($v)) {

            $this->tmp['supp_goods_id']       = $v['SUPP_GOODS_ID'];
            $this->tmp['supplier_id']         = $v['SUPPLIER_ID'];
            $this->tmp['product_id']          = $v['PRODUCT_ID'];
            $this->tmp['product_branch_id']   = $v['PRODUCT_BRANCH_ID'];
            $this->tmp['goods_name']          = $v['GOODS_NAME'];
            $this->tmp['goods_type']          = $v['GOODS_TYPE'];
            $this->tmp['cancel_flag']         = $v['CANCEL_FLAG'];
            $this->tmp['online_flag']         = $v['ONLINE_FLAG'];
            $this->tmp['goods_desc']          = $v['GOODS_DESC'];
            $this->tmp['seq']                 = $v['SEQ'];
            $this->tmp['goods_spec']          = $v['GOODS_SPEC'];
            $this->tmp['today_online_flag']   = $v['TODAY_ONLINE_FLAG'];
            $this->tmp['lowest_saled_price']  = !empty($v['LOWEST_SALED_PRICE']) ? $v['LOWEST_SALED_PRICE'] / 100 : 0;
            $this->tmp['lowest_market_price'] = !empty($v['LOWEST_MARKET_PRICE']) ? $v['LOWEST_MARKET_PRICE'] / 100 : 0;
            $this->tmp['settlement_price']    = $v['SETTLEMENT_PRICE'] / 100;
            $this->meta['run']                = 1;
        } else {
            $this->meta['run'] = 0;
            $this->_setUnavaiableKey($id, self::TYPE_GOODS);
        }

    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function timeOut()
    {
        // TODO: Implement timeOut() method.
        echo date("Y-m-d H:i:s") . PHP_EOL;
        echo 'time out!' . PHP_EOL;
    }

    // 记录日志
    private function _missLog($id = 0, $from_code = 'kxlx', $type = 1)
    {
        $log_obj_type = ($type == 1) ? "product" : "goods";
        $log_time     = date("Y-m-d H:i:s");
        $result       = "failed";
        $log_msg      = "{$from_code} request {$log_obj_type} {$id} {$result} {$log_time}";
        $log_level    = 'warning';
        $this->_writeLog($log_msg, $log_level);
    }

    /**
     * 记录日志
     * @author lixiumeng
     * @datetime 2017-09-04T16:34:50+0800
     * @param    string                   $msg [description]
     * @return   [type]                        [description]
     */
    private function _writeLog($msg = '', $log_level = 'ERROR')
    {
        $message = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
        Filelogger::getInstance()->addLog($message, $log_level);
    }

    /**
     * [_setUnavaiableKey 设置失效的key]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-03T15:31:19+0800
     * @version 1.0.0
     * @param   [type]                   $id   [description]
     * @param   integer                  $type [description]
     */
    private function _setUnavaiableKey($id, $type = 1)
    {
        if ($type == self::TYPE_PRODUCT) {
            $lost_key = $this->lost_product . $id;
        } else {
            $lost_key = $this->lost_goods . $id;
        }
        $this->redis->set($lost_key, 1);
        $this->redis->expire($lost_key, 3600 * 24 * 7);
    }

    /**
     * 获取酒店产品价钱
     * @author lixiumeng
     * @datetime 2017-09-06T13:51:36+0800
     * @param    [type]                   $product_id [description]
     * @return   [type]                               [description]
     */
    // public function getHotelPrice($id)
    // {
    //     $sql = "select LOWEST_SALED_PRICE from prod_product where PRODUCT_ID = {$id}";

    //     //$sql = "select LOWEST_SALED_PRICE from supp_goods_addition where SUPP_GOODS_ID = {$id}";

    //     $rs = $this->db_hotel->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);

    //     if (!empty($rs)) {
    //         $hotel_price = !empty($rs['LOWEST_SALED_PRICE']) ? $rs['LOWEST_SALED_PRICE'] / 100 : 0;
    //     } else {
    //         $hotel_price = 0;
    //     }

    //     $this->tmp['lowest_market_price'] = $this->tmp['lowest_saled_price'] = $hotel_price;
    // }

    /**
     * 获取邮轮产品的价钱
     * @author lixiumeng
     * @datetime 2017-10-11T10:45:45+0800
     * @return   [type]                   [description]
     */
    // public function getShipPrice($id)
    // {
    //     $sql = "select LOWEST_SALED_PRICE,LOWEST_MARKET_PRICE from prod_product_addtional where PRODUCT_ID = {$id}";

    //     $rs = $this->db_ship->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);

    //     if (!empty($rs)) {
    //         $saled_price  = !empty($rs['LOWEST_SALED_PRICE']) ? $rs['LOWEST_SALED_PRICE'] / 100 : 0;
    //         $market_price = !empty($rs['LOWEST_MARKET_PRICE']) ? $rs['LOWEST_MARKET_PRICE'] / 100 : 0;
    //     } else {
    //         $market_price = $saled_price = 0;
    //     }
    //     $this->tmp['lowest_saled_price']  = $saled_price;
    //     $this->tmp['lowest_market_price'] = $market_price;
    // }

    /**
     * 获取邮轮的基本信息
     * @author lixiumeng
     * @datetime 2017-10-24T16:24:46+0800
     * @return   [type]                   [description]
     */
    public function getShipInfo()
    {
        $sql = "SELECT `prod_product`.PRODUCT_ID,`prod_product`.PRODUCT_TYPE,`prod_product`.CATEGORY_ID,`prod_product`.PRODUCT_NAME,`prod_product`.SALE_FLAG,`prod_product`.DISTRICT_ID,`prod_product`.URL_ID,`prod_product`.SUB_CATEGORY_ID,`prod_product_addtional`.LOWEST_MARKET_PRICE,`prod_product_addtional`.LOWEST_SALED_PRICE FROM `prod_product` LEFT JOIN `prod_product_addtional` ON `prod_product`.`PRODUCT_ID` = `prod_product_addtional`.`PRODUCT_ID` WHERE `prod_product`.PRODUCT_ID = {$this->meta['id']}";

        $this->rs = $this->db_ship->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);
    }

    /**
     * 获取酒店产品的基本信息
     * @author lixiumeng
     * @datetime 2017-10-24T16:24:59+0800
     * @return   [type]                   [description]
     */
    public function getHotelInfo()
    {
        $sql = "SELECT `LOWEST_SALED_PRICE`,`PRODUCT_ID`,`PRODUCT_TYPE`,`CATEGORY_ID`,`DISTRICT_ID`,`PRODUCT_NAME`,`SALE_FLAG`,`URL_ID`,`SUB_CATEGORY_ID` FROM prod_product WHERE `PRODUCT_ID` = {$this->meta['id']}";

        $this->rs = $this->db_hotel->fetchOne($sql, Phalcon\Db::FETCH_ASSOC);
    }

    /**
     * 通知重构产品附加信息
     * @author lixiumeng
     * @datetime 2017-09-13T14:24:44+0800
     * @return   [type]                   [description]
     */
    public function notificationAddition()
    {
        $key = $this->meta['rediskey'];
        $this->kafka->sendMsg($key);
    }

    /**
     * 获取目的地ID
     * @author lixiumeng
     * @datetime 2017-09-15T16:15:12+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function getDest()
    {
        $id = $this->meta['id'];

        // 如果是邮轮,获取邮轮相关的信息
        if (!empty($this->tmp['category_id']) && in_array($this->tmp['category_id'], $this->cates_ship)) {
            $sql = "select DEST_ID,MAIN_FLAG from prod_dest_re where PRODUCT_ID = {$id}";
            $rs  = $this->db_ship->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);
        } else {
            $sql = "select DEST_ID,MAIN_FLAG from pp_product_dest_rel where PRODUCT_ID = {$id}";
            $rs  = $this->db_propool->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);
        }

        // $sql = "select distinct DEST_ID from pp_product_dest_rel where PRODUCT_ID = {$id}";

        // $rs = $this->db_propool->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);
        $main    = [];
        $normal  = [];
        $dest_id = 0;
        if (!empty($rs)) {
            foreach ($rs as $value) {
                if ($value['MAIN_FLAG'] == 'Y') {
                    $main[] = $value['DEST_ID'];
                } else {
                    $normal[] = $value['DEST_ID'];
                }
            }
            $dest_id = implode(',', array_merge($main, $normal));
            //$dest_id = implode(',', array_unique(array_column($rs, 'DEST_ID')));
        } else {
            $dest_id = 0;
        }
        $this->tmp['dest_id'] = $dest_id;
    }

    /**
     * 更新redis数据
     * @author lixiumeng
     * @datetime 2017-09-21T19:11:27+0800
     * @param    [type]                   $key  [description]
     * @param    [type]                   $info [description]
     * @return   [type]                         [description]
     */
    public function updateRedis($key, $info)
    {
        $rs = $this->redis->hGetall($key);
        if (!empty($rs) && !empty($info)) {
            foreach ($info as $k => $v) {
                $this->redis->hset($key, $k, $v);
            }
        }
    }

    /**
     *
     * @author lixiumeng
     * @datetime 2017-09-21T19:20:49+0800
     * @return   [type]                   [description]
     */
    public function saveRedis()
    {
        $key = $this->meta['rediskey'];
        if ($this->meta['all']) {
            $this->redis->hMset($key, $this->tmp);
        } else {
            $this->updateRedis($key, $this->tmp);
        }
        $this->redis->expire($key, 2592000);
    }

}
