<?php
/**
* 优惠券
*/
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class TemplateSubjectCouponService extends DataServiceBase
{
	const TABLE_NAME = 'sj_template_subject_coupon';

	/**
     * 新增优惠券信息
     * @param $data 添加数据
     * @return bool|mixed
     */
	public function insertCoupon($data)
	{
		return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
	}

	/**
     * 更新优惠券信息
     * @param $id 优惠券ID
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function updateCouponById($id, $data)
    {
        $condition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $condition);
    }

    /**
     * 删除优惠券信息
     * @param $id 优惠券ID
     * @return bool|mixed
     */
    public function deleteCouponById($id, $data)
    {
        $condition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $condition);
        // $condition = 'id = ' . $id;
        // return $this->getAdapter()->delete(self::TABLE_NAME, $condition);
    }

    /**
     * @purpose 根据主键获取一条优惠券信息
     * @param $id 优惠券ID
     * @return bool|mixed
     */
    public function getCouponById($id)
    {
    	$data = array();
        $condition = array('id' => "=" . $id);
        $data = $this->getOne($condition, self::TABLE_NAME);

        return $data;
    }

    /**
     * @purpose 根据条件获取优惠券信息
     * @param $condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getCouponList($condition, $limit = NULL, $columns = NULL, $order = NULL)
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
    public function getCouponTotal($condition)
    {
    	$data = array();
        $data=$this->getTotalBy($condition, self::TABLE_NAME);

        return $data;
    }

    /**
     * @purpose 根据coupon_numb获取一条优惠券信息
     * @param $coupon_numb 优惠券ID
     * @return bool|mixed
     */
    public function getCouponByNumb($coupon_numb)
    {
        $data = array();
        $condition = array('coupon_numb' => "=" . $coupon_numb);
        $data = $this->getOne($condition, self::TABLE_NAME);

        return $data;
    }
}