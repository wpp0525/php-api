<?php
use Phalcon\Mvc\Dispatcher;
/**
 * 环境服务器资源管理
 *
 * @author dirc.wang
 *
 */

class EnvconfigController extends ControllerBase {

  private $sys_data_service;

  public function initialize() {
      parent::initialize();
      $this->sys_data_service = $this->di->get('cas')->get('sys_data_service');
  }

/**
*在action之前进行判断
*处理结果
 */
  // public function beforeExecuteRoute(Dispatcher $dispatcher)
  // {
  //     $dispatcher->getControllerClass();
  // }

/**
 * token验证不成功
 * @return array  token验证错误
 */
  public function tokenerrorAction(){
    return $this->jsonResponse(array(
      'error' => '4011',
      'error_description' => 'token验证错误',
    ));
  }

/**
 * 线下服务器列表
 * @return array 查询结果
 */
  public function offlineServerListAction(){
    // 拉取页面.每页100个
    $inonesum = 100;
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '获取成功',
      'data' => array(
        'list' => $this->sys_data_service->getofflineServerPage($this->curpage, $inonesum),
        'count' => $this->sys_data_service->getofflineServercount(),
        'inonesum' => $inonesum
      )
    ));
  }

/**
 * 线下服务器添加
 * @return $array; 结果
 */
  public function offlineServerInsertAction(){
    $info = $this->sys_data_service->infoofflineServer(
      array(
        'ip' => $this->ip
      ),
      array()
    );
    if($info){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该ip的服务器已经添加',
      ));
    }
    $result = $this->sys_data_service->insertofflineServer(array(
      "ip" => $this->ip,
      "cpu" => $this->cpu,
      "mem" => $this->mem,
      "disk" => $this->disk,
      "is_virtual" => $this->is_virtual,
      "host_ip" => $this->host_ip,
      "remark" => $this->remark,
      "status" => $this->status,
      "create_time" => time(),
      "update_time" => time(),
    ));
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '添加成功',
    ));
  }

/**
 * 线下服务器删除
 * @return array 删除结果
 */
  public function offlineServerDelAction(){
    $ids = json_decode($this->request->getPost('ids'), true);
    $this->sys_data_service->delofflineServer($ids);
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '删除成功',
    ));
  }

/**
 * 线下服务器编辑
 * @return array 编辑结果
 */
  public function offlineServerEditAction(){
    $result = $this->sys_data_service->editofflineServer(array(
      "cpu" => $this->cpu,
      "mem" => $this->mem,
      "disk" => $this->disk,
      "is_virtual" => $this->is_virtual,
      "host_ip" => $this->host_ip,
      "remark" => $this->remark,
      "status" => $this->status,
      "update_time" => time(),
    ),
    array(
      'id' => $this->id,
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '修改成功',
      ));
    }
    return $this->jsonResponse(array(
      'code' => '4002',
      'msg' => '修改失败',
    ));
  }

  /**
   * 线下服务器硬件信息编辑
   * @return array 编辑结果
   */
    public function offlineServerHardEditAction(){
      $datain = array();
      if($this->mem)
        $datain['mem'] = $this->mem;
      if($this->mem_used)
        $datain['mem_used'] = $this->mem_used;
      if($this->disk)
        $datain['disk'] = $this->disk;
      if($this->disk_used)
        $datain['disk_used'] = $this->disk_used;
      $result = $this->sys_data_service->editofflineServer($datain, array(
        'ip' => $this->ip,
      ));
      if($result === TRUE){
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => '修改成功',
        ));
      }
      return $this->jsonResponse(array(
        'code' => '4002',
        'msg' => '修改失败',
      ));
    }

/**
 * 线下服务器信息获取
 * @return array 服务器信息
 */
  public function offlineServerInfoAction(){
    $info = $this->sys_data_service->infoofflineServer(
      array(
        'id' => $this->id
      ),
      array()
    );
    if($info)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '查询成功',
        'data' => $info
      ));
    return $this->jsonResponse(array(
      'code' => '4004',
      'msg' => '查找的数据不存在',
      'data' => $info
    ));
  }

/**
 * 线下服务器角色列表
 * @return array 列表信息
 */
  public function offlineRoleListAction(){
    // 拉取页面.每页50个
    $inonesum = 50;
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '获取成功',
      'data' => array(
        'list' => $this->sys_data_service->getofflineRolePage($this->curpage, $inonesum),
        'count' => $this->sys_data_service->getofflineRolecount(),
        'inonesum' => $inonesum
      )
    ));
  }

/**
 * 线下服务器角色添加
 * @return array 添加结果
 */
  public function offlineRoleInsertAction(){
    $result = $this->sys_data_service->insertofflineRole(array(
      'name' => $this->name,
      'desc' => $this->desc,
      'type' => $this->type,
      'remark' => $this->remark,
      "create_time" => time(),
      "update_time" => time(),
    ));
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '添加成功',
    ));
  }

/**
 * 线下服务器角色删除
 * @return array 删除结果
 */
  public function offlineRoleDelAction(){
    $ids = json_decode($this->request->getPost('ids'), true);
    $this->sys_data_service->delofflineRole($ids);
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '删除成功',
    ));
  }

/**
 * 线下服务器角色编辑
 * @return array 编辑结果
 */
  public function offlineRoleEditAction(){
    $result = $this->sys_data_service->editofflineRole(array(
      'name' => $this->name,
      'desc' => $this->desc,
      'remark' => $this->remark,
      'type' => $this->type,
      "update_time" => time(),
    ),
    array(
      'id' => $this->id,
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '修改成功',
      ));
    }
    return $this->jsonResponse(array(
      'code' => '4002',
      'msg' => '修改失败',
    ));
  }

