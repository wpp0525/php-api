<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 订单信息 服务类
 *
 * @author libiying
 *
 */
class SemOrderDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_order';//对应数据库表
    const PRIMARY_KEY = 'ORDER_ID'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    const TABLE_ORDER_LOSC = 'sem_order_losc';
    const TABLE_ORDER_LOSC_REPORT = 'sem_order_losc_report';
    const TABLE_ORDER_ITEM = 'sem_order_item';

    private $rel_tables = array(
        'user' => array(
            'table' => 'sem_user',
            'key' => 'USER_ID',
            'foreign_key' => 'user_no',
        ),
        'order_item' => array(
            'table' => 'sem_order_item',
            'key' => 'ORDER_ID',
            'foreign_key' => 'ORDER_ID',
        )
    );

    /**
     * 添加订单信息
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
//        return null;
    }

    /**
     * 更新订单信息
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
//        return null;
    }

    /**
     * 删除订单信息
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
//        return null;
    }

    /**
     * @purpose 根据条件获取订单信息
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getOrderList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取订单信息总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getOrderTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条订单信息
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneOrder($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条订单信息
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array(self::PRIMARY_KEY => "=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    public function getOrderWithLosc($condition, $select = null, $group = null, $limit = null){

        $select = $select ? " SELECT " . $select : " SELECT *";
        $from = " FROM " . self::TABLE_NAME;
        $join = " INNER JOIN " . self::TABLE_ORDER_LOSC . " ON " . self::TABLE_NAME . ".ORDER_ID = " . self::TABLE_ORDER_LOSC . ".ORDER_ID ";
        $where = $this->initWhere($condition);
        $group = $group ? " GROUP BY " . $group : "";
        $limit = $limit ? " LIMIT " . $limit : "";

        $sql =  $select . $from .  $join .  $where . $group . $limit;
        $result = $this->query($sql, 'All');

        return $result;
    }

    /**
     * 获取订单全量信息，（可包括用户，明细等）
     * @param $condition
     * @param null $select
     * @param array $joins
     * @param null $group
     * @param null $limit
     * @return bool|mixed
     */
    public function getFullOrderList($condition, $select = null, $joins = array('user' ,'order_item'), $group = null, $limit = null){

        $select = $select ? " SELECT " . $select : " SELECT *";
        $from = " FROM " . self::TABLE_NAME;
        $join = '';
        foreach ($joins as $j){
            if(isset($this->rel_tables[$j])){
                $join .= $this->initJoin($this->rel_tables[$j], self::TABLE_NAME);
            }
        }
        $where = $this->initWhere($condition);
        $group = $group ? " GROUP BY " . $group : "";
        $limit = $limit ? " LIMIT " . $limit : "";

        $sql =  $select . $from .  $join .  $where . $group . $limit;
        $result = $this->query($sql, 'All');

        return $result;
    }

    public function insertOrderLoscReport($params){
        if(!is_array($params)){
            return false;
        }
        $data = array();
        foreach ($params as $key => $p){
            if(in_array($key, array('LOSC_ID', 'ORDER_ID', 'DISTRIBUTOR_CODE', 'DISTRIBUTOR_ID', 'ACTUAL_AMOUNT', 'PAYMENT_TIME', 'TYPE'))){
                $data[$key] = $p;
            }
        }
        return $this->getAdapter()->insert(self::TABLE_ORDER_LOSC_REPORT, array_values($data), array_keys($data));
    }

    public function getOrderLoscReport($condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($condition, self::TABLE_ORDER_LOSC_REPORT, $limit, $columns, $order);
        return $data?$data:false;
    }

    public function getOrderLoscReportTotal($condition){
        $data=$this->getTotalBy($condition, self::TABLE_ORDER_LOSC_REPORT);
        return $data?$data:false;
    }

}