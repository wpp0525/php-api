<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-23
 * Time: 上午10:16
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;


class QaCommonDataService extends DataServiceBase {

    /**
     * @param $table
     * @param $id
     * @return array|bool|mixed
     */
    public function getOneById($table, $id){
        if(!$this->getAdapter()->tableExists($table))
            return array('error' => 1,'message' => '数据表不存在！');
        $where_condition = array('id' => "=".$id);
        $data = $this->getOne($where_condition, $table);
        return $data;
    }


    public function setHashDataToRedis($data = array(), $redis_key = ''){
        if(empty($data) || !$redis_key)
            $this->messageOutput('400');
        return $this->redis->hMSet($redis_key, $data);
    }

    public function getHashDataFromRedis($redis_key = '', $key_array = array()){
        if(empty($key_array) || !$redis_key)
            $this->messageOutput('400');
        return $this->redis->hMGet($redis_key, $key_array);
    }

    /**
     * @param $operate_table
     * @param $data
     * @param null $key
     * @return array
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function operateDataById($operate_table, $data = array(), $key=null){

        if(!$this->getAdapter()->tableExists($operate_table))
            $this->messageOutput('400', array(), "数据表 {$operate_table} 不存在！");
        if(empty($data))
            $this->messageOutput('402');

        // $key 如果有值的话 就是修改 如果没有值的话 就是新增
        $id = '';

        if($key){
            $where = 'id = ' . $key;
            $is_ok = $this->getAdapter()->update($operate_table, array_keys($data), array_values($data), $where);
//            var_dump($is_ok); die;
            if($is_ok){
                $id = $key;
            }
        }else{
            $is_ok = $this->getAdapter()->insert($operate_table, array_values($data), array_keys($data));
            if($is_ok){
                $id = $this->getAdapter()->lastInsertId();
            }
        }
//        var_dump($id); die;
        if($id){
            return $id;
        }else{
            $this->messageOutput('500', '', "更新数据失败，稍后重试！");
        }
    }


    /**
     * 查询关系id
     * @param $table
     * @param $find
     * @param $key
     * @param $val
     * @return array
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function findRelationByCondition($table, $find, $key, $val){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");

        $table = "`{$table}`";
        $select = "`{$find}`";
        $key = "`{$key}`";

        $sql = "SELECT {$select} FROM {$table} WHERE {$key} = {$val}";
        $result = $this->getAdapter()->fetchAll($sql);
        $res = array();
        if($result){
            foreach($result as $val){
                $res[] = $val[$find];
            }
        }
        unset($result);

        return $res;
    }


    /**
     * 根据某字段查询一行结果
     * @param $table
     * @param $key
     * @param $val
     * @return array
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function getRowByCondition($table, $key, $val){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");

        $table = "`{$table}`";
        $key = "`{$key}`";

        $sql = "SELECT * FROM {$table} WHERE {$key} = {$val}";
        $res = $this->getAdapter()->fetchOne($sql);
        return $res;
    }


    /**
     * 获取单一表仅数据
     * @param $table
     * @param $where
     * @return array
     */
    public function getAllByCondition($table, $where){
        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");
        $table = "`{$table}`";
        $sql = "SELECT * FROM {$table} WHERE {$where}";
        $res = $this->getAdapter()->fetchAll($sql);
        return $res;
    }

