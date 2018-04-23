<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * pk统计信息
 *
 * @author win.shenxiang
 *        
 */
class PkCountDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_pk_count';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	public function getHotList($dest_type = 'CITY', $pages = array()){
		if(!$pages){
			$pages = array('page' => 1,'pageSize' => 5);
		}
		$start_limit = ($pages['page'] - 1) * $pages['pageSize'];
		$sql = "SELECT * FROM ".self::TABLE_NAME." WHERE dest_type = '{$dest_type}' ORDER BY `total` DESC LIMIT {$start_limit},{$pages['pageSize']}";
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	/**
	 * 获取目的地被PK过的列表
	 */
	public function getLists($dest_id = 0,$pages = array()) {
		if(!$pages){
			$pages = array('page' => 1,'pageSize' => 15);
		}
		$count = $this->getCountByDestId($dest_id);
		$countPage = ceil($count / $pages['pageSize']);
		$pages['page'] = $pages['page'] < 1 ? 1 : $pages['page'];
		$pages['page'] = $pages['page'] > $countPage ? $countPage : $pages['page'];
		$start = ($pages['page'] - 1) * $pages['pageSize'];
		$sql = 'SELECT * FROM '.self::TABLE_NAME.' WHERE destA_id = '.$dest_id.' OR destB_id='.$dest_id.' ORDER BY `total` DESC LIMIT '.$start.','.$pages['pageSize'];
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	/**
	 * 取得指定目的地的被pk过的数量
	 */
	public function getCountByDestId($dest_id = 0) {
		if(!$dest_id) return 0;
		$sql = 'SELECT COUNT(destA_id) AS n FROM ' . self::TABLE_NAME . ' WHERE destA_id = '.$dest_id.' OR destB_id = '.$dest_id;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$rs = $result->fetch();
		return isset($rs['n']) ? $rs['n'] : 0;
	}
	/**
	 * 取得指定目的地作为A目的地被pk过的数量
	 */
	public function getCountByDestAId($dest_id = 0) {
		if(!$dest_id) return 0;
	    $sql = 'SELECT COUNT(destA_id) AS n FROM ' . self::TABLE_NAME . ' WHERE destA_id = '.$dest_id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		$rs = $result->fetch();
		return isset($rs['n']) ? $rs['n'] : 0;
	}
	/**
	 * 取得指定目的地作为B目的地被pk过的数量
	 */
	public function getCountByDestBId($dest_id = 0) {
		if(!$dest_id) return 0;
		$sql = 'SELECT COUNT(destA_id) AS n FROM ' . self::TABLE_NAME . ' WHERE destB_id = '.$dest_id;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$rs = $result->fetch();
		return isset($rs['n']) ? $rs['n'] : 0;
	}
	
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