<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolVstDestDataService extends DataServiceBase {

	const TABLE_NAME = 'pp_vst_dest';//对应数据库表

	const EXPIRE_TIME = 86400;

	private $fields = array(
		'productId','dest_id'
	);

	public function get($product_id,$dest_id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE productId = ' . $product_id.' AND dest_id = '.$dest_id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}

	/**
	 * 保存产品和目的地关系
	 * @param $data
	 * @return bool
	 */
	public function save($data, $table_name = ''){
		foreach($data as $k=>$v){
			if(!in_array($k,$this->fields)){
				unset($data[$k]);
			}
		}
		return $this->insert($data);
	}
	//批量保存产品与目的地的关系
	public function batchSave($product_id,$dest_ids){
		if(empty($product_id)) return false;
		try{
			$this->beginTransaction();
			$this->deleteFrom('productId = '.$product_id,self::TABLE_NAME);
			foreach($dest_ids as $dest_id){
				$this->execute(
					'INSERT INTO '.self::TABLE_NAME.'(`productId`,`dest_id`) VALUES(:productId,:dest_id)',
					array(':productId' => $product_id,':dest_id' => $dest_id)
				);
			}
			$this->commit();
			return true;
		}catch (\PDOException $e){
			$this->rollBack();
			var_dump($e);
			return false;
		}
	}

    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }
}