    /**
     * 根据字符串条件查询一行结果
     * @param $table
     * @param string $select
     * @param $where
     * @param $type
     * @return array
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function getRowByConditionSrt($table, $select = '*', $where, $type = 'one'){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");

        $sql = "SELECT {$select} FROM {$table} WHERE {$where}";
        if($type == 'all'){
            $res = $this->getAdapter()->fetchAll($sql);
        }else{
            $res = $this->getAdapter()->fetchOne($sql);
        }
        return $res;
    }




    /**
     * 虎哥版select增强版 -- new name -> getByParams
     * @param array $params
     * @return array
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function getByParams($params = array()){
        $init_params = array(
            'table' =>'',
            'select' => '*',
            'join' => array(),
            'where' => '1',
            'in' => array(),
            'between' => array(),
            'order' => '',
            'group' => '',
            'limit' => '',
            'page' => array()
        );

        $params = array_merge($init_params, $params);

        $table = explode(' ', $params['table']);
        if(!$this->getAdapter()->tableExists($table[0]))
            $this->messageOutput('400', array(), "数据表 {$table[0]} 不存在！");
        $table_name = "`{$table[0]}` ";
        if(isset($table[1])) $table_name .= $table[1];

        $select = $this->parseSelectCondition($params['select']);

        $join = $this->parseJoinCondition($params['join']);

        $between = $this->parseAndBetweenCondition($params['between']);

        $where_arr = $this->parseWhereCondition($params['where']);
        $params['where'] = is_array($where_arr) ? implode(' AND ',$where_arr['where']) : '1';

        $wherein = '';
        if($params['in'])
            $wherein = $this->parseWhereInCondition($params['in']);

        $sql = "SELECT {$select} FROM {$table_name} {$join} WHERE {$params['where']} {$wherein} {$between}";
//echo $sql; die;
        $group = '';
        if($params['group']){
            $group = $this->parseGroupCondition($params['group']);
            $sql .= $group;
        }

        if($params['order']){
            $order = $this->parseOrderCondition($params['order']);
            $sql .= " ORDER BY {$order}";
        }

        if($params['page']) {
            $params['page']['pageSize'] = isset($params['page']['pageSize']) ? $params['page']['pageSize'] : '10';
            $params['page']['page'] = isset($params['page']['page']) ? $params['page']['page'] : '1';
            $offset = ($params['page']['page'] - 1) * $params['page']['pageSize'];
            $sql .= " LIMIT {$offset},{$params['page']['pageSize']}";
        }elseif ($params['limit']) {
            $limit_arr = explode(',', $params['limit']);
            if (count($limit_arr) == 1)
                $params['limit'] = '0,' . $limit_arr[0];
            else
                $params['limit'] = $limit_arr[0] . ',' . $limit_arr[1];
            $sql .= " LIMIT {$params['limit']}";
        }

//            echo $group." ORDER BY {$order}"." LIMIT {$offset},{$params['page']['pageSize']}"; die;
        if(isset($where_arr['param']))
            $result = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC, $where_arr['param']);
        else
            $result = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        $data = array();
        $data['list'] = $result;

        if($params['page']){
            if($group){
                $count_sql = "SELECT count(*) as itemCount FROM (SELECT  count(*)  FROM {$table_name} {$join} WHERE {$params['where']} {$wherein} {$between} {$group}) tb";
            }else{
                $count_sql = "SELECT count(*) as itemCount FROM {$table_name} {$join} WHERE {$params['where']} {$wherein} {$between}";
            }
//            echo $count_sql; die;
            if(isset($where_arr['param']))
                $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC, $where_arr['param']);
            else
                $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);

//            var_dump($count_res); die;

            $itemCount = $count_res['itemCount'];
            $data['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $params['page']['pageSize']),
                'page' => $params['page']['page'],
                'pageSize' => $params['page']['pageSize']
            );
        }
        return $data;

    }

    /**
     * 虎哥版
     * @param array $where
     * @return array|string
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    private function parseWhereCondition($where = array()){
        if(empty($where) || !is_array($where)) return '1';
        $res = array();
        foreach($where as $key => $value){
            if(strpos($value, '|')){
                $a_val = explode('|', $value);
                $sign = $a_val[0];
                $val = $a_val[1];
            }else{
                $sign = '=';
                $val = $value;
            }
            if(strpos($key, '.')){
                $a_key = explode('.', $key);
                $ak0 = $a_key[0];
                $ak1 = $a_key[1];
                $tmp = ':v' . $ak1;
                $res['where'][] = $ak0.".`{$ak1}` {$sign} {$tmp}";
                $res['param']['v'.$ak1] = $val;
            }else{
                $tmp = ':v' . $key;
                $res['where'][] = "`{$key}` {$sign} {$tmp}";
                $res['param']['v'.$key] = $val;
            }
        }
        return $res;
    }

    /**
     * 组成 JOIN
     * @param array $join
     * @return array|string
     * 请不要在未沟通前做任何修改
     *
     *  例：
     *  'join' => array(
     *      array(
     *          'type' => 'LEFT',       // 选值   LEFT | RIGHT | INNER ...
     *          'table' => 'qa_question q',     // 前后不要空格，中间只能有一个空格
     *          'on' => 'qpr.`question_id` = q.`id`',
     *      )
     *      ... ...
     *  ),
     * @author liuhongfei
     */
    private function parseJoinCondition($join = array()){
        if(empty($join) || !is_array($join)) return '';
        $res = '';
        foreach($join as $val){
            if($val['type'] && $val['table'] && $val['on']){
                $join_table = explode(' ', $val['table']);
                if(!$this->getAdapter()->tableExists($join_table[0]))
                    $this->messageOutput('400', array(), "数据表 {$join_table[0]} 不存在！");

                $cols = explode('=', $val['on']);
                foreach($cols as $key => $v){
                    $col = explode('.', trim($v));
                    $cols[$key] = "{$col[0]}.`{$col[1]}`";
                }
                $str_on = implode(' = ', $cols);

                $res .= strtoupper($val['type'])." JOIN `{$join_table[0]}` {$join_table[1]} ON {$str_on} ";
            }
        }
        return $res;
    }

    /**
     * 组成 between 字符串
     *
     * @param array $between
     * @return string
     * 请不要在未沟通前做任何修改
     *
     *  传入between 结构
     *  'between' => array(
     *      array(
     *          'key'=>'',      // 字段名
     *          'type' => '',       // between Or not between 大写
     *          'value' => array(1111,2222),
     *      ),
     *      ... ...
     *  ),
     * @author liuhongfei
     */
    private function parseAndBetweenCondition($between = array()){
        if(empty($between) || !is_array($between)) return '';
        $res = '';
        foreach($between as $value){
            if($value['key'] && $value['type'] && is_array($value['value'])){
                if($value['value'][0] && $value['value'][1]){
                    $a_key = explode('.', $value['key']);
                    if($a_key[1]){
                        $key = "{$a_key[0]}.`{$a_key[1]}`";
                    }else{
                        $key = "`{$value['key']}`";
                    }
                    $type = strtoupper($value['type']) == 'BETWEEN'?'BETWEEN':'NOT BETWEEN';
                    $res .= " AND ({$key} {$type} '{$value['value'][0]}' AND '{$value['value'][1]}' )";
                }
            }
        }
        return $res;
    }

