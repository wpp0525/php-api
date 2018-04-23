<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/11/22
 * Time: 10:45
 * 专题分站关联关系
 */
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class SubSiteRelDataService extends DataServiceBase{

    const TABLE_NAME='sj_website_sub_rel';//网站分站关系表


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
    /**
     * 更新
     *
     */
    public function update($id, $data,$table_name) {
        $whereCondition = 'id = ' . $id;
        if($id = $this->getAdapter()->update($table_name, array_keys($data), array_values($data), $whereCondition) ) {
        }
    }

    /**
     * @param $sub_id
     * @return bool|mixed
     * 根据专题ID获取专题分站列表
     */
    public function getSiteListBySubId($sub_id){
        $sql="SELECT sbs.* FROM ".self::TABLE_NAME."  sb  INNER JOIN sj_subject_site sbs ON sb.`sub_site_id`=sbs.`id` WHERE sb.subject_id=".$sub_id." GROUP BY sb.sub_site_id ORDER BY sbs.id ASC";
        return $this->query($sql,'All');
    }

    public function getOneById($sub_id){
        $where_condition=array('id'=>"=".$sub_id);
        $result=$this->getOne($where_condition,self::TABLE_NAME);
        return $result?$result:array();
    }

    public function getOneBySite($sub_id,$site_id){
        $where_condition=array('subject_id'=>"=".$sub_id,'sub_site_id'=>"=".$site_id);
        $result=$this->getOne($where_condition,self::TABLE_NAME);
        return $result?$result:array();
    }

    public function otherSiteGetList($sub_id,$site_id){
        $where_condition=array('subject_id'=>"=".$sub_id,'sub_site_id'=>"!=".$site_id);
        $result=$this->getList($where_condition,self::TABLE_NAME);
        return $result?$result:array();
    }

    public function getWebSiteByIds($sub_id,$site_id){
        $sql="SELECT sws.* FROM  sj_website_sub_rel sb INNER JOIN sj_web_site sws  ON sb.`website_id`=sws.`id` WHERE sb.`sub_site_id`=".$site_id." AND sb.`subject_id`=".$sub_id;
        return $this->query($sql,'All');
    }
}
