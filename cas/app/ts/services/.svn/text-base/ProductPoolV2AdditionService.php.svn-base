<?php

class ProductPoolV2AdditionService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
    const PRODUCT_KEY = 'productpoolv2';

    const GOODS_KEY = 'goodspoolv2';

    public $product_addition_prefix = 'productpoolv2:addition:';

    public $goods_addition_prefix = 'goodspoolv2:addition:';

    public $district_prifix = 'district:';

    public $route_array = [15, 181, 182, 183, 32, 16]; // 线路的分类

    /**
     * 构造函数
     * @author lixiumeng
     * @datetime 2017-09-08T17:31:19+0800
     * @param    [type]                   $di [description]
     */
    public function __construct($di)
    {
        $this->di    = $di;
        $this->redis = $this->di->get('cas')->getRedis();
        //$this->db_propool = new MasterSlaveDbAdapter($this->di->get('config')['dbpropool']->toArray());
        //$this->db_vst     = new MasterSlaveDbAdapter($this->di->get('config')['dbvst']->toArray());
        $this->tsrv = $this->di->get('tsrv');

        $this->db_propool = $this->di->get('cas')->getDbServer('dbpropool');
        $this->db_vst     = $this->di->get('cas')->getDbServer('dbvst');

        $this->key = '';
    }

    /**
     * 处理数据
     * @author lixiumeng
     * @datetime 2017-09-08T17:31:26+0800
     * @param    [type]                   $data [description]
     * @return   [type]                         [description]
     */
    public function handle($data)
    {
        var_dump($data);
        echo "\n";

        if (!empty($data) && !empty($data->payload)) {
            $key = $data->payload;

            $this->data     = []; // 要处理的数据
            $this->tmp_data = []; // 临时数据
            $this->meta     = [
                'rediskey' => $key,
            ]; // 部分过渡数据

            $v = explode(':', $key);

            $id       = $v[1];
            $this->id = $id;

            if ($v[0] == self::PRODUCT_KEY) {
                $this->productUpdate($id);
            } else {
                $this->goodsUpdate($id);
            }
        }
    }

    /**
     * 更新产品
     * @author lixiumeng
     * @datetime 2017-09-12T11:01:18+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function productUpdate($id)
    {
        // 获取目的地信息
        $this->getDestDistrictInfo();
        // 获取出发地信息
        $this->getStartDistrictInfo();
        // 获取图片列表
        $this->getPicList();
        // 写入redis
        $this->_writeRedis();
    }

    /**
     * 预留
     * @author lixiumeng
     * @datetime 2017-10-16T17:13:24+0800
     * @param    [type]                   $id [description]
     * @return   [type]                       [description]
     */
    public function goodsUpdate($id)
    {

    }

    /**
     * 获目的地取行政区信息
     * @author lixiumeng
     * @datetime 2017-10-16T17:20:22+0800
     * @return   [type]                   [description]
     */
    public function getDestDistrictInfo()
    {
        // 从redis获取产品的districtid
        $info       = $this->redis->hgetall($this->meta['rediskey']);
        $this->info = $info;
        //酒店
        if ($info['category_id'] == 1) {
            $district_id = !empty($info['district_id']) ? $info['district_id'] : 0;
        } else {
            $dest_id = !empty($info['dest_id']) ? explode(',', $info['dest_id'])[0] : 0;

            $district_id = $this->getDistrictIdByDestId($dest_id);
        }

        $this->meta['district_id'] = $district_id;

        $this->data['product_id'] = $this->id;
        $key                      = 'district:tree:' . $this->meta['district_id'];
        $tmp_info                 = $this->redis->hGetall($key);

        $this->data['district_town']     = !empty($tmp_info) ? $tmp_info['town'] : '';
        $this->data['district_province'] = !empty($tmp_info) ? $tmp_info['province'] : '';
        $this->data['district_country']  = !empty($tmp_info) ? $tmp_info['country'] : '';
        $this->data['district_city']     = !empty($tmp_info) ? $tmp_info['city'] : '';
        $this->data['district_county']   = !empty($tmp_info) ? $tmp_info['county'] : '';

        // 获取目的地行政区的省份信息
        //$dis_info = $this->getTreeInfoByDistrictId($this->meta['district_id']);

        //$this->data['district_province_json'] = $dis_info['district_province_json'];
    }

    /**
     * 添加出发地信息
     * @author lixiumeng
     * @datetime 2017-09-13T18:24:58+0800
     * @return   [type]                   [description]
     */
    public function getStartDistrictInfo()
    {
        $rs = [];
        // 线路类的产品行政区就是出发地
        if (in_array($this->info['category_id'], [14, 15, 16, 17, 18, 29, 32, 42])) {

            if (!empty($this->info['district_id'])) {
                $rs = [
                    [
                        'START_DISTRICT_ID' => $this->info['district_id'],
                    ],
                ];
            }
        } else {
            $sql = "select PRODUCT_ID,START_DISTRICT_ID from lmm_pp.pp_startdistrict_addtional where product_id = " . $this->id;

            $rs = $this->query($sql);
        }

        $info = [];
        if (!empty($rs)) {
            /**从缓存中获取行政区信息*/
            foreach ($rs as $v) {
                $key   = 'district:' . $v['START_DISTRICT_ID'];
                $value = $this->redis->hGetall($key);
                if (!empty($value)) {
                    switch ($value['district_type']) {
                        case 'CITY':
                            $info['district_city'][] = $value['district_name'];
                            break;

                        case 'COUNTRY':
                            $info['district_country'][] = $value['district_name'];
                            break;

                        case 'COUNTY':
                            $info['district_county'][] = $value['district_name'];
                            break;

                        case 'PROVINCE':
                            $info['district_province'][] = $value['district_name'];
                            break;

                        case 'PROVINCE_AN': // 民族自治区
                            $info['district_province'][] = $value['district_name'];
                            break;

                        case 'PROVINCE_SA': // 港澳
                            $info['district_city'][] = $value['district_name'];
                            break;

                        case 'PROVINCE_DCG': // 直辖市
                            $info['district_city'][] = $value['district_name'];
                            break;

                        case 'TOWN':
                            $info['district_town'][] = $value['district_name'];
                            break;
                    }
                }
            }
            /***从缓存中获取行政区信息**/
        }
        $this->data['start_district_country']  = !empty($info['district_country']) ? implode(',', array_unique($info['district_country'])) : "";
        $this->data['start_district_county']   = !empty($info['district_county']) ? implode(',', array_unique($info['district_county'])) : "";
        $this->data['start_district_city']     = !empty($info['district_city']) ? implode(',', array_unique($info['district_city'])) : "";
        $this->data['start_district_province'] = !empty($info['district_province']) ? implode(',', array_unique($info['district_province'])) : "";
        $this->data['start_district_town']     = !empty($info['district_town']) ? implode(',', array_unique($info['district_town'])) : "";
    }

    /**
     * [getPicList description]
     * @author lixiumeng
     * @datetime 2017-09-14T10:21:55+0800
     * @return   [type]                   [description]
     */
    public function getPicList()
    {
        $params = array('objectId' => $this->id, 'objectType' => 'PRODUCT_ID', 'isFilter' => false);
        $rs     = $this->tsrv->exec('product/findImageList', array('params' => json_encode($params)));

        $this->data['img_1'] = !empty($rs['returnContent'][1]['photoUrl']) ? $rs['returnContent'][1]['photoUrl'] : '';
        $this->data['img_2'] = !empty($rs['returnContent'][2]['photoUrl']) ? $rs['returnContent'][2]['photoUrl'] : '';
        $this->data['img_3'] = !empty($rs['returnContent'][3]['photoUrl']) ? $rs['returnContent'][3]['photoUrl'] : '';
    }

    /**
     * 写数据
     * @author lixiumeng
     * @datetime 2017-09-12T16:34:20+0800
     * @return   [type]                   [description]
     */
    private function _writeRedis()
    {
        $this->key = $this->product_addition_prefix . $this->id;
        $this->redis->hmset($this->key, $this->data);
        $this->redis->expire($this->key, 2592000);
        $this->key  = '';
        $this->data = [];
    }

    /**
     * 查询
     * @author lixiumeng
     * @datetime 2017-09-22T16:37:05+0800
     * @param    [type]                   $sql [description]
     * @param    integer                  $one [description]
     * @return   [type]                        [description]
     */
    public function query($sql, $one = 0)
    {
        if ($one) {
            return $this->db_propool->fetchOne($sql, \PDO::FETCH_ASSOC);
        }
        return $this->db_propool->fetchAll($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 查询
     * @author lixiumeng
     * @datetime 2017-09-22T16:37:05+0800
     * @param    [type]                   $sql [description]
     * @param    integer                  $one [description]
     * @return   [type]                        [description]
     */
    public function query_vst($sql, $one = 0)
    {
        if ($one) {
            return $this->db_vst->fetchOne($sql, \PDO::FETCH_ASSOC);
        }
        return $this->db_vst->fetchAll($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 获取目的地的行政区信息
     * @author lixiumeng
     * @datetime 2017-09-22T16:42:25+0800
     * @param    [type]                   $dest_id [description]
     * @return   [type]                            [description]
     */
    public function getDistrictIdByDestId($dest_id)
    {
        if (empty($dest_id)) {
            return 0;
        }

        $sql = "select district_id from lmm_vst_destination.biz_dest where dest_id = " . $dest_id;

        $rs = $this->query_vst($sql, 1);

        if (!empty($rs) && !empty($rs['district_id'])) {
            return $rs['district_id'];
        }
        return 0;
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
                        $this->tmp_data['province']                 = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['district_province_json'][] = [
                            'province_id'   => $id,
                            'province_name' => $this->tmp_data['province'],
                        ];
                        break;
                    case 'PROVINCE_AN': // 民族自治区
                        $this->tmp_data['province']                 = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['district_province_json'][] = [
                            'province_id'   => $id,
                            'province_name' => $this->tmp_data['province'],
                        ];
                        break;
                    case 'PROVINCE_SA': // 港澳
                        $this->tmp_data['province']                 = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['city']                     = $this->tmp_data['province'];
                        $this->tmp_data['district_province_json'][] = [
                            'province_id'   => $id,
                            'province_name' => $this->tmp_data['province'],
                        ];
                        break;

                    case 'PROVINCE_DCG': // 直辖市
                        $this->tmp_data['province']                 = !empty($rs['province_name']) ? $rs['province_name'] : $rs['district_name'];
                        $this->tmp_data['city']                     = $this->tmp_data['province'];
                        $this->tmp_data['district_province_json'][] = [
                            'province_id'   => $id,
                            'province_name' => $this->tmp_data['province'],
                        ];
                        break;

                    case 'TOWN':
                        $this->tmp_data['town'] = !empty($rs['district_name']) ? $rs['district_name'] : '';
                        break;
                }
                $this->getTreeInfoByDistrictId($rs['parent_id']);
            }

            $arr = ['town', 'province', 'county', 'city', 'continent', 'district_province_json'];

            foreach ($arr as $k) {
                if (!isset($this->tmp_data[$k])) {
                    $this->tmp_data[$k] = '';
                }
            }
        }

        return $this->tmp_data;
    }

}
