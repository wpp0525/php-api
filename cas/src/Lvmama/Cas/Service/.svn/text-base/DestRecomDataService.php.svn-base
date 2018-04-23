<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地详细数据 服务类
 */
class DestRecomDataService extends DataServiceBase{


    const TABLE_NAME='ly_scenic_viewspot';
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
     * 获取后台二级导航栏目---推荐目的地下所有被推荐目的地的ID
     * @param $dest_id
     * @param null $table_name
     * @return bool|mixed
     */
      public function getRecomDest($dest_id,$recom_type,$dest_type='',$table_name=null){
          $table_name=$table_name?$table_name:self::TABLE_NAME;
          if($dest_type){
              $dest_type=" AND dest_type='".$dest_type."' ";
          }
          $sql="SELECT viewspot_id as dest_id,seq FROM ".$table_name." WHERE `status`='99' AND  recommend_type='".$recom_type."' ".$dest_type." AND dest_id= ".$dest_id.' ORDER BY seq ASC';
          return $this->query($sql,'All');
      }

}