<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 优惠券信息 服务类
 *
 * @author guoqiya
 *
 */
class SemCouponDataService extends DataServiceBase {

    const TABLE_NAME = 'mark_coupon_usage';//对应数据库表
    const PRIMARY_KEY = 'usage_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    const TABLE_COUPON_DEPT = 'mark_coupon_dept';

    /**
     * 添加优惠券信息
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新优惠券信息
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除优惠券信息
     * @param $id 编号
     * @param $data 删除数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取优惠券信息
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
     * @purpose 根据条件获取优惠券信息总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getCouponTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条优惠券信息
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneCoupon($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条优惠券信息
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array(self::PRIMARY_KEY => "=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取优惠券及其关联订单信息
     * @param $condition 查询条件
     * @param $select 查询字段
     * @param $group 分组字段
     * @param $limit 查询条数
     * @return bool|mixed
     */
    public function getCouponWithOrder($condition, $select = null, $group = null, $limit = null){
        $select = $select ? " SELECT " . $select : " SELECT *";
        $from = " FROM " . self::TABLE_NAME;
        $join = " LEFT JOIN " . self::TABLE_COUPON_DEPT . " ON " . self::TABLE_COUPON_DEPT . ".coupon_id = " . self::TABLE_NAME . ".coupon_id ";
        $where = $this->initWhere($condition);
        $group = $group ? " GROUP BY " . $group : "";
        $limit = $limit ? " LIMIT " . $limit : "";

        $sql =  $select . $from .  $join .  $where . $group . $limit;
        $result = $this->query($sql, 'All');

        return $result;
    }

}