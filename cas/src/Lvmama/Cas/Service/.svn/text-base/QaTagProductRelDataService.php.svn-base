<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 问答标签分类与产品对应表
 *
 * @author win.shenxiang
 *        
 */
class QaTagProductRelDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_tag_product_rel';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE tag_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	public function getTagByProductId($product_id){
		$sql = 'SELECT tag_id FROM ' . self::TABLE_NAME . ' WHERE product_id = :product_id';
		$dbh = $this->getAdapter();
		$sth = $dbh->prepare($sql);
		$sth->bindValue(':product_id', $product_id, \PDO::PARAM_STR);
		$sth->setFetchMode(\PDO::FETCH_ASSOC);
		$sth->execute();
		return $sth->fetchAll();
	}
	public function getProductIdByTagId($tag_id){
		$sql = 'SELECT product_id FROM ' . self::TABLE_NAME . ' WHERE tag_id = :tag_id';
		$dbh = $this->getAdapter();
		$sth = $dbh->prepare($sql);
		$sth->bindValue(':tag_id', $tag_id, \PDO::PARAM_STR);
		$sth->setFetchMode(\PDO::FETCH_ASSOC);
		$sth->execute();
		return $sth->fetchAll();
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
	    $whereCondition = 'tag_id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	        return $id;
	    }
	}
	public function deleteTag($data = array()){
		if(!$data) return false;
		$dbh = $this->getAdapter();
		$where = '';
		foreach($data as $k=>$v){
			$where .= $k.' = :'.$k;
		}
		$sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE '.$where;
		$sth = $dbh->prepare($sql);
		foreach($data as $k=>$v){
			$sth->bindValue(':'.$k, $v);
		}
		return $sth->exec();
	}
}