    /**
     * 格式化 select
     * @param string $select
     * @return string
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    private function parseSelectCondition($select = '*'){
        if(!$select || trim($select) == "*" || !is_string($select)){ return "*"; }
        $res = '';
        $tmp = array();
        $a_select = explode(',', $select);
        foreach($a_select as $val){
            if(strpos($val, '*')){
                $tmp[] = trim($val);
            }elseif(strpos($val, '.')){
                $a_key = explode('.', trim($val));
                if(strpos($a_key[1], " as ")){
                    $as_key = explode(' as ', trim($a_key[1]));
                    $real_key = trim($as_key[0]);
                    $name_key = trim($as_key[1]);
                    $tmp[] = "{$a_key[0]}.`{$real_key}` AS {$name_key}";
                }else{
                    $tmp[] = "{$a_key[0]}.`{$a_key[1]}`";
                }
            }else{
                $tmp[] = "`".trim($val)."`";
            }
        }
        if(count($tmp)>0){
            $res = implode(', ',$tmp);
        }else{
            $res = '*';
        }
//        echo $res;die;
        return $res;
    }

    /**
     * 格式化 group
     * @param string $group
     * @return string
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    private function parseGroupCondition($group = ''){
        if(!$group || trim($group) == "" || !is_string($group)){ return ""; }
        $res = '';
        if(strpos($group, '.')){
            $a_group = explode('.', $group);
            $res = " GROUP BY {$a_group[0]}.`{$a_group[1]}`";
        }else{
            $res = " GROUP BY `{$group}`";
        }
        return $res;
    }

    /**
     * 格式化 order
     * @param string $order
     * @return string
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    private function parseOrderCondition($order = ''){
        if(!$order || trim($order) == "" || !is_string($order)){ return ""; }
        $con_order =  explode(',', $order);
        foreach($con_order as $order_val){
            $a_order = explode(' ', trim($order_val));
            if(count($a_order) > 1)
                $a_order[1] = strtoupper($a_order[1]);
            else
                $a_order[1] = "ASC";

            if(strpos($a_order[0], '.')){
                $a_aorder = explode('.', $a_order[0]);
                $res_temp[] = "{$a_aorder[0]}.`{$a_aorder[1]}` {$a_order[1]}";
            }else{
                $res_temp[] = "`{$a_order[0]}` {$a_order[1]}";
            }
        }
        $res = implode(', ', $res_temp);
        return $res;
    }

    /**
     * 格式化  wherein
     * @param $wherein
     * @return string
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    private function parseWhereInCondition($wherein = array()){
        if(empty($wherein) || !is_array($wherein)) return '';
        $res = '';
        foreach($wherein as $key => $val){
            if(strpos($key, '.')){
                $a_key = explode('.', $key);
                $res = " AND {$a_key[0]}.`{$a_key[1]}` IN {$val}";
            }else{
                $res = " AND `{$a_key}` IN {$val}";
            }
        }
        return $res;
    }

    /**
     * @param array $params
     * @param string $output
     * @return int
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function deleteData($params = array() ,$output = ''){
        $init_params = array(
            'table' => '',
            'where' => array(),
        );
        $params = array_merge($init_params,$params);
        $table_name = $params['table'];
        if(!$this->getAdapter()->tableExists($table_name))
            $this->messageOutput('400', array(), "数据表 {$table_name} 不存在！");
        if(empty($params['where']))
            $this->messageOutput('402');

        $this->getAdapter()->delete($table_name, $params['where']);

        if($output == 'Y'){
            $this->messageOutput('200', array(), "删除成功！");
        }else{
            return 200;
        }

    }

    /**
     * 输出json
     * @param $code
     * @param array $data
     * @param string $msg
     * @param string $type
     */
    public function messageOutput($code, $data = array(), $msg = '', $type = ''){
        $CODE = array(
            '200' => '请求成功',
            '300' => '非法请求',
            '301' => '非法访问',
            '400' => '缺少参数',
            '401' => '参数类型不正确',
            '402' => '参数非法',
            '500' => '服务器异常',
            '501' => '返回数据不能正常解释',
            '502' => '返回数据非法'
        );
        $msg = $msg?$msg:(isset($CODE[$code])?$CODE[$code]:'');
        $result = array('code' => $code, 'message' => $msg);
        if($data){ $result['data'] = $data; }
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
     * 关系表
     * @param $table
     * @param $uid
     * @param $rel_col
     * @param $val
     * @return array
     */
    public function findInfoByRel($table, $uid, $rel_col, $val){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");

        $sql = "SELECT * FROM `{$table}` WHERE `{$rel_col}` = {$val} and `uid` = '{$uid}'";
        $result = $this->getAdapter()->fetchOne($sql);

        return $result;
    }

}

