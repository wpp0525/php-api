<?php

use Lvmama\Cas\Component\Kafka\Producer;

class CanalPromotionSyncService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{

    public $product_pool_key = 'productpoolv2:';

    public $goods_pool_key = 'goodspoolv2:';

    public $productpoolv2topic = 'productpoolv2Ts';

    public $tmp_set = "productpoolv2:promotion:tmp_set";

    public function __construct($di)
    {
        $this->di = $di;
        //$this->db_sem = new MasterSlaveDbAdapter($this->di->get('config')['dbsem']->toArray());
        $this->db_sem = $this->di->get('cas')->getDbServer('dbsem');
        $this->kafka  = new Producer($this->di->get('config')->kafka->toArray()[$this->productpoolv2topic]);
        $this->redis  = $this->di->get('cas')->getRedis();
        //$this->redis  = '';
    }

    /**
     * [handle description]
     * @author lixiumeng
     * @datetime 2017-09-18T16:52:55+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
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

            if ($database = 'lmm_sem') {
                $info  = json_decode($data->payload, true);
                $cinfo = $this->parseCdata($info);
                $this->upData($action, $database, $table, $cinfo);
            }
        }
    }

    /**
     * [parseCdata description]
     * @author lixiumeng
     * @datetime 2017-09-18T16:52:59+0800
     * @param    [type]                   $info [description]
     * @return   [type]                         [description]
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
     * [upData description]
     * @author lixiumeng
     * @datetime 2017-09-18T16:59:06+0800
     * @param    [type]                   $action [description]
     * @param    [type]                   $db     [description]
     * @param    [type]                   $table  [description]
     * @param    [type]                   $data   [description]
     * @return   [type]                           [description]
     */
    private function upData($action, $db, $table, $data)
    {
        if ($table == 'sem_prom_promotion') {
            $ids = implode(',', array_column($data, 'PROM_PROMOTION_ID'));
            if (empty($ids)) {
                return false;
            }
            $sql = "select SUPP_GOODS_ID,OBJECT_TYPE from sem_prom_goods where PROM_PROMOTION_ID in ({$ids})";

            $rs = $this->db_sem->fetchAll($sql, \PDO::FETCH_ASSOC);

            foreach ($rs as $k => $v) {
                if ($v['OBJECT_TYPE'] == 'PRODUCT') {
                    $key = $this->product_pool_key . $v['SUPP_GOODS_ID'] . ":promotion";
                } elseif ($v['OBJECT_TYPE'] == 'GOODS') {
                    $key = $this->goods_pool_key . $v['SUPP_GOODS_ID'];
                }
                $this->redis->sadd($this->tmp_set, $key);
            }

        } elseif ($table == 'sem_prom_goods') {

            foreach ($data as $k => $v) {
                if ($v['OBJECT_TYPE'] == 'PRODUCT') {
                    $key = $this->product_pool_key . $v['SUPP_GOODS_ID'] . ":promotion";
                } elseif ($v['OBJECT_TYPE'] == 'GOODS') {
                    $key = $this->goods_pool_key . $v['SUPP_GOODS_ID'];
                }
                $this->redis->sadd($this->tmp_set, $key);
            }
        }

        $time        = time();
        $up_flag     = 'productpoolv2:promotion:uptime';
        $last_uptime = $this->redis->get($up_flag);

        // 每5s执行一次
        if (empty($last_uptime) || ($time - $last_uptime) > 5) {

            $this->updateByTime();
            $this->redis->set($up_flag, $time);
        }
    }

    /**
     * 每隔几秒更新一次
     * @author lixiumeng
     * @datetime 2017-10-17T17:38:09+0800
     * @return   [type]                   [description]
     */
    public function updateByTime()
    {
        $memebers = $this->redis->smembers($this->tmp_set);
        $this->redis->del($this->tmp_set);

        if (!empty($memebers)) {
            foreach ($memebers as $value) {
                $this->kafka->sendMsg($value);
            }
        }
    }

    /**
     * [error description]
     * @author lixiumeng
     * @datetime 2017-09-18T16:53:04+0800
     * @return   [type]                   [description]
     */
    public function error()
    {

    }

    /**
     * [timeOut description]
     * @author lixiumeng
     * @datetime 2017-09-18T16:53:08+0800
     * @return   [type]                   [description]
     */
    public function timeOut()
    {

    }

}
