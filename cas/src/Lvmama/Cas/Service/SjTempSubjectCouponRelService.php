<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 模版专题 服务类
 * xnw
 */
class SjTempSubjectCouponRelService extends DataServiceBase {

    const TABLE_NAME = 'sj_template_subject_coupon_rel';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $where = self::PRIMARY_KEY.' = '. $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);
    }

    /**
     * 更新2
     * @param $sid 专题id
     * @param $cid 优惠券批次号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update2($sid, $cid, $data) {
        $where = 'subject_id = '. $sid .' AND coupon_id = '. $cid;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);
    }


    /**
     * 删除
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $where = self::PRIMARY_KEY.' = '. $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where);
    }

    /**
     * @purpose 根据条件获取
     * @param $where 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getDataList($where, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取总数
     * @param $where 查询条件
     * @return array|mixed
     */
    public function getTotal($where){
        $data=$this->getTotalBy($where, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条
     * @param $where 查询条件
     * @return bool|mixed
     */
    public function getDataOne($where){
        $data=$this->getOne($where, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据专题ID和优惠券ID删除绑定记录
     * @param $sid 专题ID
     * @param $cid 优惠券ID
     * @return bool|mixed
     */
    public function delSidBindCoupon($sid, $cid){
        if(empty($sid) || empty($cid)) return false;
        $where_condition = 'subject_id ='.$sid.' AND coupon_id ='.$cid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
    /**
     * @purpose 根据专题ID和优惠券ID删除所有绑定记录
     * @param $sid 专题ID
     * @return bool|mixed
     */
    public function delBySidAll($sid){
        if(empty($sid)) return false;
        $where_condition = 'subject_id ='.$sid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }

    /***
     * @purpose 根据专题父级id获取已绑定优惠券列表
     * @param $subject_id 专题id
     * @return bool|mixed
     */
    public function subjectGetBindCoupon($subject_id){
        $sql = "SELECT
                   coupon.*,coupon_rel.`order_num`
                FROM
                  `sj_template_subject_coupon` AS coupon
                  INNER JOIN `sj_template_subject_coupon_rel` AS coupon_rel
                    ON coupon.`id` = coupon_rel.`coupon_id`
                WHERE coupon_rel.`subject_id` = {$subject_id}
                ORDER BY coupon_rel.`order_num` DESC";

        $data = $this->query($sql,'All');
        return $data;
    }

    /***
     * @purpose 根据专题父级id获取已绑定有效优惠券列表
     * @param $subject_id 专题id
     * @return bool|mixed
     */
    public function getBindValidCouponList($subject_id){
        $sql = "SELECT
                    coupon.*
                FROM
                  `sj_template_subject_coupon` AS coupon
                  INNER JOIN `sj_template_subject_coupon_rel` AS coupon_rel
                    ON coupon.`id` = coupon_rel.`coupon_id`
                WHERE coupon_rel.`subject_id` = {$subject_id} AND coupon.`status` = 1";

        $data = $this->query($sql,'All');
        return $data;
    }

    /***
     * @purpose 根据专题父级id获取已绑定有效优惠券条数
     * @param $subject_id 专题id
     * @return bool|mixed
     */
    public function getBindValidCouponNum($subject_id){
        $sql = "SELECT
                   count(1) AS num
                FROM
                  `sj_template_subject_coupon` AS coupon
                  INNER JOIN `sj_template_subject_coupon_rel` AS coupon_rel
                    ON coupon.`id` = coupon_rel.`coupon_id`
                WHERE coupon_rel.`subject_id` = {$subject_id} AND coupon.`status` = 1";

        $data = $this->query($sql);
        return $data['num']?$data['num']:false;
    }
}