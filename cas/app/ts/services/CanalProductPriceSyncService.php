<?php

use Lvmama\Common\Components\ApiClient;

/**
 * 产品和商品实时价格同步
 * 从kafka中取出数据进行匹配, 如果符合规则就进行更新
 * @author lixiumeng
 */
class CanalProductPriceSyncService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{

    public $product_key = 'productpoolv2:';

    public $goods_key = 'goodspoolv2:';
    /**
     * 构建函数
     * @author lixiumeng
     * @datetime 2017-09-06T10:01:39+0800
     * @param    [type]                   $di [description]
     */
    public function __construct($di)
    {
        $this->di   = $di;
        $this->es   = $this->di->get('config')->get('elasticsearch');
        $this->host = $this->es->host;
        $this->port = $this->es->port;

        $this->ppd    = $this->di->get('cas')->get('product_pool_data');
        $this->dprvs  = $this->di->get('cas')->get('dest_product_rel_v2_service');
        $this->db_svs = '';

        $this->redis  = $this->di->get('cas')->getRedis();
        $this->client = new ApiClient('http://' . $this->host . ':' . $this->port);
    }

    /**
     * [handle 处理程序]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-28T09:14:46+0800
     * @version 1.0.0
     * @param   [type]                   $data [description]
     * @return  [type]                         [description]
     */
    public function handle($data)
    {
        var_dump($data);
        echo "\n";
        if (!empty($data->key) && !empty($data->payload)) {
            $key      = explode('|', $data->key);
            $action   = $key[0];
            $database = $key[1];
            $table    = $key[2];

            if ($database == 'lmm_pp') {
                $info  = json_decode($data->payload, true);
                $cinfo = $this->parseCdata($info);
                $this->upData($action, $database, $table, $cinfo);
            }
        }
    }

    /**
     * [parseCdata 解析消息]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-28T09:14:56+0800
     * @version 1.0.0
     * @param   [type]                   $info [description]
     * @return  [type]                         [description]
     */
    private function parseCdata($info)
    {
        $r = [];
        foreach ($info as $k => $v) {
            $cData = isset($v['cDatas']) ? $v['cDatas'] : null;
            if ($cData) {
                foreach ($cData as $m => $n) {
                    $r[$k][$n['name']] = $n['value'];
                    // 标识更新的字段
                    if ($n['updated'] == true) {
                        $r[$k]['updated_cloumns'][] = $n['name'];
                    }
                }
            }
        }
        return $r;
    }

    /**
     * [upData 更新数据]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-28T09:15:09+0800
     * @version 1.0.0
     * @param   [type]                   $action   [description]
     * @param   [type]                   $database [description]
     * @param   [type]                   $table    [description]
     * @param   [type]                   $data     [description]
     * @return  [type]                             [description]
     */
    private function upData($action, $database, $table, $data)
    {
        switch ($table) {
            // pp_startdistrict_addtional 中的 LOWEST_SALED_PRICE 更新时, 同时更新 pp_place的 product_price
            case 'pp_startdistrict_addtional':
                $this->proccessStartDistrict($action, $data);
                break;
            // pp_product_goods_addition 中的  LOWEST_SALED_PRICE 更新时,同步 pp_place 中的product_price
            case 'pp_product_goods_addition':
                $this->proccessGoodsAddition($action, $data);
                break;
            // pp_product 的价格更新的时候同步更新ES和dest_product_rel_v2 的sale_flag
            case 'pp_product':
                $this->proccessProduct($action, $data);
                break;
            // 更新redis的产品价格
            case 'pp_product_addtional':
                $this->proccessProductAddition($action, $data);
                break;
            // 团购秒杀价格同步
            case 'pp_product_tuan_seckill_info':
                $this->proccessTuanSeckill($action, $data);
                break;
        }
    }
    /**
     * 更新多出发地
     * @author lixiumeng
     * @datetime 2017-09-22T10:10:34+0800
     * @return   [type]                   [description]
     */
    public function proccessStartDistrict($action, $data)
    {
        $allow_array = ['LOWEST_SALED_PRICE'];
        $table       = 'pp_place';
        $this->up_db = $this->ppd;
        foreach ($data as $r) {
            if (!$this->isContainUp($allow_array, $r['updated_cloumns'])) {
                return false;
            }
            // 更新条件
            $where = "real_product_id = " . $r['PRODUCT_ID'] . " and product_district_id = " . $r['START_DISTRICT_ID'];
            // 更新内容
            $upinfo = [
                'product_price' => !empty($r['LOWEST_SALED_PRICE']) ? $r['LOWEST_SALED_PRICE'] / 100 : 0,
            ];
            $this->upDataByCondition($table, $where, $upinfo);
        }
    }

