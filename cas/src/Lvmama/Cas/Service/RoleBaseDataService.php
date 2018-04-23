<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 角色数据 服务类
 *
 * @author flash.guo
 *
 */
class RoleBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_role';//对应数据库表
    const PRIMARY_KEY = 'role_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加角色数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新角色数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'role_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除角色数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'role_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取角色数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getRoleList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取角色总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getRoleTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条角色数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneRole($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条角色数据
     * @param $role_id 编号
     * @return bool|mixed
     */
    public function getOneById($role_id){
        $where_condition=array('role_id'=>"=".$role_id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据名称获取一条角色数据
     * @param $name 名称
     * @return bool|mixed
     */
    public function getOneByName($name){
        if(!$name) return false;
        $where_condition=array('status'=>" = 1",'role_name'=>"='".$name."'");
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }
}