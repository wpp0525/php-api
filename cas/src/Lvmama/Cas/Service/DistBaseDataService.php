<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 行政区数据 服务类
 *
 * @author flash.guo
 *
 */
class DistBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'biz_district';//对应数据库表
    const PRIMARY_KEY = 'district_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加行政区数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新行政区数据
     * @param $id 编号
     * @param $data array 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'district_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 根据条件获取行政区数据
     * @param $where_condition array|string 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDistList($where_condition, $limit = NULL, $columns = "*", $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取行政区总数
     * @param $where_condition array|string 查询条件
     * @return array|mixed
     */
    public function getDistTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条行政区数据
     * @param $where_condition array|string 查询条件
     * @return bool|mixed
     */
    public function getOneDist($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条行政区数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('district_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
}