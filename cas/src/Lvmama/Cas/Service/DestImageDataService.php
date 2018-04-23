<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地详细数据 服务类
 */
class DestImageDataService extends DataServiceBase{

    const TABLE_NAME='ly_elite_image';
    /**
     * @purpose 插入数据
     * @param $data   数据
     * @param $table_name  详情表表名
     * @return array
     * @throws \Exception
     */
    public function insert($data,$table_name){
        $table_name=$this->dest_type?$this->dest_type:$table_name;
        $is_exist=$this->isTableExist($table_name);
        if($is_exist){
            if($id = $this->getAdapter()->insert($table_name, array_values($data), array_keys($data)) ){
            }
            $result = array('error'=>0, 'result'=>$id);
            return $result;
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }

    public function getImageNumByDestId($dest_id){
        if(!$dest_id) return false;
        $sql="SELECT COUNT(1)  as  total FROM ly_elite_image WHERE object_type='dest_id' AND object_id=".$dest_id;
        $result=$this->query($sql);
        return $result?$result['num']:false;
    }
}