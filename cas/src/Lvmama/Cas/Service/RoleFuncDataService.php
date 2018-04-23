<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 角色功能关联数据 服务类
 *
 * @author flash.guo
 *
 */
class RoleFuncDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_role_function';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加角色功能关联数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新角色功能关联数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除角色功能关联数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取角色功能关联数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getRoleFuncList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条角色功能关联数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneRoleFunc($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条角色功能关联数据
     * @param $role_id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据角色ID获取一条角色功能关联数据
     * @param $roleid 角色ID
     * @return bool|mixed
     */
    public function getFuncByRoleid($roleid){
        if(!$roleid) return false;
        $where_str = is_array($roleid) ? ' role_id in('.implode(',', $roleid).')' : ' role_id = '.$roleid;
        $sql="SELECT rf.*,f.function_name,f.function_key  FROM ".self::TABLE_NAME." rf LEFT JOIN cms_function f ON f.function_id = rf.function_id WHERE ".$where_str;
//        echo $sql; die;
        $base_data=$this->query($sql,'All');
        return $base_data?$base_data:false;
    }


    public function getMethodByStaffid($staffId){
        if(!$staffId) return array();
        $sql = "SELECT `action_id`, `action_status` FROM `cms_staff_permission` WHERE staff_id = '{$staffId}'";
//        echo $sql;die;
        $con_data = $this->query($sql, 'All');

        $return = array('Y' => array(), 'N' => array());
        if($con_data && is_array($con_data)){
            foreach($con_data as $con){
                if($con['action_status'] == 1){
                    $return['Y'][] = $con['action_id'];
                }else{
                    $return['N'][] = $con['action_id'];
                }
            }
        }
        return $return;

    }

    /**
     * @param $roleid
     * @return array
     * new 根据权限获取有权限的方法
     */
    public function getMethodByRoleid($roleid){

        if(!$roleid) return array();
        $sql = "SELECT `controller_id`, `controller` FROM `cms_role_controller` WHERE role_id = '{$roleid}'";
        $con_data = $this->query($sql, 'All');

        if($con_data && is_array($con_data)){
            foreach($con_data as $con){
                $in[] = $con['controller'];
            }
        }
        unset($con_data);

        $condition = '';
        if($in){
            $imp = implode("','", $in);
            $condition = "`class_name` IN ('{$imp}') OR";
        }
        unset($in);

        $sql2 = "SELECT `action_id` FROM `cms_role_permission` WHERE role_id = '{$roleid}'";
        $con_data2 = $this->query($sql2, 'All');
        if($con_data2 && is_array($con_data2)){
            foreach($con_data2 as $con){
                $in[] = $con['action_id'];
            }
        }
        unset($con_data2);
        if($in){
            $imp = implode("','", $in);
            $condition .= " `id` IN ('{$imp}') OR";
        }
        unset($in);

//        $sql_2 = "SELECT `id`,`method`,`class_name` FROM `cms_action` WHERE {$condition} `action_name` = `method`";
        $sql_2 = "SELECT `id` FROM `cms_action` WHERE {$condition} `action_name` = `method`";
        unset($condition);
        $con_data_2 = $this->query($sql_2, 'All');

        if($con_data_2 && is_array($con_data_2)){
            foreach($con_data_2 as $con_2){
                $in_ids[] = $con_2['id'];
            }
        }

        return $in_ids ? $in_ids: array();

    }



    /**
     * @purpose 根据角色ID获取一条管理员角色关联数据
     * @param $roleid 角色ID
     * @param $funcid 功能ID
     * @return bool|mixed
     */
    public function delFuncByRoleid($roleid, $funcid = 0){
        if(!$roleid) return false;
        $where_condition = 'role_id ='.$roleid;
        if($roleid) $where_condition .= ' AND function_id = ' . $funcid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
    
    /**
     * @purpose 根据功能ID获取一条管理员角色关联数据
     * @param $funcid 功能ID
     * @return bool|mixed
     */
    public function delFuncByFuncid($funcid){
        if(!$funcid) return false;
        $where_condition = 'function_id ='.$funcid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
}