/**
 * 线下服务器角色信息
 * @return array 角色信息
 */
  public function offlineRoleInfoAction(){
    $info = $this->sys_data_service->infoofflineRole(
      array(
        'id' => $this->id
      ),
      array()
    );
    if($info)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '查询成功',
        'data' => $info
      ));
    return $this->jsonResponse(array(
      'code' => '4004',
      'msg' => '查找的数据不存在',
      'data' => $info
    ));
  }

  /**
   * 线下角色关联应用添加
   * @return array 添加结果
   */
  public function offlineRoleApplicationAddAction(){
    $info = $this->sys_data_service->infoofflineRole(
      array(
        'id' => $this->role_id
      ),
      array()
    );
    if(!$info){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '角色不存在',
        'data' => $info
      ));
    }
    $info = $this->sys_data_service->getappinfo(
      array(
        'id' => $this->app_id,
      ),
      array()
    );
    if(!$info){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '应用不存在',
        'data' => $info
      ));
    }
    $result = $this->sys_data_service->insertofflineRoleApp(
      array(
        'role_id' => $this->role_id,
        'app_id' => $this->app_id,
        "create_time" => time(),
        "update_time" => time(),
      )
    );
    if($result !== FALSE)
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '角色应用添加成功',
      ));
    return $this->jsonResponse(array(
      'error' => '4003',
      'error_description' => '角色应用添加失败',
    ));
  }

/**
 * 线下角色关联应用删除匹配相应的id
 * @return array 删除结果
 */
  public function offlineRoleAppbyIdsDelAction(){
    $ids = json_decode($this->request->getPost('ids'), true);
    if(!$ids){
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '请上传必填写参数',
      ));
    }
    $result = $this->sys_data_service->delofflineRoleAppbyIds($ids);
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '删除失败',
    ));
  }

/**
 * 线下角色关联服务删除-匹配role_ids
 * @return array 删除结果
 */
  public function offlineRoleAppbyRoleidDelAction(){
    $role_ids = json_decode($this->request->getPost('role_ids'), true);
    if(!$role_ids){
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '请上传必填写参数',
      ));
    }
    $result = $this->sys_data_service->delofflineRoleAppbyRoleid($role_ids);
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '删除失败',
    ));
  }

/**
 * 线下角色关联服务编辑
 * @return array 编辑结果
 */
  public function offlineRoleAppEditAction(){
    $result = $this->sys_data_service->editofflineRoleApp(
      array(
        'remark' => $this->remark,
        "update_time" => time(),
      ),
      array(
        'id' => $this->id
      )
    );
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '修改成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '修改失败',
    ));
  }

// /**
//  * 线下角色关联信息o
//  * old
//  * @return array 线下角色信息
//  */
//   public function offlineRoleAppInfoAction(){
//     $info = $this->sys_data_service->infoofflineRoleApp(
//       array(
//         'role_id' => $this->role_id
//       )
//     );
//     if($info)
//       return $this->jsonResponse(array(
//         'code' => '4000',
//         'msg' => '查询成功',
//         'data' => $info
//       ));
//     return $this->jsonResponse(array(
//       'code' => '4004',
//       'msg' => '查找的数据不存在',
//       'data' => $info
//     ));
//   }

  /**
   * 线下角色关联信息o
   * old
   * @return array 线下角色信息
   */
    public function offlineRoleAppInfoAction(){
      $info = $this->sys_data_service->infoofflineRoleApp(
        array(
          'role_id' => $this->role_id
        )
      );
      if($info)
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => '查询成功',
          'data' => $info
        ));
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '查找的数据不存在',
        'data' => $info
      ));
    }

/**
 * 线下角色服务添加
 * @return array 添加结果
 */
  public function offlineRoleServerAddAction(){
    $info = $this->sys_data_service->infoofflineServer(
      array(
        'id' => $this->server_id
      ),
      array()
    );
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '服务器不存在',
        'data' => $info
      ));
    $info = $this->sys_data_service->infoofflineRole(
        array(
          'id' => $this->role_id
        ),
        array()
      );
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '角色不存在',
        'data' => $info
      ));
    $info = $this->sys_data_service->insertServerRole(array(
      'role_id' => $this->role_id,
      'server_id' => $this->server_id,
      'remark' => $this->remark,
    ));
    if(isset($info['code']))
      return $this->jsonResponse($info);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
      'data' => $info
    ));
  }


  /**
   * 线下角色服务添加
   * @return array 添加结果
   */
    public function offlineRoleServerEditAction(){
      $info = $this->sys_data_service->editServerRole(array(
          'remark' => $this->remark,
        ),
        array(
          'id' => $this->id,
        )
      );
      if(!$info)
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '修改失败',
          'data' => $info
        ));
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '修改成功',
        'data' => $info
      ));
    }


/**
 * 线下角色绑定的服务器列表
 * @return array 结果
 */
  public function offlineRoleServerListAction(){
    $info = $this->sys_data_service->infoofflineRole(
        array(
          'id' => $this->role_id
        ),
        array()
      );
    if(!$info)
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '角色不存在',
        'data' => $info
      ));
    $info = $this->sys_data_service->getRoleServerList(array(
      'role_id' => $this->role_id,
    ));
    return $this->jsonResponse(array(
      'code' => '4000',
      'msg' => '获取成功',
      'data' => $info
    ));
  }

/**
 * 线下角色绑定的服务器删除匹配id
 * @return array  结果
 */
  public function offlineRoleServerDelbyIdsAction(){
    $ids = json_decode($this->request->getPost('ids'), true);
    if(!$ids){
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '请上传必填写参数',
      ));
    }
    $result = $this->sys_data_service->delServerRole($ids);
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '删除失败',
    ));
  }

/**
 * 线下角色绑定的服务器删除匹配role_id
 * @return array 结果
 */
  public function offlineRoleServerDelbyRoleIdsAction(){
    $ids = json_decode($this->request->getPost('role_ids'), true);
    if(!$ids){
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '请上传必填写参数',
      ));
    }
    $result = $this->sys_data_service->delServerRolebyRoleid($ids);
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '删除失败',
    ));
  }

