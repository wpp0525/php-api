<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 *
 * @author win.shenxiang
 *        
 */
class MoCommentDataService extends DataServiceBase {
	
	const TABLE_NAME = 'mo_comment';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	
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

	public function getComment($segment_ids = array(),$uid = 0,$fields = array()){
		if(!$segment_ids) return array();
		$field = $fields ? '`'.implode('`,`',$fields).'`' : '*';
		$result = $this->getAdapter()->query("SELECT {$field} FROM ".self::TABLE_NAME." WHERE `uid`='{$uid}' AND `channel`='trip' AND `object_type`='pic' AND `object_id` IN (".implode(',',$segment_ids).") AND valid='Y' GROUP BY object_id");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	public function getCommentCount($object_id = 0){
		if(!$object_id) return 0;
		$sth = $this->getAdapter()->query("SELECT COUNT(comment_id) AS n FROM ".self::TABLE_NAME." WHERE `channel`='trip' AND `object_type`='pic' AND `object_id`='{$object_id}' AND valid='Y'");
		$sth->setFetchMode(\PDO::FETCH_ASSOC);
		$tmp = $sth->fetch();
		return isset($tmp['n']) ? $tmp['n'] : 0;
	}
	
	/**
	 * 添加
	 * 
	 */
	public function insert($data) {
	    if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){
// 	        $this->findOneBy(array('id'=>$id), self::TABLE_NAME, null, true);
// 	        return array('error'=>0, 'result'=>$id);
	    }
	    
		$result = array('error'=>0, 'result'=>$id);
		return $result;
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