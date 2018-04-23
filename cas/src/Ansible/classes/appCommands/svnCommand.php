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
class svnCommand extends CommandFormat
{

  private $list =  array(
        'up',
        'info',
        'revert'
      );

  public function getTypeName(){
    return 'svn';
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

  public function Command0($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->svnCommand($ip, 'up', $chdir, $params, $chown = 'root');
  }

  public function Command1($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->svnCommand($ip, 'info', $chdir, $params, $chown = 'root');
  }

  public function Command2($ip, $chdir = '/tmp', $params = "", $chown = 'root'){
    $Ansible = new \Ansible\classes\Ansiblesystem();
    return $Ansible->svnCommand($ip, 'revert -R ./*', $chdir, $params, $chown = 'root');
  }




}
