<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolGoodsService extends DataServiceBase {

	const TABLE_NAME = 'pp_product_goods';//对应数据库表

	const EXPIRE_TIME = 86400;
	
	public function getByProductId($product_ids)
    {
	    $sql = 'SELECT PRODUCT_ID,CATEGORY_ID,SUB_CATEGORY_ID FROM ' . self::TABLE_NAME . ' WHERE PRODUCT_ID in (' . $product_ids .') and CANCEL_FLAG = "Y" ';
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

    public function getAllByGoodsId($goods_ids)
    {
        //$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE SUPP_GOODS_ID in (' . $goods_ids .') ';
        $sql =  'select goods.SUPP_GOODS_ID,goods.PRODUCT_ID,goods.GOODS_NAME,goods.CATEGORY_ID,goods_addition.LOWEST_MARKET_PRICE,goods_addition.LOWEST_SALED_PRICE,goods.CANCEL_FLAG from pp_product_goods as goods left join pp_product_goods_addition as goods_addition on (goods.SUPP_GOODS_ID = goods_addition.SUPP_GOODS_ID) where goods.SUPP_GOODS_ID in (' . $goods_ids .') ';
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
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