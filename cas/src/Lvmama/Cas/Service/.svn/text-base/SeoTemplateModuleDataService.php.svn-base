<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 模板模块关联 服务类
 *
 * @author flash.guo
 *
 */
class SeoTemplateModuleDataService extends DataServiceBase {

    const TABLE_NAME = 'seo_template_module';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加模板模块关联
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新模板模块关联
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除模板模块关联
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取模板模块关联
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getModuleList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME." tm LEFT JOIN seo_module m ON m.module_id = tm.module_id", $limit, "tm.*,m.module_name,m.update_time AS module_update_time");
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条模板模块关联
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneModule($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条模板模块关联
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据模板ID获取一条模板模块关联
     * @param $tid 模板ID
     * @return bool|mixed
     */
    public function getOneByTid($tid){
        if(!$tid) return false;
        $where_condition=array('template_id'=>"=".$tid);
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据模板ID删除一条模板模块关联
     * @param $tid 模板ID
     * @param $varname 变量名称
     * @return bool|mixed
     */
    public function delModuleByTid($tid, $mid = 0){
        if(empty($tid)) return false;
        $where_condition = 'template_id ='.$tid;
        if(!empty($mid)) $where_condition .= " AND module_id = " . $mid;
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
}
