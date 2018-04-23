<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-5-4
 * Time: 上午10:36
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class SctSystemCoreDataService extends DataServiceBase {

    /**
     * 新增/修改
     * @param $operate_table
     * @param $data
     * @param null $key
     * @param null $keyname
     * @return array
     * @author liuhongfei
     */
    public function operateDataById($operate_table, $data = array(), $key=null, $keyname=null){

        if(!$this->getAdapter()->tableExists($operate_table))
            return $this->messageOutput('400', "数据表 {$operate_table} 不存在！");
        if(empty($data))
            return $this->messageOutput('402');

        // $key 如果有值的话 就是修改 如果没有值的话 就是新增
        $id = '';
//        return ($data); die;
        if($key){
            $where = 'id = ' . $key;
            if($keyname){ $where = " {$keyname} = " . $key; }

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
            return $this->messageOutput('500', "更新数据失败，稍后重试！");
        }
    }

    /**
     * 根据某字段查询全部结果
     * @param $table
     * @param $select
     * @param $val
     * @param $key
     * @return array
     * @author liuhongfei
     */
    public function getAllByCondition($table, $select, $val, $key = 'id'){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', "数据表 {$table} 不存在！");

        $sql = "SELECT {$select} FROM `{$table}` WHERE `{$key}` = '{$val}'";
        $res = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        return $res;
    }

    /**
     * 根据某字段查询一条结果
     * @param $table
     * @param $select
     * @param $val
     * @param $key
     * @return array
     * @author liuhongfei
     */
    public function getOneByCondition($table, $select, $val, $key = 'id'){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', "数据表 {$table} 不存在！");

        $sql = "SELECT {$select} FROM `{$table}` WHERE `{$key}` = '{$val}'";

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
     * @param array $params
     * @param string $output
     * @return int
     * 请不要在未沟通前做任何修改
     * @author liuhongfei
     */
    public function deleteData($params = array() ,$output = ''){
        $init_params = array(
            'table' => '',
            'where' => '',
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
     * @param $table
     * @param string $select
     * @param int/string $where
     * @param string $type
     * @return array
     * @author liuhongfei
     */
    public function getDataByConditionSrt($table, $select = '*', $where, $type = 'all'){

        if(!$this->getAdapter()->tableExists($table))
            $this->messageOutput('400', array(), "数据表 {$table} 不存在！");

        $sql = "SELECT {$select} FROM {$table} WHERE {$where}";
//        return$sql;
        if($type == 'all'){
            $res = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);
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

//return $params;

//        var_dump($params['join'][3]); die;

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
//        return $sql; die;
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

    public function findMenuByAllow($allowIds){

        if(!$allowIds || !is_array($allowIds)){
            return false;
        }

        $imp = implode("','", $allowIds);
        $condition = "`action_id` IN ('{$imp}')";
        if($condition){
            $sql = "SELECT `menu_id`,`group_id`,`parent_id` FROM `cms_menu` WHERE `status` = 1 AND ".$condition;
            $con_data = $this->query($sql, 'All');
        }

        $ids_all = array();
        if($con_data && is_array($con_data)){
            foreach($con_data as $cd3){
                $ids_all[] = $cd3['menu_id'];
                $ids_all[] = $cd3['group_id'];
                if($cd3['parent_id']) $ids_all[] = $cd3['parent_id'];
            }
            $condition = '';
            if($ids_all){
                $imps = implode("','", $ids_all);
                $condition = "`menu_id` IN ('{$imps}')";
            }

            if($condition){
                $sql_4 = "SELECT `menu_id`,`menu_name`,`menu_key`,`group_id`,`parent_id`,`sort_num`,`function_path` FROM `cms_menu` WHERE `status` = 1 AND ".$condition." ORDER BY sort_num ASC, menu_id ASC, group_id ASC, parent_id ASC";
                $con_data_4 = $this->query($sql_4, 'All');

                return array('menu' => $con_data_4);

            }else{

                return false;
            }

        }
    }


    public function getMethodIdByRoute($url){
        $sql = "SELECT `id` FROM `cms_action` WHERE `action_name` != `method` AND action_route = '".$url."'";
        $data = $this->getAdapter()->fetchOne($sql);
        if($data && is_array($data)){
            return $data['id'];
        }
    }

    public function getBindWFStaff($groupId, $itemId, $orderNo){
        $sql = "SELECT `bind_managers` FROM cms_work_flow_info WHERE `wf_group` = ".$groupId." AND `item_id` = ".$itemId." AND `wf_order` = '".$orderNo."'";
        $data = $this->getAdapter()->fetchOne($sql);
        if($data && is_array($data)){
            if(empty($data['bind_managers'])){
                return array();
            }else{
                return explode(',', $data['bind_managers']);
            }
        }else{
            return array();
        }
    }

    public function isAllowPermission($staffid, $url, $permission_params){

        $sql = "SELECT `id`,`class_name`,`permission_status`,`permission_params` FROM `cms_action` WHERE `action_name` != `method` AND action_route = '".$url."'";
        $data = $this->getAdapter()->fetchOne($sql);
//        return $data;
        if($data){
            if(is_array($data)){
//                return $data['id']; die;

                if($data['permission_status'] == 'S'){
                    $sql_user = "SELECT id,action_status from cms_staff_permission where staff_id = ".$staffid." AND action_id = '".$data['id']."' AND params_permission = '".$permission_params."' ";
                }else{
                    $sql_user = "SELECT id,action_status from cms_staff_permission where staff_id = ".$staffid." AND action_id = '".$data['id']."'";
                }
//                 return $sql_user; die;
                $user_data = $this->getAdapter()->fetchOne($sql_user);
                if($user_data && is_array($user_data)){
                    if($user_data['action_status']){
                        return $user_data['action_status'];
                    }
                }

                $sql_role = "SELECT `role_id` from `cms_staff_role` where `staff_id` = {$staffid}";
                $role_data = $this->getAdapter()->fetchOne($sql_role);
//                return json_encode($role_data); die;
                if($role_data && is_array($role_data)){

                    $sql_con = "SELECT `id` from `cms_role_controller` where `role_id` = {$role_data['role_id']} AND `controller` = '{$data['class_name']}'";
                    $con_data = $this->getAdapter()->fetchOne($sql_con);
//
//                return json_encode($con_data); die;
                    if($con_data && is_array($con_data)){
                        return 1;
                    }


                    if($data['permission_status'] == 'S'){
                        $sql_act = "SELECT `id` from `cms_role_permission` where `role_id` = {$role_data['role_id']} AND `action_id` = '{$data['id']}' AND `params_permission` = '{$permission_params}' ";
                    }else{
                        $sql_act = "SELECT `id` from `cms_role_permission` where `role_id` = {$role_data['role_id']} AND `action_id` = '{$data['id']}' ";
                    }

                    $act_data = $this->getAdapter()->fetchOne($sql_act);

                    if($act_data && is_array($act_data)){
                        return 1;
                    }

                }
            }
            return -1;

        }else{
            return 1;
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



    public function getSimplyCount($params = array()){

        // 初始数据
        $init_params = array(
            'table' =>'',
            'where' => '1',
            'group' => ''
        );
        $params = array_merge($init_params, $params);

        $count_sql = "SELECT count(*) as itemCount FROM {$params['table']} WHERE {$params['where']} ";

        if($params['group']) $count_sql .= " GROUP BY ".$params['group'];

        $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);

        return $count_res;

    }


    public function updateDataByConditionSrt($table, $setData, $conditionSrt){
        $is_ok = $this->getAdapter()->update($table, array_keys($setData), array_values($setData), $conditionSrt);
        if($is_ok){
            return 200;
        }else{
            return 500;
        }
    }



    /**
     * 根据控制器查找菜单ID
     * @param $controller
     * @return array
     */
    public function getMenuIdByController($controller)
    {
        $result = $this->getOneByCondition('cms_action_controller','system_id',ucfirst($controller) . 'Controller','controller');
        return $result ? $result['system_id'] : '0';
    }
}
