<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 聚合词服务类
 *
 * @author libiying
 *        
 */
class CombinationDataService extends DataServiceBase {

	const TABLE_TOPIC = 'ly_combination_topic';
	const TABLE_MODULE_TOPIC = 'ly_combination_moduletopic';
	const TABLE_MODULE = 'ly_combination_module';

	/**
	 * 根据目的地id查询聚合词
	 * @param $dest_id
	 * @param null $order
	 * @param null $limit
	 * @return array|bool
	 */
	public function getTopicByDestId($dest_id, $order = null, $limit = null){
		$condition = array(
			self::TABLE_MODULE_TOPIC . '.module_destid = ' => $dest_id,
			self::TABLE_TOPIC . '.`show` = ' => "'Y'"
		);
		$relation = array(
			self::TABLE_TOPIC, 'id',
			self::TABLE_MODULE_TOPIC, 'topic_id', 'module_id',
			self::TABLE_MODULE, 'id'
		);
		return $this->getMany2Many($condition, $relation, self::TABLE_TOPIC . '.*', 'All', $order, $limit, self::TABLE_TOPIC . '.id');
	}

}