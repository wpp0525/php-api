<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/12/12
 * Time: 10:36
 **/
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon as UCommon;
class FencePoiDataService extends DataServiceBase{

    const TABLE_NAME='fence_poi';


    /**
     * @purpose 插入数据
     * @param $data   数据
     * @return array
     * @throws \Exception
     */
    public function insert($data){
        $is_exist=$this->isTableExist(self::TABLE_NAME);
        if($is_exist){
            if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){

            }
            $result = array('error'=>0, 'result'=>$id);
            return $result;
        }else{
            throw new \Exception(self::TABLE_NAME."表未定义");
        }
    }

    /**
     * 更新目的地数据
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function delete($id){
        $whereCondition= 'id = ' .$id;
        return $this->getAdapter()->delete(self::TABLE_NAME,$whereCondition);
    }

    /**
     * @param $where
     * @param null $limit
     * @return array
     */
    public function getListByCondition ($where,$limit = null){
        $list=$this->getList($where,self::TABLE_NAME,$limit);
        $total=$this->getTotalBy($where,self::TABLE_NAME);
        if($total){
            return array('list'=>$list,'total'=>$total);
        }else{
            return array();
        }
    }

    public function getFenceListByPosition($position=array(),$fence_size=10000){
        if(!$position['latitude'] || !$position['longitude']){
            return false;
        }
        $newlatlong=UCommon::getMaxMinLonLat($position['latitude'],$position['longitude'],$fence_size);
        $max_lat=$newlatlong['max_lati'];
        $min_lat=$newlatlong['min_lati'];
        $max_long=$newlatlong['max_long'];
        $min_long=$newlatlong['min_long'];
        $sql="SELECT * FROM  ".self::TABLE_NAME." WHERE longitude <".$max_long." AND longitude > ".$min_long ." AND latitude <".$max_lat." AND latitude>".$min_lat;
        return $this->query($sql,'All');
    }
    public function getFenceByName($name){
        $sql="SELECT fence_name,id,poi_id FROM ".self::TABLE_NAME." WHERE fence_name LIKE '%".$name."%'";
        return $this->query($sql,'All');
    }

    public function getFenceById($id){
        $sql="SELECT * FROM ".self::TABLE_NAME." WHERE id= ".$id;
        return $this->query($sql);
    }
}