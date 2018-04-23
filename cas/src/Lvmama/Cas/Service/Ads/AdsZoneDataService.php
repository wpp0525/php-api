<?php

namespace Lvmama\Cas\Service\Ads;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * 广告系统：广告位服务类
 *
 * @author sx
 *        
 */
class AdsZoneDataService extends DataServiceBase {

	const TABLE_NAME = 'ad_zone';//对应数据库表
	const PRIMARY_KEY = 'id';

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
		return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
	}

	/**
	 * 根据条件查询记录列表
	 *
	 * @param array|string $condition
	 * @param null $limit example: array('page_num' => 1, 'page_size' => 10) or '1,10'
	 * @param null $columns
	 * @param null $order
	 * @return $this|bool
	 */
	public function getZoneList($condition, $limit = null, $columns = null, $order = null){
		return $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);
	}

	/**
	 * 根据条件查询单条记录
	 *
	 * @param array|string $condition
	 * @param null $columns
	 * @param int $show_property 是否显示关联的属性
	 * @return bool|mixed
	 */
	public function getZone($condition, $columns = null, $show_property = 0){
		$zone = $this->getOne($condition, self::TABLE_NAME, $columns);
		if($show_property && $zone){
			$property = $this->getZoneProperty($zone['id']);
			if(!$property){
				$property = array();
			}
			$zone['property'] = $property;
		}
		return $zone;
	}

	/**
	 * 查询广告位属性
	 *
	 * @param $id
	 * @return array|bool
	 */
	public function getZoneProperty($id){
		$condition = self::TABLE_NAME . '.id = ' . $id;
		$relation = array(
			self::TABLE_NAME, 'id',
			'ad_zone_property', 'zone_id', 'property_id',
			'ad_property', 'id'
		);
		return $this->getMany2Many($condition, $relation);
	}

	/**
	 * 查询广告位候选人列表
	 * @param $id
	 * @return array|bool
	 */
	public function getZoneCampaign($id){
		$condition = self::TABLE_NAME . '.id = ' . $id;
		$relation = array(
			self::TABLE_NAME, 'id',
			'ad_campaign', 'zone_id', 'banner_id',
			'ad_banner', 'id'
		);
		$columns = 'ad_campaign.*, ad_banner.name';
		return $this->getMany2Many($condition, $relation, $columns);
	}

	/**
	 * 根据条件查询记录总数
	 *
	 * @param $condition
	 * @return bool|mixed|null|string
	 */
	public function getTotal($condition){
		return $this->getTotalBy($condition, self::TABLE_NAME);
	}
}