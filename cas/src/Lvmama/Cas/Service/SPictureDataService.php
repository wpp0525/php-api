<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 图片类
 *
 * @author win.shenxiang
 *        
 */
class SPictureDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_s_picture';//对应数据库表
	
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
	public function getLists($segment_ids = '',$page = 1,$pageSize = 15){
		if(!$segment_ids) return array();
		$result = $this->getAdapter()->query("SELECT COUNT(segment_id) AS n FROM ".self::TABLE_NAME." WHERE `segment_id` IN({$segment_ids})");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		$rs = $result->fetch();
		if(isset($rs['n']) && $rs['n'] > 0){
			$count = $rs['n'];
			$pages = ceil($count / $pageSize);
			$page = $page <= 0 ? 1 : $page;
			$page = $page > $pages ? $pages : $page;
			$result = $this->getAdapter()->query("SELECT segment_id,memo,img_url,original_time,camera,longitude,latitude FROM ".self::TABLE_NAME." WHERE `segment_id` IN({$segment_ids}) ORDER BY segment_id ASC LIMIT ".(($page - 1) * $pageSize).','.$pageSize);
			$result->setFetchMode(\PDO::FETCH_ASSOC);
			return array('list' => $result->fetchAll(),'pages' => array('itemCount' => $count,'pageCount' => $pages,'page' => $page,'pageSize' => $pageSize));
		}
		return array();
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