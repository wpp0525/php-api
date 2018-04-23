<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 管理员角色关联数据 服务类
 *
 * @author flash.guo
 *
 */
class StaffRoleDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_staff_role';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加管理员角色关联数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新管理员角色关联数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取管理员角色关联数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getStaffRoleList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条管理员角色关联数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneStaffRole($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条管理员角色关联数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据管理员ID获取一条管理员角色关联数据
     * @param $staffid 管理员ID
     * @return bool|mixed
     */
    public function getRoleByStaffid($staffid){
        if(!$staffid) return false;
        $where_condition=array('staff_id'=>"='".$staffid."'");
        $role_data=$this->getList($where_condition, self::TABLE_NAME);
        return $role_data?$role_data:false;
    }

    public function findRoleByStaffid($staffid){
        if(!$staffid) return false;
        $where_condition = array('staff_id' => "='".$staffid."'");
        $role_data = $this->getOne($where_condition, self::TABLE_NAME);
        return $role_data ? $role_data : false;
    }




    /**
     * @purpose 根据管理员ID获取一条管理员角色关联数据
     * @param $staffid 管理员ID
     * @param $roleid 角色ID
     * @return bool|mixed
     */
    public function delRoleByStaffid($staffid, $roleid = 0){
        if(!$staffid) return false;
        $where_condition = 'staff_id ='.$staffid;
        if($roleid) $where_condition .= ' AND role_id = ' . $roleid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
    
    /**
     * @purpose 根据角色ID获取一条管理员角色关联数据
     * @param $roleid 角色ID
     * @return bool|mixed
     */
    public function delRoleByRoleid($roleid){
        if(!$roleid) return false;
        $where_condition = 'role_id ='.$roleid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }


    public function getStaffsByRole($roleid){
        if(!$roleid) return false;
        $sql = "SELECT b.id,b.username,b.fullname FROM ".self::TABLE_NAME." AS a LEFT JOIN cms_staff AS b ON a.staff_id = b.id WHERE a.role_id = {$roleid} ORDER BY b.username ASC";
        $data = $this->query($sql, 'All');
        return $data ? $data : array();
    }



}