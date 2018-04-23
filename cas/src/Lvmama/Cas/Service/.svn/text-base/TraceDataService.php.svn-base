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
class TraceDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_trace';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE trace_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	private function getRsBySql($sql){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	public function getTripIdsByDestId($dest_id = 0){
		$sql = "SELECT trip_id FROM ".self::TABLE_NAME." WHERE deleted='N' AND dest_id = {$dest_id}";
		$tmp = $this->getRsBySql($sql);
		$data = array();
		foreach($tmp as $v){
			if(!in_array($v['trip_id'],$data)){
				$data[] = $v['trip_id'];
			}
		}
		return $data;
	}
	/**
	 * 获取
	 * 
	 */
	public function getTripsByInterval($startTime, $endTime) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE user_status = 1 AND modify_time >= ' . $startTime . ' AND modify_time <= ' . $endTime;
	    $result = $this->getAdapter()->query($sql);
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