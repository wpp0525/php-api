<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 游记收益 服务类
 *
 * @author mac.zhao
 *        
 */
class TravelBonusDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_trip_bonus_count'; // 对应数据库表
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT amt FROM ' . self::TABLE_NAME . ' WHERE trip_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
}