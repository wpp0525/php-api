<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 喜欢 服务类
 *
 * @author mac.zhao
 *        
 */
class LikeDataService extends DataServiceBase {
	
	const TABLE_NAME = 'mo_praise';//对应数据库表
	
	/**
	 * 添加
	 * 
	 * @return multitype:number multitype:\Phalcon\Db\Result\array
	 */
	public function insert($data) {
	    if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ) {
	        return $id;
	    }
	}
	
	/**
	 * 更新
	 * 
	 */
	public function update($id, $data) {
	    $whereCondition = 'trip_id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	        return $id;
	    }
	}
	
	/**
	 * 更新
	 * 
	 */
	public function updateByTripid($tripid, $uid, $data) {
	    $whereCondition = 'object_id = ' . $tripid . ' AND uid = '. $uid;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	        return $id;
	    }
	}
}