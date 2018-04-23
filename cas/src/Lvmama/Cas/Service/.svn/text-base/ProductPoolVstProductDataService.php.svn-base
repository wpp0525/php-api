<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolVstProductDataService extends DataServiceBase {

	const TABLE_NAME = 'pp_vst_product';//对应数据库表

	const EXPIRE_TIME = 86400;

	private $fields = array(
		'productId','productName','recommendLevel','saleFlag',
		'senisitiveFlag','source','updateTime','urlId','abandonFlag','auditStatus',
		'cancelFlag','categoryId','createTime','createUser','ebkSupplierGroupId',
		'muiltDpartureFlag','managerId','productType','filiale','packageType',
		'suppProductName','managerIdPerm','bu','districtId','travellerDelayFlag',
		'updateUser','syncDetailFlag','modelVersion'
	);
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE productId = ' . $id;
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
		foreach($data as $k=>$v){
			if(!in_array($k,$this->fields)){
				unset($data[$k]);
			}
		}
		if(empty($data['createTime'])){//未传该字段时
			$data['createTime'] = '';
		}else{//因Java的时间格式问题,特殊处理下
			$tmp = explode(' ',$data['createTime']);
			if(!empty($tmp[2])) unset($tmp[2]);
			$data['createTime'] = implode(' ',$tmp);
		}
		if(empty($data['updateTime'])){
			$data['updateTime'] = '';
		}else{//因Java的时间格式问题,特殊处理下
			$tmp = explode(' ',$data['updateTime']);
			if(!empty($tmp[2])) unset($tmp[2]);
			$data['updateTime'] = implode(' ',$tmp);
		}

		//兼容下面字段未传值的情况下,表中又未设置默认值的导致SQL报错
		if(empty($data['recommendLevel'])) $data['recommendLevel'] = 0;
		if(empty($data['categoryId'])) $data['categoryId'] = 0;
		if(empty($data['ebkSupplierGroupId'])) $data['ebkSupplierGroupId'] = 0;
		if(empty($data['packageType'])) $data['packageType'] = '';

		$rs = $this->get($data['productId']);
		if($rs){
			return $this->update($rs['id'],$data);
		}else{
			return $this->insert($data);
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