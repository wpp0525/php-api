<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/11/22
 * Time: 10:45
 * 专题分站表
 */
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class SubWebSiteDataService extends DataServiceBase{

    const TABLE_NAME='sj_web_site';

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
            throw new \Exception(self::TABLE_NAME."表未定义");
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
    public function getOneById($subject_id){
        $where_condition=array('id'=>"=".$subject_id);
        $result=$this->getOne($where_condition,self::TABLE_NAME);
        return $result?$result:array();
    }
    public function getListByCondition ($where,$limit = null){
        $list=$this->getList($where,self::TABLE_NAME,$limit);
        $total=$this->getTotalBy($where,self::TABLE_NAME);
        if($total){
            return array('list'=>$list,'total'=>$total);
        }else{
            return array();
        }
    }
}
