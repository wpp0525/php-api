<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 阿里云机器服务
 *
 * @author win.shenxiang
 *        
 */
class AliyunDataService{
	
	const TABLE_NAME = 'productbase';//对应数据库表
	
	/**
	 * 添加
	 * 
	 */
	public function insert($data) {
	    if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){
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
}