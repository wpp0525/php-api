<?php

/**
 * pp_place 表中的product_id字段处理为real_product_id
 */
class CanalFixProductIdService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{

    /**
     * 构造函数
     * @author lixiumeng
     * @datetime 2017-09-25T13:52:08+0800
     * @param    [type]                   $di [description]
     */
    public function __construct($di)
    {
        $this->di = $di;

        $this->ppd = $this->di->get('cas')->get('product_pool_data');
    }

    /**
     * 处理
     * @author lixiumeng
     * @datetime 2017-09-25T13:52:22+0800
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
            if ($action == 'INSERT' && $database == 'lmm_pp' && $table = 'pp_place') {
                $info  = json_decode($data->payload, true);
                $cinfo = $this->parseCdata($info);
                $this->upData($action, $database, $table, $cinfo);
            }
        }
    }

    /**
     * 转换数据
     * @author lixiumeng
     * @datetime 2017-09-06T18:18:09+0800
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
                }
            }
        }
        return $r;
    }

    /**
     * 处理新插入的数据
     * @author lixiumeng
     * @datetime 2017-09-06T18:23:23+0800
     * @param    [type]                   $action   [description]
     * @param    [type]                   $database [description]
     * @param    [type]                   $table    [description]
     * @param    [type]                   $cinfo    [description]
     * @return   [type]                             [description]
     */
    private function upData($action, $database, $table, $cinfo)
    {
        if ($action == 'INSERT' && $database = 'lmm_pp' && $table == 'pp_place') {
            foreach ($cinfo as $v) {

                $real_product_id = intval(substr($v['product_id'], -10));

                $where = " id = " . $v['id'];

                $info = [
                    'real_product_id' => $real_product_id,
                ];
                $this->ppd->updateByWhere($where, $info);

                echo "Update" . $where . " real_product_id: ";
                echo $v['product_id'] . "->" . $real_product_id . "\n";

            }

        }
    }

    public function error()
    {

    }

    public function timeOut()
    {

    }

}
