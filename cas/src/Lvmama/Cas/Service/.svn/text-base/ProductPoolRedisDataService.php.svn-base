<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Common\Utils\UCommon;

/*************************************
 * 取redis中的产品数据
 *
 * methodlist
 * getProductInfoByProductId        获取产品信息
 * getGoodsInfoByGoodsId            获取商品信息
 * missLog                          记录日志
 * _fmtSnakeToCamel                 蛇形转小驼峰命名
 ************************************/
class ProductPoolRedisDataService extends DataServiceBase
{
    const MISS_LOG_TYPE_PRODUCT = 1;
    const MISS_LOG_TYPE_GOODS   = 2;
    protected $kafka_topic_name = 'productpoolv2Ts';
    protected $lose_key         = 'unavaiable:';
    protected $baseUri          = 'http://ca.lvmama.com';

    /**
     * [__construct 服务初始化]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-31T17:32:46+0800
     * @version 1.0.0
     * @param   [type]                   $di        [description]
     * @param   [type]                   $adapter   [description]
     * @param   [type]                   $redis     [description]
     * @param   [type]                   $beanstalk [description]
     */
    public function __construct($di, $adapter, $redis = null, $beanstalk = null)
    {
        $this->di     = $di;
        $this->client = new ApiClient($this->baseUri);
        $this->redis  = $this->di->get('cas')->getRedis();
        $this->kafka  = new Producer($this->di->get("config")->kafka->toArray()[$this->kafka_topic_name]);
    }

