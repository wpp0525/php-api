<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Phalcon\Mvc\Model\Manager as ModelsManager;
/**
 * 环境数据库处理
 *
 * @author dirc.wang
 *
 */
class SysDataService extends DataServiceBase {

  private $params = array();

/**
 * 获取线下服务器页面
 * @param  integer $cpage     当前页面数
 * @param  integer $inonepage 一个页面的条数
 * @return array            页面信息
 */
  public function getofflineServerPage($cpage = 1, $inonepage = 15){
    $offset = intval( $cpage -1 ) * $inonepage;
    $this->params = array();
    $sql = 'SELECT A.`id`, A.`ip`, A.`mem`, A.`mem_used`, A.`cpu`, A.`disk_used`, A.`disk`, A.`is_virtual`, A.`host_ip`, A.`remark`, A.`status`, group_concat( concat_ws( "/", D.`server_port`, F.`name` )) AS ports
    FROM `sys_server` AS A
    LEFT JOIN `sys_role_server_rel` AS B ON A.id = B.server_id
    LEFT JOIN `sys_role_port` AS C ON B.role_id = C.role_id
    LEFT JOIN `sys_app_port` AS D ON D.id = C.app_port_id
    LEFT JOIN `sys_role` AS F ON F.id = C.role_id
    GROUP BY A.`id` ORDER BY A.`ip` LIMIT ' . $inonepage . ' OFFSET ' .  $offset;
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
// ports转成数组，去重复
      if(isset($robot['ports'])){
        $rports = array_unique(explode(',', $robot['ports']));
        if(!is_array($rports))
          $rports = array();
        $ports = array();
//按照端口重建角色数组
        foreach($rports as $r_val){
          $exrp = explode('/', $r_val);
          $p = isset($exrp['0']) ? $exrp['0'] : '';
          $r = isset($exrp['1']) ? $exrp['1'] : '';
          if($p){
            if(!isset($ports[$p]))
              $ports[$p] = array();
            array_push($ports[$p], $r);
          }
        }
        foreach ($ports as $k=> $k_val) {
          $k_val = array_unique($k_val);
          $ports[$k] = implode(" , ", $k_val);
        }
        $robot['ports'] = $ports;
      }
      if(!$robot['ports'])
        $robot['ports'] = array();
      array_push($out, $robot);
    }
    return $out;
  }

/**
 * 获取线下服务器总数
 * @param  array  $data 获取条数条件
 * @return array       结果
 */
  public function getofflineServercount(array $data = array()){
    return $this->getCount($data, 'sys_server');
  }

/**
 * 插入服务器
 * @param  array $datain 添加的服务器信息
 * @return array         添加结果
 */
  public function insertofflineServer($datain){
    return $this->insert($datain, 'sys_server');
  }

/**
 * 线下服务器删除
 * @param  array $ids 删除的服务器id数组
 * @return array      结果
 */
  public function delofflineServer($ids){
    return $this->delbyids($ids, 'sys_server');
  }

/**
 * 线下服务器编辑
 * @param  array $data  新的服务器信息
 * @param  array $where 条件
 * @return [type]        [description]
 */
  public function editofflineServer($data, $where){
    return $this->update($data, $where, 'sys_server');
  }

  /**
   * 线下服务器信息
   * @param  array $where   查询条件
   * @param  array $dataout 输出字节
   * @return array          结果
   */
  public function infoofflineServer($where, $dataout){
    $list = $this->info($where, $dataout, 'sys_server');
    if($list && isset($list[0])){
      return $list[0];
    }
    return array();
  }

/**
 * 获取大于id的下一条数据
 * @param  int $id 当前id
 * @return array     服务器信息
 */
  public function getNextServerInfo($offest){
    $this->params = array();
    $sql = 'SELECT * from `sys_server` ORDER BY `id` LIMIT 1 OFFSET '. intval($offest);
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
      array_push($out, $robot);
    }
    if(count($out) && isset($out['0']))
      return $out['0'];
    return array();
  }

/**
 * 获取角色列表
 * @param  int $cpage     当前页
 * @param  int $inonepage 一页数量
 * @return array             结果
 */
  public function getofflineRolePage($cpage = 1, $inonepage = 15){
    $offset = intval( $cpage -1 ) * $inonepage;
    $this->params = array();
    $sql = 'SELECT A.`id`, A.`name`, A.`desc`, A.`type`, A.`remark`, group_concat(C.`ip`, " / ", B.`remark`) AS ips, group_concat(F.`name`, "/",E.`server_port`) AS ports
    FROM `sys_role` AS A
    LEFT JOIN `sys_role_server_rel` AS B ON A.`id` = B.`role_id`
    LEFT JOIN `sys_server` AS C ON C.`id` = B.`server_id`
    LEFT JOIN `sys_role_port` AS D ON D.`role_id` = A.`id`
    LEFT JOIN `sys_app_port` AS E ON D.`app_port_id` = E.`id`
    LEFT JOIN `sys_app` AS F ON E.`app_id` = F.`id`
    GROUP BY A.id ORDER BY A.`name` LIMIT ' . $inonepage . ' OFFSET ' .  $offset;
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
// ip转成数组，去重复
      if(isset($robot['ips'])){
        $rips = array();
        $ips = array_unique(explode(',', $robot['ips']));
//按照ip重建remark数组
        foreach ($ips as $k => $k_val) {
          $exrp = explode('/', $k_val);
          $p = isset($exrp['0']) ? $exrp['0'] : '';
          $r = isset($exrp['1']) ? $exrp['1'] : '';
          if($p){
            if(!isset($rips[$p]))
              $rips[$p] = array();
            if($r)
              array_push($rips[$p], $r);
          }
        }
        foreach ($rips as $r => $r_val) {
          $rips[$r] = trim(implode(",", $r_val));
        }
        $robot['ips'] = $rips;
      }
      if(!$robot['ips'])
        $robot['ips'] = array();
// ports转成数组，去重复
      if(isset($robot['ports'])){
        $robot['ports'] = array_unique(explode(',', $robot['ports']));
      }
      if(!$robot['ports'])
        $robot['ports'] = array();
      array_push($out, $robot);
    }
    return $out;
  }

  public function getofflineRolecount(array $data = array()){
    return $this->getCount($data, 'sys_role');
  }

  public function insertofflineRole($datain){
    return $this->insert($datain, 'sys_role');
  }

  public function delofflineRole($ids){
    return $this->delbyids($ids, 'sys_role');
  }

  public function editofflineRole($data, $where){
    return $this->update($data, $where, 'sys_role');
  }

  public function infoofflineRole($where, $dataout){
    $list = $this->info($where, $dataout, 'sys_role');
    if($list && isset($list[0])){
      return $list[0];
    }
    return array();
  }