/**
 * 删除角色服务器
 * @return array 结果
 */
  public function offlineRoleServerDelbyServeridsAction(){
    $ids = json_decode($this->request->getPost('server_ids'), true);
    if(!$ids){
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '请上传必填写参数',
      ));
    }
    $result = $this->sys_data_service->delServerRolebyServerId($ids);
    if($result)
      return $this->jsonResponse(array(
        'code' => '4000',
        'msg' => '删除成功',
      ));
    return $this->jsonResponse(array(
      'code' => '4003',
      'msg' => '删除失败',
    ));
  }

  /**
   * 获取并修改服务器内存和硬盘信息
   * @return array 存取结果
   */
  	public function offlineServerInfoFromIpAction(){
  		$ip = $this->ip;
  		$Ansiblesystem = new \Ansible\classes\Ansiblesystem();
  		$info = array();
  		$val = $Ansiblesystem->getDfCount($ip);
  		$info['dfall'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
  		$val = $Ansiblesystem->getDfused($ip);
  		$info['dfused'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
  		$val = $Ansiblesystem->getFreetotal($ip);
  		$info['freetotal'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
  		$val = $Ansiblesystem->getFreeused($ip);
  		$info['freeused'] = ($val !== FALSE) ? round($val/1048576, 2) : '';
      $val = $Ansiblesystem->getCpuinfo($ip);
      $info['cpusum'] = ($val !== FALSE) ? intval($val) : '';
//保存返回正确的参数
      $datain = array();
      if($info['freetotal'])
        $datain['mem'] = $info['freetotal'];
      if($info['freeused'])
        $datain['mem_used'] = $info['freeused'];
      if($info['dfall'])
        $datain['disk'] = $info['dfall'];
      if($info['dfused'])
        $datain['disk_used'] = $info['dfused'];
      if($info['cpusum'])
        $datain['cpu'] = $info['cpusum'];
      $result = $this->sys_data_service->editofflineServer($datain, array(
        'ip' => $this->ip,
      ));
      if($result === TRUE){
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '修改成功',
          'info' => $info
        ));
      }
      return $this->jsonResponse(array(
        'error' => '4002',
        'error_description' => '修改失败',
        'info' => $info
      ));
  	}

/**
 * 获取应用列表
 * @return array 查询结果
 */
    public function appListAction(){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '获取成功',
        'data' => array(
          'list' => $this->sys_data_service->getapplist($this->curpage, $this->inonesum),
          'count' => $this->sys_data_service->getappcount(),
          'inonesum' => $this->inonesum,
          'curpage' => $this->curpage
        )
      ));
    }

/**
 * 添加应用
 * @return array 添加结果
 */
    public function appInsertAction(){
      $return = $this->sys_data_service->appInsert(array(
        'name' => $this->name,
        'desc' => $this->desc,
      ));
      if(isset($return['error']))
        return $this->jsonResponse($return);
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '添加成功',
        'info' => $return
      ));
    }
/**
 * 通过id删除app
 * @return array 删除结果
 */
    public function appDelbyIdsAction(){
      $ids = json_decode($this->request->getPost('app_ids'), true);
      $result = $this->sys_data_service->appDelete($ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 修改应用
 * @return array 修改结果
 */
    public function appEditAction(){
      $result = $this->sys_data_service->appEdit(array(
          'name' => $this->name,
          'desc' => $this->desc
        ),
        array(
          'id' => $this->id,
        )
      );
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '修改成功',
        ));
      return $this->jsonResponse($result);
    }

/**
 * 应用端口列表
 * @return array 应用端口列表
 */
    public function appPortListAction(){
      $list = $this->sys_data_service->getAppPort($this->app_id);
      if($list && isset($list[0]) )
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '获取成功',
          'data' => array(
            'list' => $list
          )
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '获取失败',
        'data' => array(
          'list' => array()
        )
      ));
    }

/**
 * 应用端口添加
 * @return array 添加结果
 */
    public function appPortInsertAction(){
      $list = $this->sys_data_service->info(array(
        'id' => intval($this->app_id),
      ), array(), 'sys_app');
      if(!$list || !isset($list[0]))
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '该应用不存在',
        ));
      $result = $this->sys_data_service->insertAppPort(array(
        'app_id' => $this->app_id,
        'server_port' => $this->server_port,
        'remark' => $this->remark,
      ));
      if(isset($result['error']))
        return $this->jsonResponse($result);
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '添加成功',
        'data' => $result,
      ));
    }

/**
 * 应用解绑端口
 * @return array 删除结果
 */
    public function appPortDelByIdAction(){
      $ids = json_decode($this->request->getPost('ids'), true);
      $result = $this->sys_data_service->deleteAppPortByids($ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 删除应用下的所有端口
 * @return array 解绑结果
 */
    public function  appPortDelByAppIdAction(){
      $app_ids = json_decode($this->request->getPost('app_ids'), true);
      $result = $this->sys_data_service->deleteAppPortByAppid($app_ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }


/**
 * 更新app端口
 * @return array 更新结果
 */
    public function appPortUpdateAction(){
      $result = $this->sys_data_service->updateAppPort(array(
          'remark' => $this->remark,
        ),
        array(
          'id' => $this->id
        )
      );
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '更新成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '更新失败',
      ));
    }

  /**
   * 获取应用默认配置文件
   * @return array 配置文件列表
   */
    public function appCfgListAction(){
      $list = $this->sys_data_service->getAppCfgList($this->app_id);
      if($list && isset($list[0]) )
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '获取成功',
          'data' => array(
            'list' => $list
          )
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '获取失败',
        'data' => array(
          'list' => array()
        )
      ));
    }

/**
 * 添加应用默认配置文件
 * @return array 添加结果
 */
    public function appCfgAddAction(){
      $list = $this->sys_data_service->info(array(
        'id' => intval($this->app_id),
      ), array(), 'sys_app');
      if(!$list || !isset($list[0]))
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '该应用不存在',
        ));
      $result = $this->sys_data_service->addAppCfg(array(
        'app_id' => $this->app_id,
        'name' => $this->name,
        'remark' => $this->remark,
        'cfg_type' => $this->cfg_type,
        'file_name' => $this->file_name,
        'name_change_type' => $this->name_change_type,
        'file_path' => $this->file_path,
        'path_change_type' => $this->path_change_type,
        'file_chmod' => $this->file_chmod,
        'file_owner' => $this->file_owner,
        'file_group' => $this->file_group,
      ));
      if($result === FALSE)
        return $this->jsonResponse(array(
          'error' => '4001',
          'error_description' => '添加失败',
        ));
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '添加成功',
        'data' => $result,
      ));
    }

