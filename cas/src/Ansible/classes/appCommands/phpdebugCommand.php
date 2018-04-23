<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes\appCommands;

use Ansible\classes\appCommands\CommandFormat;
/**
 * Provides basic utility to config build the file system.
 *
 * @author dirc.wang
 */
class phpdebugCommand extends CommandFormat
{
  private $list = array(
    'display_errors_On',
    'error_reporting_ALL',
    'close_error_reporting',
  );

  public function getTypeName(){
    return 'phpdebug';
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

  public function runCommand($id, $ip, $chdir = '/tmp', $params = "", $chown){
    $id = intval($id);
    if(method_exists($this, 'Command' . $id)){
      return call_user_func_array(array($this, 'Command' . $id), array($ip, $chdir, $params, $chown));
    }
    return FALSE;
  }

  public function Command0($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->phpdebugCommand($ip, 'showdebug', $chdir, $params, $chown = 'root');
  }

  public function Command1($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->phpdebugCommand($ip, 'logdebug', $chdir, $params, $chown = 'root');
  }

  public function Command2($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->phpdebugCommand($ip, 'closedebug', $chdir, $params, $chown = 'root');
  }

}
