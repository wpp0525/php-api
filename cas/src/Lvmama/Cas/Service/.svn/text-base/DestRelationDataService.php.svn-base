<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地详细数据 服务类
 */
class DestRelationDataService extends DataServiceBase{
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



     public function delete($table,$where_condition){
        if($id=$this->getAdapter()->delete($table,$where_condition)){
        }
     }

    /**
     * @param $where_condition
     * @param $data
     * @param $talbe_name
     */
    public function update($where_condition, $data,$talbe_name) {
        if($id = $this->getAdapter()->update($talbe_name, array_keys($data), array_values($data), $where_condition) ) {
        }
    }
    public function getViewNumByBaseId($base_id){
        if(!$base_id) return false;
         $sql="SELECT COUNT(1)  as  total FROM  relation_dest_viewspot rdv INNER JOIN  dest_base db ON rdv.cid=db.base_id WHERE rdv.pid=".$base_id
               ." AND db.cancel_flag=1 AND db.showed=1";
         $result=$this->query($sql);
        return $result?$result['total']:false;
    }
    public function getImageNumByDestId($dest_id){
        if(!$dest_id) return false;
        $sql="SELECT COUNT(1) AS total FROM relation_dest_image WhERE dest_id=".$dest_id;
        $result=$this->query($sql);
        return $result?$result['total']:false;
    }

    public function getTripIdsByDestId($dest_id){
        $sql="SELECT trip_id FROM relation_dest_trip WHERE dest_id=".$dest_id;
        $result=$this->query($sql,'All');
        return $result;
	}
    public function getDestChildList($base_id,$page='',$dest_type,$recom_ids){
        if(!$base_id) return false;
        if($recom_ids){
            foreach($recom_ids as $key=>$row){
                $id_arr[]=$row['dest_id'];
            }
            $ids_str=implode(',',$id_arr);
            $not_in=" AND db.dest_id NOT IN({$ids_str})";
        }
        if($page){
            $page=$this->initPage($page);
        }
        $sql="SELECT  db.`base_id`,db.`dest_id`,db.`dest_type`,db.`parent_id`,db.`dest_name`,dvs.`intro`,dvs.`img_url`,dvs.`pinyin`,dvs.`count_been`,(CASE dvs.`img_url` WHEN '' THEN 0 ELSE 1 END) AS have_image
FROM relation_dest_".$dest_type." rdv INNER JOIN dest_base db
ON rdv.`cid`=db.`base_id` INNER JOIN  dest_".$dest_type." dvs
ON db.`base_id`=dvs.`base_id`
WHERE rdv.`pid`=".$base_id." AND db.`cancel_flag`=1 AND db.`showed`=1".$not_in."
ORDER BY have_image DESC ,dvs.`count_been` DESC ".$page;
        return $this->query($sql,'All');
    }

    public function getViewSpotByPid($parent_base_id){
        $sql="SELECT cid FROM relation_dest_viewspot WHERE pid=".$parent_base_id;
        return $this->query($sql,'All');

    }

    public function getDistrictByPid($pid,$dest_type){
        $sql="SELECT * FROM dest_base WHERE district_parent_id=".$pid." AND dest_type='".$dest_type."'";
        return $this->query($sql,'All');
    }

    public function getViewNumByDisId($dis_id){
        $sql="SELECT district_id,count(1) AS num FROM dest_base WHERE dest_type='VIEWSPOT' AND district_id IN(".$dis_id.") GROUP BY district_id ORDER BY num DESC";
        return $this->query($sql,'All');

    }
    public function getViewByDis($dis_id){
        $sql="SELECT dest_id FROM dest_base WHERE  district_id=".$dis_id;
        return $this->query($sql,'All');
    }
    public function getShopByPid($parent_base_id){
        $sql="SELECT cid FROM relation_dest_shop WHERE pid=".$parent_base_id;
        return $this->query($sql,'All');
    }
    public function getRestByPid($parent_base_id){
        $sql="SELECT cid FROM relation_dest_restaurant WHERE pid=".$parent_base_id;
        return $this->query($sql,'All');
    }

    /**
     * @param $base_ids string|array 以英文逗号分隔的base_id字符串 或者 一维数组
     * @param $dest_type string base_id对应的目的地类型
     * @return array 以base_id为key，祖先为value的数组
     */
    public function getDestParentsList($base_ids, $dest_type){
        if(is_array($base_ids)){
            $base_ids = implode(',', $base_ids);
        }
        $dest_type = strtolower($dest_type);

        $sql = "
        SELECT
            rdv.cid as child_base_id, db.*
        FROM
            `relation_dest_" . $dest_type . "` AS rdv
        JOIN `dest_base` as db ON rdv.pid = db.base_id
        WHERE
            rdv.cid in(" . $base_ids . ");
        ";

        $data = $this->query($sql, 'All');
        return $data;
    }

}