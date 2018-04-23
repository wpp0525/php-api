<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\Kafka\Producer;
use Lvmama\Common\Utils\UCommon;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\Filelogger;

/**
 * 产品池优化2期
 *
 * @author jackdong|lixiumeng|shenxiang
 *
 */
class ProductPoolRedisV2Service implements DaemonServiceInterface
{

    /**
     * 产品表
     * @var ProductPoolProductService
     */
    private $product_pool_product;

    /**
     * @var destin_multi_relation_base_service
     */
    private $destin_multi_relation_base;

    /**
     * redis service
     * @var $redis
     */
    private $redis;

    /**
     * @var $productpoolv2Redis
     */
    private $productpoolv2Redis;

    /**
     * redis key
     * @var string
     */
    private $redis_cache_key = 'product_pool_redis_v2_jack';

    /**
     * 产品池新的key值
     * @var string
     */
    private $product_pool_key_v2 = 'productpoolv2:';
    /**
     * @var Lvmama\Cas\Service\DestBaseDataService
     */
    private $dest_base;

    private $tsrv;

    private $baseUrl = 'http://ca.lvmama.com/';

    public $process_key = "productpoolv2:processing"; // 正在处理的域 列表

    public $timeout = 600; // 10 分钟

    public $sub_process_limit = 100;

    public $proccessed = 0; // 当前处理范围是否已处理

    public $kafka_topic_name = 'productpoolv2cronproducer';

    public $district_prifix = 'district:';

    private $ticket_category = array(11);

    public $provincial      = [
        '河北省'      => '石家庄',
        '河南省'      => '郑州',
        '湖北省'      => '武汉',
        '湖南省'      => '长沙',
        '江苏省'      => '南京',
        '江西省'      => '南昌',
        '辽宁省'      => '沈阳',
        '吉林省'      => '长春',
        '黑龙江省'     => '哈尔滨',
        '陕西省'      => '西安',
        '山西省'      => '太原',
        '山东省'      => '济南',
        '四川省'      => '成都',
        '青海省'      => '西宁',
        '安徽省'      => '合肥',
        '海南省'      => '海口',
        '广东省'      => '广州',
        '贵州省'      => '贵阳',
        '浙江省'      => '杭州',
        '福建省'      => '福州',
        '台湾省'      => '台北',
        '甘肃省'      => '兰州',
        '云南省'      => '昆明',
        '西藏自治区'    => '拉萨',
        '宁夏回族自治区'  => '银川',
        '广西壮族自治区'  => '南宁',
        '新疆维吾尔自治区' => '乌鲁木齐',
        '内蒙古自治区'   => '呼和浩特',
        // '上海'       => '上海市',
        // '北京'       => '北京市',
        // '天津'       => '天津市',
        // '重庆'       => '重庆市',
        // '香港'       => '香港',
        // '澳门'       => '澳门',
    ];

