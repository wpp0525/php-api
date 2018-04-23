<?php

/**
 *  SEO相关接口
 *
 * @author dirc.wang
 */
$parameter['envconfig'] = array(
  'offlineServerList' => array(
      'curpage' => array(
          'input' => 'curpage',
          'default' => 1,
          'rule' => '^\d+$',
          'required' => true,
      ),
  ),
  'offlineServerInsert' => array(
    'ip' => array(
        'input' => 'ip',
        'default' => '',
        'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
        'required' => true,
    ),
    'cpu' => array(
      'input' => 'cpu',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'mem' => array(
      'input' => 'mem',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'disk' => array(
      'input' => 'disk',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'is_virtual' => array(
      'input' => 'is_virtual',
      'default' => 0,
      'rule' => '^(0|1)$',
      'required' => false,
    ),
    'host_ip' => array(
      'input' => 'host_ip',
      'default' => '',
      'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'status' => array(
      'input' => 'status',
      'default' => 0,
      'rule' => '^(0|1)$',
      'required' => false,
    )
  ),
  'offlineServerHardEdit' => array(
    'ip' => array(
        'input' => 'ip',
        'default' => '',
        'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
        'required' => true,
    ),
    'mem' => array(
      'input' => 'mem',
      'default' => '^[0-9]{1,10}(\.[0-9]{0,5}){0,1}$',
      'rule' => '',
      'required' => false,
    ),
    'mem_used' => array(
      'input' => 'mem_used',
      'default' => '^[0-9]{1,10}(\.[0-9]{0,5}){0,1}$',
      'rule' => '',
      'required' => false,
    ),
    'disk' => array(
      'input' => 'disk',
      'default' => '^[0-9]{1,10}(\.[0-9]{0,5}){0,1}$',
      'rule' => '',
      'required' => false,
    ),
    'disk_used' => array(
      'input' => 'disk_used',
      'default' => '^[0-9]{1,10}(\.[0-9]{0,5}){0,1}$',
      'rule' => '',
      'required' => false,
    ),
  ),
  'offlineServerEdit' => array(
    'id' => array(
        'input' => 'id',
        'default' => '',
        'rule' => '^\d+$',
        'required' => true,
    ),
    'cpu' => array(
      'input' => 'cpu',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'mem' => array(
      'input' => 'mem',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'disk' => array(
      'input' => 'disk',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'is_virtual' => array(
      'input' => 'is_virtual',
      'default' => 0,
      'rule' => '^(0|1)$',
      'required' => false,
    ),
    'host_ip' => array(
      'input' => 'host_ip',
      'default' => '',
      'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'status' => array(
      'input' => 'status',
      'default' => 0,
      'rule' => '^(0|1)$',
      'required' => false,
    )
  ),
  'offlineServerInfo' => array(
    'id' => array(
      'input' => 'id',
      'default' => '0',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  //offlineRole
  'offlineRoleList' => array(
    'curpage' => array(
        'input' => 'curpage',
        'default' => 1,
        'rule' => '^\d+$',
        'required' => true,
    ),
  ),
  'offlineRoleInsert' => array(
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'type' => array(
      'input' => 'type',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'offlineRoleEdit' => array(
    'id' => array(
      'input' => 'id',
      'default' => '0',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'type' => array(
      'input' => 'type',
      'default' => '1',
      'rule' => '^\d+$',
      'required' => false,
    ),
  ),
  'offlineRoleInfo' => array(
    'id' => array(
      'input' => 'id',
      'default' => '0',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'offlineRoleApplicationAdd' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    ),
    'app_id' => array(
      'input' => 'app_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'offlineRoleAppEdit' => array(
    'id' => array(
      'input' => 'id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'offlineRoleAppInfo' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    )
  ),
  'offlineRoleServerAdd' => array(
    'remark' => array(
      'input' => 'remark',
      'default' => 0,
      'rule' => '',
      'required' => false,
    ),
    'role_id' => array(
      'input' => 'role_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    ),
    'server_id' => array(
      'input' => 'server_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    )
  ),
  'offlineRoleServerEdit' => array(
    'remark' => array(
      'input' => 'remark',
      'default' => 0,
      'rule' => '',
      'required' => false,
    ),
    'id' => array(
      'input' => 'id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    )
  ),
  'offlineRoleServerList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => 0,
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'offlineServerInfoFromIp' => array(
    'ip' => array(
        'input' => 'ip',
        'default' => '',
        'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
        'required' => true,
    ),
  ),
  'appList' => array(
    'curpage' => array(
      'input' => 'curpage',
      'default' => '1',
      'rule' => '^\d+$',
      'required' => false,
    ),
    'inonesum' => array(
      'input' => 'inonesum',
      'default' => '100',
      'rule' => '^\d+$',
      'required' => false,
    ),
  ),
  'appInsert' => array(
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'appEdit' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'appPortList' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'appPortInsert' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'server_port' => array(
      'input' => 'server_port',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'appPortUpdate' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'appCfgList' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'appCfgAdd' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_type' => array(
      'input' => 'cfg_type',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_name' => array(
      'input' => 'file_name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'name_change_type' => array(
      'input' => 'name_change_type',
      'default' => '',
      'rule' => '^(1|2)$',
      'required' => true,
    ),
    'file_path' => array(
      'input' => 'file_path',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'path_change_type' => array(
      'input' => 'path_change_type',
      'default' => '',
      'rule' => '^(1|2)$',
      'required' => true,
    ),
    'file_chmod' => array(
      'input' => 'file_chmod',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_owner' => array(
      'input' => 'file_owner',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_group' => array(
      'input' => 'file_group',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
  ),
  'appCfgUpdate' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_type' => array(
      'input' => 'cfg_type',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_name' => array(
      'input' => 'file_name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'name_change_type' => array(
      'input' => 'name_change_type',
      'default' => '',
      'rule' => '^(1|2)$',
      'required' => true,
    ),
    'file_path' => array(
      'input' => 'file_path',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'path_change_type' => array(
      'input' => 'path_change_type',
      'default' => '',
      'rule' => '^(1|2)$',
      'required' => true,
    ),
    'file_chmod' => array(
      'input' => 'file_chmod',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_owner' => array(
      'input' => 'file_owner',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_group' => array(
      'input' => 'file_group',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
  ),
  'roleAppPortList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppPortInsert' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'app_port_id' => array(
      'input' => 'app_port_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppPortBindList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppPortupdate' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'roleAppCfgBuild' => array(),
  'roleCfgList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'cpage' => array(
      'input' => 'cpage',
      'default' => '1',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'inonepage' => array(
      'input' => 'inonepage',
      'default' => '40',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleCfgFileDetailed' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleCfgHtmlCode' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleCfgHtmlPreviewCode' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'cfg_version' => array(
      'input' => 'cfg_version',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleCfgParamsAddNode' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'cfg_key' => array(
      'input' => 'cfg_key',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_val' => array(
      'input' => 'cfg_val',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'params_id' => array(
      'input' => 'params_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_key_id' => array(
      'input' => 'type_key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_val_id' => array(
      'input' => 'type_val_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_val_type' => array(
      'input' => 'cfg_val_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => false,
    ),
  ),
  'cfgTypeKeys' => array(
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'cfgTypeKeyVals' => array(
    'type_key_id' => array(
      'input' => 'type_key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'roleCfgEditHtmlCode' => array(
    'type_key_id' => array(
      'input' => 'type_key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_val_id' => array(
      'input' => 'type_val_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'params_parent_id' => array(
      'input' => 'params_parent_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'parent_type_id' => array(
      'input' => 'parent_type_id',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'key_val' => array(
      'input' => 'key_val',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'val_val' => array(
      'input' => 'val_val',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'params_id' => array(
      'input' => 'params_id',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_val_type' => array(
      'input' => 'cfg_val_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => false,
    ),
  ),
  'roleCfgNodeUpdate' => array(
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_param_id' => array(
      'input' => 'cfg_param_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_key' => array(
      'input' => 'cfg_key',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_val' => array(
      'input' => 'cfg_val',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'type_key_id' => array(
      'input' => 'type_key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_val_id' => array(
      'input' => 'type_val_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_val_type' => array(
      'input' => 'cfg_val_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => false,
    ),
  ),
  'roleCfgNodeDelete' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_param_id' => array(
      'input' => 'cfg_param_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'roleCfgAddHtmlCode' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'params_parent_id' => array(
      'input' => 'params_parent_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'parent_type_id' => array(
      'input' => 'parent_type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'params_id' => array(
      'input' => 'params_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'cfgTypeList' => array(),
  'cfgTypeAdd' => array(
    'type_name' => array(
      'input' => 'type_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'cfgTypeKeyAdd' => array(
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_key' => array(
      'input' => 'type_key',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'key_change_type' => array(
      'input' => 'key_change_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'val_change_type' => array(
      'input' => 'val_change_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'cfgTypeKeyValsAdd' => array(
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_key_id' => array(
      'input' => 'type_key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'type_key_val' => array(
      'input' => 'type_key_val',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'cfg_val_type' => array(
      'input' => 'cfg_val_type',
      'default' => '1',
      'rule' => '^[-\d]+$',
      'required' => false,
    ),
  ),
  'cfgTypeEdit' => array(
    'type_name' => array(
      'input' => 'type_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'type_id' => array(
      'input' => 'type_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'cfgTypeKeyEdit' => array(
    'type_key' => array(
      'input' => 'type_key',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'key_change_type' => array(
      'input' => 'key_change_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'val_change_type' => array(
      'input' => 'val_change_type',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'desc' => array(
      'input' => 'desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'key_id' => array(
      'input' => 'key_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'cfgTypeKeyValsEdit' => array(
    'type_key_val' => array(
      'input' => 'type_key_val',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'val_id' => array(
      'input' => 'val_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
    'cfg_val_type' => array(
      'input' => 'cfg_val_type',
      'default' => '1',
      'rule' => '^[-\d]+$',
      'required' => false,
    ),
  ),
  'roleCfgUsableList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^[-\d]+$',
      'required' => true,
    ),
  ),
  'roleCfgFileAdd' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'name' => array(
      'input' => 'name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_type' => array(
      'input' => 'cfg_type',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_name' => array(
      'input' => 'file_name',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_path' => array(
      'input' => 'file_path',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_chmod' => array(
      'input' => 'file_chmod',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_owner' => array(
      'input' => 'file_owner',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'file_group' => array(
      'input' => 'file_group',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
  ),
  'roleCfgFileUpdate' => array(
    'file_chmod' => array(
      'input' => 'file_chmod',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'file_owner' => array(
      'input' => 'file_owner',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'file_group' => array(
      'input' => 'file_group',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'remark' => array(
      'input' => 'remark',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => false,
    ),
  ),
  'roleCfgFileBuild' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'getCfgServerList' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'deployCfgFile' => array(
    'cfg_id' => array(
      'input' => 'cfg_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'ip' => array(
        'input' => 'ip',
        'default' => '',
        'rule' => '^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',
        'required' => true,
    ),
    'cfg_version' => array(
      'input' => 'cfg_version',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'server_id' => array(
      'input' => 'server_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleCfgFileDelete' => array(),
  'getCommandList' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'addAppCommand' => array(
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'command_desc' => array(
      'input' => 'command_desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_name' => array(
      'input' => 'command_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir' => array(
      'input' => 'command_chdir',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir_change' => array(
      'input' => 'command_chdir_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params' => array(
      'input' => 'command_params',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params_change' => array(
      'input' => 'command_params_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown' => array(
      'input' => 'command_chown',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown_change' => array(
      'input' => 'command_chown_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'deleteAppCommand' => array(),
  'updateAppCommand' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'command_desc' => array(
      'input' => 'command_desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_name' => array(
      'input' => 'command_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir' => array(
      'input' => 'command_chdir',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir_change' => array(
      'input' => 'command_chdir_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params' => array(
      'input' => 'command_params',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params_change' => array(
      'input' => 'command_params_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown' => array(
      'input' => 'command_chown',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown_change' => array(
      'input' => 'command_chown_change',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'roleAppCommandList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppCommandadd' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'app_id' => array(
      'input' => 'app_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'command_desc' => array(
      'input' => 'command_desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_name' => array(
      'input' => 'command_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir' => array(
      'input' => 'command_chdir',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params' => array(
      'input' => 'command_params',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown' => array(
      'input' => 'command_chown',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'roleAppCommanddel' => array(),
  'roleAppCommandUpdate' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'command_desc' => array(
      'input' => 'command_desc',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_name' => array(
      'input' => 'command_name',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chdir' => array(
      'input' => 'command_chdir',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_params' => array(
      'input' => 'command_params',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
    'command_chown' => array(
      'input' => 'command_chown',
      'default' => '',
      'rule' => '',
      'required' => false,
    ),
  ),
  'roleAppCommandAvalibleList' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppCommandInfo' => array(
    'role_id' => array(
      'input' => 'role_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppCommandDeploy' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
  ),
  'roleAppCommandDeploySend' => array(
    'id' => array(
      'input' => 'id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'sort_id' => array(
      'input' => 'sort_id',
      'default' => '',
      'rule' => '^\d+$',
      'required' => true,
    ),
    'ip' => array(
      'input' => 'ip',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
    'server_id' => array(
      'input' => 'server_id',
      'default' => '',
      'rule' => '',
      'required' => true,
    ),
  )
);
