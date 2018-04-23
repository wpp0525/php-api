<?php
/**
* 优惠券
*/
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class TemplateSubjectCouponRecordsService extends DataServiceBase
{
	const TABLE_NAME = 'sj_template_subject_coupon_records';

	/**
     * 新增优惠券领取记录信息
     * @param $data 添加数据
     * @return bool|mixed
     */
	public function insertCouponRecords($data)
	{
		return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
	}

    /**
     * @purpose 根据条件获取优惠券信息
     * @param $condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getCouponReceiveList($condition, $limit = NULL, $columns = NULL, $order = NULL)
    {
        $data = array();
        $data = $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);

        return $data;
    }

    /**
     * @purpose 根据条件获取优惠券信息总数
     * @param $condition 查询条件
     * @return array|mixed
     */
    public function getCouponReceiveTotal($condition)
    {
        $data = array();
        $data=$this->getTotalBy($condition, self::TABLE_NAME);

        return $data;
    }

    /**
     * @purpose 根据用户ID和专题ID和优惠券批次号获取领取记录信息
     * @param $user_no 用户ID
     * @param $subject_id 专题ID
     * @param $coupon_numb 优惠券批次号
     * @return bool|mixed
     */
    public function getCouponRecords($user_no, $subject_id,$coupon_numb)
    {
        $condition = array('uid' => "='".$user_no."'", 'subject_id' => "=" . $subject_id ,'coupon_id'=> "=" . $coupon_numb);
        return $this->getTotalBy($condition, self::TABLE_NAME);
    }

    /**
     * @purpose 根据用户ID和专题ID获取领取记录信息
     * @param $user_no 用户ID
     * @param $subject_id 专题ID
     * @return bool|mixed
     */
    public function getCouponRecordsBat($user_no, $subject_id)
    {
        $condition = array('uid' => "='".$user_no."'", 'subject_id' => "=" . $subject_id);
        return $this->getTotalBy($condition, self::TABLE_NAME);
    }
}