    /**
     * 更新商品
     * @author lixiumeng
     * @datetime 2017-09-22T10:17:26+0800
     * @param    [type]                   $action [description]
     * @param    [type]                   $data   [description]
     * @return   [type]                           [description]
     */
    public function proccessGoodsAddition($action, $data)
    {
        $allow_array = ['LOWEST_SALED_PRICE'];
        $type        = 2; // type 为1 产品 2 商品
        $table       = 'pp_place';
        $this->up_db = $this->ppd;

        foreach ($data as $r) {
            if (!$this->isContainUp($allow_array, $r['updated_cloumns'])) {
                return false;
            }
            $where = 'supp_goods_id = ' . $r['SUPP_GOODS_ID'];

            $price  = !empty($r['LOWEST_SALED_PRICE']) ? ($r['LOWEST_SALED_PRICE'] / 100) : 0;
            $upinfo = [
                'product_price' => $price,
            ];
            // 准备redis更新商品价格
            $id       = $r['SUPP_GOODS_ID'];
            $up_redis = [
                'lowest_saled_price' => $price,
            ];

            $this->upRedis($id, $up_redis, $type);

            $this->upDataByCondition($table, $where, $upinfo);
        }
    }

    /**
     * 处理产品
     * @author lixiumeng
     * @datetime 2017-09-22T15:07:46+0800
     * @param    [type]                   $action [description]
     * @param    [type]                   $data   [description]
     * @return   [type]                           [description]
     */
    public function proccessProduct($action, $data)
    {
        $allow_array = [
            'SALE_FLAG',
            'CATEGORY_ID',
            'SUB_CATEGORY_ID',
            'DISTRICT_ID',
            'URL_ID',
            'PRODUCT_NAME',
        ];
        $type        = 1;
        $table       = 'dest_product_rel_v2';
        $this->up_db = $this->dprvs;

        foreach ($data as $r) {
            if (!$this->isContainUp($allow_array, $r['updated_cloumns'])) {
                return false;
            }
            // 更新redis的产品可售状态
            $id       = $r['PRODUCT_ID'];
            $up_redis = [
                'sale_flag'       => $r['SALE_FLAG'],
                'product_name'    => $r['PRODUCT_NAME'],
                'category_id'     => $r['CATEGORY_ID'],
                'sub_category_id' => $r['SUB_CATEGORY_ID'],
                'district_id'     => $r['DISTRICT_ID'],
                'url_id'          => $r['URL_ID'],
            ];

            $this->upRedis($id, $up_redis, $type);

            // 如果更新可售状态
            if (in_array('SALE_FLAG', $r['updated_cloumns'])) {
                $where  = 'product_id = ' . $r['PRODUCT_ID'];
                $upinfo = [
                    'sale_flag' => $r['SALE_FLAG'],
                ];

                $this->upDataByCondition($table, $where, $upinfo);
                // 更新ES
                $this->upEs($id, $r['SALE_FLAG']);
            }
        }
    }

    /**
     * 处理产品价钱
     * @author lixiumeng
     * @datetime 2017-09-22T15:07:58+0800
     * @param    [type]                   $action [description]
     * @param    [type]                   $data   [description]
     * @return   [type]                           [description]
     */
    public function proccessProductAddition($action, $data)
    {
        $allow_array = [
            'LOWEST_SALED_PRICE',
        ];
        $type = 1;

        foreach ($data as $r) {
            if (!$this->isContainUp($allow_array, $r['updated_cloumns'])) {
                return false;
            }
            $id     = $r['PRODUCT_ID'];
            $upinfo = [
                'lowest_saled_price' => !empty($r['LOWEST_SALED_PRICE']) ? $r['LOWEST_SALED_PRICE'] / 100 : 0,
            ];
            $this->upRedis($id, $upinfo, $type);
        }

    }

