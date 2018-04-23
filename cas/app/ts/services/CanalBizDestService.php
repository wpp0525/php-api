<?php
/**
 * Created by PhpStorm.
 * User: jackdong
 * Date: 17/8/15
 * Time: 下午4:28
 */

class CanalBizDestService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{

    // 规则分隔符
    const PATTEN = ".";

    // 规则数字为4位
    const ORDER_UNITS = 4;

    // 经纬度坐标
    const COORDINATE_FLAG = 1;

    // 大目的地base url
    public $destBaseUri = 'http://ca.lvmama.com/';

    public $di;

    /**
     * @var Lvmama\Cas\Service\DestinBaseDataService
     */
    public $destin_base_service;

    /**
     * @var Lvmama\Cas\Service\DestinBaseMultiRelationDataService
     */
    public $destin_multi_relation_base_service;

    /**
     * @var kafka消息
     */
    public $kafka;

    /**
     * biz_dest
     * @var array
     */
    public $biz_dest_columns = array(
        'parent_id',
        'district_id',
        'dest_type',
        'dest_name',
        'en_name',
        'pinyin',
        'short_pinyin',
        'dest_alias',
        'local_lang',
        'cancel_flag',
        'foreign_flag',
        'dest_mark',
        'update_time',
    );

    /**
     * 经纬度相关
     * @var array
     */
    public $biz_com_coordinate_columns = array(
        'search_key',
        'address',
        'longitude',
        'latitude'
    );

    /**
     * biz_dest_relation
     * @var array
     */
    public $biz_dest_relation_columns = array(
        'parent_id',
    );

    /**
     * dest_nav
     * @var array
     */
    public $dest_nav_columns = array(
        'dest_name',
        'parent_id',
        'pinyin',
    );

    public function __construct($di, $kafka = '')
    {

        // 坑位 规则
        //$this->pp_engine_rule_service = $di->get('cas')->get('pp_engine_rule_service');

        // 目的地基础表biz dest
        $this->destin_base_service = $di->get('cas')->get('destin_base_service');

        //
        $this->destin_multi_relation_base_service = $di->get('cas')->get('destin_multi_relation_base_service');

        $this->di = $di;

        $this->kafka = $kafka;

    }

    public function handle($data)
    {
        // TODO: Implement handle() method.
        echo 'www';
        var_dump($data);

        // 校验数据
        if (!empty($data) && $data->err == 0) {

            $data_origin = isset($data->payload) ? $data->payload : null;

            $data_origin_arr = array();
            $data_origin_arr = json_decode($data_origin, true);
            
            $action_flag = isset($data_origin_arr['doActionJack']) ? $data_origin_arr['doActionJack'] : '';

            switch ( $action_flag ) {

                case 'insertNew':

                    unset( $data_origin_arr['doActionJack'] );
                    $this->insertBizDestData( $data_origin_arr );

                    break;

                default:

                    if (empty($data_origin_arr['destId'])) {

                        echo '非JAVA酒店数据，数据异常:缺少dest_id';

                    } else {

                        /****兼容java那边的字段错误    08月24号  李秀萌*****/
                        $data_origin_arr['foreignFlag'] = $data_origin_arr['foreighFlag'];
                        unset($data_origin_arr['foreighFlag']);

                        $data_origin_arr['updateTime'] = time();

                        /********兼容java那边的字段错误******/

                        /**********仅更新dest_type为HOTEL的数据   李秀萌  ************/
                        $rs = $this->_isHotel($data_origin_arr['destId']);
                        if ($rs) {

                            /***********仅更新dest_type为HOTEL的数据*********/

                            // biz_dest 处理逻辑
                            $this->tableSelfAdaptionAct('biz_dest', $data_origin_arr, $this->biz_dest_columns);

                            // biz_com_coordinate 处理逻辑
                            if ( isset($data_origin_arr['comCoordinateList']) && !empty($data_origin_arr['comCoordinateList']) ) {
                                $this->tableSelfAdaptionAct('biz_com_coordinate', $data_origin_arr['comCoordinateList'][0], $this->biz_com_coordinate_columns, self::COORDINATE_FLAG );
                            }

                            // biz_dest_relation 处理逻辑
                            $this->tableSelfAdaptionAct('biz_dest_relation', $data_origin_arr, $this->biz_dest_relation_columns);

                            // dest_nav 处理逻辑
                            //                $this->tableSelfAdaptionAct('dest_nav',$data_origin_arr, $this->biz_dest_relation_columns);

                            // dest_nav_abroad 处理逻辑
                            // 待定

                        }

                    }


                    break;

            }


        } else {
            echo 'data is invalid!';
        }

    }

