<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\DataServiceBase;

/**
 * 目的地详细数据 服务类
 */
class DestTripRelDataService extends DataServiceBase{

    const TRAVEL_TABLE='dest_have_trips';
    /**
     * @purpose 插入数据
     * @param $data   数据
     * @param $table_name  详情表表名
     * @return array
     * @throws \Exception
     */
    public function insert($data,$table_name){
        $is_exist=$this->isTableExist($table_name);
        if($is_exist){
            if($id = $this->getAdapter()->insert($table_name, array_values($data), array_keys($data)) ){

            }
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }
}