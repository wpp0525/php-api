<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes\appCommands;

/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
class thriftCommand extends CommandFormat
{
  private $list = array(
    'status',
    'stop',
    'start -d',
    'restart -d',
  );

  public function getTypeName(){
    return 'thrift';
  }

  public function commandList(){
    return $this->list;
  }

  public function getCommandString($id){
    return isset($this->list[$id]) ? $this->list[$id] : '';
  }

  public function NeedParams(){
    return array(
      'command_chdir',
    );
  }

  public function runCommand($id, $ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $id = intval($id);
    if(method_exists($this, 'Command' . $id)){
      return call_user_func_array(array($this, 'Command' . $id), array($ip, $chdir, $params, $chown));
    }
    return FALSE;
  }

  public function Command0($ip, $chdir, $params, $chown){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->thriftCommand($ip, 'status', $chdir, $params, $chown);
  }

  public function Command1($ip, $chdir, $params, $chown){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->thriftCommand($ip, 'stop', $chdir, $params, $chown);
  }

  public function Command2($ip, $chdir, $params, $chown){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->thriftCommand($ip, 'start -d', $chdir, $params, $chown);
  }

  public function Command3($ip, $chdir, $params, $chown){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->thriftCommand($ip, 'restart -d', $chdir, $params, $chown);
  }
}
