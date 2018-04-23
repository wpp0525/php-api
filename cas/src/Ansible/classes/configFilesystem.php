<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 * 特殊type_id
 * -10  为 key => array(key => val)
 * -11  为 key => array(val)
 * -1 为 key => val
 */
class configFilesystem
{

/**
 * 获取已经添加的配置类型
 * @return array 结果
 */

  private $Type_Object;

  public function getConfigtypeMaping(){
    $tplist = glob(dirname(__FILE__) . "/ConfigType/*Config.php");
    $list = array();
    foreach($tplist as $f){
      $classname = "\\Ansible\\classes\\ConfigType\\" . basename($f, ".php");
      $class = new $classname();
        $key = basename($f, "Config.php");
        $val = $class->getTypeName();
        if($val){
          $list[$key] = $val;
        }
    }
    return $list;
  }

  public function hasTypeObject($type){
    if(is_file(dirname(__FILE__) . '/ConfigType/' . $type . 'Config.php')){
      return TRUE;
    }
    return FALSE;
  }

  public function setTypeObject($type){
    if(is_file(dirname(__FILE__) . '/ConfigType/' . $type . 'Config.php')){
      $classname = "\\Ansible\\classes\\ConfigType\\" . $type . 'Config';
      $this->Type_Object =  new $classname();
      return TRUE;
    }
    $this->Type_Object = null;
    return FALSE;
  }

/**
 * 格式化数据读取的数据
 * @param  array  $tabledata 数据库列表数据
 * @return array            结果
 */
  public function getformatTableData($tabledata = array()){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->getformatTableData($tabledata);
  }

/**
 * 将数据库列表数据转换为数组
 * @param  array  $tabledata 数据库列表数据
 * @return array            结果
 */
  public function tableDataToConfigData($tabledata = array()){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->tableDataToConfigData($tabledata);
  }

/**
 * 设置输出预处理数组
 * @param array $data 预处理数组
 */
  public function setConfigData($data){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->setConfigData($data);
  }

//生成实体文件
  public function buileConfigFile($path, $mod){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->buileConfigFile($path, $mod);
  }

/**
 * 获取输出文件内容
 * @return string 文件内容
 */
  public function getConfigOutData(){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->getConfigOutData();
  }

  public function getConfigOutHtmlCode($data, $cfg_id = -1, $file_info){
    if(!$this->Type_Object){
      return '';
    }
    return $this->Type_Object->getConfigOutHtmlCode($data, $cfg_id, $file_info);
  }

  public function buildEditPopUpHtml($data, $options){
    if(!$this->Type_Object){
      return '';
    }
    return $this->Type_Object->buildEditPopUpHtml($data, $options);
  }

  public function buildAddPopUpHtml($data, $options){
    if(!$this->Type_Object){
      return '';
    }
    return $this->Type_Object->buildAddPopUpHtml($data, $options);
  }

  public function buildPriviewHtml($data, $cfg_id, $file_info){
    if(!$this->Type_Object){
      return '';
    }
    return $this->Type_Object->buildPriviewHtml($data, $cfg_id = -1, $file_info);
  }
}
