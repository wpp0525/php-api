<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class ProductPoolDistrictProductDataService extends DataServiceBase {

	const TABLE_NAME = 'pp_district_product';//对应数据库表

	const EXPIRE_TIME = 86400;

	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	public function save($data, $table_name = ''){
		$row = $this->getRsBySql('SELECT id FROM '.self::TABLE_NAME.' WHERE keyword_id = '.$data['keyword_id'].' AND module_id = '.$data['module_id'].' AND district_id = '.$data['district_id'],true);
		if($row['id']){
			if(!isset($data['updatetime'])){
				$data['updatetime'] = time();
			}
			return $this->update($row['id'],$data);
		}else{
			if(!isset($data['createtime'])){
				$data['createtime'] = time();
			}
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