    /**
     * 团购秒杀
     * @author lixiumeng
     * @datetime 2017-09-22T15:08:06+0800
     * @param    [type]                   $action [description]
     * @param    [type]                   $data   [description]
     * @return   [type]                           [description]
     */
    public function proccessTuanSeckill($action, $data)
    {
        $allow_array = ['SPIKE_TYPE_PRICE', 'GROUP_PRICE'];
        $table       = 'pp_place';
        $this->up_db = $this->ppd;
        foreach ($data as $r) {
            if (!$this->isContainUp($allow_array, $r['updated_cloumns'])) {
                return false;
            }
            $dis_channel = explode(',', $r['DISTRIBUTION_CHANNEL']);
            if (in_array(108, $dis_channel) || in_array(110, $dis_channel)) {
                if ($r['CATEGORY_ID'] == 'TICKET') {
                    $type  = 2; // 商品
                    $id    = $r['SUPP_GOODS_ID'];
                    $where = 'supp_goods_id = ' . $id;
                } else {
                    $type  = 1; // 产品
                    $id    = $r['PRODUCT_ID'];
                    $where = 'real_product_id =' . $id;
                }

                if (in_array(108, $dis_channel)) {
                    $price = $r['GROUP_PRICE'];
                }
                if (in_array(110, $dis_channel)) {
                    $price = !empty($r['SPIKE_TYPE_PRICE']) ? $r['SPIKE_TYPE_PRICE'] : $r['GROUP_PRICE'];
                }

                if (empty($price)) {
                    $price = 0;
                } else {
                    $price = $price / 100;
                }
                if ($r['MUILT_DEPARTURE_FLAG'] == 'Y') {
                    $upinfo = ['product_price' => $price];

                    $this->upDataByCondition($table, $where, $upinfo);
                } else {
                    $up_redis = ['lowest_saled_price' => $price];
                    $this->upRedis($id, $up_redis, $type);
                }
            }
        }
    }

    /**
     * 更新数据库
     * @author lixiumeng
     * @datetime 2017-09-22T10:10:12+0800
     * @param    [type]                   $db     [description]
     * @param    [type]                   $table  [description]
     * @param    [type]                   $where  [description]
     * @param    [type]                   $upinfo [description]
     * @return   [type]                           [description]
     */
    private function upDataByCondition($table, $where, $upinfo)
    {
        $this->up_db->updateByWhere($where, $upinfo);
        echo "Update {$table} : {$where}\n";
        foreach ($upinfo as $key => $value) {
            echo "$key -> $value \n ";
        }
    }

    /**
     * [upEs 更新ES]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-31T13:59:00+0800
     * @version 1.0.0
     * @param   [type]                   $id   [description]
     * @param   [type]                   $flag [description]
     * @return  [type]                         [description]
     */
    private function upEs($id, $flag)
    {
        $index = 'lmm_destination';
        $type  = 'ly_hotsale_product';

        $baseurl = 'http://' . $this->host . ':' . $this->port . '/' . $index . '/' . $type . '/';
        // 先查询
        $rs = $this->client->external_exec(
            $baseurl . '_search?q=product_id:' . $id,
            array(),
            array(),
            'get'
        );
        if (isset($rs['hits']['hits'][0]['_source']) && $rs['hits']['hits'][0]['_source']) {
            $data = $rs['hits']['hits'][0]['_source'];
            // 更新
            $up = [
                'doc' => [
                    'sale_flag' => $flag,
                ],
            ];
            $id = $data['id'];
            $this->client->external_exec(
                $baseurl . $id . '/_update',
                json_encode($up, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE),
                array(),
                'POST'
            );

            echo "Update ES lmm_destination.ly_hotsale_product {$id} sale_flag -> {$flag} \n";
        }
    }

    /**
     * [upRedis 更新redis中的产品/商品信息]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-31T13:59:13+0800
     * @version 1.0.0
     * @param   [type]                   $id   [description]
     * @param   [type]                   $info [description]
     * @param   [type]                   $type [description]
     * @return  [type]                         [description]
     */
    private function upRedis($id, $info, $type)
    {
        // 产品为1 商品为2
        if ($type == 1) {
            $key = $this->product_key . $id;
        } elseif ($type == 2) {
            $key = $this->goods_key . $id;
        }
        $rs = $this->redis->hGetall($key);
        if ($rs) {
            echo "Update Redis: {$key} \n";
            foreach ($info as $k => $v) {
                $this->redis->hset($key, $k, $v);
                echo " $k -> $v \n";
            }
        }

    }

    /**
     * b数组是否包含a数组的字段, 包含返回true,不含返回false
     * @author lixiumeng
     * @datetime 2017-09-06T16:02:23+0800
     * @return   boolean                  [description]
     */
    public function isContainUp($a, $b)
    {
        return (count(array_diff($a, $b)) < count($a));
    }

    // error interface
    public function error()
    {

    }

    public function timeOut()
    {

    }

}
