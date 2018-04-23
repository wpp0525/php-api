<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 浏览记录 服务类
 *
 * @author mac.zhao
 *        
 */
class PageviewsDataService extends DataServiceBase {
	
	const TABLE_NAME = 'mo_pageviews';//对应数据库表
	
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
}