/**
 * 应用配置文件更新
 * @return array 更新结果
 */
    public function appCfgUpdateAction(){
      $result = $this->sys_data_service->updateAppCfg(array(
          'name' => $this->name,
          'remark' => $this->remark,
          'cfg_type' => $this->cfg_type,
          'file_name' => $this->file_name,
          'name_change_type' => $this->name_change_type,
          'file_path' => $this->file_path,
          'path_change_type' => $this->path_change_type,
          'file_chmod' => $this->file_chmod,
          'file_owner' => $this->file_owner,
          'file_group' => $this->file_group,
        ),
        array(
          'id' => $this->id
        )
      );
      if($result === FALSE)
        return $this->jsonResponse(array(
          'error' => '4001',
          'error_description' => '修改失败',
        ));
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '修改成功',
      ));
    }

/**
 * 通过id删除应用配置文件
 * @return array 删除结果
 */
    public function appCfgDelbyIdsAction(){
      $ids = json_decode($this->request->getPost('ids'), true);
      $result = $this->sys_data_service->deleteAppCfgByids($ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 通过app_id删除应用配置文件
 * @return array 删除结果
 */
    public function appCfgDelbyAppidsAction(){
      $app_ids = json_decode($this->request->getPost('app_ids'), true);
      $result = $this->sys_data_service->deleteAppCfgByAppid($app_ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 可选择的配置文件类新列表
 * @return array 结果
 */
    public function configTypelistAction(){
      $configFilesystem = new \Ansible\classes\configFilesystem();
      $list = $configFilesystem->getConfigtypeMaping();
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '获取成功',
        'data' => $list,
      ));
    }

/**
 * 角色应用端口列表
 * @return array 查询结果
 */
    public function roleAppPortListAction(){
      $info = $this->sys_data_service->getroleAppPortlist(
        array(
          'role_id' => $this->role_id
        )
      );
      if($info)
        return $this->jsonResponse(array(
          'code' => '4000',
          'msg' => '查询成功',
          'data' => $info
        ));
      return $this->jsonResponse(array(
        'code' => '4004',
        'msg' => '查找的数据不存在',
        'data' => $info
      ));
    }

/**
 * 角色应用端口添加
 * @return array 添加结果
 */
    public function roleAppPortInsertAction(){
      $info = $this->sys_data_service->infoofflineRole(
          array(
            'id' => $this->role_id
          ),
          array()
        );
      if(!$info)
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '角色不存在',
          'data' => $info
        ));
      $result = $this->sys_data_service->roleAppPortInsert(array(
          'role_id' => $this->role_id,
          'app_port_id' => $this->app_port_id,
        )
      );
      if(isset($result['error'])){
        return $this->jsonResponse($result);
      }
      return $this->jsonResponse(array(
            'error' => '4000',
            'error_description' => '添加成功',
            'data' => $info
          ));
    }

/**
 * 通过id删除应用接口
 * @return array 删除结果
 */
    public function roleAppPortDeleteByIdsAction(){
      $ids = json_decode($this->request->getPost('ids'), true);
      $result = $this->sys_data_service->roleAppPortdeleteByIds($ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 通过role_id删除角色应用端口
 * @return array 删除结果
 */
    public function roleAppPortDeleteByRoleidsAction(){
      $role_ids = json_decode($this->request->getPost('role_ids'), true);
      $result = $this->sys_data_service->roleAppPortdeleteByRoleids($role_ids);
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '删除成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '删除失败',
      ));
    }

/**
 * 获取应用可绑定的端口
 * @return array 查询结果
 */
    public function roleAppPortBindListAction(){
      $info = $this->sys_data_service->infoofflineRoleApp(
        array(
          'role_id' => $this->role_id
        )
      );
      if(!$info){
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '还未绑定应用，请先绑定应用',
        ));
      }
      $app_ids = array();
      foreach ($info as $x) {
        if(isset($x['app_id']) && $x['app_id'])
          array_push($app_ids, $x['app_id']);
      }
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '获取成功',
        'data' => $this->sys_data_service->roleAppPortBindlist($app_ids),
      ));
    }

/**
 * 角色应用端口更新
 * @return array 更新结果
 */
    public function roleAppPortupdateAction(){
      $result = $this->sys_data_service->roleAppPortUpdate(
        array(
          'remark' => $this->remark,
        ),
        array(
          'id' => $this->id
        )
      );
      if($result === TRUE)
        return $this->jsonResponse(array(
          'error' => '4000',
          'error_description' => '更新成功',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '更新失败',
      ));
    }

/**
 * 配置类型列表
 * @return array 结果
 */
  public function cfgTypeListAction(){
    $TypeList = $this->sys_data_service->getcfgTypeList();
    $type_id = (isset($TypeList['0']) && isset($TypeList['0']['id'])) ? $TypeList['0']['id'] : -1;
// 获取kyes
    $cfgTypekeys = $this->sys_data_service->getCfgTypeKeys($type_id);
    $cfgTypevals = array();
//获取默认val
    if($cfgTypekeys && isset($cfgTypekeys['0']) && isset($cfgTypekeys['0']['id'])){
      $cfgTypevals = $this->sys_data_service->getCfgTypeKeyVals($cfgTypekeys['0']['id']);
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'TypeList' => $TypeList,
        'cfgTypekeys' => $cfgTypekeys,
        'cfgTypevals' => $cfgTypevals,
      )
    ));
  }

  /**
   * 配置类型列表的keys
   * @return array 结果
   */
  public function cfgTypeKeysAction(){
// 获取kyes
    $cfgTypekeys = $this->sys_data_service->getCfgTypeKeys($this->type_id);
    $cfgTypevals = array();
//获取默认val
    if($cfgTypekeys && isset($cfgTypekeys['0']) && isset($cfgTypekeys['0']['id'])){
      $cfgTypevals = $this->sys_data_service->getCfgTypeKeyVals($cfgTypekeys['0']['id']);
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'cfgTypekeys' => $cfgTypekeys,
        'cfgTypevals' => $cfgTypevals,
      )
    ));
  }

