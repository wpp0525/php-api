<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-3-3
 * Time: 下午5:07
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolDataService extends DataServiceBase {

    const TABLE_NAME = 'pp_place';//对应数据库表

    /**
     * 新增/修改
     * @param $operate_table
     * @param $data
     * @param null $key
     * @return array
     * @author liuhongfei
     */
    public function operateDataById($operate_table, $data = array(), $key=null){

        if(!$this->getAdapter()->tableExists($operate_table))
            $this->messageOutput('400', "数据表 {$operate_table} 不存在！");
        if(empty($data))
            $this->messageOutput('402');

        // $key 如果有值的话 就是修改 如果没有值的话 就是新增
        $id = '';

        if($key){
            $where = 'id = ' . $key;
            $is_ok = $this->getAdapter()->update($operate_table, array_keys($data), array_values($data), $where);
            if($is_ok){
                $id = $key;
            }
        }else{
            $is_ok = $this->getAdapter()->insert($operate_table, array_values($data), array_keys($data));
            if($is_ok){
                //$id = $this->getAdapter()->lastInsertId();
                $id = $is_ok;
            }
        }

        if($id){
            return (int)$id;
        }else{
            $this->messageOutput('500', "更新数据失败，稍后重试！");
        }
    }

    /**
     * 查询
     * @param array $params
     * 例： 'table' => 'table1 t1' ,
     *          'select' => 't1.col1, t2.col2',
     *          'join' => array('left join table2 t2 on t2.col1 = t1.col1', 'left join table3 t3 on t3.col1 = t1.col1'),
     *          'where' => 't1.col1 = 0',
     *          'order' => 't1.col2 DESC',
     *          'group' => 't1.col2',
     *          'page' => array('page' => 1, 'pageSize' => 5);
     * @return array
     * @author liuhongfei
     */
    public function getPageByParams($params = array()){

        // 初始数据
        $init_params = array(
            'table' =>'',
            'select' => '*',
            'join' => array(),
            'where' => '1',
            'order' => '',
            'group' => '',
            'page' => array('page' => 1, 'pageSize' => 10)
        );

        $params = array_merge($init_params, $params);

        // 组成join条件
        $join = '';
        if($params['join']){
            $join = implode(', ', $params['join']);
        }

        $sql = "SELECT {$params['select']} FROM {$params['table']} {$join} WHERE {$params['where']}";
//        return $sql;

        // group by
        if($params['group']){
            $sql .= ' GROUP BY '.$params['group'];
        }

        // order
        if($params['order']){
            $sql .= " ORDER BY {$params['order']}";
        }

        // limit
        $pageSize = isset($params['page']['pageSize']) ? $params['page']['pageSize'] : '10';
        $page = isset($params['page']['page']) ? $params['page']['page'] : '1';
        $offset = ($page - 1) * $pageSize;
        $sql .= " LIMIT {$offset},{$pageSize}";

        $result = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        $data = array();
        $data['list'] = $result;

        if($params['group']){
            $count_sql = "SELECT count(1) as itemCount FROM (SELECT  count(1)  FROM {$params['table']} {$join} WHERE {$params['where']}  GROUP BY {$params['group']}) tmptable";
        }else{
            $count_sql = "SELECT count(1) as itemCount FROM {$params['table']} {$join} WHERE {$params['where']}";
        }

        $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);

        $itemCount = $count_res['itemCount'];
        $data['pages'] = array(
            'itemCount' => $itemCount,
            'pageCount' => ceil($itemCount / $params['page']['pageSize']),
            'page' => $params['page']['page'],
            'pageSize' => $params['page']['pageSize']
        );

        return $data;
    }

    /**
     * 根据某字段查询一行结果
     * @param $table
     * @param $select
     * @param $val
     * @param $key
     * @param $type  =>  NUM/STR
     * @return array
     * @author liuhongfei
     */
    public function getOneByCondition($table, $select, $val, $key = 'id', $type = 'NUM'){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', "数据表 {$table} 不存在！");

        if(strtoupper($type) == 'NUM'){
            $sql = "SELECT {$select} FROM `{$table}` WHERE `{$key}` = {$val}";
        }else{
            $sql = "SELECT {$select} FROM `{$table}` WHERE `{$key}` = '{$val}'";
        }

        $res = $this->getAdapter()->fetchOne($sql);

        return $res;
    }

    /**
     * 查询ALL - 慎用
     * @param array $params
     * 例： 'table' => 'table1 t1' ,
     *          'select' => 't1.col1, t2.col2',
     *          'join' => array('left join table2 t2 on t2.col1 = t1.col1', 'left join table3 t3 on t3.col1 = t1.col1'),
     *          'where' => 't1.col1 = 0',
     *          'order' => 't1.col2 DESC',
     *          'group' => 't1.col2',
     * @return array
     * @author liuhongfei
     */
    public function getAllByParams($params = array()){

        // 初始数据
        $init_params = array(
            'table' =>'',
            'select' => '*',
            'join' => array(),
            'where' => '1',
            'order' => '',
            'group' => ''
        );

        $params = array_merge($init_params, $params);

        // 组成join条件
        $join = '';
        if($params['join']){
            $join = implode(', ', $params['join']);
        }

        $sql = "SELECT {$params['select']} FROM {$params['table']} {$join} WHERE {$params['where']}";
//        return $sql;

        // group by
        if($params['group']){
            $sql .= ' GROUP BY '.$params['group'];
        }

        // order
        if($params['order']){
            $sql .= " ORDER BY {$params['order']}";
        }

//        return $sql;
        $data = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if($data && is_array($data)){
            return $data;
        }else{
            return array();
        }

    }

    /**
     * 查询one
     * @param array $params
     * 例： 'table' => 'table1 t1' ,
     *          'select' => 't1.col1, t2.col2',
     *          'join' => array('left join table2 t2 on t2.col1 = t1.col1', 'left join table3 t3 on t3.col1 = t1.col1'),
     *          'where' => 't1.col1 = 0',
     *          'order' => 't1.col2 DESC',
     *          'group' => 't1.col2',
     * @return array
     * @author liuhongfei
     */
    public function getOneByParams($params = array()){

        // 初始数据
        $init_params = array(
            'table' =>'',
            'select' => '*',
            'join' => array(),
            'where' => '1',
            'order' => '',
            'group' => ''
        );

        $params = array_merge($init_params, $params);

        // 组成join条件
        $join = '';
        if($params['join']){
            $join = implode(', ', $params['join']);
        }

        $sql = "SELECT {$params['select']} FROM {$params['table']} {$join} WHERE {$params['where']}";

        // group by
        if($params['group']){
            $sql .= ' GROUP BY '.$params['group'];
        }

        // order
        if($params['order']){
            $sql .= " ORDER BY {$params['order']}";
        }

        $sql .= " LIMIT 1";
//return $sql;
        $data = $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * 输出json
     * @param $code
     * @param array $data
     * @param string $msg
     * @param string $type
     */
    public function messageOutput($code, $msg = '', $data = array(), $type = ''){
        $CODE = array(
            200 => '请求成功',
            300 => '非法请求',
            301 => '非法访问',
            400 => '缺少参数',
            401 => '参数类型不正确',
            402 => '参数非法',
            500 => '服务器异常',
            501 => '返回数据不能正常解释',
            502 => '返回数据非法',

        );
        $msg = $msg?$msg:(isset($CODE[$code])?$CODE[$code]:'');

        if($code == 200){
            $result = array('error'=>0, 'description'=>$msg, 'data' =>$data);
        }else{
            $result = array('error'=>$code, 'description'=>$msg);
        }

        if($type){
            $jsonString = json_encode($result, $type);
        }else{
            $jsonString = json_encode($result);
        }
        header('Content-type: application/json; charset=utf-8');
        echo $jsonString;
        die();
    }


    /**
     * 将图片上传到图库
     * @param string $image_path
     * @param int $travel_id
     * @param int $travel_content_id
     * @return array|string
     */
    private function UploadImage2Pic($image_path = '',$travel_id = 0,$travel_content_id = 0){
        $java_upload_url = 'http://piclib.lvmama.com/photo-back/photo/photo/uploadImgAndSave.do';
        $post_fields = array(
            'photoType' => "trip",
            'photoSecondType' => "trip",
            'photoName' => $travel_id,
            'photoCopyrightId' => '162',
            'isPHP_PC' => 'true',
            'photoAutoCut' => 1,
            'imagePath' => $image_path,
        );
        list($width, $height, $type, $attr) = getimagesize($image_path);
        $result = $this->curl($java_upload_url, 'POST', $post_fields);

        if (isset($result['success']) && $result['success']) {
            $image_result = array('error' => 1);
            $dest_rel_params = array(
                'table' => 'travel_content_dest_rel',
                'select' => 'dest_id',
                'where' => array('travel_content_id' => $travel_content_id,'travel_id' => $travel_id),
            );
            $image_result = $this->traveldatasvc->select($dest_rel_params);
            if(!isset($image_result["list"]) || is_null($image_result["list"]['0']['dest_id']))
                return json_encode(array("error" => "1", 'msg' => 'select dest_id faild'));

            $dest_id = $image_result["list"]['0']['dest_id'];

//            $image_params = array(
//                'table' => 'image',
//                'data' => array(
//                    'dest_id' => $dest_id,
//                    'url' => $result['url'],
//                    'pic_url' => $result['url'],
//                    'width' => $width,
//                    'create_time' => time(),
//                    'update_time' => time(),
//                ),
//            );
//            $image_result = $this->traveldatasvc->insert($image_params);

            // 返回数据
            if ($image_result['error'])
                return array("error" => "1", 'msg' => 'insert image faild');

            $image_rel_params = array(
                'table' => 'travel_image_rel',
                'data' => array(
                    'travel_id' => $travel_id,
                    'travel_content_id' => $travel_content_id,
                    'image_id' => $image_result['result'],
                ),
            );
            $image_result = $this->traveldatasvc->insert($image_rel_params);

            // 返回数据
            if ($image_result['error'])
                return array("error" => "1", 'msg' => 'insert image_rel faild');

            return array('error' => '0','msg' => 'SUCCESS','data' => $result['url']);
        }
        return array("error" => "1", 'msg' => 'upload image to pic faild');
    }


    private function curl($url, $method = 'GET', $postfields = array(), $headers = array()) {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, 'CAS API SERVICE' . ' ' . '1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
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
                curl_setopt($ci, CURLOPT_POST, FALSE);
                if (!empty($postfields)) {
                    $url = $url . "?" . http_build_query($postfields);
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $string = curl_exec($ci);
        curl_close($ci);
        //过滤非标准json格式字符
        $aBP = strpos($string, '[');//数组符号第一个位置
        $oBP = strpos($string, '{');//对象符号第一个位置
        //如果都不存在有这两个符号，表示非json数据，直接返回原始数据
        if ($aBP === false && $oBP === false) {
            $data = $string;
        } else {
            $aEP = strrpos($string, ']');//数组符号最后一个位置
            $oEP = strrpos($string, '}');//对象符号最后一个位置
            //否则,如果只存在{，那么只返回对象部分数据
            if ($aBP === false) {
                $jsonData = substr($string, $oBP, ($oEP - $oBP + 1));
            } elseif ($oBP === false) {
                //如果只存在[,那么只返回数组部分数据
                $jsonData = substr($string, $aBP, ($aEP - $aBP + 1));
            } else {
                //[和{都存在，那么比较位置大小，取值最小的
                $bP = min($aBP, $oBP);
                $eP = ($bP == $aBP) ? $aEP : $oEP;
                $jsonData = substr($string, $bP, ($eP - $bP + 1));
            }
            $data = json_decode($jsonData, true);
            //超时或者无效接口，直接返回错误信息
            if (isset($data['error']) && $data['error'] && isset($data['status']) && !$data['status']) return array('error' => 'api timeout error');
            //判断是否json数据,非json数据，返回获取到的字符串
            if ($data === null) $data = $string;
        }
        return $data;
    }


    /**
     *
     * @param $fields
     * @param $where_arr
     * @param string $other_where
     * @return array
     */
    public function getByCondition($fields, $where_arr ,$other_where=''){

        if(!$this->getAdapter()->tableExists($table=self::TABLE_NAME)) {
            $this->messageOutput('400', "数据表 {$table} 不存在！");
        }

        if ( !is_array($where_arr) ) {
            return array();
        } else {
            $where = $other_where;
            foreach ( $where_arr as $key=>$value ) {
                if ( strpos($key,'!') === false ) {
                    $where .= " and {$key} = '{$value}'";
                } else {
                    $key_tmp = trim($key,"!");
                    $where .= " and {$key_tmp} != '{$value}'";
                }
            }
            $where = ltrim($where,' and');
        }

        $sql = "SELECT {$fields} FROM ". self::TABLE_NAME ." WHERE {$where} ";

        $res = $this->getAdapter()->fetchAll($sql,\PDO::FETCH_ASSOC);

        return $res;
    }

    public function getSingleCountByParams($table, $where){
        $count_sql = "SELECT count(1) as itemCount FROM {$table} WHERE {$where}";
        $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);
        return $count_res['itemCount'];
    }

    /**
     *
     * @param $sql
     * @return bool|\Phalcon\Db\ResultInterface
     */
    public function simpleQuery($res,$product_id_arr,$district_product_list)
    {
        // 构建
        $display_order = array();
        foreach ( $res as $res_key=>$res_value ) {
            if ( isset($product_id_arr[$res_key]) ) {
                $display_order[$res_value['id']] = $product_id_arr[$res_key];
            }
        }

        $ids = implode(',', array_keys($display_order));

        if ( strpos($product_id_arr[0],'|') === false ) {

            $sql = "UPDATE pp_place SET product_id = CASE id ";
            foreach ($display_order as $id => $ordinal) {
                $sql .= sprintf("WHEN %d THEN '%s' ", $id, $ordinal);
            }

        } else {

            $sql = "UPDATE pp_place SET product_id = CASE id ";
            foreach ($display_order as $id => $ordinal) {
                $ordinal_tmp = explode('|',$ordinal);
                $sql .= sprintf("WHEN %d THEN '%s' ", $id, $ordinal_tmp[0]);
            }

            $sql .= ' END,supp_goods_id = CASE id ';
            foreach ($display_order as $id => $ordinal) {
                $ordinal_tmp_supp = explode('|',$ordinal);
                $sql .= sprintf("WHEN %d THEN '%s' ", $id, $ordinal_tmp_supp[1]);
            }

        }



        if(!empty($district_product_list)) {
            //出发地
            $sql .= "END , product_district_id = CASE id ";
            foreach ($display_order as $id => $ordinal) {
                if(!empty($district_product_list[$ordinal])){
                    $sql .= sprintf("WHEN %d THEN '%s' ", $id, $district_product_list[$ordinal]['district_id']);
                }
            }
            //价格
            $sql .= "END , product_price = CASE id ";
            foreach ($display_order as $id => $ordinal) {
                if(!empty($district_product_list[$ordinal])){
                    $sql .= sprintf("WHEN %d THEN '%s' ", $id, $district_product_list[$ordinal]['product_price']);
                }
            }
            //url
            $sql .= "END , product_url = CASE id ";
            foreach ($display_order as $id => $ordinal) {
                if(!empty($district_product_list[$ordinal])){
                    $sql .= sprintf("WHEN %d THEN '%s' ", $id, $district_product_list[$ordinal]['product_url']);
                }
            }
        }
        $sql .= "END WHERE id IN ($ids)";

	    $this->getAdapter()->forceMaster();
        $res = $this->getAdapter()->query($sql);

        return $res;
    }

    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }
    public function updateByWhere($where,$data){
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);
    }

	public function buildRule($coordinate){
        // 验证传入的坐标是否合法
        if($coordinate == ''){
            $this->messageOutput('100001','参数有误');
        }
        $coo_data = explode('.', $coordinate);
        if(count($coo_data) != 4){
            $this->messageOutput('100001','参数有误');
        }else{
            foreach($coo_data as $key => $val){
                $coo_data[$key] = (int)$val;
                if($key == 1 && (int)$val != 0){
                    $this->messageOutput('100001','参数有误');
                }elseif($key != 1 && (int)$val == 0){
                    $this->messageOutput('100001','参数有误');
                }
            }
        }

        // ====== 组成数据 BEGIN ======
        $data_array = array();
        $data_array['channel_id'] = floor($coo_data[0]/100000);
        $data_array['route_id'] = $coo_data[0] - $data_array['channel_id'] * 100000;
        $data_array['position'] = $coo_data[2];
        $data_array['place_num'] = $coo_data[3];

        // 判断数据是否已经存在
        $where = "del_status = '1' AND lock_status = '1' AND position = '{$data_array['position']}'";
        $where .= " AND channel_id = '{$data_array['channel_id']}' AND route_id = '{$data_array['route_id']}'";

        $params = array(
            'table' =>'pp_black_rule',
            'select' => 'id, place_num',
            'where' => $where
        );

        $ishave = $this->getOneByParams($params);

        $sign = 0;
        $allowupdate = 1;
        if($ishave && is_array($ishave)){
            $sign = intval($ishave['id']) ? intval($ishave['id']) : 0;
            if($ishave['place_num'] == $data_array['place_num']){
                $allowupdate = 0;
            }
        }

        if($allowupdate){
            $data_array['lock_status'] = 1;
            $data_array['del_status'] = 1;

            // 查询路由信息补全数据
            $find = $this->getOneByCondition('pp_route', 'route, intro', $data_array['route_id']);
            if($find && is_array($find)){
                $data_array['route'] = $find['route'];
                $data_array['name'] = $find['intro'].' - '.$coo_data[2];
            }
            // ====== 组成数据 END ======

            // 写入数据库
            $res = $this->operateDataById('pp_black_rule', $data_array, $sign);

            return $res;
        }else{
            return $ishave['id'];
        }

    }



    private function is_json($str){
        return is_null(json_decode($str))?0:1;
    }


    /**
     * 生成坑位并sendMsg
     * @param $coordinates
     *          可接收 数组 json 字符串
     *          数组 每个元素为一个spm码
     *          json 把一个数组中每个元素是一个spm码的转换成json格式
     *          字符串 spm码中间用英文逗号连接 结尾不要以英文逗号结尾
     * @param int $throw_all
     *          0 一次一个 $kafka->sendMsg($kfk_array);
     *          1 一次以json格式 $kafka->sendMsg(json_encode($throw_array));
     * @return int|string
     *
     * 特别声明：
     *      此方法支持的sendMsg的数据格式 不代表消费方已经支持
     *      对消费方不支持接收的格式产生的错误 不背锅
     */
    public function buildPlaceByCoordinate($coordinates, $throw_all = 0){

        if(!$coordinates){
            $this->messageOutput('100001','参数有误');
        }

        if(is_array($coordinates)){
            $coordinate_array = $coordinates;
        }elseif($this->is_json($coordinates)){
            $coordinate_array = json_decode($coordinates, true);
        }else{
            $coordinate_array = explode(',', $coordinates);
        }

        $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['ruleEnginePit']);
        $md_ret = 0;
        $throw_array = array();

        foreach($coordinate_array as $coordinate){

            // 验证传入的坐标是否合法
            if($coordinate == ''){
                $this->messageOutput('1100001','参数有误');
            }
            $coo_data = explode('.', $coordinate);
            if(count($coo_data) != 4){
                $this->messageOutput('1100001','参数有误');
            }else{
                foreach($coo_data as $key => $val){
                    $coo_data[$key] = (int)$val;
                    if($key == 3 && (int)$val != 0){
                        $this->messageOutput('1100001','参数有误');
                    }elseif($key != 3 && (int)$val == 0){
                        $this->messageOutput('1100001','参数有误');
                    }
                }
            }

            // ====== 组成数据 BEGIN ======
            $data_array = array();
            $data_array['channel_id'] = floor($coo_data[0]/100000);
            $data_array['route_id'] = $coo_data[0] - $data_array['channel_id'] * 100000;
            $data_array['position'] = $coo_data[2];

            // 判断 有多少个坑
            $where = "del_status = '1' AND position = '{$data_array['position']}'";
            $where .= " AND channel_id = '{$data_array['channel_id']}' AND route_id = '{$data_array['route_id']}'";
            $params = array(
                'table' =>'pp_black_rule',
                'select' => 'place_num',
                'where' => $where
            );
            $ishave = $this->getOneByParams($params);

            if($ishave && $ishave['place_num']){

                $key_id = $coo_data[1];
                // 查询已有数据
                $params2 = array(
                    'table' =>'pp_place',
                    'select' => 'id, place_order, lock_status',
                    'where' => $where." AND key_id = '{$key_id}' ",
                    'order' => ' place_order ASC'
                );
                $res = $this->getAllByParams($params2);

                $po_array = $parray = $plock = array();
                if($res && is_array($res)){
                    $j = 1;
                    foreach($res as $val){
                        $place_order = $val['place_order'];
                        $po_array[$j] = $place_order;
                        $parray[$place_order] = $val['id'];

                        if($val['lock_status'] != 1){
                            $plock[] = $place_order;
                        }

                        $j++;
                    }
                }

                $coordinate_3 = "{$coo_data[0]}.{$coo_data[1]}.{$coo_data[2]}";

                $count = array();
                for($i = 1;$i<=$ishave['place_num']; $i++){
                    $po_key = array_search($i, $po_array);
                    if($po_key){
                        $count[] = $po_array[$po_key];
                        unset($po_array[$po_key]);
                    }else{
                        $post_array = array(
                            'place_coordinate' => $coordinate_3.'.'.$i,
                            'channel_id' => $data_array['channel_id'],
                            'route_id' => $data_array['route_id'],
                            'position' => $data_array['position'],
                            'key_id' => $key_id,
                            'place_order' => $i,
                            'lock_status' => 1,
							'product_name' => '',
							'product_img' => '',
							'real_product_id' => 0,
                            'del_status' => 1
                        );
                        // 写入数据库
                        $res2 = $this->operateDataById('pp_place', $post_array);
                        $count[] = $res2;
                        unset($res2);
                        unset($post_array);
                    }

                    // 需要扔进mq的数据
                    if(!in_array($i, $plock)){
                        $kfk_array = $coordinate_3.'.'.$i;
                        if($throw_all){
                            $throw_array[] = $kfk_array;
                        }else{
                            $kafka->sendMsg($kfk_array);
                            unset($kfk_array);
                        }

                    }

                }

                // 逻辑删除多出数据...
                if(count($po_array)){
                    foreach($po_array as $po_val){
                        $post_array = array(
                            'del_status' => 9
                        );
                        $res3 = $this->operateDataById('pp_place', $post_array, $parray[$po_val]);
                        unset($res3);
                        unset($post_array);
                    }
                }

                if($ishave['place_num'] == count($count)){
                    $md_ret += $ishave['place_num'];

                }else{
                    $this->messageOutput('100002', '程序发生错误，请重试！');
                }
            }else{
                $this->messageOutput('100002', '规则不存在，无法生成！');
            }

        }

        if($throw_all && $throw_array){
//            echo json_encode($throw_array); die;
            $kafka->sendMsg(json_encode($throw_array));
            unset($throw_array);
        }
        return $md_ret;
    }
    public function refreshByFilter($kid){
        //根据修改刷新下坑位产品规则
        $dest_keyword_srv = $this->getDI()->get('cas')->get('seo_dest_keyword_service');
        $tpl_srv = $this->getDI()->get('cas')->get('seo_template_service');
        $tpl_var_srv = $this->getDI()->get('cas')->get('seo_template_variable_service');
        $keyword_info = $dest_keyword_srv->getOneKeyword(array(
            'keyword_id = ' => $kid,
            'status = ' => 1
        ));
        $template_info = $tpl_srv->getOneTemplate(array(
            'template_id = ' => $keyword_info['template_id']
        ),'template_id,channel_id,route_id');
        $template_vars = $tpl_var_srv->getVarList(array(
            'template_id = ' => $keyword_info['template_id'],
            'group_type = ' => '\'product\''
        ));
        $spm_arr = array();
        foreach($template_vars as $row){
            $param = array(
                'channel_id' => $template_info['channel_id'],
                'route_id' => $template_info['route_id'],
                'key_id' => $kid,
                'position' => $row['variable_id'],
                'place_order' => 0
            );
            //$this->cas->exec('product/cleanPlaceByWhere',$param);
            $spm = UCommon::buildRule($param);
            $spm_arr[] = $spm;
        }
        $this->buildPlaceByCoordinate($spm);
    }
}