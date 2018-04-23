<?php 
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
* 产品与目的地关系
*/
class PpProductDestRelService extends DataServiceBase
{
	const TABLE_NAME = 'pp_product_dest_rel';//对应数据库表

	public function getProductDestRelsTotal($category_ids = '')
	{
		$condtion = '';
		if(!empty($category_ids)){
			$condtion = "where ppp.CATEGORY_ID in (" . $category_ids . ")";
		}
		$sql = "select count(1) as count from (select ppdr.PRODUCT_ID from ". self::TABLE_NAME ." ppdr inner join pp_product ppp on ppdr.PRODUCT_ID = ppp.PRODUCT_ID ". $condtion ." GROUP BY ppdr.PRODUCT_ID) temp;";
		$result = $this->query($sql);
		return !empty($result['count']) ? $result['count'] : 0;
	}

	public function getProductDestRels($cur_page = 0, $page_size = 1000, $category_ids = '')
	{
		$condtion = '';
		if(!empty($category_ids)){
			$condtion = "where ppp.CATEGORY_ID in (" . $category_ids . ")";
		}
		$sql = "select ppdr.RE_ID, ppdr.PRODUCT_ID, group_concat(ppdr.DEST_ID) as DEST_IDS, ppp.CATEGORY_ID, ppp.SUB_CATEGORY_ID from ". self::TABLE_NAME ." ppdr inner join pp_product ppp on ppdr.PRODUCT_ID = ppp.PRODUCT_ID ". $condtion ." GROUP BY ppdr.PRODUCT_ID ORDER BY ppdr.RE_ID ASC LIMIT " . $cur_page . ", " . $page_size;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		
		return $result->fetchAll();
	}

	public function getProductDestBYProdectId($product_id)
	{
		$sql = "select ppdr.RE_ID, ppdr.PRODUCT_ID, group_concat(ppdr.DEST_ID) as DEST_IDS, ppp.CATEGORY_ID from ". self::TABLE_NAME ." ppdr inner join pp_product ppp on ppdr.PRODUCT_ID = ppp.PRODUCT_ID WHERE ppdr.PRODUCT_ID = " . $product_id . " GROUP BY ppdr.PRODUCT_ID ORDER BY ppdr.RE_ID ASC;";
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		
		return $result->fetchAll();
	}
}