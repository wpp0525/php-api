<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 评论 服务类
 *
 * @author mac.zhao
 *        
 */
class CommentDataService extends DataServiceBase {
	
	const TABLE_NAME = 'mo_comment';//对应数据库表
	
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE segment_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getCount($where = ''){
		if($where){
			$result = $this->getAdapter()->query("select COUNT(comment_id) AS n FROM ".self::TABLE_NAME." WHERE {$where}");
		}else{
			$result = $this->getAdapter()->query("select COUNT(comment_id) AS n FROM ".self::TABLE_NAME);
		}
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$rs = $result->fetch();
		return isset($rs['n']) ? $rs['n'] : 0;
	}

	public function getLists($segment_ids = '',$uid = 0){
		if(!$segment_ids) return array();
		$result = $this->getAdapter()->query("SELECT * FROM ".self::TABLE_NAME." WHERE `uid`='{$uid}' AND `channel`='trip' AND `object_type`='pic' AND `object_id` IN ({$segment_ids}) AND valid='Y' GROUP BY object_id");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	
	/**
	 * 添加
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
	    }
	}
}