<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 管理员数据 服务类
 *
 * @author flash.guo
 *
 */
class StaffBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_staff';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加管理员数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function create($data){
        $is_ok = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
        if($is_ok){
            $id = $this->getAdapter()->lastInsertId();
        }
        return $id ? $id : 0 ;
    }



    /**
     * 更新管理员数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取管理员数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getStaffList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取管理员总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getStaffTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条管理员数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneStaff($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条管理员数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据名称获取一条管理员数据
     * @param $username 名称
     * @return bool|mixed
     */
    public function getOneByUsername($username){
        if(!$username) return false;
        $where_condition=array('status'=>" = 1",'username'=>"='".$username."'");
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }
}