    public function __construct($di)
    {
        $this->di                   = $di;
        $this->product_pool_product = $di->get('cas')->get('product_pool_product');
        $this->dest_base            = $di->get('cas')->get('dest_base_service');
        $this->product_pool_product->setReconnect(true);

        $this->tsrv  = $di->get('tsrv');
        $this->redis = $di->get('cas')->getRedis();
        //$this->client = new ApiClient($this->baseUrl);
        $this->step = microtime(true); // 记录初始化时间,用于性能调试
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

    public function process($a = null, $b = null)
    {
        $loop = true;
        while ($loop) {
            // 分页条数
            $limit = 5000;
            // 获取游标
            $last_id = $this->getLastId();
            //$this->stp = $last_id;
            $where = '`pp_product`.PRODUCT_ID > '.$last_id;
            if(!empty($a[1]) && is_numeric($a[1])){
                $where .= ' AND `pp_product`.CATEGORY_ID = '.$a[1];
            }
            $this->cost("start");
            $columns       = '`pp_product`.PRODUCT_ID,`pp_product`.CATEGORY_ID,`pp_product`.PRODUCT_NAME,`pp_product`.SALE_FLAG,`pp_product`.DISTRICT_ID,`pp_product`.URL_ID,`pp_product`.SUB_CATEGORY_ID,`pp_product_addtional`.LOWEST_MARKET_PRICE,`pp_product_addtional`.LOWEST_SALED_PRICE';
            $product_infos = $this->product_pool_product->getProductAndAddtional($where, $limit, $columns);

            $this->cost("sql");
            if (empty($product_infos)) {
                // 重置游标
                $loop = false;
                $this->setLastId(0);
                $this->stopFlag();
            }
            $this->edp = (int) $product_infos[count($product_infos) - 1]['PRODUCT_ID']; //end process
            echo '游标值:' . $this->getLastId() . PHP_EOL;
            foreach ($product_infos as $product_infos_value) {
                echo ' product_id:' . $product_infos_value['PRODUCT_ID'] . PHP_EOL;

                if (empty($product_infos_value['PRODUCT_ID'])) {
                    continue;
                }
                /**
                 * 对门票类型的产品设置所属城市的行政区ID
                 * 需要符合的条件:1、门票类型 2、本身的行政区ID为空 3、URL_ID存在
                 */
                if(in_array($product_infos_value['CATEGORY_ID'],$this->ticket_category) && empty($product_infos_value['DISTRICT_ID']) && $product_infos_value['URL_ID']){
                    $product_infos_value['DISTRICT_ID'] = $this->dest_base->getDistrictIdByProductIdAndUrlId($product_infos_value['PRODUCT_ID'],$product_infos_value['URL_ID']);
                }

                $insert_data                    = array();
                $insert_data['product_id']      = $product_infos_value['PRODUCT_ID'];
                $insert_data['product_name']    = $product_infos_value['PRODUCT_NAME'];
                $insert_data['category_id']     = $product_infos_value['CATEGORY_ID'];
                $insert_data['sub_category_id'] = $product_infos_value['SUB_CATEGORY_ID'];
                $insert_data['sale_flag']       = $product_infos_value['SALE_FLAG'];
                $insert_data['district_id']     = $product_infos_value['DISTRICT_ID'];
                $insert_data['url_id']          = $product_infos_value['URL_ID'];
                $insert_data['lowest_market_price'] = empty($product_infos_value['LOWEST_MARKET_PRICE']) ? 0 : $product_infos_value['LOWEST_MARKET_PRICE'] / 100;
                $insert_data['lowest_saled_price']  = empty($product_infos_value['LOWEST_SALED_PRICE']) ? 0 : $product_infos_value['LOWEST_SALED_PRICE'] / 100;
                $insert_data['category_name'] = Ucommon::categoryNameById($insert_data['category_id']);
                $insert_data['img_url']         = '';
                $insert_data['url']             = '';
                $insert_data['comment_good']    = '';
                $insert_data['comment_count']   = '';
                // 获取产品对应的前端页面url地址
                try {
                    $pid = empty($product_infos_value['URL_ID']) ? $product_infos_value['PRODUCT_ID'] : $product_infos_value['URL_ID'];
                    $url = $this->tsrv->exec('product/findProductUrl', array(
                        'params' => '{"productId":"' . $pid . '","categoryId":"' . $product_infos_value['CATEGORY_ID'] . '"}',
                    ));
                    if (isset($url['success']) && $url['success'] == 1) {
                        $insert_data['url'] = isset($url['content']) ? $url['content'] : '';
                    }
                    //补全产品图片url
                    $params = array('objectId' => $product_infos_value['PRODUCT_ID'], 'objectType' => 'PRODUCT_ID');
                    $rs     = $this->tsrv->exec('product/findImageList', array('params' => json_encode($params)));
                    if (!empty($rs['returnContent']) && !empty($rs['returnContent']['photoUrl'])) {
                        $insert_data['img_url'] = $rs['returnContent']['photoUrl'];
                    }
                    //点评数据
                    $comment = $this->tsrv->exec('product/getVstCmtTitleStatisticsByProductId', array('productId' => $product_infos_value['PRODUCT_ID']));
                    if (isset($comment['commentCount'])) {
                        $insert_data['comment_count'] = isset($comment['commentCount']) ? $comment['commentCount'] : '';
                        $insert_data['comment_good']  = isset($comment['formatAvgScore']) ? $comment['formatAvgScore'] : '';
                    }
                    $this->redis->hMset($this->product_pool_key_v2 . $product_infos_value['PRODUCT_ID'], $insert_data);
                    $this->redis->expire($this->product_pool_key_v2 . $product_infos_value['PRODUCT_ID'], RedisDataService::REDIS_EXPIRE_ONE_MONTH); //过期时间1个月
                    // 更新游标
                    $this->setLastId($product_infos_value['PRODUCT_ID']);
                    usleep(100);
                } catch (\Exception $e) {
                    Filelogger::getInstance()->addLog(
                        '异常,参数['.json_encode($product_infos_value,JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE).']'.$e->getFile().','.$e->getMessage(),
                        'WARNING'
                    );
                    print_r($e);
                }
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
     * 添加处理内容到列表
     * @addtime 2017-08-29T23:08:36+0800
     */
    public function addProcessList()
    {
        //如果处理列表中有该范围.且时间小于超时时间. 则不允许使用该范围
        $now  = time();
        $list = $this->redis->lrange($this->process_key, 0, $this->sub_process_limit);

        foreach ($list as $v) {
            $tmp      = explode(":", $v);
            $duration = $now - $tmp[1];
            if ($tmp[0] == $this->stp && ($duration < $this->timeout)) {
                $this->setLastId($this->edp);
                return false;
            }
        }
        $str = "{$this->stp}:" . $now;
        $this->redis->lpush($this->process_key, $str);
        // 如果是继续处理被中断的任务, 不处理游标
        if (!$this->proccessed) {
            $this->setLastId($this->edp);
        }

        return true;
    }

    /**
     * 获取需要处理的范围
     * @addtime 2017-08-29T23:07:17+0800
     * @return  [type]                   [description]
     */
    public function getProcessRange()
    {
        // 如果列表中有超过时间的, 视为任务中断
        $list = $this->redis->lrange($this->process_key, 0, $this->sub_process_limit);
        $now  = time();
        foreach ($list as $v) {
            $t        = explode(":", $v);
            $duration = $now - $t[1];
            if ($duration > $this->timeout) {
                $this->redis->lrem($this->process_key, $v, 1);
                $this->proccessed = true; // 标记该区域是已经被处理过的
                return $t[0];
            }
        }
        $this->processed = false; // 标记该分段未被处理过
        return $this->getLastId();
    }

    /**
     * 从处理列表里移除已处理过的标记
     * @addtime 2017-08-29T23:08:12+0800
     * @return  [type]                   [description]
     */
    public function markSolve()
    {
        // remove  from list
        $rs = $this->redis->lrange($this->process_key, 0, $this->sub_process_limit);
        foreach ($rs as $v) {
            $tmp = explode(":", $v);
            if ($tmp[0] == $this->stp) {
                return $this->redis->lrem($this->process_key, $v, 5);
            }
        }
        return false;
    }

    /**
     * 每天将产品信息送入kafka
     * @author lixiumeng
     * @datetime 2017-08-31T19:03:33+0800
     * @return   [type]                   [description]
     */
    public function putProductInKafka()
    {
        $limit = 10000;

        $key = "product_cron_flag";

        $flag        = $this->redis->get($key);
        $product_id  = !empty($flag) ? $flag : 0;
        $this->kafka = new Producer($this->di->get("config")->kafka->toArray()[$this->kafka_topic_name]);
        $this->db    = $this->di->get('cas')->get('product_pool_data');
        $this->db->setReconnect(true);

        $time_limit = "2016-01-01 00:00:00"; //仅处理16年之后有更新的

        // 更新时间要加引号
        $where = "CANCEL_FLAG = 'Y' and PRODUCT_ID > " . $product_id . " and UPDATE_TIME > '" . $time_limit . "' limit " . $limit;

        $params = [
            'select' => 'PRODUCT_ID',
            'table'  => 'pp_product',
            'where'  => $where,
        ];

        $rs = $this->db->getAllByParams($params);

        if (!empty($rs)) {

            foreach ($rs as $v) {
                $product_id = $v['PRODUCT_ID'];

                $kafka_key = $this->product_pool_key_v2 . $product_id;

                $this->kafka->sendMsg($kafka_key);

            }

            $flag = $product_id;

        } else {
            $flag = 0;
        }
        $this->redis->set($key, $flag);
        echo "well ,done $product_id\n";

    }

    /**
     * 修复产品id
     * @author lixiumeng
     * @datetime 2017-09-06T18:10:16+0800
     * @return   [type]                   [description]
     */
    public function fixProductId()
    {
        $this->ppd = $this->di->get('cas')->get('product_pool_data');
        $this->ppd->setReconnect(true);

        $loop = true;
        $id   = 0;

        while ($loop) {

            $params = [
                'table'  => 'pp_place',
                'select' => 'id,product_id',
                'where'  => 'real_product_id = 0 and product_id > 0 and id > ' . $id . ' limit 1000',
            ];

            $rs = $this->ppd->getAllByParams($params);

            if (!empty($rs)) {

                foreach ($rs as $v) {
                    $id              = $v['id'];
                    $real_product_id = intval(substr($v['product_id'], -10));

                    $where = "id = " . $id;
                    $info  = [
                        'real_product_id' => $real_product_id,
                    ];

                    $this->ppd->updateByWhere($where, $info);

                }

                echo "update done " . $id . "\n";

            } else {
                $loop = false;
            }
            sleep(2);
        }

    }

    /**
     * 建行政区信息索引
     * @author lixiumeng
     * @datetime 2017-10-13T11:48:07+0800
     * @return   [type]                   [description]
     */
    public function buildDistrict()
    {
        $this->db = $this->di->get('cas')->getDbServer('dbvst');

        $loop = true;
        $time = 3600 * 24 * 30;
        $id   = 0;

        while ($loop) {
            $sql = "select  * from biz_district where district_id > {$id} limit 1000";
            $rs  = $this->db->fetchAll($sql, Phalcon\Db::FETCH_ASSOC);

            if (empty($rs)) {
                $loop = false;
                $id   = 0;
            } else {
                foreach ($rs as $v) {
                    $id = $v['district_id'];
                    // 设置  district:8 基本信息
                    $key = $this->district_prifix . $id;
                    $this->redis->hmset($key, $v);
                    $this->redis->expire($key, $time);

                    // district:children:8 所有下一级行政区集合,例如查询江苏省的所有市
                    $key = $this->district_prifix . "children:" . $v['parent_id'];
                    $this->redis->sadd($key, $id);
                    $this->redis->expire($key, $time);

                    // district:tree:8查询一个行政区相关的所有父级信息 如 南京->江苏->中国->亚洲
                    $this->tmp_data                = [];
                    $this->tmp_data['district_id'] = $id;
                    $this->getTreeInfoByDistrictId($id);

                    if (empty($this->tmp_data['town'])) {
                        if (empty($this->tmp_data['county'])) {
                            if (empty($this->tmp_data['city'])) {
                                if (empty($this->tmp_data['province'])) {
                                    if (empty($this->tmp_data['country'])) {
                                        if (empty($this->tmp_data['continent'])) {
                                            $this->tmp_data['current_district'] = '';
                                        } else {
                                            $this->tmp_data['current_district'] = $this->tmp_data['continent'];
                                        }
                                    } else {
                                        $this->tmp_data['current_district'] = $this->tmp_data['country'];
                                    }
                                } else {
                                    $this->tmp_data['current_district'] = $this->tmp_data['province'];
                                }
                            } else {
                                $this->tmp_data['current_district'] = $this->tmp_data['city'];
                            }
                        } else {
                            $this->tmp_data['current_district'] = $this->tmp_data['county'];
                        }
                    } else {
                        $this->tmp_data['current_district'] = $this->tmp_data['town'];
                    }

                    if (isset($this->provincial[$this->tmp_data['province']])) {
                        $this->tmp_data['provincial'] = $this->provincial[$this->tmp_data['province']];
                    } else {
                        $this->tmp_data['provincial'] = '';
                    }

                    $key = $this->district_prifix . "tree:" . $id;
                    $this->redis->hmset($key, $this->tmp_data);
                    $this->redis->expire($key, $time);

                    echo "district:" . $id . "\n";
                }
            }
            // $this->redis->set($build_flag, $id);
        }
        echo "well,done";
    }

    /**
     * 获取行政区的所有信息
     * @author lixiumeng
     * @datetime 2017-10-13T16:57:33+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function getTreeInfoByDistrictId($id)
    {
        if (!empty($id)) {
            $key = $this->district_prifix . $id;
            $rs  = $this->redis->hgetall($key);
            if (!empty($rs)) {
                switch ($rs['district_type']) {
                    case 'CONTINENT':
                        $this->tmp_data['continent'] = $rs['district_name'];
                        break;
                    case 'CITY':
                        $this->tmp_data['city'] = !empty($rs['city_name']) ? $rs['city_name'] : $rs['district_name'];
                        break;
                    case 'COUNTRY':
                        $this->tmp_data['country'] = !empty($rs['district_name']) ? $rs['district_name'] : '';
                        break;
                    case 'COUNTY':
                        $this->tmp_data['county'] = !empty($rs['district_name']) ? $rs['district_name'] : '';
                        break;
                    case 'PROVINCE':
                        $this->tmp_data['province'] = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        break;
                    case 'PROVINCE_AN': // 民族自治区
                        $this->tmp_data['province'] = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        break;
                    case 'PROVINCE_SA': // 港澳
                        $this->tmp_data['province'] = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['city']     = $this->tmp_data['province'];
                        break;

                    case 'PROVINCE_DCG': // 直辖市
                        $this->tmp_data['province'] = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['city']     = $this->tmp_data['province'];
                        break;

                    case 'TOWN':
                        $this->tmp_data['town'] = !empty($rs['district_name']) ? $rs['district_name'] : '';
                        break;
                }
                $this->getTreeInfoByDistrictId($rs['parent_id']);
            }

            $arr = ['town', 'province', 'county', 'city', 'continent'];

            foreach ($arr as $k) {
                if (!isset($this->tmp_data[$k])) {
                    $this->tmp_data[$k] = '';
                }
            }
        }

        return $this->tmp_data;
    }

    /**
     * 补全门票类所属城市及以上级别的行政区ID
     * 1、获取指定类型的产品ID集合
     * 2、获取产品ID对应的详细信息
     * 3、取得产品的URL_ID
     * 4、根据URL_ID获取其所在城市的DEST_ID
     * 5、根据DEST_ID取得DISTRICT_ID
     * 6、将得到的DISTRICT_ID写到相应的产品信息的行政区ID字段
     * @author shenxiang
     */
    public function complementTicketDistrictId(){
        foreach($this->ticket_category as $category_id){
            $this->dest_base->complementProductPoolV2DistrictId($category_id);
        }
        return true;
    }
}
