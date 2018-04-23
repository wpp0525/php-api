<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolDestRelService extends DataServiceBase {

	const TABLE_NAME = 'pp_product_dest_rel';//对应数据库表

	const EXPIRE_TIME = 86400;
	
	public function getByDestId($dest_id,$limit)
    {
	    $sql = 'SELECT PRODUCT_ID FROM ' . self::TABLE_NAME . ' WHERE DEST_ID = "' . $dest_id.'" limit '. $limit;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	public function getRsBySql($sql,$one = false)
    {
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}

	public function save($data)
    {
		$rs = $this->get($data['productId'],$data['suppGoodsId']);
		if(!$rs){
			return $this->insert($data);
		}
	}

    public function insert($data)
    {
		return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    public function update($id, $data)
    {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }
}