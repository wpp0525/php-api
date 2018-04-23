<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 游记 服务类
 *
 * @author win.shenxiang
 *        
 */
class TravelDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_travel';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	/**
	 * 获取行程列表
	 * 
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
		$sql = 'SELECT * FROM '.self::TABLE_NAME.' WHERE `status`=99 AND dest_id='.$dest_id.' LIMIT '.$start.','.$pages['pageSize'];
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	
	/**
	 * 取得指定目的地的行程的数量
	 */
	public function getCountByDestId($dest_id = 0) {
		if(!$dest_id) return 0;
	    $sql = 'SELECT COUNT(travel_id) AS n FROM ' . self::TABLE_NAME . ' WHERE dest_id = '.$dest_id;
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