/**
 * 添加用用
 * @param  array $datain 添加的内容
 * @return int|boolean  添加结果
 */
  public function insertofflineRoleApp($datain){
    return $this->insert($datain, 'sys_role_app');
  }

/**
 * 删除角色应用
 * @param  array $ids 要删除的应用id数组
 * @return boolean      删除结果
 */
  public function delofflineRoleAppbyIds($ids){
    return $this->delbyids($ids, 'sys_role_app');
  }

  public function delofflineRoleAppbyRoleid($data){
    if(!is_array($data) || !$data || !count($data))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_role_service" . $this->buildIntIn("role_id", $data);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

  public function editofflineRoleApp($data, $where){
    return $this->update($data, $where, 'sys_role_app');
  }

/**
 * 角色应用信息
 * @param  array $where 查询条件
 * @return array        查询结果
 */
  public function infoofflineRoleApp($where, $dataout = array()){
    $this->params = array();
    $this->params[':role_id'] = isset($where['role_id']) ? $where['role_id'] : '';
    $sql = 'SELECT A.`id`, A.`app_id`, B.`name`, B.`desc`, A.`remark` FROM `sys_role_app` AS A
            LEFT JOIN `sys_app` AS B ON B.`id` = A.`app_id`
            WHERE A.`role_id`=:role_id
            ORDER BY B.`name`';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

  public function insertServerRole($data){
    $list = $this->info($data, array('id'), 'sys_role_server_rel');
    if($list && isset($list[0])){
      return array(
        'code' => '4002',
        'msg' => '已经绑定过该ip',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_role_server_rel');
  }

/**
 * 编辑服务器
 * @param  array $data 更新数据
 * @param  array $data  条件
 * @return bool       更新数据
 */
  public function editServerRole($data, $where){
    return $this->update($data, $where, 'sys_role_server_rel');
  }

  public function delServerRole($ids){
    return $this->delbyids($ids, 'sys_role_server_rel');
  }

  public function delServerRolebyRoleid($data){
    if(!is_array($data) || !$data || !count($data))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_role_server_rel" . $this->buildIntIn("role_id", $data);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

  /**
   * 根据server_id删除绑定的角色
   * @param  array $data 服务器id组
   * @return bool       结果
   */
    public function delServerRolebyServerId($data){
      if(!is_array($data) || !$data || !count($data))
        return FALSE;
      $connection = $this->getAdapter();
      $this->params = array();
      $sql = "DELETE FROM sys_role_server_rel" . $this->buildIntIn("server_id", $data);
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          $connection->rollback();
          return FALSE;
      }
    }

/**
 * 获取角色已经绑定的服务器列表
 * @param  array $where   筛选条件
 * @param  array  $dataout 输出数据的字段
 * @return array          查询结果
 */
  public function getRoleServerList($where, $dataout = array()){
    $this->params = array();
    $this->params[':role_id'] = isset($where['role_id']) ? $where['role_id'] : '';
    $sql = "SELECT A.`id` AS id,B.`ip` AS ip,B.`id` as server_id,A.`remark` FROM `sys_role_server_rel` AS A LEFT JOIN `sys_server` AS B ON A.`server_id`=B.`id`  WHERE A.`role_id` = :role_id ";
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 获取应用列表
 * @param  integer $cpage     当前页面id
 * @param  integer $inonepage 一页显示条数
 * @return array            查询结果
 */
  public function getapplist($cpage = 1, $inonepage = 15){
    $offset = intval( $cpage -1 ) * $inonepage;
    $this->params = array();
    $sql = 'SELECT *
            FROM `sys_app`
            GROUP BY `id`  ORDER BY `name` LIMIT ' . $inonepage . ' OFFSET ' .  $offset;
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

/**
 * 获取应用信息
 * @param  array $where   条件
 * @param  array $dataout 输出字段
 * @return array          查询结果
 */
  public function getappinfo($where, $dataout){
    $list = $this->info($where, $dataout, 'sys_app');
    if($list && isset($list[0])){
      return $list[0];
    }
    return array();
  }

/**
 * 获取应用总数
 * @param  array $data   查询条件
 * @return int       查询结果
 */
  public function getappcount($data = array()){
    return $this->getCount($data, 'sys_app');
  }

/**
 * 添加应用
 * @param  array $data 插入的数据
 * @return array|initialize       错误信息或者插入的id
 */
  public function appInsert($data){
    $name = isset($data['name']) ? $data['name'] : '';
    $list = $this->info(array("name" => $name), array('id'), 'sys_app');
    if($list && isset($list[0])){
      return array(
        'error' => '4002',
        'error_description' => '已经存在该应用',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_app');
  }


/**
 * 通过id批量删除app
 * @param  array $ids 要删除的id
 * @return boolean     删除结果
 */
  public function appDelete($ids){
    return $this->delbyids($ids, 'sys_app');
  }

/**
 * 编辑应用
 * @param  array $data  修改后的数据
 * @param  array $where 修改条件
 * @return array|boolean         修改结果
 */
  public function appEdit($data, $where){
    $name = isset($data['name']) ? $data['name'] : '';
    $id = isset($where['id']) ? $where['id'] : '';
    $list = $this->info(array("id" => $id), array('id', 'name'), 'sys_app');
    if(!$list || !$list[0]){
      return array(
        'error' => '4002',
        'error_description' => '该应用不存在',
      );
    }
    $this->params = array(
      ':name' => $name,
      ':id' => $id,
    );
    $sql = 'SELECT `id` FROM `sys_app` WHERE `name` = :name  AND `id` != :id';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
      array_push($out, $robot);
    }
    if(count($out)){
      return array(
        'error' => '4003',
        'error_description' => '修改后的应用名已经存在',
      );
    }
    $data['update_time'] = time();
    return $this->update($data, $where, 'sys_app');
  }

/**
 * 获取应用端口列表
 * @param  int $app_id app_id
 * @return array         获取结果
 */
  public function getAppPort($app_id){
    return $this->info(array(
      'app_id' => intval($app_id),
    ), array(), 'sys_app_port');
  }

/**
 * 添加应用端口
 * @param  array $data 添加描述
 * @return array       添加结果
 */
  public function insertAppPort($data){
    $port = isset($data['server_port']) ? $data['server_port'] : '';
    $app_id = isset($data['app_id']) ? $data['app_id'] : '';
    if(!$port)
      return array(
        'error' => '4003',
        'error_description' => 'port参数不存在',
      );
    $same = $this->info(array(
       'app_id' => $app_id,
       'server_port' => $port,
     ), array(), 'sys_app_port');
     if($same && isset($same['0']))
       return array(
         'error' => '4006',
         'error_description' => '该port已经被绑定',
       );
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_app_port');
  }

/**
 * 更新appPortList
 * @param  array  $data  更新的数组
 * @param  array $where 更新的条件
 * @return boolean 更新的结果
 */
  public function updateAppPort($data, $where){
    return $this->update($data, $where, 'sys_app_port');
  }

/**
 * 通过端口数据口id删除绑定的端口
 * @param  array $ids ids
 * @return boolean      删除结果
 */
  public function deleteAppPortByids($ids){
    return $this->delbyids($ids, 'sys_app_port');
  }

/**
 * 通过appid删除应用
 * @param  array $app_ids app_id数组
 * @return boolean          删除结果
 */
  public function deleteAppPortByAppid($app_ids){
    if(!is_array($app_ids) || !$app_ids || !count($app_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_app_port" . $this->buildIntIn("app_id", $app_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * 角色配置文件列表
 * @param  integer $cpage     当前页面id
 * @param  integer   $inonepage 每一页显示数
 * @return array             查询结果
 */
public function rolecfglist($cpage = 1, $inonepage = 15 ,$where = array(), $dataout=array()){
  $offset = intval( $cpage -1 ) * $inonepage;
  $this->params = array();
  $this->params[':role_id'] = isset($where['role_id']) ? $where['role_id'] : '';
  $sql = 'SELECT A.`id`, A.`app_id`, B.`name` AS `app_name`,A.`cfg_type`, A.`name`, A.`role_id`, A.`file_name`, A.`file_path`, A.`file_owner`, A.`file_group`, A.`file_chmod`, A.`remark`
  FROM sys_cfg AS A
  LEFT JOIN sys_app AS B ON A.`app_id` = B.`id`
  WHERE A.`role_id` = :role_id
  ORDER BY A.`app_id` LIMIT ' . $inonepage . ' OFFSET ' .  $offset;
  $result = $this->getAdapter()->query($sql, $this->params);
  $out = array();
  while ($robot = $result->fetch()) {
    foreach($robot as $x => $x_val){
      if(is_numeric($x))
        unset($robot[$x]);
    }
     if($dataout){
       $sub = array();
       foreach($dataout as $x){
         $sub[$x] = $robot[$x];
       }
       array_push($out, $sub);
     }else{
       array_push($out, $robot);
     }
  }
  return $out;
}


/**
 * 角色可添加的配置文件
 * @param  int $role_id 角色id
 * @return array          查询结果
 */
  public function roleAppCfgUsableList($role_id, $dataout = array()){
    $this->params = array();
    $this->params[':role_id'] = intval($role_id);
    $sql = 'SELECT A.`role_id`, B.`name` AS app_name, C.*
            FROM `sys_role_app` AS A
            LEFT JOIN `sys_app` AS B ON A.`app_id` = B.`id`
            LEFT JOIN `sys_app_cfg` AS C ON C.`app_id` = A.`app_id`
            WHERE A.`role_id` = :role_id AND C.`id` IS NOT NULL ORDER BY A.`role_id`';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 获取角色配置文件总数
 * @param  int $role_id 角色id
 * @return int          数量
 */
public function getRoleCfgCount($role_id){
  return $this->getCount(array(
      'role_id' => intval($role_id),
    ), 'sys_cfg');
}

/**
 * 获取所有配置文件列表
 * @param int  $cfg_id 配置文件id
 * @return array        配置文件列表
 */
public function getCfgParams($cfg_id){
  return $this->info(array(
    'cfg_id' => intval($cfg_id),
  ), array(), 'sys_cfg_params');
}

/**
 * 获取版本配置文件详情
 * @param  int  $cfg_id  配置文件详情
 * @param  integer $version 版本号
 * @return int           查询结果
 */
public function getCfgVresionParams($cfg_id, $version = 1, $dataout = array()){
  $this->params = array();
  $this->params[':cfg_id'] = $cfg_id;
  $this->params[':version'] = $version;
  $sql = 'SELECT `param_id` AS id, `cfg_id` ,`cfg_key`, `cfg_val_type`,`type_key_id`, `cfg_val`, `type_val_id`, `type_id`, `params_parent_id`, `cfg_version`
          FROM `sys_cfg_version`
          WHERE `cfg_id` = :cfg_id AND cfg_version = :version';
  $result = $this->getAdapter()->query($sql, $this->params);
  $out = array();
  while ($robot = $result->fetch()) {
    foreach($robot as $x => $x_val){
      if(is_numeric($x))
        unset($robot[$x]);
    }
     if($dataout){
       $sub = array();
       foreach($dataout as $x){
         $sub[$x] = $robot[$x];
       }
       array_push($out, $sub);
     }else{
       array_push($out, $robot);
     }
  }
  return $out;
}

/**
 * 获取配置文件信息
 * @param  array $cfg_id 配置文件id
 * @return array         获取结果
 */
public function getCfgFileInfo($cfg_id){
  $info = $this->info(array(
    'id' => intval($cfg_id),
  ), array(), 'sys_cfg');
  if($info && isset($info['0']))
    return $info['0'];
  return array();
}


/**
 * 删除配置文件信息
 * @param  array $cfg_id 配置文件id
 * @return array         获取结果
 */
public function deleteCfgFile($ids){
  return $this->delbyids($ids, 'sys_cfg');
}
/**
 * 获取应用配置文件列表
 * @param  int $app_id 应用id
 * @return array         应用列表
 */
  public function getAppCfgList($app_id){
    return $this->info(array(
      'app_id' => intval($app_id),
    ), array(), 'sys_app_cfg');
  }

  /**
   * 通过端口数据口id删除配置文件
   * @param  array $ids ids
   * @return boolean      删除结果
   */
    public function deleteAppCfgByids($ids){
      return $this->delbyids($ids, 'sys_app_cfg');
    }

  /**
   * 通过appid删除应用配置文件
   * @param  array $app_ids app_id数组
   * @return boolean          删除结果
   */
    public function deleteAppCfgByAppid($app_ids){
      if(!is_array($app_ids) || !$app_ids || !count($app_ids))
        return FALSE;
      $connection = $this->getAdapter();
      $this->params = array();
      $sql = "DELETE FROM sys_app_cfg" . $this->buildIntIn("app_id", $app_ids);
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          $connection->rollback();
          return FALSE;
      }
    }

/**
 * 应用配置文件添加
 * @param array $data 配置文件信息
 */
  public function addAppCfg($data){
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_app_cfg');
  }

/**
 * 更新数据库
 * @param  array $data  更新内容
 * @param  array $where 更新条件
 * @return boolean        更新结果
 */
  public function updateAppCfg($data, $where){
    return $this->update($data, $where, 'sys_app_cfg');
  }

/**
 * 角色端口列表
 * @return array 查询结果
 */
  public function getroleAppPortlist($where, $dataout = array()){
    $this->params = array();
    $this->params[':role_id'] = isset($where['role_id']) ? $where['role_id'] : '';
    $sql = 'SELECT A.`id`, A.`role_id`, C.`name`, A.`remark`, B.`app_id`, B.`server_port`, A.`app_port_id`
    FROM  `sys_role_port` AS A
    LEFT JOIN `sys_app_port` AS B ON B.`id` = A.`app_port_id`
    LEFT JOIN `sys_app` AS C ON B.app_id = C.`id`
    WHERE A.`role_id` = :role_id
    ORDER BY C.`name`';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 角色端口绑定
 * @param  array $data 添加的数据
 * @return array       添加u结果哦
 */
  public function roleAppPortInsert($data){
    $where = array(
      'id' => isset($data['app_port_id']) ? $data['app_port_id'] : '',
    );
    $list = $this->info($where, array(), 'sys_app_port');
    if(!is_array($list) || !isset($list['0']) || !isset($list['0']['server_port'])){
      return array(
        'error' => '4004',
        'error_description' => '绑定的端口不存在',
      );
    }
    $role_id = isset($data['role_id']) ? $data['role_id'] : '';
    $info = $this->roleAppPortCheck($list['0']['server_port'], $role_id);
    if($info){
      $bindname = isset($info['0']['name']) ? $info['0']['name'] : '';
      $port =  isset($info['0']['server_port']) ? $info['0']['server_port'] : '';
      return array(
        'error' => '4005',
        'error_description' => '端口' . $port . '已经被' . $bindname . '绑定',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_role_port');
  }

/**
 * 检测角色是否已经绑定该端口
 * @param  int $port    端口号
 * @param  int $role_id 角色id
 * @return array          添加结果
 */
  public function roleAppPortCheck($port, $role_id, $dataout = array()){
    $this->params = array();
    $this->params[':role_id'] = $role_id;
    $this->params[':port'] = $port;
    $sql = 'SELECT A.`id`, A.`role_id`, C.`name`, A.`remark`, B.`app_id`, B.`server_port`
    FROM  `sys_role_port` AS A
    LEFT JOIN `sys_app_port` AS B ON B.`id` = A.`app_port_id`
    LEFT JOIN `sys_app` AS C ON B.app_id = C.`id`
    WHERE A.`role_id` = :role_id AND B.`server_port` = :port
    ORDER BY C.`name`';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 删除应用接口
 * @param  array $ids 要删除的接口关系id数组
 * @return boolean      删除结果
 */
  public function roleAppPortdeleteByIds($ids){
    return $this->delbyids($ids, 'sys_role_port');
  }

  /**
   * 删除应用接口
   * @param  array $role_ids 要删除的接口关系role_id数组
   * @return boolean      删除结果
   */
  public function roleAppPortdeleteByRoleids($role_ids){
    if(!is_array($role_ids) || !$role_ids || !count($role_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_role_port" . $this->buildIntIn("role_id", $role_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * 角色应用可绑定端口列表
 * @param  array $app_ids 应用id数组
 * @return array          查询结果
 */
  public function roleAppPortBindlist($app_ids, $dataout = array()){
    if(!is_array($app_ids) || !$app_ids || !count($app_ids))
      return array();
    $this->params = array();
    $sql = 'SELECT A.`id`, A.`app_id`, A.`server_port`, A.`remark`, B.`name` FROM `sys_app_port` AS A
    LEFT JOIN `sys_app` AS B ON A.`app_id` = B.`id`' . $this->buildIntIn("app_id", $app_ids);
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 角色应用端口更新
 * @param  array $data  更新内容
 * @param  array $where 更新条件
 * @return array        更新结果
 */
  public function roleAppPortUpdate($data, $where){
    $data['update_time'] = time();
    return $this->update($data, $where, 'sys_role_port');
  }

/**
 * 获取配置文件类型列表
 * @return array 结果
 */
  public function getcfgTypeList(){
    return $this->info(array(), array(), 'sys_type');
  }

/**
 * 添加配置参数
 * @param  array $data 添加的参数类型
 * @return boolean|int       添加结果
 */
  public function addcfgType($data){
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_type');
  }

/**
 * 添加配置参数key
 * @param  array $data 添加的参数key
 * @return array       添加结果
 */
  public function addcfgTypeKey($data){
    if(!isset($data['type_id'])){
      return array(
        'error' => '4003',
        'error_description' => 'type_id 不存在',
      );
    }
    $result = $this->info(array(
      'id' => $data['type_id'],
    ), array(), 'sys_type');
    if(!$result || !isset($result['0'])){
      return array(
        'error' => '4004',
        'error_description' => 'type_id 不存在',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_type_keys');
  }

  /**
   * 添加配置参数keyval
   * @param  array $data 添加的参数keyval
   * @return array       添加结果
   */
  public function addcfgTypeKeyVal($data){
    if(!isset($data['type_id']) ||  !isset($data['type_key_id'])){
      return array(
        'error' => '4003',
        'error_description' => 'type_id 或 type_key_id不存在',
      );
    }
    $result = $this->info(array(
      'id' => $data['type_id'],
    ), array(), 'sys_type');
    if(!$result || !isset($result['0'])){
      return array(
        'error' => '4004',
        'error_description' => 'type_id 不存在',
      );
    }
    $result = $this->info(array(
      'id' => $data['type_key_id'],
    ), array(), 'sys_type_keys');
    if(!$result || !isset($result['0'])){
      return array(
        'error' => '4004',
        'error_description' => 'type_key_id 不存在',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_type_key_val');
  }

// 更新type
  public function updatecfgType($data, $where){
    $data['update_time'] = time();
    return $this->update($data, $where, 'sys_type');
  }

// 更新typekey
  public function updatecfgTypeKey($data, $where){
    $data['update_time'] = time();
    return $this->update($data, $where, 'sys_type_keys');
  }

// 更新typekeyval
  public function updatecfgTypeKeyVal($data, $where){
    $data['update_time'] = time();
    return $this->update($data, $where, 'sys_type_key_val');
  }

//通过type_id删除type
  public function deletecfgTypeByIds($ids){
    $this->deletecfgTypeKeyValByTypeIds($ids);
    $this->deletecfgTypeKeyByTypeIds($ids);
    return $this->delbyids($ids, 'sys_type');
  }

//通过type_id删除type key
  public function deletecfgTypeKeyByTypeIds($type_ids){
    if(!is_array($type_ids) || !$type_ids || !count($type_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_type_keys" . $this->buildIntIn("type_id", $type_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

//通过type_id删除type keys
  public function deletecfgTypeKeyByKeyIds($ids){
    $this->deletecfgTypeKeyValByKeyIds($ids);
    return $this->delbyids($ids, 'sys_type_keys');
  }

//通过type_id删除KeyVal
  public function deletecfgTypeKeyValByTypeIds($type_ids){
    if(!is_array($type_ids) || !$type_ids || !count($type_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_type_key_val" . $this->buildIntIn("type_id", $type_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

//通过type_id删除KeyVal
  public function deletecfgTypeKeyValByKeyIds($type_key_ids){
    if(!is_array($type_key_ids) || !$type_key_ids || !count($type_key_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_type_key_val" . $this->buildIntIn("type_key_id", $type_key_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

//通过id删除KeyVal
  public function deletecfgTypeKeyValByIds($ids){
    return $this->delbyids($ids, 'sys_type_key_val');
  }

/**
 * 获取配置文件类型所属kyes列表
 * @param  int $type_id 类型id
 * @return array          结果
 */
  public function getCfgTypeKeys($type_id){
    return $this->info(array(
      'type_id' => $type_id,
    ), array(), 'sys_type_keys');
  }

/**
 * 获取key关联的vals
 * @param  int $type_key_id keyid
 * @return array             结果
 */
  public function getCfgTypeKeyVals($type_key_id){
    return $this->info(array(
      'type_key_id' => $type_key_id,
    ), array(), 'sys_type_key_val');
  }

/**
 * 递归出所有子类
 * @param  int $cfg_param_id 配置参数id
 * @param  int $cfg_id       配置文件id
 * @return array               所有子类id
 */
  public function cfgFindNodeDepth($cfg_param_id, $cfg_id){
    $ids = array();
    $this->cfgFindNodeDepthtwo($cfg_param_id, $cfg_id, $ids);
    return $ids;
  }

  public function cfgFindNodeDepthtwo($cfg_param_id, $cfg_id, &$ids){
    $info = $this->info(array(
      'params_parent_id' => $cfg_param_id,
      'cfg_id' => $cfg_id
    ), array(), 'sys_cfg_params');
    if($info && isset($info['0'])){
      foreach ($info as $x) {
        if(isset($x['id'])){
          array_push($ids, $x['id']);
          $this->cfgFindNodeDepthtwo($x['id'], $cfg_id, $ids);
        }
      }
    }
  }

/**
 * 通过id批量删除配置参数
 * @param  array $ids 要删除的ids
 * @return boolean      要删除的id
 */
  public function cfgParamsdeleteByIds($ids){
    return $this->delbyids($ids, 'sys_cfg_params');
  }

/**
 * 更新配置文件参数节点
 * @param  array $data  更新的数据
 * @param  array $where 要跟新的节点信息
 * @return boolean   更新结果
 */
  public function updateCfgNode($update, $where){
    $update['update_time'] = time();
    return $this->update($update, $where, 'sys_cfg_params');
  }

/**
 * 添加角色配置文件
 * @param array $data 添加列表
 */
  public function addRoleCfgFile($data){
    $file_name = isset($data['file_name']) ? $data['file_name'] : '';
    $file_path = isset($data['file_path']) ? $data['file_path'] : '';
    $role_id = isset($data['role_id']) ? $data['role_id'] : '';
    $info = $this->info(array(
      'file_name' => $file_name,
      'file_path' => $file_path,
      'role_id' => $role_id,
    ), array(), 'sys_cfg');
    if($info && isset($info['0'])){
      return array(
        'error' => '4004',
        'error_description' => $file_path . $file_name .'已被添加',
      );
    }
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_cfg');
  }

/**
 * 更新角色配置文件
 * @param  array $data 修改的内容
 * @return boolean       修改结果
 */
  public function updateRoleCfgFile($data){
    $update = array();
    if(isset($data['name']))
      $update['name'] = $data['name'];
    if(isset($data['remark']))
      $update['remark'] = $data['remark'];
    if(isset($data['file_chmod']))
      $update['file_chmod'] = $data['file_chmod'];
    if(isset($data['file_owner']))
      $update['file_owner'] = $data['file_owner'];
    if(isset($data['file_group']))
      $update['file_group'] = $data['file_group'];
    $update['update_time'] = time();
    $where = array(
      'id' => isset($data['id']) ? $data['id'] : '',
    );
    return $this->update($update, $where, 'sys_cfg');
  }

/**
 * 更新角色配置文件状态为已经修改
 * @param  int $id 配置文件id
 * @return boolean     跟新结果
 */
  public function updateRoleCfgFileStatus($id){
    return $this->update(array(
      'change_status' => '2',
    ),
    array(
      'id' => $id,
    ), 'sys_cfg');
  }

/**
 * 更新文件版本
 * @param  array $id cfg_id
 * @return boolean 更新结果
 */
  public function updateRoleCfgFileVersion($id){
    // 如果没有更改项将不改变
      $connection = $this->getAdapter();
      $this->params = array();
      $this->params[':id'] = $id;
      $sql = 'UPDATE `sys_cfg` SET `cfg_last_version` = `cfg_last_version` + 1 WHERE `id` = :id';
      $sql2 = 'UPDATE `sys_cfg` SET `cfg_current_version` = `cfg_last_version` WHERE `id` = :id';
      $sql3 = 'UPDATE `sys_cfg` SET `change_status` = 1 WHERE `id` = :id';
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->execute($sql2, $this->params);
          $connection->execute($sql3, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          // 如果发现异常，回滚操作
          $connection->rollback();
          return FALSE;
      }
  }

  public function updateCfgParams($data , $where){
    return $this->update($data, $where, 'sys_cfg_params');
  }

/**
 * 通过配置文件id删除
 * @param  array $ids 要删除的配置文件id
 * @return boolean      杀出结果
 */
  public function deleteCfgParamsbyIds($ids){
    return $this->delbyids($ids, 'sys_cfg_params');
  }

/**
 * 通过配置文件id删除配置文件擦书
 * @param  array $cfg_ids 配置文件参数
 * @return boolean          删除结果
 */
  public function deleteCfgParamsbyCfgIds($cfg_ids){
    if(!is_array($cfg_ids) || !$cfg_ids || !count($cfg_ids))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM sys_cfg_params" . $this->buildIntIn("cfg_id", $cfg_ids);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * 添加角色配置文件参数节点
 * @param boolean|int $data 添加结果
 */
  public function addCfgParamsNode($data){
// 如果不存在cfg_id则不允许提交
    if(!isset($data['cfg_id']))
      return FALSE;
    if(!isset($data['params_parent_id']))
      $data['params_parent_id'] = 0;
    $data['create_time'] = time();
    $data['update_time'] = time();
    return $this->insert($data, 'sys_cfg_params');
  }

/**
 * 配置文件版本快照
 * @param  array $data 数据
 * @return boolean       结果
 */
  public function insertCfgNewVersion($data, $version){
    if(!is_array($data) && !isset($data['0'])){
      return FALSE;
    }
    $values = "";
    $time = time();
    foreach ($data as $x) {
      $values .= "('{$x['id']}', '{$x['cfg_val_type']}', '{$x['cfg_id']}', '{$x['cfg_key']}', '{$x['type_key_id']}', '{$this->quoTranslat($x['cfg_val'])}', '{$x['type_val_id']}', '{$x['type_id']}', '{$x['params_parent_id']}', '{$version}', '{$time}', '{$time}'),";
    }
    $values = rtrim($values, ",");
    $sql = "INSERT INTO `sys_cfg_version` (`param_id`, `cfg_val_type`,`cfg_id`, `cfg_key`, `type_key_id`, `cfg_val`, `type_val_id`, `type_id`, `params_parent_id`, `cfg_version`, `update_time`, `create_time`) VALUES {$values}";
    $connection = $this->getAdapter();
    $this->params = array();
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * 获取服务器已经发送的配置文件
 * @param  int $cfg_id 配置文件id
 * @return array         查询结果
 */
  public function roleCfgServerList($cfg_id, $dataout = array()){
    $this->params = array();
    $this->params[':cfg_id'] = $cfg_id;
    $sql ='SELECT F.`name` AS app_name,A.*,C.`id` AS server_id,C.`ip`,D.`cfg_id`,D.`cfg_version` FROM `sys_cfg` AS A
          LEFT JOIN `sys_app` AS F ON F.`id` = A.`app_id`
          LEFT JOIN `sys_role_server_rel` AS B ON B.`role_id`=A.`role_id`
          LEFT JOIN `sys_server` AS C ON C.`id`=B.`server_id`
          LEFT JOIN `sys_server_cfg` AS D ON D.`cfg_id` = A.`id` AND D.`server_id` = C.`id`
          WHERE A.`id` = :cfg_id';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }


  public function roleCfgServerVersion($server_id, $cfg_id, $cfg_version){
    $info = $this->info(array(
      'server_id' => $server_id,
      'cfg_id' => $cfg_id,
    ), array(), 'sys_server_cfg');
    if($info){
      return $this->update(array(
        'cfg_version' => $cfg_version
      ),
      array(
        'server_id' => $server_id,
        'cfg_id' => $cfg_id,
      ), 'sys_server_cfg');
    }
    return $this->insert(array(
      'server_id' => $server_id,
      'cfg_id' => $cfg_id,
      'cfg_version' => $cfg_version
    ), 'sys_server_cfg');
  }

/**
 * 获取应用指令列表
 * @param  应用id $id 应用id
 * @return array     指令列表
 */
    public function getappCommandList($id){
      $this->params = array();
      $this->params[':app_id'] = $id;
      $sql = "SELECT * FROM `sys_app_command` WHERE `app_id` = :app_id ";
      $result = $this->getAdapter()->query($sql, $this->params);
      $out = array();
      while ($robot = $result->fetch()) {
        foreach($robot as $x => $x_val){
          if(is_numeric($x))
            unset($robot[$x]);
        }
         if($dataout){
           $sub = array();
           foreach($dataout as $x){
             $sub[$x] = $robot[$x];
           }
           array_push($out, $sub);
         }else{
           array_push($out, $robot);
         }
      }
      return $out;
    }

/**
 * 增加应用指令
 * @param array $data 添加结果
 */
    public function addAppCommand($data){
      $data['create_time'] = time();
      $data['update_time'] = time();
      return $this->insert($data, 'sys_app_command');
    }

/**
 * 删除指令
 * @param  array $ids 要删除的id
 * @return boolean      删除结果
 */
    public function delAppCommand($command_ids){
      if(!is_array($command_ids) || !$command_ids || !count($command_ids))
        return FALSE;
      $connection = $this->getAdapter();
      $this->params = array();
      $sql = "DELETE FROM sys_app_command" . $this->buildIntIn("id", $command_ids);
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          $connection->rollback();
          return FALSE;
      }
    }

/**
 * 更新指令应用
 * @param  array    $where 更新条件
 * @param   array $data 更新后的数据
 * @return boolean         更新结果
 */
    public function updateAppCommand($data, $where){
      $data['update_time'] = time();
      return $this->update($data, $where, 'sys_app_command');
    }

/**
 * 角色应用指令列表
 * @param  id $role_id id
 * @return array          查询结果
 */
    public function roleAppCommandList($role_id){
      $this->params = array();
      $this->params[':role_id'] = $role_id;
      $sql = 'SELECT A.*,B.`name` AS `app_name` FROM `sys_role_command` AS A LEFT JOIN `sys_app` AS B ON A.`app_id`=B.`id` WHERE A.`role_id` = :role_id';
      $result = $this->getAdapter()->query($sql, $this->params);
      $out = array();
      while ($robot = $result->fetch()) {
        foreach($robot as $x => $x_val){
          if(is_numeric($x))
            unset($robot[$x]);
        }
         if($dataout){
           $sub = array();
           foreach($dataout as $x){
             $sub[$x] = $robot[$x];
           }
           array_push($out, $sub);
         }else{
           array_push($out, $robot);
         }
      }
      return $out;
    }

/**
 * 角色应用指令添加
 * @param array $data 添加结果
 */
    public function roleAppCommandadd($data){
      $data['create_time'] = time();
      $data['update_time'] = time();
      return $this->insert($data, 'sys_role_command');
    }

/**
 * 角色应用指令删除
 * @param  array $command_ids 指令ids
 * @return array             删除结果
 */
    public function roleAppCommanddel($command_ids){
      if(!is_array($command_ids) || !$command_ids || !count($command_ids))
        return FALSE;
      $connection = $this->getAdapter();
      $this->params = array();
      $sql = "DELETE FROM sys_role_command" . $this->buildIntIn("id", $command_ids);
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          $connection->rollback();
          return FALSE;
      }
    }

  /**
   * 更新角色指令应用
   * @param  array    $where 更新角色条件
   * @param   array $data 更新角色后的数据
   * @return boolean         更新角色结果
   */
  public function roleAppCommandupdate($data, $where){
    $data['update_time'] = time();
    if(isset($data['command_name']))
      unset($data['command_name']);
    return $this->update($data, $where, 'sys_role_command');
  }

/**
 * 角色应用可添加的指令
 * @param  array $app_ids 应用id
 * @return array          查询结果
 */
  public function roleAppCommandAvalibleList($app_ids){
    if(!is_array($app_ids) || !$app_ids || !count($app_ids))
      return FALSE;
    $this->params = array();
    $sql = 'SELECT A.*,B.name AS app_name FROM `sys_app_command` AS A
            LEFT JOIN `sys_app` AS B ON A.`app_id` = B.`id` ' . $this->buildIntIn("B.`id`", $app_ids);
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 角色指令信息
 * @param  int $id 指令id
 * @return array     查询结果
 */
  public function roleAppCommandInfo($id, $dataout = array()){
    $this->params = array();
    $this->params[':id'] = $id;
    $sql = 'SELECT A.*,B.`name` AS `app_name` FROM `sys_role_command` AS A LEFT JOIN `sys_app` AS B ON A.`app_id`=B.`id` WHERE A.`id` = :id';
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }


  /**
   * 角色指令服务器ip列表
   * @param  int $id 指令id
   * @return array     查询结果
   */
    public function roleAppCommandServers($id){
      $this->params = array();
      $this->params[':id'] = $id;
      $sql = 'SELECT A.`id` AS command_id, C.`id` AS server_id, C.`ip`, D.`command_content` FROM `sys_role_command` AS A
              LEFT JOIN `sys_role_server_rel` AS B ON B.`role_id` = A.`role_id`
              LEFT JOIN `sys_server` AS C ON C.id = B.`server_id`
              LEFT JOIN `sys_server_command` AS D ON D.`server_id` = B.`server_id` and D.command_id = A.id
              WHERE A.`id` = :id ';
      $result = $this->getAdapter()->query($sql, $this->params);
      $out = array();
      while ($robot = $result->fetch()) {
        foreach($robot as $x => $x_val){
          if(is_numeric($x))
            unset($robot[$x]);
        }
         if($dataout){
           $sub = array();
           foreach($dataout as $x){
             $sub[$x] = $robot[$x];
           }
           array_push($out, $sub);
         }else{
           array_push($out, $robot);
         }
      }
      return $out;
    }

/**
 * 更新命令server
 * @param  array $data 更新的数据
 * @return boolean|int       更新结果
 */
    public function roleAppCommandServersUpdate($data){
      $server_id = isset($data['server_id']) ? $data['server_id'] : '';
      $command_id = isset($data['command_id']) ? $data['command_id'] : '';
      $command_content = isset($data['command_content']) ? $data['command_content'] : '';
      if(!$this->info(array(
        "server_id" => $server_id,
        "command_id" => $command_id
      ) ,array(), 'sys_server_command')){
        return $this->insert(array(
          "server_id" => $server_id,
          "command_id" => $command_id,
          "command_content" => $command_content,
          'create_time' => time(),
          'update_time' => time()
        ), 'sys_server_command');
      }

      return $this->update(array(
          "command_content" => $command_content,
          'update_time' => time()
        ), array(
          "server_id" => $server_id,
          "command_id" => $command_id
        ), "sys_server_command");
    }

/**
 * 角色应用指令历史添加
 * @param  array $data 执行详情
 * @return boolean         执行结果
 */
    public function roleAppCommandHistoryAdd($data){
      return $this->insert(array(
        'command_id' => isset($data['command_id']) ? $data['command_id'] : '',
        'ip' => isset($data['ip']) ? $data['ip'] : '',
        'command_content' => isset($data['command_content']) ? $data['command_content'] : '',
        'command_result' => isset($data['command_result']) ? $data['command_result'] : '',
        'create_time' => time()
      ), 'sys_command_history');
    }

/**
 * 命令执行历史
 * @param  int  $command_id 指令id
 * @param  integer $offset     偏移
 * @param  integer $show       显示数量
 * @return array              查询结果
 */
 public function roleAppCommandTop($command_id, $offset = 0, $show = 30){
   return $this->info(array(
       'command_id' => $command_id
     ), array(), 'sys_command_history', $show, $offset, '`id` DESC');
 }

/**
 * 获取数据总数
 * @param  array $data 条件只能为等于条件
 * @return int       结果
 */
  public function getCount($data, $table){
    $this->params = array();
    $sql = 'SELECT count(id) as sum FROM ' . $table . $this->buildWhere($data);
    $result = $this->getAdapter()->query($sql, $this->params);
    $robot = $result->fetch();
    if(isset($robot['sum']))
      return $robot['sum'];
    return 0;
  }
/**
 * get info information
 * @param  array  $data condition just '='
 * @return array       result
 */
  public function info(array $data=array() ,array $dataout=array(), $table, $limit = 0 ,$offset = false ,$order = false){
    $this->params = array();
    $lim = ($limit > 0)?(' limit '.$limit):'';
    $offs = ($offset)?(' offset '.$offset.' '):'';
    $orde = ($order)?(' order by '.$order.' '):'';
    $sql = 'SELECT '.$this->buildShere($dataout).' FROM ' . $table . $this->buildWhere($data) . $orde.  $lim . $offs;
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

/**
 * 插入数据库
 * @param  array  $data 数据
 * @return int or boolean      结果
 */
  public function insert(array $data = array(), $table = ''){
    foreach ($data as $x => $x_val){
      if($x_val === null){
        unset($data[$x]);
      }
    }
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = 'INSERT INTO ' . $table . '('. $this->buildInsert($data) .') VALUES (' . $this->buildInsertData($data) . ')';
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $id = $connection->lastInsertId();
        $connection->commit();
        return $id;
    } catch (Exception $e) {
        // 如果发现异常，回滚操作
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * update table
 * @param  array  $data set sql
 * @param  array  $where just '='
 * @return bool      result
 */
  public function update(array $data = array(), array $where = array(), $table){
  // 如果没有更改项将不改变
    if(!count($data))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = 'UPDATE ' . $table . $this->buildSetData($data) . $this->buildWhere($where);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        // 如果发现异常，回滚操作
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * 删除数据
 * @param  array  $data 条件
 * @return string       表名
 */
  public function del(array $data = array(), $table){
    if(!$data || !count($data))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM " . $table . $this->buildWhere($data);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

  /**
   * 删除数据
   * @param  array  $ids id数组
   * @return string       表名
   */
    public function delbyids(array $data = array(), $table){
      if(!is_array($data) || !$data || !count($data))
        return FALSE;
      $connection = $this->getAdapter();
      $this->params = array();
      if(!$this->buildIdIn($data))
        return FALSE;
      $sql = "DELETE FROM " . $table . $this->buildIdIn($data);
      try {
          $connection->begin();
          $connection->execute($sql, $this->params);
          $connection->commit();
          return TRUE;
      } catch (Exception $e) {
          $connection->rollback();
          return FALSE;
      }
    }

    private function runExceptSelect($sql, array $params = array()){
      $connection = $this->getAdapter();
      try {
          $connection->begin();
          $connection->execute($sql, $params);
          $connection->commit();
          return true;
      } catch (Exception $e) {
          // 如果发现异常，回滚操作
          $connection->rollback();
          return false;
      }
    }

  //sub function
    public function buildWhere($data){
      $out = array();
      foreach($data as $x => $_val){
        $this->params['W'.$x] = $_val;
        array_push($out, $x.'=:W'.$x);
      }
      if(count($data)>0)
        return ' where '.implode(' and ', $out).' ';
      return '';
    }

    public function buildval($data){
      $out = array();
      foreach($data as $x => $x_val){
        $out[':'.$x] = $x_val;
      }
      return $out;
    }

    public function buildShere($data){
      $out = array();
      foreach($data as $x){
        array_push($out, '`' . $x . '`');
      }
      if(count($data)>0)
        return ' '.implode(',', $out).' ';
      return '*';
    }

    public function buildInsert($data){
      $out = array();
      foreach($data as $x => $x_val){
        array_push($out, '`' . $x . '`');
      }
      if(count($data)>0)
        return ' '.implode(',', $out).' ';
      return ' ';
    }

    public function buildInsertData($data){
      $out = array();
      foreach($data as $x => $x_val){
        $this->params['I' . $x] = $x_val;
        array_push($out, ':I' . $x);
      }
      if(count($data)>0)
        return ' '.implode(',', $out).' ';
      return ' ';
    }

    public function buildSetData($data){
      $out = array();
      foreach($data as $x => $x_val){
        $this->params['S' . $x] = $x_val;
        array_push($out, '`' . $x . '` =:S' . $x);
      }
      if(count($data)>0)
        return ' SET '.implode(',', $out).' ';
      return ' ';
    }

    public function  buildIdIn($ids){
      $out = array();
      foreach($ids as $id){
          array_push($out, intval($id));
      }
      $out = array_unique($out);
      if(count($out))
        return ' WHERE id in(' . implode(",", $out) . ')';
      return '';
    }

    public function  buildIntIn($key, $ids){
      $out = array();
      foreach($ids as $id){
          array_push($out, intval($id));
      }
      $out = array_unique($out);
      return " WHERE {$key} in(" . implode(",", $out) . ")";
    }

// sql单引号转换
    public function quoTranslat($data){
      return str_replace("'", "''", $data);
    }

}
