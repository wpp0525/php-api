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
class AdsPropertyDataService extends DataServiceBase {

	const TABLE_NAME = 'ad_property';//对应数据库表
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
	 * 根据条件查询属性记录列表
	 *
	 * @param array|string $condition
	 * @param null $limit example: array('page_num' => 1, 'page_size' => 10) or '1,10'
	 * @param null $columns
	 * @param null $order
	 * @return $this|bool
	 */
	public function getPropertyList($condition, $limit = null, $columns = null, $order = null){
		return $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);
	}

	/**
	 * 根据条件查询单条记录
	 *
	 * @param array|string $condition
	 * @param null $columns
	 * @return bool|mixed
	 */
	public function getProperty($condition, $columns = null){
		return $this->getOne($condition, self::TABLE_NAME, $columns);
	}

	/**
	 * 创建关联属性
	 * @param $type
	 * @param $type_id
	 * @param $property_id
	 * @return bool|int
	 */
	public function buildPropertyRelation($type, $type_id, $property_id){

		$data = array(
			$type . '_id' => $type_id,
			'property_id' => $property_id,
		);
		$table = 'ad_' . $type . '_property';
		$condition = array(
			$type . '_id = ' => $type_id,
			'property_id = ' => $property_id,
		);
		$exist_relation = $this->getOne($condition, $table);
		if($exist_relation){//如果已经存在关联
			return false;
		}
		$exist_property = $this->getOne('id = ' . $property_id, self::TABLE_NAME);
		if(!$exist_property){//如果不存在该属性
			return false;
		}
		return $this->getAdapter()->insert($table, array_values($data), array_keys($data));
	}

	/**
	 * 删除关联属性
	 * @param $type
	 * @param $type_id
	 * @param $property_id
	 * @return bool|int
	 */
	public function deletePropertyRelation($type, $type_id, $property_id){
		$condition = array(
			$type . '_id = ' => $type_id,
			'property_id = ' => $property_id,
		);
		return $this->deleteFrom($condition, 'ad_' . $type . '_property');
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