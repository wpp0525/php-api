<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 新版目的地数据 服务类
 *
 * @author jack.dong
 *
 */
class DestinBaseMultiRelationDataService extends DataServiceBase {

    const TABLE_NAME = 'biz_dest_multi_relation';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加目的地数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        $this->getAdapter()->forceMaster();
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新目的地数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'dest_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取目的地数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDestList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        if(is_array($where_condition)){
            foreach($where_condition as $key=>$row){
                $where_arr[]=$key.$row;
            }
            !empty($where_arr) && $where_str=" WHERE ".implode(' AND ',$where_arr);
        }else{
            !empty($where_condition) && $where_str=" WHERE ".$where_condition;
        }
        if($order!==null){
        	$order_str=" ORDER BY ".$order;
        } else {
        	$order_str=" ORDER BY d.dest_id ASC";
        }
        if($limit!==null && !strstr($where_str, "d.dest_id in(")){//排除in查询
        	$operation = strstr($order_str, "d.dest_id DESC") ? "<=" : ">=";
            if(is_array($limit)){
                $limit_str=" LIMIT ".$limit['page_size'];
            	if (!empty($where_str)) {
            		$where_str.=" AND d.dest_id".$operation."(SELECT d.dest_id FROM ".self::TABLE_NAME." d ".$where_str.$order_str." LIMIT ".($limit['page_num']-1)*$limit['page_size'].",1)";
            	} else {
            		$where_str=" WHERE d.dest_id".$operation."(SELECT d.dest_id FROM ".self::TABLE_NAME." d ".$where_str.$order_str." LIMIT ".($limit['page_num']-1)*$limit['page_size'].",1)";
            	}
            }else{
            	$limit_arr = explode(",", $limit);
                $limit_str=" LIMIT ".intval($limit_arr[1]);
            	if (!empty($where_str)) {
            		$where_str.=" AND d.dest_id".$operation."(SELECT d.dest_id FROM ".self::TABLE_NAME." d ".$where_str.$order_str." LIMIT ".intval($limit_arr[0]).",1)";
            	} else {
            		$where_str=" WHERE d.dest_id".$operation."(SELECT d.dest_id FROM ".self::TABLE_NAME." d ".$where_str.$order_str." LIMIT ".intval($limit_arr[0]).",1)";
            	}
            }
        }
        if($columns===null){
        	$column_str="d.*,di.district_name";
        }
        $sql="SELECT ".$column_str." FROM ".self::TABLE_NAME." d LEFT JOIN biz_district di ON di.district_id = d.district_id ".$where_str.$order_str.$limit_str;
        $base_data=$this->query($sql,'All');
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据条件获取目的地总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getDestTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME." d");
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条目的地数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneDest($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条目的地数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('dest_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    public function getRsBySql($sql,$one = false){
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $one ? $result->fetch() : $result->fetchAll();
    }

    /**
     * @purpose 根据条件获取目的地数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDefaultList($where_condition, $limit = NULL, $columns = "*", $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条目的地数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getParentOneById($id){
        $where_condition=array('parent_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * 删除
     * @param $dest_id
     * @return bool
     */
    public function deleteByDestId($dest_id){
        $condition = 'dest_id = ' .$dest_id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $condition);
    }
}