/**
 * 配置类型列表的key对应的val
 * @return array 结果
 */
  public function cfgTypeKeyValsAction(){
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $this->sys_data_service->getCfgTypeKeyVals($this->type_key_id),
    ));
  }

/**
 * 获取角色配置文件列表
 * @return array 查询结果
 */
  public function roleCfgListAction(){
    $result = $this->sys_data_service->rolecfglist(
      $this->cpage,
      $this->inonepage,
      array(
        'role_id' => $this->role_id
      )
    );
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'list' => $result,
        'count' => $this->sys_data_service->getRoleCfgCount($this->role_id),
        'inonesum' => $this->inonepage
      ),
    ));
  }

/**
 * 获取可添加的应用配置文件列表
 * @return array 查询结果
 */
  public function roleCfgUsableListAction(){
    $result = $this->sys_data_service->roleAppCfgUsableList($this->role_id);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'list' => $result,
      ),
    ));
  }

/**
 * 添加可添加的应用配置文件列表
 * @return array 查询结果
 */
  public function roleCfgFileAddAction(){
    $result = $this->sys_data_service->addRoleCfgFile(array(
      'role_id' => $this->role_id,
      'app_id' => $this->app_id,
      'name' => $this->name,
      'remark' => $this->remark,
      'cfg_type' => $this->cfg_type,
      'file_name' => $this->file_name,
      'file_path' => $this->file_path,
      'file_chmod' => $this->file_chmod,
      'file_owner' => $this->file_owner,
      'file_group' => $this->file_group,
      'cfg_current_version' => 1,
      'cfg_last_version' => 1,
      'change_status' => 1,
    ));
    if(isset($result['error'])){
      return $this->jsonResponse($result);
    }
    if($result === FALSE)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '添加失败',
        'data' => $result,
      ));
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
    ));
  }


  /**
   * 更新可添加的应用配置文件列表
   * @return array 查询结果
   */
    public function roleCfgFileUpdateAction(){
      $result = $this->sys_data_service->updateRoleCfgFile(array(
        'remark' => $this->remark,
        'file_chmod' => $this->file_chmod,
        'file_owner' => $this->file_owner,
        'file_group' => $this->file_group,
        'id' => $this->cfg_id,
      ));
      if($result === FALSE)
        return $this->jsonResponse(array(
          'error' => '4004',
          'error_description' => '更新失败',
          'data' => $result,
        ));
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '更新成功',
      ));
    }


/**
 * 获取文件详情
 * @return array 结果
 */
  public function roleCfgFileDetailedAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    $configFilesystem->setTypeObject($cfg_type);
    $Params = $this->sys_data_service->getCfgParams($this->cfg_id);
    $data = $configFilesystem->getformatTableData($Params);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $data,
    ));
  }

/**
 * 获取前端编辑页面htmlcode
 * @return array 结果
 */
  public function roleCfgHtmlCodeAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    $configFilesystem->setTypeObject($cfg_type);
    $Params = $this->sys_data_service->getCfgParams($this->cfg_id);
    $data = $configFilesystem->getformatTableData($Params);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $configFilesystem->getConfigOutHtmlCode($data, $this->cfg_id, $info),
    ));
  }
/**
 * 配置文件预览html
 * @return array 预览代码
 */
  public function roleCfgHtmlPreviewCodeAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    $configFilesystem->setTypeObject($cfg_type);
    $Params = $this->sys_data_service->getCfgVresionParams($this->cfg_id, $this->cfg_version);
    $data = $configFilesystem->getformatTableData($Params);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $configFilesystem->buildPriviewHtml($data, $this->cfg_id, $info),
    ));
  }

/**
 * 添加角色配置文件i节点
 * @return array 添加结果
 */
  public function roleCfgParamsAddNodeAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    $result = $this->sys_data_service->addCfgParamsNode(array(
      'cfg_id' => $this->cfg_id,
      'cfg_key' => $this->cfg_key,
      'cfg_val' => $this->cfg_val,
      'type_id' => $this->type_id,
      'type_key_id' => $this->type_key_id,
      'type_val_id' => $this->type_val_id,
      'params_parent_id' =>  $this->params_id,
      'cfg_val_type' => $this->cfg_val_type,
    ));
    if($result === FALSE)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '添加失败',
      ));
// 更新修改状态
    $this->sys_data_service->updateRoleCfgFileStatus($this->cfg_id);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
      'data' => $result
    ));
  }

/**
 * 配置文件编辑框
 * @return array 编辑页面q详情
 */
  public function roleCfgEditHtmlCodeAction(){
    $data = array(
      'type_id' => $this->type_id,
      'parent_type_id' => $this->parent_type_id,
      'type_key_id' => $this->type_key_id,
      'type_val_id' => $this->type_val_id,
      'cfg_id' => $this->cfg_id,
      'params_parent_id' => $this->params_parent_id,
      'key_val' => $this->key_val,
      'val_val' => $this->val_val,
      'params_id' => $this->params_id,
      'cfg_val_type' => $this->cfg_val_type
    );
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    // 设置type类型
    $configFilesystem->setTypeObject($cfg_type);
    $options = array(
      'types' => $this->sys_data_service->getcfgTypeList(),
      'keys' => $this->sys_data_service->getCfgTypeKeys($this->type_id),
      'vals' => $this->sys_data_service->getCfgTypeKeyVals($this->type_key_id),
    );
    $hout = $configFilesystem->buildEditPopUpHtml($data, $options);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $hout,
    ));
  }


