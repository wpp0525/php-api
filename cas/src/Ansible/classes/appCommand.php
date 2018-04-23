<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
class appCommand
{

  private $Type_Object = null;

  public function getCommandMaping(){
    $tplist = glob(dirname(__FILE__) . "/appCommands/*Command.php");
    $list = array();
    $params = array();
    foreach($tplist as $f){
      $classname = "\\Ansible\\classes\\appCommands\\" . basename($f, ".php");
      $class = new $classname();
        $key = basename($f, "Command.php");
        $val = $class->getTypeName();
        $val2 = $class->NeedParams();
        if($val){
          $list[$key] = $val;
        }
        if($val2){
          $params[$key] = $val2;
        }
    }
    return array(
      'list' => $list,
      'params' => $params
    );
  }

  public function hasCommandObject($type){
    if(is_file(dirname(__FILE__) . '/appCommands/' . $type . 'Command.php')){
      return TRUE;
    }
    return FALSE;
  }

  public function setCommandObject($type){
    if(is_file(dirname(__FILE__) . '/appCommands/' . $type . 'Command.php')){
      $classname = "\\Ansible\\classes\\appCommands\\" . $type . 'Command';
      $this->Type_Object =  new $classname();
      return TRUE;
    }
    $this->Type_Object = null;
    return FALSE;
  }

  public function commandList(){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->commandList();
  }

  public function NeedParams(){
    if(!$this->Type_Object){
      return array();
    }
    return $this->Type_Object->NeedParams();
  }

  public function runCommand($id, $ip, $chdir = '/tmp', $params = "", $chown = "root"){
    if(!$this->Type_Object){
      return FALSE;
    }
    return $this->Type_Object->runCommand($id, $ip, $chdir, $params, $chown);
  }


  public function getCommandString($id){
    if(!$this->Type_Object){
      return FALSE;
    }
    return $this->Type_Object->getCommandString($id);
  }
}
