<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class HtlProductDestService extends DataServiceBase {
    const TABLE_NAME = 'hd_product_dest';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有

    public function insert($data) {
        $this->getAdapter()->forceMaster();
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    public function insertRelation($sql) {
        $sql = "insert into ".self::TABLE_NAME."(`DEST_TYPE`,`OBJECT_ID`,`OBJECT_NAME`,`DICT_ID`,`PRODUCT_ID`,`ADD_FLAG`,`DISTANCE`,`UPDATE_TIME`) values ".$sql;
        $this->getAdapter()->forceMaster();
        return $this->getAdapter()->query($sql);
    }

    public function getRsBySql($sql,$one = false){
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $one ? $result->fetch() : $result->fetchAll();
    }

    public function getProdCount($prod_id = 0){
        if($prod_id != 0)
            $sqlCount = "select count(*) as count from ".self::TABLE_NAME." where `product_id` = '".$prod_id."'";
        else
            $sqlCount = "select count(*) as count from ".self::TABLE_NAME."group by product_id";

            return $this->getAdapter()->fetchOne($sqlCount, \PDO::FETCH_ASSOC);
    }

    public function deleteProd($prod_id){
        return $this->getAdapter()->delete(self::TABLE_NAME, "product_id = ".$prod_id);
    }

    public function getMutilProductByDest($dest_ids_str)
    {
        $sql = "SELECT * FROM " . self::TABLE_NAME . " where dest_id in ($dest_ids_str)";
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetchAll();

    }


}