<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 旅行 服务类
 *
 * @author win.shenxiang
 *        
 */
class SegmentDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_segment';//对应数据库表
	
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
	public function getRsBySql($where = array()){
		$result = $this->getAdapter()->query("select {$where['columns']} FROM ".self::TABLE_NAME." WHERE {$where['where']}");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	/**
	 * 取得图片墙ID
	 */
	public function getSegmentIds($trips = array()){
		if(!$trips) return array();
		$result = $this->getAdapter()->query("SELECT segment_id FROM ".self::TABLE_NAME." WHERE `type`='PICTURE' AND `verify`='99' AND `deleted`='N' AND `trip_id` IN(".implode(',',$trips).")");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$tmp = array();
		foreach($result->fetchAll() as $v){
			$tmp[] = $v['segment_id'];
		}
		return $tmp;
	}
	/**
	 * 取得指定条件的列表数据
	 */
	public function getSegmentList($trips = array()){
		if(!$trips) return array();
		$result = $this->getAdapter()->query("SELECT * FROM ".self::TABLE_NAME." WHERE `type`='PICTURE' AND `verify`='99' AND `deleted`='N' AND `trip_id` IN(".implode(',',$trips).")");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
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