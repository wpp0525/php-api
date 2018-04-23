<?php
/**
 * @author dirc.wang
 */

namespace Ansible\classes;

/**
 * Provides basic utility to Ansible the file system.
 *
 * @author dirc.wang
 */
class Ansiblesystem
{
  /**
   * 获取服务器硬盘信息
   * @param  ip $ip 目标服务器ip
   * @return int/bool     硬盘总容量单位k
   */
  public function getDfCount($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    return exec(dirname(__FILE__) . '/../Offiline/shells/getdfcount.sh ' . $ip );
  }

/**
 * 获取服务器硬盘已使用容量
 * @param  ip $ip 目标服务器ip
 * @return int/bool     硬盘已使用容量单位k
 */
  public function getDfused($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    return exec(dirname(__FILE__) . '/../Offiline/shells/getdfused.sh ' . $ip );
  }

/**
 * 获取缓存容量
 * @param  ip $ip 目标服务器ip
 * @return int/bool     缓存容量单位k
 */
  public function getFreetotal($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    return exec(dirname(__FILE__) . '/../Offiline/shells/getfreetotal.sh ' . $ip );
  }

/**
 * 获取缓存已使用容量
 * @param  ip $ip 目标服务器ip
 * @return int/bool     缓存已经使用容量单位k
 */
  public function getFreeused($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    return exec(dirname(__FILE__) . '/../Offiline/shells/getfreeused.sh ' . $ip );
  }


  /**
   * 获取CPU已使用容量
   * @param  ip $ip 目标服务器ip
   * @return int/bool     缓存已经使用容量单位k
   */
  public function getCpuinfo($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    return exec(dirname(__FILE__) . '/../Offiline/shells/getcpuinfo.sh ' . $ip );
  }

/**
 * 推送文件到目标服务器
 * @param  ip $ip 目标服务器ip
 * @return int/bool     缓存已经使用容量单位k
 */
  public function sendFile($ip, $src,  $dest, $mode = 644, $owner = 'nobody', $group = 'nobody'){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    if(!preg_match("/^0{0,1}[0-7]{1,3}$/i", $mode))
      return FALSE;
    if(!preg_match("/^[^\s]{0,150}$/i", $owner))
      return FALSE;
    if(!preg_match("/^[^\s]{0,150}$/i", $group))
      return FALSE;
    $src = $this->dealPath($src);
    $dest = $this->dealPath($dest);
    // print_r($ip . " " . $src . " ". $dest . " " . $mode . " " . $owner . " " . $group);
    // exit;
    return exec(dirname(__FILE__) . '/../Offiline/shells/ansiblesendfile.sh ' . $ip . " " . $src . " ". $dest . " " . $mode . " " . $owner . " " . $group);
  }

/**
 * 获取服务器负载
 * @param  ip $ip ip
 * @return floatval     负载值
 */
  public function getPressSum($ip){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    $cpusum = intval(exec(dirname(__FILE__) . '/../Offiline/shells/getcpuinfo.sh ' . $ip ));
    $press = floatval(exec(dirname(__FILE__) . '/../Offiline/shells/getPressSum.sh ' . $ip ));
    if($cpusum == 0 || $press == 0)
      return FALSE;
    return round($press/$cpusum, 2);
  }

/**
 * 获取真实地址
 * @param  string $path 路径
 * @return string       路径
 */
  public function dealPath($path){
    $path = strtr($path, "///", "/");
    return strtr($path, "//", "/");
  }

  public function thriftCommand($ip, $command, $chdir, $params, $chown = 'root'){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    $chdir = $this->dealPath($chdir);
    return $this->returnShellContent(dirname(__FILE__) . '/../Offiline/shells/command/thrift.sh ' . $ip . " '" . $command . "' " . $chdir . " '" . $params ."'");
  }

  public function svnCommand($ip, $command, $chdir, $params, $chown = 'root'){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    $chdir = $this->dealPath($chdir);
    return $this->returnShellContent(dirname(__FILE__) . '/../Offiline/shells/command/svn.sh ' . $ip . " '" . $command . "' " . $chdir . " '" . $params ."'");
  }

  public function phpdebugCommand($ip, $command, $chdir, $params = '', $chown = 'root'){
    if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i", $ip))
      return FALSE;
    $chdir = $this->dealPath($chdir);
    return $this->returnShellContent(dirname(__FILE__) . '/../Offiline/shells/command/phpdebug.sh ' . $ip . " '" . $command . "' " . $chdir . " '" . $params ."'");
  }

  public function returnShellContent($command){
    ob_start();
    passthru($command);
    $string = ob_get_contents();
    ob_end_clean();
    return $string;
  }
}
