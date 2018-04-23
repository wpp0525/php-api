<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 游记评论模板 服务类
 *
 * @author mac.zhao
 *        
 */
class TravelCommentTemplateDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_trip_comment_template'; // 对应数据库表
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT content FROM ' . self::TABLE_NAME . ' WHERE id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
}