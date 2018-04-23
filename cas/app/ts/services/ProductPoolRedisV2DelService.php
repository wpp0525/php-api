<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Components\ApiClient;

/**
 * 产品池优化2期
 *
 * @author jackdong
 *
 */
class ProductPoolRedisV2DelService implements DaemonServiceInterface
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
    private $redis_cache_key = 'product_pool_redis_v2_del_jack';

    /**
     * 产品池新的key值
     * @var string
     */
    private $product_pool_key_v2 = 'productpoolv2:';

    public function __construct($di)
    {

        $this->redis              = $di->get('cas')->getRedis();
        $this->productpoolv2Redis = $di->get('productpoolredis');

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
        while (1) {

            // 获取游标
            $last_id = $this->getLastId();

            echo '当前游标值:' . $last_id . PHP_EOL;

            if ( $last_id > 10000000 ) {
                // 重置游标
                $this->setLastId(0);
                $this->stopFlag();
            }

//            $this->productpoolv2Redis->hMset($this->product_pool_key_v2 . $product_infos_value['PRODUCT_ID'], $insert_data);
            $this->productpoolv2Redis->expire($this->product_pool_key_v2 . $last_id, 0 ); //过期时间1个月

            // 更新游标
            ++$last_id;
            $this->setLastId($last_id);

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

}
