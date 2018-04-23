<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 马甲用户 服务类
 *
 * @author mac.zhao
 *        
 */
class VestUserDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_xls_user'; // 对应数据库表
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT uid, username FROM ' . self::TABLE_NAME . ' WHERE id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
}