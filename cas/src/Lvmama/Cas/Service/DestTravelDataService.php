<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\DataServiceBase;

/**
 * 目的地详细数据 服务类
 */
class DestTravelDataService extends DataServiceBase{

    const TRAVEL_TABLE='ly_travel';
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

    /**
     * 根据目的地ID获取目的地行程列表
     * @param $dest_id
     * @param null $page
     * @return bool|mixed
     */
    public function getListByDestId($dest_id,$page=null){
        if(!$dest_id) return false;
        $limit_str=$this->initPage($page);
        $sql="SELECT * FROM ".self::TRAVEL_TABLE." WHERE  dest_id=".$dest_id."  AND travel_days !=0 ORDER BY seq ASC".$limit_str;
        return $this->query($sql,'All');
    }


    public function getTListByDestId($dest_id,$page=null){
        if(!$dest_id) return false;
        $limit_str=$this->initPage($page);
        $sql="SELECT * FROM ".self::TRAVEL_TABLE." WHERE  dest_id=".$dest_id." AND status = 99 AND travel_days != 0 ORDER BY seq ASC".$limit_str;
        return $this->query($sql,'All');
    }






    public function getTravelViewTotalByTravelId($travel_id){
        if(!$travel_id) return false;
        $travel_days_id=$this->getTravelDaysIdByTravelId($travel_id);
        $total_result=array();
        if($travel_days_id){
            foreach($travel_days_id as $key=>$row){
                $result=$this->getViewIdsByDay($row['travel_day_id']);
                if($result){
                    $total_result[$key]=$result;
                }
            }
        }
        if(!$total_result) {
            return false;
        }else{
             foreach($total_result as $k=>$r){
                 $temp=array_column($r,'dest_id');
                 foreach($temp as $id){
                     $dest_ids[]=$id;
                 }
             }
            return array_unique($dest_ids);
        }
    }

    public function getTravelDaysIdByTravelId($travel_id){
        if(!$travel_id) return false;
        $sql="SELECT travel_day_id FROM ly_travel_day WHERE  `status`=99 AND travel_id=".$travel_id;
        return $this->query($sql,'All');
    }
    public function getViewIdsByDay($travel_day_id)
    {
        if (!$travel_day_id) return false;
        $sql = "SELECT dest_id FROM ly_travel_day_dest WHERE travel_day_id=" . $travel_day_id." ORDER BY seq ASC";
        return $this->query($sql, 'All');
    }
}