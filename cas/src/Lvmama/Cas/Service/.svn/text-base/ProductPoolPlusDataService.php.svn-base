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

class ProductPoolPlusDataService extends DataServiceBase {

    const TABLE_NAME = 'pp_place_plus';//对应数据库表

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
                $id = $this->getAdapter()->lastInsertId();
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

        $data = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        return $data;
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

        $sql = "SELECT {$params['select']} FROM {$params['table']} {$join} WHERE {$params['where']} LIMIT 1";

        // group by
        if($params['group']){
            $sql .= ' GROUP BY '.$params['group'];
        }

        // order
        if($params['order']){
            $sql .= " ORDER BY {$params['order']}";
        }

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
            502 => '返回数据非法'
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


    public function querySql($data){

        $str = "INSERT INTO pp_place (place_coordinate, channel_id, route_id, `position`, key_id, place_order, coordinate_md5,lock_status, del_status) VALUES ";
        $this->getAdapter()->affectedRows();
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
    public function simpleQuery($res,$product_id_arr)
    {
        // 构建
        $display_order = array();
        foreach ( $res as $res_key=>$res_value ) {
            if ( isset($product_id_arr[$res_key]) ) {
                $display_order[$res_value['id']] = $product_id_arr[$res_key];
            }
        }

        $ids = implode(',', array_keys($display_order));
        $sql = "UPDATE pp_place SET product_id = CASE id ";
        foreach ($display_order as $id => $ordinal) {
            $sql .= sprintf("WHEN %d THEN '%s' ", $id, $ordinal);
        }
        $sql .= "END WHERE id IN ($ids)";

        $res = $this->getAdapter()->query($sql);

        return $res;
    }

    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }


    /**
     * 插入数据
     * @param array $data
     * @return int
     */
    public function createPlusData(array $data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }



    /**
     * 删除数据
     * @param int $id
     * @return int
     */
    public function delPlusData($id) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }


}