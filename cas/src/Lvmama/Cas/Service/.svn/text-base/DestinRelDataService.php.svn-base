<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 新版目的地次父级数据 服务类
 *
 * @author flash.guo
 *
 */
class DestinRelDataService extends DataServiceBase {

    const TABLE_NAME = 'biz_dest_relation';//对应数据库表
    const PRIMARY_KEY = 'dest_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加目的地次父级数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新目的地次父级数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'dest_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取目的地次父级数据
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDestrelList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
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
        if($columns===null){
        	$column_str="dr.*,d.dest_name";
        }
        if($order!==null){
        	$order_str=" ORDER BY ".$order;
        } else {
        	$order_str=" ORDER BY dr.dest_id ASC";
        }
        $sql="SELECT ".$column_str." FROM ".self::TABLE_NAME." dr LEFT JOIN biz_dest d ON d.dest_id = d.dest_id ".$where_str.$order_str.$limit_str;
        $base_data=$this->query($sql,'All');
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据条件获取目的地次父级总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getDestrelTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME." d");
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条目的地次父级数据
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneDestrel($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据目的地ID获取一条目的地次父级数据
     * @param $destid 目的地ID
     * @return bool|mixed
     */
    public function getRelByDestid($destid){
        if(empty($destid)) return false;
        $where_str=" WHERE dr.dest_id=".$destid;
        $column_str="dr.*,d.dest_name as parent_name";
        $sql="SELECT ".$column_str." FROM ".self::TABLE_NAME." dr LEFT JOIN biz_dest d ON d.dest_id = dr.parent_id ".$where_str;
        $rel_data=$this->query($sql,'All');
        return $rel_data?$rel_data:false;
    }
    
    /**
     * @purpose 根据管理员ID获取一条目的地次父级数据
     * @param $destid 目的地ID
     * @param $parentid 次父级ID
     * @return bool|mixed
     */
    public function delRelByDestid($destid, $parentid = 0){
        if(empty($destid)) return false;
        $where_condition = 'dest_id ='.$destid;
        if($parentid) $where_condition .= ' AND parent_id = ' . $parentid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
}