/**
 * 配置文件参数添加框
 * @return array 添加页面详情
 */
  public function roleCfgAddHtmlCodeAction(){
    $data = array(
      'type_id' => -10,
      'parent_type_id' => $this->parent_type_id,
      'type_key_id' => -1,
      'type_val_id' => -1,
      'cfg_id' => $this->cfg_id,
      'params_parent_id' => $this->params_parent_id,
      'key_val' => '',
      'val_val' => '',
      'params_id' => $this->params_id,
    );
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
    // 设置type类型
    $configFilesystem->setTypeObject($cfg_type);
    $options = array(
      'types' => $this->sys_data_service->getcfgTypeList(),
      'keys' => array(),
      'vals' => array(),
    );
    $hout = $configFilesystem->buildAddPopUpHtml($data, $options);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $hout,
    ));
  }

/**
 * 更新配置参数节点
 * @return array 更新结果
 */
  public function roleCfgNodeUpdateAction(){
    if($this->type_id != -11 && $this->type_id != -10){
      $ids = $this->sys_data_service->cfgFindNodeDepth($this->cfg_param_id, $this->cfg_id);
      $this->sys_data_service->cfgParamsdeleteByIds($ids);
    }
    $result = $this->sys_data_service->updateCfgNode(array(
      'cfg_key' => $this->cfg_key,
      'cfg_val' => $this->cfg_val,
      'type_id' => $this->type_id,
      'type_key_id' => $this->type_key_id,
      'type_val_id' =>$this->type_val_id,
      'cfg_val_type' => $this->cfg_val_type,
    ), array(
      'id' => $this->cfg_param_id,
      'cfg_id' => $this->cfg_id,
    ));
    if($result === TRUE){
// 更新修改状态
      $this->sys_data_service->updateRoleCfgFileStatus($this->cfg_id);
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '更新成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '更新失败',
    ));
  }

/**
 * 删除配置参数节点
 * @return array 删除结果
 */
  public function roleCfgNodeDeleteAction(){
    $ids = $this->sys_data_service->cfgFindNodeDepth($this->cfg_param_id, $this->cfg_id);
    array_push($ids, $this->cfg_param_id);
    $result = $this->sys_data_service->cfgParamsdeleteByIds($ids);
    if($result === TRUE){
// 更新修改状态
      $this->sys_data_service->updateRoleCfgFileStatus($this->cfg_id);
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
    ));
  }

/**
 * 添加参数类型
 * @return array 添加结果
 */
  public function cfgTypeAddAction(){
    $result = $this->sys_data_service->addcfgType(array(
      'type_name' => $this->request->getPost("type_name"),
      'remark' => $this->request->getPost("remark"),
    ));
    if($result === FALSE){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '添加失败',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
      'data' => $result
    ));
  }

/**
 * 添加参数类型keys
 * @return array 添加结果
 */
  public function cfgTypeKeyAddAction(){
    $result = $this->sys_data_service->addcfgTypeKey(array(
      'type_id' => $this->request->getPost("type_id"),
      'type_key' => $this->request->getPost("type_key"),
      'key_change_type' => $this->request->getPost("key_change_type"),
      'val_change_type' => $this->request->getPost("val_change_type"),
      'desc' => $this->request->getPost("desc"),
    ));
    if(isset($result['error'])){
      return $this->jsonResponse($result);
    }
    if($result === FALSE){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '添加失败',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
      'data' => $result
    ));
  }

  /**
   * 添加参数类型keyval
   * @return array 添加结果
   */
  public function  cfgTypeKeyValsAddAction(){
    $result = $this->sys_data_service->addcfgTypeKeyVal(array(
      'type_id' => $this->request->getPost("type_id"),
      'type_key_id' => $this->request->getPost("type_key_id"),
      'type_key_val' => $this->request->getPost("type_key_val"),
      'cfg_val_type' => $this->request->getPost("cfg_val_type"),
    ));
    if(isset($result['error'])){
      return $this->jsonResponse($result);
    }
    if($result === FALSE){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '添加失败',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '添加成功',
      'data' => $result
    ));
  }

/**
 * type编辑
 * @return array 修改结果
 */
  public function cfgTypeEditAction(){
    $result = $this->sys_data_service->updatecfgType(array(
      'type_name' => $this->type_name,
      'remark' => $this->remark,
    ),
    array(
      'id' => $this->type_id,
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '编辑成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '编辑失败',
      'data' => $result
    ));
  }

  /**
   * type key编辑
   * @return array 修改结果
   */
  public function cfgTypeKeyEditAction(){
    $result = $this->sys_data_service->updatecfgTypeKey(array(
      'type_key' => $this->type_key,
      'key_change_type' => $this->key_change_type,
      'val_change_type' => $this->val_change_type,
      'desc' => $this->desc,
    ),
    array(
      'id' => $this->key_id,
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '编辑成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '编辑失败',
      'data' => $result
    ));
  }

/**
 * type key val编辑
 * @return array 修改结果
 */
  public function cfgTypeKeyValsEditAction(){
    $result = $this->sys_data_service->updatecfgTypeKeyVal(array(
      'type_key_val' => $this->type_key_val,
      'cfg_val_type' => $this->cfg_val_type,
    ),
    array(
      'id' => $this->val_id,
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '编辑成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '编辑失败',
      'data' => $result
    ));
  }

/**
 * type删除
 * @return array 删除结果
 */
  public function cfgTypeDeleteByIdsAction(){
    $type_ids = json_decode($this->request->getPost('type_ids'), true);
    $result = $this->sys_data_service->deletecfgTypeByIds($type_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
      'data' => $result
    ));
  }

/**
 * type key删除
 * @return array 删除结果
 */
  public function cfgTypeKeyDeleteByIdsAction(){
    $key_ids = json_decode($this->request->getPost('key_ids'), true);
    $result = $this->sys_data_service->deletecfgTypeKeyByKeyIds($key_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
      'data' => $result
    ));
  }

