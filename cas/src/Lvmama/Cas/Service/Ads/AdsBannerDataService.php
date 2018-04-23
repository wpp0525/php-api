<?php

namespace Lvmama\Cas\Service\Ads;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * 广告系统：广告服务类
 *
 * @author sx
 *        
 */
class AdsBannerDataService extends DataServiceBase {

	const TABLE_NAME = 'ad_banner';//对应数据库表
	const PRIMARY_KEY = 'id';

	const DETAIL_TABLE_NAME = 'ad_banner_detail';

	const TYPE_TXT = 'txt';
	const TYPE_IMG = 'img';

	/**
	 * 添加
	 *
	 */
	public function insert($data) {
		return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
	}

	/**
	 * 更新
	 *
	 */
	public function update($id, $data) {
		$whereCondition = self::PRIMARY_KEY . ' = ' . $id;
		$whereCondition = $this->softCondition($whereCondition);
		return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
	}

	/**
	 * 根据条件查询广告记录列表
	 *
	 * @param array|string $condition
	 * @param null $limit example: array('page_num' => 1, 'page_size' => 10) or '1,10'
	 * @param null $columns
	 * @param null $order
	 * @return $this|bool
	 */
	public function getBannerList($condition, $limit = null, $columns = null, $order = null){
		$condition = $this->softCondition($condition);
		return $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);
	}

	/**
	 * 根据条件查询单条广告记录
	 *
	 * @param array|string $condition
	 * @param null $columns
	 * @param boolean $show_detail
	 * @return bool|mixed
	 */
	public function getBanner($condition, $columns = null, $show_detail = true, $show_property = 0){
		$condition = $this->softCondition($condition);
		$banner = $this->getOne($condition, self::TABLE_NAME, $columns);
		if($show_detail && $banner){
			$single = in_array($banner['type'], array(self::TYPE_IMG, self::TYPE_TXT)) ? true : false;
			$detail = $this->getBannerDetail(array('banner_id = ' => $banner['id']), null, $single);
			if(!$detail){
				$detail = array();
			}
			$banner['detail'] = $detail;
		}
		if($show_property && $banner){
			$property = $this->getBannerProperty($banner['id']);
			if(!$property){
				$property = array();
			}
			$banner['property'] = $property;
		}
		return $banner;
	}

	/**
	 * 查询广告详情
	 *
	 * @param $condition
	 * @param null $columns
	 * @param bool $single
	 * @return $this|bool|mixed
	 */
	public function getBannerDetail($condition, $columns = null, $single = false){
		$condition = $this->softCondition($condition);
		if($single){
			return $this->getOne($condition, self::DETAIL_TABLE_NAME, $columns);
		}else{
			return $this->getList($condition, self::DETAIL_TABLE_NAME, null, $columns);
		}
	}

	/**
	 * 查询广告属性
	 *
	 * @param $id
	 * @return array|bool
	 */
	public function getBannerProperty($id){
		$condition = self::TABLE_NAME . '.id = ' . $id;
		$relation = array(
			self::TABLE_NAME, 'id',
			'ad_banner_property', 'banner_id', 'property_id',
			'ad_property', 'id'
		);
		$condition = $this->softCondition($condition, self::TABLE_NAME . '.is_del');
		return $this->getMany2Many($condition, $relation);
	}

	/**
	 * 通过属性查询关联的广告
	 *
	 * @param $property_id
	 * @return array|bool
	 */
	public function getBannerByProperty($property_id){
		$condition = 'ad_property.id = ' . $property_id;
		$relation = array(
			'ad_property', 'id',
			'ad_banner_property', 'property_id', 'banner_id',
			self::TABLE_NAME, 'id'
		);
		$condition = $this->softCondition($condition, self::TABLE_NAME . '.is_del');
		return $this->getMany2Many($condition, $relation);
	}

	/**
	 * 添加详情
	 *
	 */
	public function insertDetail($data) {
		return $this->getAdapter()->insert(self::DETAIL_TABLE_NAME, array_values($data), array_keys($data));
	}

	/**
	 * 更新详情
	 *
	 */
	public function updateDetail($id, $data) {
		$whereCondition = self::PRIMARY_KEY . ' = ' . $id;
		$whereCondition = $this->softCondition($whereCondition);
		return $this->getAdapter()->update(self::DETAIL_TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
	}

	/**
	 * 软删除
	 */
	public function deleteDetail($id){
		$whereCondition = self::PRIMARY_KEY . ' = ' . $id;

		$exist = $this->getBannerDetail($whereCondition, null, true);
		if(!$exist){
			return false;
		}
		$data = array(
			'is_del' => 1,
			'update_time' => time(),
		);
		return $this->getAdapter()->update(self::DETAIL_TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
	}

	/**
	 * 根据条件查询广告记录总数
	 *
	 * @param $condition
	 * @return bool|mixed|null|string
	 */
	public function getTotal($condition){
		$condition = $this->softCondition($condition);
		return $this->getTotalBy($condition, self::TABLE_NAME);
	}

	private function softCondition($condition, $full_name = 'is_del'){
		if(is_string($condition)){
			$condition .= " AND $full_name = 0 ";
		}else if(is_array($condition)){
			$condition["$full_name ="] = 0;
		}
		return $condition;
	}

}