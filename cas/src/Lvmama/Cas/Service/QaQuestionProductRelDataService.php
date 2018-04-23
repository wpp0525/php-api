<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 问答问题产品对应表
 *
 * @author win.shenxiang
 *        
 */
class QaQuestionProductRelDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_question_product_rel';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE question_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	public function getQuestionIdByProductId($product_id){
		$sql = 'SELECT question_id FROM ' . self::TABLE_NAME . ' WHERE product_id = :product_id';
		$dbh = $this->getAdapter();
		$sth = $dbh->prepare($sql);
		$sth->bindValue(':product_id', $product_id, \PDO::PARAM_STR);
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
	    $whereCondition = 'question_id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	        return $id;
	    }
	}


}