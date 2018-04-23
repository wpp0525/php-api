<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 新版目的地数据 服务类
 *
 * @author flash.guo
 *
 */
class DestinBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'biz_dest';//对应数据库表
    const PRIMARY_KEY = 'dest_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;
    const EXPIRE_TIME = 86400;

    /**
     * 添加目的地数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
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
     * @param $id
     * @param $data
     * @return bool|void
     */
    public function updateCustom($table_name, $id, $data)
    {
        $whereCondition = 'dest_id = ' . $id;
        return $this->getAdapter()->update($table_name, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @param $id
     * @param $data
     * @return bool|void
     */
    public function updateCustomForCoordinate($table_name, $where_data, $data)
    {
        $whereCondition = ' object_id = "' . $where_data['object_id'] . '" and coord_type = "' . $where_data['coord_type'] . '"';
        return $this->getAdapter()->update($table_name, array_keys($data), array_values($data), $whereCondition);
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

    /**
     * 根据拼音获取数据
     * @param $pinyin
     * @return bool|mixed
     */
    public function getOneByOtherDestInfo($dest_name, $dest_type, $district_id )
    {
        $sql = "SELECT dest_id FROM ". self::TABLE_NAME . " WHERE dest_name = '$dest_name' and dest_type = '$dest_type' and district_id = '$district_id' ";
        $result=$this->query($sql);
        return $result?$result:false;
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
     * 获取biz_dest_temp表中数据
     * @param $where_condition
     * @param null $limit
     * @param string $columns
     * @param null $order
     * @return $this|bool
     */
    public function getDefaultListByTableName($where_condition, $limit = NULL, $columns = "*", $order = NULL){
        $data=$this->getList($where_condition, 'biz_dest_temp', $limit, $columns, $order);
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
     * 根据行政区ID获取相应的目的地基本信息
     * @param $district_id
     * @return array
     * @author shenxiang
     */
    public function getDistrictById($district_id){
        $data = array();
        if(!$district_id || !is_numeric($district_id)) return $data;
        $key = str_replace('{id}',$district_id,RedisDataService::REDIS_DISTRICT_INFO);
        $data = $this->redis->hGetAll($key);
        if(!$data){
            $sql = 'SELECT * FROM biz_district WHERE district_id = '.$district_id.' AND cancel_flag = \'Y\'';
            $data = $this->getRsBySql($sql,true);
            $this->redis->hmset($key,$data);
            $this->redis->expire($key,self::EXPIRE_TIME);
        }
        return $data;
    }

    /**
     * @purpose 根据主键获取一条目的地数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function geDestNameByIds($ids){
        $result = array();
        $sql = "select dest_id, dest_name from " . self::TABLE_NAME . " where dest_id in (" . implode(',', $ids) . ")";
        $data = $this->getAdapter()->query($sql);

        $data = $data->fetchAll();
        
        if(!empty($data)){
            foreach($data as $item){
                $result[$item['dest_id']] = $item['dest_name'];
            }
        }

        return $result;
    }
    
    public function getDestChildList($dest_id, $dest_type)
    {
        $res = $dest_ids = array();
        $sql = "select dest_id from biz_dest where parent_id = " . $dest_id . " and dest_type = 'COUNTY' and cancel_flag = 'Y'";
        $dest_list = $this->query($sql,'All');
        if(!empty($dest_list)){
            foreach ($dest_list as $item) {
                $dest_ids[] = $item['dest_id'];
            }
        }
        $dest_ids[] = $dest_id;

        $sql_dest_ids = "select dest_id from biz_dest where cancel_flag = 'Y' and dest_type = '" . $dest_type . "' and parent_id in (" . implode(',', $dest_ids) . ")";
        $res = $this->query($sql_dest_ids, 'All');

        return $res;
    }

    /**
     * 获取dist符合信息（ip定位使用）
     * @param $limit
     */
    public function getDistrictInfo($limit){
        $sql = "
            SELECT
                biz_dest.dest_id,
                biz_dest.dest_type,
                biz_dest.dest_name,
                biz_district.district_id,
                biz_district.parent_id AS parent_district_id,
                dest_com_city.city_id,
                dest_com_city.city_name,
                dest_com_city.province_id,
                dest_com_city.province_name
            FROM
                biz_dest
            INNER JOIN biz_district ON biz_district.district_id = biz_dest.district_id
            LEFT JOIN dest_com_city ON dest_com_city.district_id = biz_district.district_id
            WHERE
                biz_dest.cancel_flag = 'Y'
                AND biz_dest.dest_type IN (
                        'CITY',
                        'CONTINENT',
                        'COUNTRY',
                        'COUNTY',
                        'PROVINCE',
                        'TOWN'
                    )";
        if($limit){
            $sql .= " LIMIT $limit";
        }
        return $this->query($sql,'All');
    }

    /**
     * 获取dist符合信息（ip定位使用）
     * @param $limit
     */
    public function getDistrictInfo2($limit){
        $sql = "
            SELECT
                biz_district.district_id,
                biz_district.parent_id AS parent_district_id,
                biz_district.district_name,
                biz_district.province_name
            FROM
                biz_district  
            WHERE
                biz_district.cancel_flag = 'Y'
                AND biz_district.foreign_flag = 'N'
                AND biz_district.district_type IN (
                        'PROVINCE',
                        'PROVINCE_DCG',
                        'PROVINCE_AN'
                    )";
        if($limit){
            $sql .= " LIMIT $limit";
        }
        return $this->query($sql,'All');
    }


    /**
     * 获取dist符合信息（ip定位使用）
     * @param $limit
     */
    public function getProvinceProductInfo($limit, $provinceId, $categoryIds){
        $sql = "
            SELECT
                dest_product_rel_v2.product_id
            FROM
                dest_product_rel_v2
            WHERE
                dest_product_rel_v2.province_id = " . $provinceId . "
                AND dest_product_rel_v2.category_id IN (". implode($categoryIds,",") .")";

        if($limit){
            $sql .= " LIMIT $limit";
        }
        return $this->query($sql,'All');
    }

    public function getDestChildListByParentId($dest_id)
    {
        $result = array();
        $sql = "select dest_id from biz_dest where parent_id = " . $dest_id . " and cancel_flag = 'Y'";
        $dest_list = $this->query($sql, 'All');
        if(!empty($dest_list)){
            foreach($dest_list as $item){
                $result[] = $item['dest_id'];
            }
        }

        return $result;
    }

	public function getAllDestType(){
        $redis_key_all = RedisDataService::REDIS_DEST_TYPE_LIST;
        $redis_key_type = RedisDataService::REDIS_DEST_TYPE_CODE;
        $dest_types = $this->redis->zrange($redis_key_all,0,-1);
        if($dest_types){
            $tmp = array();
            foreach($dest_types as $score => $dest_type){
                $tmp[$dest_type] = $this->redis->hgetall($redis_key_type.$dest_type);
            }
            $dest_types = $tmp;
        }else{
            $rs = $this->getList(array(),'ly_dest_type');
            $dest_types = array();
            foreach($rs as $dest_type){
                $this->redis->zadd($redis_key_all,$dest_type['dest_type_id'],$dest_type['code']);
                $this->redis->hmset($redis_key_type.$dest_type['code'],$dest_type);
                $dest_types[$dest_type['code']] = $dest_type;
            }
            $this->redis->expire($redis_key_all,RedisDataService::REDIS_EXPIRE_HALF_MONTH);
        }
        return $dest_types;
    }

    /**
     * 根据条件获取dest_type信息
     * @param array $condition
     * @author shenxiang
     */
    public function getDestType($condition = array()){
        $return = array();
        $dest_types = array();
        if(!empty($condition['code'])){
            $redis_key = RedisDataService::REDIS_DEST_TYPE_CODE.$condition['code'];
            $redis_data = $this->redis->hGetAll($redis_key);
            if($redis_data){
                $return[] = $redis_data;
                return $return;
            }
            $condition['code'] = ' = \''.$condition['code'].'\'';
        }
        $redis_key = RedisDataService::REDIS_DEST_TYPE_CODE.'*';
        $keys = $this->redis->keys($redis_key);
        foreach($keys as $key){
            $dest_types[] = $this->redis->hGetAll($key);
        }
        if(!empty($condition['dest_type_name'])){
            foreach($dest_types as $row){
                if(!empty($row['dest_type_name'])) return array($row);
            }
            $condition['dest_type_name'] = ' = \''.$condition['dest_type_name'].'\'';
        }
        if(!empty($condition['dest_type_id'])){
            foreach($dest_types as $row){
                if($row['dest_type_id']) return array($row);
            }
            $condition['dest_type_id'] = ' = '.$condition['dest_type_id'];
        }
        if(!empty($condition['group_id'])){
            foreach($dest_types as $row){
                if(!empty($row['group_id'])){
                    $return[] = $row;
                }
            }
            if($return)
                return $return;
            else
                $condition['group_id'] = ' = '.$condition['group_id'];
        }
        $dest_types = $this->getList($condition,'ly_dest_type');
        foreach($dest_types as $row){
            $redis_key = RedisDataService::REDIS_DEST_TYPE_CODE.$row['code'];
            $this->redis->hmset($redis_key,$row);
        }
        return $dest_types;
    }
    /**
     * 根据搜索条件获取目的地数据
     */
    public function search($condition = array(),$limit = array(),$fields = array()){
        if(empty($fields)) $fields = array('dest_id','dest_name','parent_id','parent_name','dest_type','cancel_flag','showed','abroad','dest_type_name','district_name');
        //防止设置pageSize太大拖垮库
        $limit['page_size'] = isset($limit['page_size']) && is_numeric($limit['page_size']) ? ($limit['page_size'] > 30 ? 30 : $limit['page_size']) : 15;
        $limit['page_num'] = isset($limit['page_num']) && is_numeric($limit['page_num']) ? $limit['page_num'] : 1;
        $where = ' WHERE 1 = 1';
        if(isset($condition['dest_id'])) $where .= ' AND dest_id = '.$condition['dest_id'];
        if(isset($condition['stage'])) $where .= ' AND stage = '.$condition['stage'];
        if(isset($condition['dest_type'])) $where .= ' AND dest_type = \''.$condition['dest_type'].'\'';
        if(isset($condition['cancel_flag'])) $where .= ' AND cancel_flag = \''.$condition['cancel_flag'].'\'';
        if(isset($condition['dest_name'])) $where .= ' AND dest_name LIKE \'%'.$condition['dest_name'].'%\'';
        if(isset($condition['parent_name'])) $where .= ' AND parent_name LIKE \'%'.$condition['parent_name'].'%\'';

        //获取符合条件的总条数
        $tmp = $this->query('SELECT COUNT(dest_id) AS c FROM ly_destination'.$where);
        $count = intval($tmp['c']);
        //总页码
        $totalPage = ceil($count / $limit['page_size']);
        $limit['page_num'] = $limit['page_num'] > $totalPage ? $totalPage : $limit['page_num'];
        $list = $this->query('SELECT `'.implode('`,`',$fields).'` FROM ly_destination'.$where.' LIMIT '.(($limit['page_num'] - 1)*$limit['page_size']).','.$limit['page_size'],'All');
        return array('list' => $list,'count' => $count,'page_num' => $limit['page_num'],'page_size' => $limit['page_size'],'maxPage' => $totalPage);
    }
}