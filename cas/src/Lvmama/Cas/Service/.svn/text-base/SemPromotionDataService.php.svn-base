<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 促销信息 服务类
 *
 * @author guoqiya
 *
 */
class SemPromotionDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_ord_promotion';//对应数据库表
    const PRIMARY_KEY = 'ORD_PROMOTION_ID'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    const TABLE_ORDER = 'sem_order';
    const TABLE_ORDER_ITEM = 'sem_order_item';
    const TABLE_PROM_PROMOTION = 'sem_prom_promotion';
    const TABLE_ORDER_AMOUNT_ITEM = 'sem_order_amount_item';

    /**
     * 添加促销信息
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新促销信息
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除促销信息
     * @param $id 编号
     * @param $data 删除数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取促销信息
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getCouponList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取促销信息总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getCouponTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条促销信息
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneCoupon($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条促销信息
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array(self::PRIMARY_KEY => "=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取促销及其关联部门信息
     * @param $condition 查询条件
     * @param $select 查询字段
     * @param $group 分组字段
     * @param $limit 查询条数
     * @return bool|mixed
     */
    public function getPromotionWithDept($condition, $select = null, $group = null, $limit = null){
        $select = $select ? " SELECT " . $select : " SELECT *";
        $from = " FROM " . self::TABLE_NAME;
        $join = " LEFT JOIN " . self::TABLE_ORDER_ITEM . " ON " . self::TABLE_ORDER_ITEM . ".ORDER_ITEM_ID = " . self::TABLE_NAME . ".ORDER_ITEM_ID AND MAIN_ITEM = 'true' ";
        $join .= " LEFT JOIN " . self::TABLE_ORDER . " ON " . self::TABLE_ORDER . ".ORDER_ID = " . self::TABLE_ORDER_ITEM . ".ORDER_ID AND PAYMENT_STATUS = 'PAYED' AND ORDER_STATUS != 'CANCEL' ";
        $join .= " LEFT JOIN " . self::TABLE_PROM_PROMOTION . " ON " . self::TABLE_PROM_PROMOTION . ".PROM_PROMOTION_ID = " . self::TABLE_NAME . ".PROM_PROMOTION_ID AND VALID = 'Y' ";
        $where = $this->initWhere($condition);
        $group = $group ? " GROUP BY " . $group : "";
        $limit = $limit ? " LIMIT " . $limit : "";

        $sql =  $select . $from .  $join .  $where . $group . $limit;
        $result = $this->query($sql, 'All');

        return $result;
    }

    /**
     * @purpose 根据条件获取无费用承担部门的促销信息
     * @param $condition 查询条件
     * @param $select 查询字段
     * @param $group 分组字段
     * @param $limit 查询条数
     * @return bool|mixed
     */
    public function getPromotionNoDept($condition, $select = null, $group = null, $limit = null){
        $select = $select ? " SELECT " . $select : " SELECT *";
        $from = " FROM " . self::TABLE_ORDER_AMOUNT_ITEM;
        $join .= " LEFT JOIN " . self::TABLE_ORDER . " ON " . self::TABLE_ORDER . ".ORDER_ID = " . self::TABLE_ORDER_AMOUNT_ITEM . ".ORDER_ID AND PAYMENT_STATUS = 'PAYED' AND ORDER_STATUS != 'CANCEL' ";
        $join .= " LEFT JOIN " . self::TABLE_ORDER_ITEM . " ON " . self::TABLE_ORDER_ITEM . ".ORDER_ID = " . self::TABLE_ORDER . ".ORDER_ID AND MAIN_ITEM = 'true' ";
        $join .= " LEFT JOIN " . self::TABLE_NAME . " ON " . self::TABLE_NAME . ".ORDER_ITEM_ID = " . self::TABLE_ORDER_ITEM . ".ORDER_ITEM_ID ";
        $where = $this->initWhere($condition);
        $group = $group ? " GROUP BY " . $group : "";
        $limit = $limit ? " LIMIT " . $limit : "";

        $sql =  $select . $from .  $join .  $where . $group . $limit;
        $result = $this->query($sql, 'All');

        return $result;
    }

}