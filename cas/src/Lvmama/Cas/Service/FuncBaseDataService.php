<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 功能数据 服务类
 *
 * @author flash.guo
 *
 */
class FuncBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_function';//对应数据库表
    const PRIMARY_KEY = 'function_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加功能数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新功能数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'function_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除功能数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'function_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取功能数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getFuncList($where_condition, $limit=null, $columns="*", $order="sort_num"){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条功能数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneFunc($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条功能数据
     * @param $function_id 编号
     * @return bool|mixed
     */
    public function getOneById($function_id){
        $where_condition=array('function_id'=>"=".$function_id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据名称获取一条功能数据
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