    /**
     * [regModule 注册模块]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-01T11:17:28+0800
     * @version 1.0.0
     * @param   string                   $module [模块名字]
     * @param   integer                  $type   [1 产品   2 商品]
     * @return  [type]                           [description]
     */
    public function regModule($module = '', $type = 1)
    {
        if ($type == self::MISS_LOG_TYPE_PRODUCT) {
            $module_key = 'productpoolv2:regmodule';
        } else {
            $module_key = 'goodspoolv2:regmodule';
        }

        $ml = $this->redis->get($module_key);
        if (!empty($ml) && $module) {
            $module_info   = json_decode($ml, true);
            $module_info[] = $module;
        } else {
            $module_info = [$module];
        }
        if ($this->redis->set($module_key, json_encode(array_unique($module_info)))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [moduleList 已注册的模块]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-01T11:17:15+0800
     * @version 1.0.0
     * @param   integer                  $type [1 产品 2 商品]
     * @return  [type]                         [description]
     */
    public function moduleList($type = 1)
    {
        if ($type == self::MISS_LOG_TYPE_PRODUCT) {
            $module_key = 'productpoolv2:regmodule';
        } else {
            $module_key = 'goodspoolv2:regmodule';
        }

        $m = $this->redis->get($module_key);
        if ($m) {
            return json_decode($m, true);
        } else {
            return [];
        }
    }

    /**
     * [getProductInfoByProductId 根据产品id获取产品的信息并记录miss日志]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-19T16:13:06+0800
     * @version 1.0.0
     * @param   string                   $product_ids [description]
     * @param   string                   $from_code   [description]
     * @return  [type]                                [description]
     */
    public function getProductInfoByProductId($product_ids = '', $from_code = '')
    {
        $ids  = array_filter(explode(',', $product_ids));
        $type = self::MISS_LOG_TYPE_PRODUCT;

        return $this->_main($ids, $type, $from_code, 'product_id');
    }

    /**
     * getGoodsInfoByGoodsId 根据商品id获取商品数据并记录miss日志
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-19T16:12:28+0800
     * @version 1.0.0
     * @param   string                   $goods_ids   [description]
     * @param   string                   $from_code   [description]
     * @return  [type]                                [description]
     */
    public function getGoodsInfoByGoodsId($goods_ids = '', $from_code = '')
    {
        $ids  = array_filter(explode(',', $goods_ids));
        $type = self::MISS_LOG_TYPE_GOODS;

        return $this->_main($ids, $type, $from_code, 'supp_goods_id');
    }

    /**
     * [_main 主要流程]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-07T18:30:45+0800
     * @version 1.0.0
     * @param   [type]                   $ids       [description]
     * @param   [type]                   $type      [description]
     * @param   [type]                   $from_code [description]
     * @param   [type]                   $main_key  [description]
     * @return  [type]                              [description]
     */
    private function _main($ids, $type, $from_code, $main_key)
    {
        $r         = []; // 返回数据
        $not_empty = []; // 非空数据
        $node      = []; // redis节点数组
        $values    = []; //
        foreach ($ids as $id) {
            $key = $this->_genRedisKey($id, $type);

            $node[UCommon::calRedisNode($key)][] = $key;
        }
        foreach ($node as $k => $v) {
            $this->redis = $this->getRedisCli($k);
            $this->redis->pipeline();
            foreach ($v as $redis_key) {
                $this->redis->hGetall($redis_key);
            }
            $temp   = $this->redis->exec();
            $values = array_merge($values, $temp);
        }

        //循环redis得到的数据
        foreach ($values as $value) {
            if (!empty($value)) {
                $tid         = $value[$main_key];
                $not_empty[] = $tid;
                $r[$tid]     = $this->_fmtValue($value);
            }
        }
        $empty = array_diff($ids, $not_empty);
        //处理不存在的数据
        $this->redis = $this->di->get('cas')->getRedis();
        foreach ($empty as $n) {
            $r[$n] = [];
            $key   = $this->_genRedisKey($n, $type);
            // $this->missLog($n, $from_code, $type);
            // 检查是否数据本身无法获取,数据无法获取的话会有标记 unavaiable:productpoolv2:1
            $un = $this->redis->get($this->lose_key . $key);
            if (!$un) {
                //将未命中产品放入队列,进行产品数据的重新拉取
                $this->_sendKafkaMissInfo($key);
            }
        }
        return $r;
    }

    /**
     * [_fmtValue 处理redis中的信息的格式]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-07T19:35:10+0800
     * @version 1.0.0
     * @param   [type]                   $v [description]
     * @return  [type]                      [description]
     */
    private function _fmtValue($v)
    {
        $r = [];
        // 处理图片的cdn
        if (!empty($v['img_url'])) {
            $v['img_url'] = $this->_randCDN() . $v['img_url'];
        }
        foreach ($v as $key => $value) {
            $camel_key     = $this->_fmtSnakeToCamel($key);
            $r[$camel_key] = $value;
        }
        return $r;
    }

    /**
     * [_randCDN 随机一个cdn]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-04T14:07:50+0800
     * @version 1.0.0
     * @return  [type]                   [description]
     */
    private function _randCDN()
    {
        $list = [
            'http://pic.lvmama.com/',
            'http://s3.lvjs.com.cn/pics',
            'http://s2.lvjs.com.cn/pics',
            'http://s1.lvjs.com.cn/pics',
        ];
        return $list[array_rand($list, 1)];
    }

    /**
     * [missLog 记录未命中的产品或商品日志]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-19T16:17:13+0800
     * @version 1.0.0
     * @param   integer                  $oid        对象id
     * @param   string                   $from_code  来源模块
     * @param   string                   $result     请求结果
     * @param   integer                  $type       对象类型（1 产品   2 商品）
     * @return  [type]                               description
     */
    public function missLog($oid = 0, $from_code = '', $type = 1, $result = "failed")
    {
        $log_obj_type = ($type == 1) ? "product" : "goods";
        $log_time     = date("Y-m-d H:i:s");
        $log_msg      = "{$from_code} request {$log_obj_type} {$oid} {$result} {$log_time}";
        $log_level    = "warning";
        $log_data     = [
            'message'   => $log_msg,
            'log_level' => $log_level,
        ];
        $this->client->exec('filelogger/add-log', $log_data, '', 'POST');
    }

    /**
     * 将蛇形命名变为驼峰,可选小驼峰/大驼峰
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-14T18:04:13+0800
     * @version 1.0.0
     * @param   string                   $str      要转换的字符串
     * @param   boolean                  $ucfirst 第一个字母是否大写
     * @return  string                            处理过的字符串
     */
    private function _fmtSnakeToCamel($str = '', $ucfirst = false)
    {
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', lcfirst($str));
        return $ucfirst ? ucfirst($str) : $str;
    }

    /**
     * [_genRedisKey 根据类型和ID获取redis中key]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-25T14:43:28+0800
     * @version 1.0.0
     * @param   integer                  $id   [description]
     * @param   integer                  $type [description]
     * @return  [type]                         [description]
     */
    private function _genRedisKey($id = 0, $type = 1)
    {
        switch ($type) {
            case self::MISS_LOG_TYPE_PRODUCT:
                $redis_key_prefix = 'productpoolv2:';
                break;
            case self::MISS_LOG_TYPE_GOODS:
                $redis_key_prefix = 'goodspoolv2:';
                break;
        }
        return $redis_key_prefix . $id;
    }

    /**
     * [_sendKafkaMissInfo 向kafka发送要更新的产品或者商品信息]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-25T14:47:12+0800
     * @version 1.0.0
     * @param   string                   $info [description]
     * @return  [type]                         [description]
     */
    private function _sendKafkaMissInfo($info = '')
    {
        $this->kafka->sendMsg($info);
    }

    /**
     * [updateRedisInfo 更新redis中的产品或者商品信息]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-25T15:33:06+0800
     * @version 1.0.0
     * @param   [type]                   $id   [对象id]
     * @param   [type]                   $data [要更新的信息数组]
     * @param   [type]                   $type [类型 1 产品  2 商品]
     * @return  [type]                         [description]
     */
    public function updateRedisInfo($id, $data, $type)
    {
        $this->redis = $this->di->get('cas')->getRedis();
        $key         = $this->_genRedisKey($id, $type);
        $info        = $this->redis->hGetall($key);
        if ($info) {
            foreach ($data as $k => $v) {
                $this->redis->hset($key, $k, $v);
            }
        } else {
            $un = $this->redis->get($this->lose_key . $key);
            if (!$un) {
                // 通知更新
                $this->_sendKafkaMissInfo($key);
            }
        }
    }

    // 获取redis集群中的真实节点
    private function getRedisCli($n = 0)
    {
        return $this->di->get('cas')->getRedis($n);

    }

    /**
     * 将指定的产品或商品信息丢入kafka进行重新构建
     * @addtime 2017-08-29T13:56:41+0800
     * @param   string                   $ids  [description]
     * @param   integer                  $type [description]
     * @return  [type]                         [description]
     */
    public function rebuildInfo($ids = '', $range = '', $type = 1)
    {

        if ($type == self::MISS_LOG_TYPE_PRODUCT) {
            $pre_str = 'productpoolv2:';
        } else {
            $pre_str = 'goodspoolv2:';
        }

        if (!empty($ids)) {
            $idArr = explode(',', $ids);

            foreach ($idArr as $id) {
                $key = $pre_str . $id;
                $this->_sendKafkaMissInfo($key);
            }
            return $ids;
        } elseif (!empty($range)) {
            $a  = explode('-', $range);
            $st = (int) $a[0];
            $ed = (int) $a[1];
            for ($i = $st; $i <= $ed; $i++) {
                $key = $pre_str . $i;
                $this->_sendKafkaMissInfo($key);
            }
            return $range;
        }
        return false;
    }

    /**
     * 重置产品池更新的标记
     * @addtime 2017-08-29T13:56:26+0800
     * @return  [type]                   [description]
     */
    public function flashRedisFlag($id, $type = 1)
    {
        if ($type == self::MISS_LOG_TYPE_PRODUCT) {
            $redis_flag = 'product_pool_redis_v2_jack';
        } else {
            $redis_flag = 'goods_pool_redis_v2_jack';
        }

        if (empty($id)) {
            $id = 1;
        }

        return $this->redis->set($redis_flag, $id, 3600 * 2);
    }

}