    /**
     * 判断给定id数据是否是酒店
     * @addtime 2017-08-25T18:14:01+0800
     * @author   lixiumeng@lvmama.com
     * @param   [type]                   $id [description]
     * @return  boolean                      [description]
     */
    private function _isHotel($id = 0)
    {

        $data = $this->destin_base_service->getOneById($id);

        if (!empty($data['dest_type'])) {
            return $data['dest_type'] == 'HOTEL';
        }
        return false;

    }

    public function bizDestMultiRelationUpdate($data_origin_arr)
    {

        if (!empty($data_origin_arr['parentId'])) {

            $update_arr = array(
                'parent_id' => $data_origin_arr['parentId'],
            );

            $data = $this->destin_multi_relation_base_service->getOneById($data_origin_arr['destId']);

            if (!empty($data)) {

                foreach ($data as $data_key => $data_value) {

                    if ($data_value == $data_origin_arr['parentId']) {
                        $update_arr[$data_key] = $data_value;
                    }

                }

                $status = $this->destin_multi_relation_base_service->update($data_origin_arr['destId'], $update_arr);

            }

        } else {

        }

    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function timeOut()
    {
        // TODO: Implement timeOut() method.
        echo 'time out!';
    }

    /**
     * @param $data_origin_arr
     */
    public function tableSelfAdaptionAct($table_name, $data_origin_arr, $filter_arr, $flag = 0)
    {

        $biz_dest_where_arr  = array();

        $biz_dest_update_arr = array();

        foreach ($data_origin_arr as $data_origin_arr_key => $data_origin_arr_value) {

            if (!empty($data_origin_arr_value)) {

                $tmp_key = $this->uncamelize($data_origin_arr_key);

                if (in_array($tmp_key, $filter_arr)) {
                    $biz_dest_update_arr[$tmp_key] = $data_origin_arr_value;
                }

            }

        }
        if ( $flag == 0 ) {

            $status = $this->destin_base_service->updateCustom($table_name, $data_origin_arr['destId'], $biz_dest_update_arr);

        } elseif ( $flag == self::COORDINATE_FLAG ) {

            $biz_dest_where_arr['object_id'] = $data_origin_arr['objectId'];
            $biz_dest_where_arr['coord_type'] = $data_origin_arr['coordType'];

            $status = $this->destin_base_service->updateCustomForCoordinate($table_name, $biz_dest_where_arr, $biz_dest_update_arr);

        }


    }

    public function insertBizDestData($insert_data)
    {
        $this->destin_base_service->insert($insert_data);
    }

    /**
     * 驼峰命名转下划线命名,两级--待优化
     * 思路:小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
     */
    public function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    private function curl($url, $method = 'GET', $postfields = array(), $headers = array())
    {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, 'CAS API SERVICE' . ' ' . '1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_HEADER, false);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    $postfields['imagePath'] = new CURLFile($postfields['imagePath']);
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'PUT':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
                }
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            default:
                curl_setopt($ci, CURLOPT_POST, false);
                if (!empty($postfields)) {
                    $url = $url . "?" . http_build_query($postfields);
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        $string = curl_exec($ci);
        curl_close($ci);
        //过滤非标准json格式字符
        $aBP = strpos($string, '['); //数组符号第一个位置
        $oBP = strpos($string, '{'); //对象符号第一个位置
        //如果都不存在有这两个符号，表示非json数据，直接返回原始数据
        if ($aBP === false && $oBP === false) {
            $data = $string;
        } else {
            $aEP = strrpos($string, ']'); //数组符号最后一个位置
            $oEP = strrpos($string, '}'); //对象符号最后一个位置
            //否则,如果只存在{，那么只返回对象部分数据
            if ($aBP === false) {
                $jsonData = substr($string, $oBP, ($oEP - $oBP + 1));
            } elseif ($oBP === false) {
                //如果只存在[,那么只返回数组部分数据
                $jsonData = substr($string, $aBP, ($aEP - $aBP + 1));
            } else {
                //[和{都存在，那么比较位置大小，取值最小的
                $bP       = min($aBP, $oBP);
                $eP       = ($bP == $aBP) ? $aEP : $oEP;
                $jsonData = substr($string, $bP, ($eP - $bP + 1));
            }
            $data = json_decode($jsonData, true);
            //超时或者无效接口，直接返回错误信息
            if (isset($data['error']) && $data['error'] && isset($data['status']) && !$data['status']) {
                return array('error' => 'api timeout error');
            }

            //判断是否json数据,非json数据，返回获取到的字符串
            if ($data === null) {
                $data = $string;
            }

        }
        return $data;
    }

}
