<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes\ConfigType;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
abstract class ConfigTypeFormat
{
  protected $type_name = null;

  protected $Config_OutData = '';

  protected $Config_Data;

  public function __construct(){
    $this->type_name = $this->setTypeName();
  }

/**
 * 必须实例化 返回字符串
 */
  abstract protected function setTypeName();

/**
 * 输出内容处理函数
 */
  abstract protected function replaceRedirectData();

  abstract public function setConfigData();

  public function getTypeName(){
    return $this->type_name;
  }

  public function replaceQuotation($string){
    $r = array('\\\'', '\\\"');
    $s = array("'", '"');
    return str_replace($r, $s, $string);
  }

  public function buileConfigFile($path, $mod = 644){
    $this->replaceRedirectData();
    $fs = new \Ansible\classes\Filesystem();
    $fs->dumpFile($path, $this->Config_OutData);
  }

  public function getConfigOutData(){
    $this->replaceRedirectData();
    return $this->Config_OutData;
  }

// ******************************* 处理ValType start *****************************************
/**
 * 根据值类型格式化
 * @param  string  $data 输入字符串
 * @param  int  $type 字符类型
 * @param  boolean $quo  是否输出带引号的的字符串
 * @return string        格式化结果
 */
  protected function dealValType($data, $type, $quo = true){
    $type = intval($type);
    if(method_exists($this, 'valTypeTranslate' . $type)){
      return call_user_func_array(array($this, 'valTypeTranslate' . $type), array($data, $quo));
    }
    if($quo)
      return "'" . $data . "'";
    return $data;
  }

  protected function valTypeTranslate1($data, $quo){
    if($quo)
      return "'" . $data . "'";
    return $data;
  }

  protected function valTypeTranslate2($data, $quo){
    $len = strlen($data);
    if($quo)
      return "'" . str_repeat("*", $len) . "'";
    return str_repeat("*", $len);
  }

  protected function valTypeTranslate3($data, $quo){
    preg_match_all("/^@@@(.*)@@@$/", $data, $newpath, PREG_SET_ORDER);
    if(isset($newpath['0']) && isset($newpath['0']['1']))
      return $newpath['0']['1'];
    return $data;
  }
// ******************************* 处理ValType end *****************************************
}
