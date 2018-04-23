<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 模板变量 服务类
 *
 * @author flash.guo
 *
 */
class SeoTemplateVariableDataService extends DataServiceBase {

    const TABLE_NAME = 'seo_template_variable';//对应数据库表
    const PRIMARY_KEY = 'variable_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加模板变量
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新模板变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除模板变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取模板变量
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getVarList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条模板变量
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneVar($where_condition,$columns = NULL){
        $data=$this->getOne($where_condition, self::TABLE_NAME,$columns);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条模板变量
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('variable_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据模板ID获取一条模板变量
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
     * @purpose 根据关键词ID删除一条模板变量
     * @param $tid 模板ID
     * @param $varname 变量名称
     * @return bool|mixed
     */
    public function delVarByTid($tid, $varname = ''){
        if(empty($tid)) return false;
        $where_condition = 'template_id ='.$tid;
        if(!empty($varname)) $where_condition .= " AND variable_name = '" . $varname . "'";
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }
}
