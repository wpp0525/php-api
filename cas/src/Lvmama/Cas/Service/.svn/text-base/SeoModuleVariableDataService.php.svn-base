<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 模块变量 服务类
 *
 * @author flash.guo
 *
 */
class SeoModuleVariableDataService extends DataServiceBase {

    const TABLE_NAME = 'seo_module_variable';//对应数据库表
    const PRIMARY_KEY = 'variable_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加模块变量
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新模块变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除模块变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取模块变量
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getVarList($where_condition, $limit = NULL,$columns=NULL,$order=NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit,$columns,$order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条模块变量
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneVar($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条模块变量
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('variable_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据模块ID获取一条模块变量
     * @param $mid 模块ID
     * @return bool|mixed
     */
    public function getOneByMid($mid){
        if(!$mid) return false;
        $where_condition=array('module_id'=>"=".$mid);
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }

    /**
     * @purpose 根据模块ID删除一条模块变量
     * @param $mid 模块ID
     * @param $varname 变量名称
     * @return bool|mixed
     */
    public function delVarByMid($mid, $varname = ''){
        if(empty($mid)) return false;
        $where_condition = 'module_id ='.$mid;
        if(!empty($varname)) $where_condition .= " AND variable_name = '" . $varname . "'";
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }

    /**
     * 批量获取模板参数
     * @param  array $mids 模板数值
     * @return array       查询结果
     */
    public function getModsVars($mids){
      $this->params = array();
      $sql = 'SELECT `variable_name`,`module_id` FROM `seo_module_variable` ' . $this->buildIntIn('module_id', $mids);
      $result = $this->getAdapter()->query($sql, $this->params);
      $out = array();
      while ($robot = $result->fetch()) {
        foreach($robot as $x => $x_val){
          if(is_numeric($x))
            unset($robot[$x]);
        }
        array_push($out, $robot);
      }
      return $out;
    }

//sheng成sql where语句
    public function  buildIntIn($key, $ids){
      $out = array();
      foreach($ids as $id){
          array_push($out, intval($id));
      }
      $out = array_unique($out);
      return " WHERE {$key} in(" . implode(",", $out) . ")";
    }
}
