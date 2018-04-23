<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 操作日志数据 服务类
 *
 * @author flash.guo
 *
 */
class LogBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'cms_log';//对应数据库表
    const PRIMARY_KEY = 'log_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加操作日志数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新操作日志数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'log_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取操作日志数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getLogList($where_condition, $limit = NULL, $order = NULL){
        if(is_array($where_condition)){
            foreach($where_condition as $key=>$row){
                $where_arr[]=$key.$row;
            }
            !empty($where_arr) && $where_str=" WHERE ".implode(' AND ',$where_arr);
        }else{
            !empty($where_condition) && $where_str=" WHERE ".$where_condition;
        }
        if($limit!==null){
            if(is_array($limit)){
                $limit_str=" LIMIT ".($limit['page_num']-1)*$limit['page_size']." , ".$limit['page_size'];
            }else{
                $limit_str=" LIMIT ".$limit;
            }
        }
        if($order!==null){
        	$order_str=" ORDER BY ".$order;
        } else {
        	$order_str=" ORDER BY log_id DESC";
        }
        $sql="SELECT l.*,s.username  FROM ".self::TABLE_NAME." l LEFT JOIN cms_staff s ON s.id = l.staffid ".$where_str.$order_str.$limit_str;
        $base_data=$this->query($sql,'All');
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据条件获取操作日志总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getLogTotal($where_condition){
        if(is_array($where_condition)){
            foreach($where_condition as $key=>$row){
                $where_arr[]=$key.$row;
            }
            !empty($where_arr) && $where_str=" WHERE ".implode(' AND ',$where_arr);
        }else{
            !empty($where_condition) && $where_str=" WHERE ".$where_condition;
        }
        $sql="SELECT COUNT(1) AS num FROM ".self::TABLE_NAME." l LEFT JOIN cms_staff s ON s.id = l.staffid ".$where_str;
        $result=$this->query($sql);
		return $result['num']?$result['num']:false;
    }

    /**
     * @purpose 根据条件获取一条操作日志数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneLog($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条操作日志数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('log_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据管理员ID获取一条操作日志数据
     * @param $staffid 管理员ID
     * @return bool|mixed
     */
    public function getLogByStaffid($staffid){
        if(!$staffid) return false;
        $where_condition=array('staffid'=>"='".$staffid."'");
        $role_data=$this->getList($where_condition, self::TABLE_NAME);
        return $role_data?$role_data:false;
    }
    
    /**
     * @purpose 根据管理员ID获取一条操作日志数据
     * @param $staffid 管理员ID
     * @param $action 操作类型
     * @return bool|mixed
     */
    public function delLogByStaffid($staffid, $action = ""){
        if(!$staffid) return false;
        $where_condition = 'staffid ='.$staffid;
        if($action) $where_condition .= ' AND $action = ' . $action;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
}