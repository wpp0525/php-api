<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 专题产品管理 服务类
 *
 * @author xnw
 *
 */
class SeoSubjectBlockService extends DataServiceBase {

    const TABLE_NAME = 'seo_subject_block';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新
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
     * @param $id 编号
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * 删除父级
     * @param $parent_id 编号
     * @return bool|mixed
     */
    public function deleteParent($parent_id) {
        $whereCondition = 'parent_id = ' . $parent_id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getBlockList($where_condition, $limit = NULL,$order=null){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit , null, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getBlockOne($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取block总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getBlockTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
}