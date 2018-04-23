<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolProductService extends DataServiceBase {

	const TABLE_NAME = 'pp_product';//对应数据库表

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

    public function getAllByProductId($product_ids)
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE PRODUCT_ID in (' . $product_ids .') ';
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * 添加产品信息
     * @param $product_ids
     * @return array
     */
    public function getAllWithAddtionalByProductId($product_ids)
    {
        $sql =  'select pp_product.PRODUCT_ID,pp_product.CATEGORY_ID,pp_product.PRODUCT_NAME,pp_product.SALE_FLAG,pp_product.SUB_CATEGORY_ID,pp_product_addtional.LOWEST_MARKET_PRICE,pp_product_addtional.LOWEST_SALED_PRICE from  pp_product left join pp_product_addtional on (pp_product.PRODUCT_ID = pp_product_addtional.PRODUCT_ID) where pp_product.PRODUCT_ID in (' . $product_ids .') ';
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * 产品信息，包含价格等
     * @param $where_condition
     * @param null $limit
     * @param string $columns
     * @param null $order
     * @return array
     */
    public function getProductAndAddtional($where_condition, $limit = NULL, $columns = "*", $order = NULL)
    {
        $sql = "select $columns from `pp_product` left join `pp_product_addtional` on `pp_product`.PRODUCT_ID = `pp_product_addtional`.PRODUCT_ID where $where_condition limit $limit";
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * 商品信息，包含价格等
     * @param $where_condition
     * @param null $limit
     * @param string $columns
     * @param null $order
     * @return array
     */
    public function getGoodsAndAddition($where_condition, $limit = NULL, $columns = "*", $order = NULL)
    {
        $sql = "select $columns from `pp_product_goods` left join `pp_product_goods_addition` on `pp_product_goods`.SUPP_GOODS_ID = `pp_product_goods_addition`.SUPP_GOODS_ID where `pp_product_goods`.SUPP_GOODS_ID > $where_condition limit $limit";
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * @purpose 产品查询
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getDefaultList($where_condition, $limit = NULL, $columns = "*", $order = NULL)
    {
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
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