/**
 * type key val删除
 * @return array 删除结果
 */
  public function cfgTypeKeyValsDeleteByIdsAction(){
    $val_ids = json_decode($this->request->getPost('val_ids'), true);
    $result = $this->sys_data_service->deletecfgTypeKeyValByIds($val_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '编辑成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '编辑失败',
      'data' => $result
    ));
  }

/**
 * 生成版本文件
 * @return array 生成结果
 */
  public function roleCfgFileBuildAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    if($info['change_status'] == 1){
      return $this->jsonResponse(array(
        'error' => '4002',
        'error_description' => '文件没有被修改',
      ));
    }
    $cfg_type = isset($info['cfg_type']) ? $info['cfg_type'] : '';
    $configFilesystem = new \Ansible\classes\configFilesystem();
    if(!$configFilesystem->hasTypeObject($cfg_type)){
      return $this->jsonResponse(array(
        'error' => '4005',
        'error_description' => '该配置文件的类型不存在，无法解析',
      ));
    }
//更新版本
    $this->sys_data_service->updateRoleCfgFileVersion($this->cfg_id);
    $version = intval($info['cfg_last_version']) + 1;
    $file_name = "/cfg-" . $info['id'] . "/" . $info['file_name'] . '.' . $version;
//设置配置文件类型
    $configFilesystem->setTypeObject($cfg_type);
//获取配置文件数据库数据
    $Params = $this->sys_data_service->getCfgParams($this->cfg_id);
//数据库数据转换为需要的数据
    $this->sys_data_service->insertCfgNewVersion($Params, $version);
    $data = $configFilesystem->tableDataToConfigData($Params);
//设置依赖数据
    $configFilesystem->setConfigData($data);
//将数据生成到文件
    $path = '/opt/ansible/cfg_files';
    $mode = 644;
    $config = $this->di->get('config');
    if(isset($config->syscfgfiles)){
      if(isset($config->syscfgfiles->path))
        $path = $config->syscfgfiles->path;
      if(isset($config->syscfgfiles->mode))
        $mode = $config->syscfgfiles->mode;
    }
    $path = $path . $file_name;
    $configFilesystem->buileConfigFile($path);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '生成成功',
    ));
  }

/**
 * 获取服务器配置文件参数
 * @return array 查询结果
 */
  public function getCfgServerListAction(){
    $info = $this->sys_data_service->roleCfgServerList($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $info,
    ));
  }

/**
 * 删除配置文件
 * @return array 查询结果
 */
  public function roleCfgFileDeleteAction(){
    $cfg_ids = json_decode($this->request->getPost('cfg_ids'), true);
    $result = $this->sys_data_service->deleteCfgFile($cfg_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
      'data' => $result
    ));
  }

/**
 * 获取服务器配置文件部署
 * @return array 部署结果
 */
  public function deployCfgFileAction(){
    $info = $this->sys_data_service->getCfgFileInfo($this->cfg_id);
    if(!$info)
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    $filename = isset($info['file_name']) ? $info['file_name'] : '';
    $file_path = isset($info['file_path']) ? $info['file_path'] : '';
    $mode = isset($info['file_chmod']) ? $info['file_chmod'] : '';
    $owner = isset($info['file_owner']) ? $info['file_owner'] : '';
    $group = isset($info['file_group']) ? $info['file_group'] : '';
// 本地文件
    $path = '/opt/ansible/cfg_files';
    $config = $this->di->get('config');
    if(isset($config->syscfgfiles)){
      if(isset($config->syscfgfiles->path))
        $path = $config->syscfgfiles->path;
    }
    $oldfile = $path . "/cfg-" . $this->cfg_id . "/" . $filename . "." . $this->cfg_version;
    $newfile = $file_path . "/" . $filename;
    if(!is_file($oldfile)){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '该配置文件不存在',
      ));
    }
    $Ansiblesystem = new \Ansible\classes\Ansiblesystem();
    $this->sys_data_service->roleCfgServerVersion($this->server_id, $this->cfg_id, $this->cfg_version);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => $Ansiblesystem->sendFile($this->ip, $oldfile,  $newfile, $mode, $owner, $group)
    ));
  }
/**
 * 获取指令列表
 * @return array 配置文件列表
 */
  public function getCommandListAction(){
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'list' => $this->sys_data_service->getappCommandList($this->app_id),
      )
    ));
  }

/**
 * 添加应用指令
 */
  public function addAppCommandAction(){
    $result = $this->sys_data_service->addAppCommand(array(
      'app_id' => $this->app_id,
      'command_desc' => $this->command_desc,
      'command_name' => $this->command_name,
      'command_chdir' => $this->command_chdir,
      'command_chdir_change' => $this->command_chdir_change,
      'command_params' => $this->command_params,
      'command_params_change' => $this->command_params_change,
      'command_chown' => $this->command_chown,
      'command_chown_change' => $this->command_chown_change,
    ));
    if($result !== FALSE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '添加成功',
        'data' => $result
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '添加失败',
    ));
  }

/**
 * 删除应用指令
 * @return array 删除结果
 */
  public function deleteAppCommandAction(){
    $command_ids = json_decode($this->request->getPost('command_ids'), true);
    $result = $this->sys_data_service->delAppCommand($command_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
    ));
  }

/**
 * 更新应用指令
 * @return array 更新结果
 */
  public function updateAppCommandAction(){
    $result = $this->sys_data_service->updateAppCommand(array(
      'command_desc' => $this->command_desc,
      'command_name' => $this->command_name,
      'command_chdir' => $this->command_chdir,
      'command_chdir_change' => $this->command_chdir_change,
      'command_params' => $this->command_params,
      'command_params_change' => $this->command_params_change,
      'command_chown' => $this->command_chown,
      'command_chown_change' => $this->command_chown_change,
    ), array(
      'id' => $this->id
    ));
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '更新成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '更新失败',
    ));
  }

/**
 * 角色指令列表
 * @return array 列表结果
 */
  public function roleAppCommandListAction(){
    $result = $this->sys_data_service->roleAppCommandList($this->role_id);
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'list' => $result,
      )
    ));
  }

