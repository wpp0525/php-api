<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolVstGoodsDataService extends DataServiceBase {

	const TABLE_NAME = 'pp_vst_goods';//对应数据库表

	const EXPIRE_TIME = 86400;
	
	public function get($product_id,$goods_id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE productId = ' . $product_id.' AND suppGoodsId = '.$goods_id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	public function save($data, $table_name = ''){
		$rs = $this->get($data['productId'],$data['suppGoodsId']);
		if(!$rs){
			return $this->insert($data);
		}
	}
	public function batchSave($data){
		try{
			$this->beginTransaction();
			foreach($data as $row){
				if(empty($row['productId'])) return false;
				$this->deleteFrom('productId = '.$row['productId'],self::TABLE_NAME);
				$param = array(
					':productId' => $row['productId'],
					':categoryId' => empty($row['categoryId']) ? 0 : $row['categoryId'],
					':managerId' => empty($row['managerId']) ? 0 : $row['managerId'],
					':filiale' => empty($row['filiale']) ? '' : $row['filiale'],
					':suppGoodsId' => empty($row['suppGoodsId']) ? 0: $row['suppGoodsId']
				);
				$this->execute(
					'INSERT INTO '.self::TABLE_NAME.'(`productId`,`categoryId`,`managerId`,`filiale`,`suppGoodsId`) VALUES(:productId,:categoryId,:managerId,:filiale,:suppGoodsId)',
					$param
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