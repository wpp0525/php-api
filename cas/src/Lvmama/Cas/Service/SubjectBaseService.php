<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/11/22
 * Time: 10:45
 * 专题管理列表
 */
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class SubjectBaseService extends DataServiceBase{

    const TABLE_NAME='sj_subject_list';

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
                return $id;
            }
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }

    /**
     * 删除
     * @param $where 条件
     * @param $table_name 表名
     * @return bool|mixed
     */
    public function delete($where,$table_name) {
        $whereCondition = $where?$where:0;
        return $this->getAdapter()->delete($table_name, $whereCondition);
    }

    /**
     * 更新
     *
     */
    public function update($id, $data,$table_name) {
        $whereCondition = 'id = ' . $id;
        if($id = $this->getAdapter()->update($table_name, array_keys($data), array_values($data), $whereCondition) ) {
            return $id;
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