/**
 * 角色指令可帮列表
 * @return array 搜索接口
 */
  public function roleAppCommandAvalibleListAction(){
    $info = $this->sys_data_service->infoofflineRoleApp(
      array(
        'role_id' => $this->role_id
      )
    );
    if(!$info){
      return $this->jsonResponse(array(
        'error' => '4004',
        'error_description' => '还未绑定应用，请先绑定应用',
      ));
    }
    $app_ids = array();
    foreach ($info as $x) {
      if(isset($x['app_id']) && $x['app_id'])
        array_push($app_ids, $x['app_id']);
    }
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => array(
        'list' => $this->sys_data_service->roleAppCommandAvalibleList($app_ids)
      )
    ));
  }

/**
 * 角色指令添加
 */
  public function roleAppCommandaddAction(){
    $result = $this->sys_data_service->roleAppCommandadd(array(
      'role_id' => $this->role_id,
      'app_id' => $this->app_id,
      'command_desc' => $this->command_desc,
      'command_name' => $this->command_name,
      'command_chdir' => $this->command_chdir,
      'command_params' => $this->command_params,
      'command_chown' => $this->command_chown,
    ));
    if($result !== FALSE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '添加成功',
        'data' => $result
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '添加失败',
    ));
  }

/**
 * 角色指令删除
 * @return array 删除结果
 */
  public function roleAppCommanddelAction(){
    $command_ids = json_decode($this->request->getPost('command_ids'), true);
    $result = $this->sys_data_service->roleAppCommanddel($command_ids);
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '删除成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '删除失败',
    ));
  }

/**
 * 角色指令更新
 * @return array 更新结果
 */
  public function roleAppCommandUpdateAction(){
    $result = $this->sys_data_service->roleAppCommandupdate(array(
      'command_desc' => $this->command_desc,
      'command_chdir' => $this->command_chdir,
      'command_params' => $this->command_params,
      'command_chown' => $this->command_chown,
    ),
    array(
      'id' => $this->id
    )
    );
    if($result === TRUE){
      return $this->jsonResponse(array(
        'error' => '4000',
        'error_description' => '更新成功',
      ));
    }
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '更新失败',
    ));
  }

/**
 * 获取单一指令信息
 * @return array 指令信息
 */
public function roleAppCommandInfoAction(){
  $result = $this->sys_data_service->roleAppCommandInfo($this->id);
  return $this->jsonResponse(array(
    'error' => '4000',
    'error_description' => '获取成功',
    'data' => isset($result['0']) ? $result['0'] : array(),
  ));
}

/**
 * 角色指令部署信息
 * @return array 查询结果
 */
public function roleAppCommandDeployAction(){
  $result = $this->sys_data_service->roleAppCommandInfo($this->id);
  $commandinfo = isset($result['0']) ? $result['0'] : array();
  $role_id = isset($commandinfo['role_id']) ? $commandinfo['role_id'] : '';
  $command_name = isset($commandinfo['command_name']) ? $commandinfo['command_name'] : '';
  $appCommand = new \Ansible\classes\appCommand();
  if(!$appCommand->hasCommandObject($command_name)){
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '指令类型不存在',
    ));
  }
  $appCommand->setCommandObject($command_name);
  $commandlist = $appCommand->commandList();
  $info = $this->sys_data_service->roleAppCommandServers($this->id);
  return $this->jsonResponse(array(
    'error' => '4000',
    'error_description' => '获取成功',
    'data' => array(
      'commandlist' => $commandlist,
      'servers' => $info,
      'commandinfo' => $commandinfo,
      'history' => $this->sys_data_service->roleAppCommandTop($this->id),
    ),
  ));

}

public function roleAppCommandDeploySendAction(){
  $result = $this->sys_data_service->roleAppCommandInfo($this->id);
  $commandinfo = isset($result['0']) ? $result['0'] : array();
  $role_id = isset($commandinfo['role_id']) ? $commandinfo['role_id'] : '';
  $command_name = isset($commandinfo['command_name']) ? $commandinfo['command_name'] : '';
  $appCommand = new \Ansible\classes\appCommand();
  if(!$appCommand->hasCommandObject($command_name)){
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '指令类型不存在',
    ));
  }
  $chdir = isset($commandinfo['command_chdir']) ? $commandinfo['command_chdir'] : '/tmp';
  $params = isset($commandinfo['command_params']) ? $commandinfo['command_params'] : '';
  $chown = isset($commandinfo['command_chown']) ? $commandinfo['command_chown'] : 'root';

  $appCommand->setCommandObject($command_name);
  $content = $appCommand->runCommand($this->sort_id, $this->ip, $chdir, $params, $chown);
  $command_content = $command_name . " " . $appCommand->getCommandString($this->sort_id);
  if($content === FALSE)
    return $this->jsonResponse(array(
      'error' => '4004',
      'error_description' => '分发失败',
    ));
  $this->sys_data_service->roleAppCommandServersUpdate(array(
    "server_id" => $this->server_id,
    "command_id" => $this->id,
    "command_content" => $command_content,
  ));
  $this->sys_data_service->roleAppCommandHistoryAdd(array(
    'command_id' => $this->id,
    'ip' => $this->ip,
    'command_content' => $command_content,
    'command_result' => str_replace("\n", "<br>", $content),
  ));
  return $this->jsonResponse(array(
    'error' => '4000',
    'error_description' => '分发成功',
    'data' => array(
      'info' => str_replace("\n", "<br>", $content),
      "command_content" => $command_content,
      'ip' => $this->ip,
      'time' => date("Y-m-d H:i:s", time())
    )
  ));
}

/**
 * 命令列表
 * @return array 命令列表
 */
  public function getCommandTypeListAction(){
    $appCommand = new \Ansible\classes\appCommand();
    return $this->jsonResponse(array(
      'error' => '4000',
      'error_description' => '获取成功',
      'data' => $appCommand->getCommandMaping(),
    ));
  }

}
