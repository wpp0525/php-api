<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Utils\UCommon;

/**
 * 商品池优化2期
 *
 * @author jackdong
 *
 */
class GoodsPoolRedisV2Service implements DaemonServiceInterface
{

    /**
     * 产品表
     * @var ProductPoolProductService
     */
    private $product_pool_product;

    /**
     * redis service
     * @var $redis
     */
    private $redis;

    /**
     * @var $goodspoolv2Redis
     */
    private $goodspoolv2Redis;

    /**
     * redis 当前更新指针 key
     * @var string
     */
    private $redis_cache_key = 'goods_pool_redis_v2_jack';

    /**
     * 产品池新的key值
     * @var string
     */
    private $product_pool_key_v2 = 'goodspoolv2:';

    /**
     * @var tsrv
     */
    private $tsrv;

    public $kafka_topic_name = 'productpoolv2cronproducergoods';

    public function __construct($di)
    {
        $this->di                   = $di;
        $this->product_pool_product = $di->get('cas')->get('product_pool_product');
        $this->product_pool_product->setReconnect(true);

        $this->tsrv  = $di->get('tsrv');
        $this->redis = $di->get('cas')->getRedis();
        $this->step  = microtime(true);
    }

    private function getRedisNodeByKey($id)
    {
        $k = $this->product_pool_key_v2 . $id;
        $n = UCommon::calRedisNode($k);
        echo "this node " . $n . "\n";
        return $this->di->get('cas')->getRedis($n);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    public function process($a = null, $b = 'c')
    {
        $loop = true;
        while ($loop) {

            // 分页条数
            $limit = 1000;

            // 获取游标
            $last_id = $this->getLastId();

            $where = $last_id;

            //$where = " `pp_product`.PRODUCT_ID > $last_id ";
            //$goods_infos = $this->product_pool_product->getDefaultList($where, $limit);
            $this->cost("start");
            // 待优化
            $columns = '`pp_product_goods`.SUPP_GOODS_ID,`pp_product_goods`.SEQ,`pp_product_goods`.SUPPLIER_ID,`pp_product_goods`.PRODUCT_ID,`pp_product_goods`.PRODUCT_BRANCH_ID,`pp_product_goods`.GOODS_NAME,`pp_product_goods`.GOODS_TYPE,`pp_product_goods`.CANCEL_FLAG,`pp_product_goods`.ONLINE_FLAG,`pp_product_goods`.GOODS_DESC,`pp_product_goods`.GOODS_SPEC,`pp_product_goods`.TODAY_ONLINE_FLAG,`pp_product_goods`.CATEGORY_ID,
        `pp_product_goods_addition`.LOWEST_SALED_PRICE,`pp_product_goods_addition`.LOWEST_MARKET_PRICE,`pp_product_goods_addition`.SETTLEMENT_PRICE';
            $goods_infos = $this->product_pool_product->getGoodsAndAddition($where, $limit, $columns);
            $this->cost("sql");
            if (empty($goods_infos)) {
                // 重置游标
                $loop = false;
                $this->setLastId(0);
                $this->stopFlag();
            }

            foreach ($goods_infos as $goods_infos_value) {

                echo '游标值:' . $this->getLastId() . PHP_EOL;
                echo 'supp_goods_id:' . $goods_infos_value['SUPP_GOODS_ID'] . PHP_EOL;

                if (empty($goods_infos_value['SUPP_GOODS_ID'])) {
                    continue;
                }

                $insert_data                  = array();
                $insert_data['supp_goods_id'] = $goods_infos_value['SUPP_GOODS_ID'];
                $insert_data['supplier_id']   = $goods_infos_value['SUPPLIER_ID'];
                $insert_data['product_id']    = $goods_infos_value['PRODUCT_ID'];

                $insert_data['product_branch_id'] = $goods_infos_value['PRODUCT_BRANCH_ID'];
                $insert_data['goods_name']        = $goods_infos_value['GOODS_NAME'];
                $insert_data['goods_type']        = $goods_infos_value['GOODS_TYPE'];
                $insert_data['cancel_flag']       = $goods_infos_value['CANCEL_FLAG'];

                $insert_data['online_flag']       = $goods_infos_value['ONLINE_FLAG'];
                $insert_data['goods_desc']        = $goods_infos_value['GOODS_DESC'];
                $insert_data['seq']               = $goods_infos_value['SEQ'];
                $insert_data['goods_spec']        = $goods_infos_value['GOODS_SPEC'];
                $insert_data['today_online_flag'] = $goods_infos_value['TODAY_ONLINE_FLAG'];

                $insert_data['lowest_saled_price']  = !empty($goods_infos_value['LOWEST_SALED_PRICE']) ? $goods_infos_value['LOWEST_SALED_PRICE'] / 100 : 0;
                $insert_data['lowest_market_price'] = !empty($goods_infos_value['LOWEST_MARKET_PRICE']) ? $goods_infos_value['LOWEST_MARKET_PRICE'] / 100 : 0;
                $insert_data['settlement_price']    = !empty($goods_infos_value['SETTLEMENT_PRICE']) ? $goods_infos_value['SETTLEMENT_PRICE'] / 100 : 0;
                $insert_data['url']                 = '';
                $this->cost("set");
                /*************从接口获取商品url的信息 lixiumeng 17-08-04****************/
                try {
                    $url = $this->tsrv->exec('unityProduct/getTMHUrl', array(
                        'params' => json_encode(array('objectId' => $goods_infos_value['SUPP_GOODS_ID'], 'objectType' => 'BRANCH')),
                    ));
                    if (!empty($url['returnContent'])) {
                        $insert_data['url'] = $url['returnContent'];
                    }
                } catch (\Exception $e) {

                }
                /*************从接口获取商品url的信息 lixiumeng 17-08-04***************/
                $this->cost("url");

                $this->redis->hMset($this->product_pool_key_v2 . $goods_infos_value['SUPP_GOODS_ID'], $insert_data);
                $this->redis->expire($this->product_pool_key_v2 . $goods_infos_value['SUPP_GOODS_ID'], 2592000); //过期时间1个月

                // 更新游标
                $this->setLastId($goods_infos_value['SUPP_GOODS_ID']);

            }
        }

    }

    /**
     * 设置游标
     * @param $id
     * @return mixed
     */
    public function setLastId($id)
    {
        $result = $this->redis->set($this->redis_cache_key, $id, 86400);
        return $result;
    }

    /**
     * 获取游标
     * @return mixed
     */
    public function getLastId()
    {
        $result = $this->redis->get($this->redis_cache_key);

        if (empty($result)) {
            $this->redis->set($this->redis_cache_key, 0, 86400);
            $result = $this->redis->get($this->redis_cache_key);
        }

        return $result;
    }

    private function stopFlag()
    {
        exit('程序跑完了，回家吃饭!');
    }

    private function cost($tag = '')
    {
        $cost       = microtime(true) - ($this->step);
        $this->step = microtime(true);
        echo $tag . " cost time: " . $cost . " s\n";
    }

    /**
     * 每天将产品信息送入kafka
     * @author lixiumeng
     * @datetime 2017-08-31T19:03:33+0800
     * @return   [type]                   [description]
     */
    public function putGoodsInKafka()
    {
        $limit       = 10000;
        $key         = "goods_cron_flag";
        $flag        = $this->redis->get($key);
        $goods_id    = !empty($flag) ? $flag : 0;
        $this->kafka = new Producer($this->di->get("config")->kafka->toArray()[$this->kafka_topic_name]);
        $this->db    = $this->di->get('cas')->get('product_pool_data');
        $this->db->setReconnect(true);
        $time_limit = "2016-06-01 00:00:00";

        $where = "CANCEL_FLAG = 'Y' and SUPP_GOODS_ID > " . $goods_id . ' and UPDATE_TIME > "' . $time_limit . '" limit ' . $limit;

        $params = [
            'select' => 'SUPP_GOODS_ID',
            'table'  => 'pp_product_goods',
            'where'  => $where,
        ];

        $rs = $this->db->getAllByParams($params);

        if (!empty($rs)) {

            foreach ($rs as $v) {
                $goods_id = $v['SUPP_GOODS_ID'];

                $kafka_key = $this->product_pool_key_v2 . $goods_id;

                $this->kafka->sendMsg($kafka_key);

            }
            $flag = $goods_id;
        } else {
            $flag = 0;

        }

        $this->redis->set($key, $flag);

        echo "well ,done, $goods_id \n";

    }

}
