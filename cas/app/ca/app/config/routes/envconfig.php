<?php

$router->add('/envconfig/offline/server/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerList',
))->setName('envconfig_offlineServerList');

$router->add('/envconfig/offline/server/insert', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerInsert',
))->setName('envconfig_offlineServerInsert');

$router->add('/envconfig/offline/server/del', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerDel',
))->setName('envconfig_offlineServerDel');

$router->add('/envconfig/offline/server/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerEdit',
))->setName('envconfig_offlineServerEdit');

$router->add('/envconfig/offline/server/edithard', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerHardEdit',
))->setName('envconfig_offlineServerHardEdit');

$router->add('/envconfig/offline/server/info', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerInfo',
))->setName('envconfig_offlineServerInfo');

$router->add('/envconfig/offline/role/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleList',
))->setName('envconfig_offlineRoleList');

$router->add('/envconfig/offline/role/insert', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleInsert',
))->setName('envconfig_offlineRoleInsert');

$router->add('/envconfig/offline/role/del', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleDel',
))->setName('envconfig_offlineRoleDel');

$router->add('/envconfig/offline/role/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleEdit',
))->setName('envconfig_offlineRoleEdit');

$router->add('/envconfig/offline/role/info', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleInfo',
))->setName('envconfig_offlineRoleInfo');

$router->add('/envconfig/offline/role/app/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleApplicationAdd',
))->setName('envconfig_offlineRoleApplicationAdd');

$router->add('/envconfig/offline/role/app/delbyid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleAppbyIdsDel',
))->setName('envconfig_offlineRoleAppbyIdsDel');

$router->add('/envconfig/offline/role/app/delbyroleid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleAppbyRoleidDel',
))->setName('envconfig_offlineRoleAppbyRoleidDel');

$router->add('/envconfig/offline/role/app/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleAppEdit',
))->setName('envconfig_offlineRoleAppEdit');

$router->add('/envconfig/offline/role/app/info', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleAppInfo',
))->setName('envconfig_offlineRoleAppInfo');

$router->add('/envconfig/offline/role/server/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerAdd',
))->setName('envconfig_offlineRoleServerAdd');

$router->add('/envconfig/offline/role/server/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerEdit',
))->setName('envconfig_offlineRoleServerEdit');

$router->add('/envconfig/offline/role/server/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerList',
))->setName('envconfig_offlineRoleServerList');

$router->add('/envconfig/offline/role/server/delbyid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerDelbyIds',
))->setName('envconfig_offlineRoleServerDelbyIds');

$router->add('/envconfig/offline/role/server/delbyroleid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerDelbyRoleIds',
))->setName('envconfig_offlineRoleServerDelbyRoleIds');

$router->add('/envconfig/offline/role/server/delbyserverids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineRoleServerDelbyServerids',
))->setName('envconfig_offlineRoleServerDelbyServerids');

$router->add('/envconfig/offline/server/info/fromip', array(
    'controller'  =>  'envconfig',
    'action'      =>  'offlineServerInfoFromIp',
))->setName('envconfig_offlineServerInfoFromIp');

$router->add('/envconfig/offline/app/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appList',
))->setName('envconfig_appList');

$router->add('/envconfig/offline/app/insert', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appInsert',
))->setName('envconfig_appInsert');

$router->add('/envconfig/offline/app/delete', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appDelbyIds',
))->setName('envconfig_appDelbyIds');

$router->add('/envconfig/offline/app/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appEdit',
))->setName('envconfig_appEdit');

$router->add('/envconfig/offline/app/port/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appPortList',
))->setName('envconfig_appPortList');

$router->add('/envconfig/offline/app/port/delbyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appPortDelById',
))->setName('envconfig_appPortDelById');

$router->add('/envconfig/offline/app/port/delbyappids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appPortDelByAppId',
))->setName('envconfig_appPortDelByAppId');

$router->add('/envconfig/offline/app/port/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appPortUpdate',
))->setName('envconfig_appPortUpdate');

$router->add('/envconfig/offline/app/port/insert', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appPortInsert',
))->setName('envconfig_appPortInsert');

$router->add('/envconfig/offline/app/cfg/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appCfgList',
))->setName('envconfig_appCfgList');

$router->add('/envconfig/offline/app/cfg/insert', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appCfgAdd',
))->setName('envconfig_appCfgAdd');

$router->add('/envconfig/offline/app/cfg/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appCfgUpdate',
))->setName('envconfig_appCfgUpdate');

$router->add('/envconfig/offline/cfg/type/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'configTypelist',
))->setName('envconfig_configTypelist');

$router->add('/envconfig/offline/app/cfg/delbyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appCfgDelbyIds',
))->setName('envconfig_appCfgDelbyIds');

$router->add('/envconfig/offline/app/cfg/delbyappids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'appCfgDelbyAppids',
))->setName('envconfig_appCfgDelbyAppids');

$router->add('/envconfig/offline/role/port/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortList',
))->setName('envconfig_roleAppPortList');

$router->add('/envconfig/offline/role/port/bind', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortInsert',
))->setName('envconfig_roleAppPortInsert');

$router->add('/envconfig/offline/role/port/deletebyid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortDeleteByIds',
))->setName('envconfig_roleAppPortDeleteByIds');

$router->add('/envconfig/offline/role/port/deletebyroleid', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortDeleteByRoleids',
))->setName('envconfig_roleAppPortDeleteByRoleids');

$router->add('/envconfig/offline/role/port/bindlist', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortBindList',
))->setName('envconfig_roleAppPortBindList');

$router->add('/envconfig/offline/role/port/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppPortupdate',
))->setName('envconfig_roleAppPortupdate');

$router->add('/envconfig/offline/role/cfg/build', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCfgBuild',
))->setName('envconfig_roleAppCfgBuild');

$router->add('/envconfig/offline/role/cfg/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgList',
))->setName('envconfig_roleCfgList');

$router->add('/envconfig/offline/role/cfg/detailed', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgFileDetailed',
))->setName('envconfig_roleCfgFileDetailed');

$router->add('/envconfig/offline/role/cfg/buildedithtml', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgHtmlCode',
))->setName('envconfig_roleCfgHtmlCode');

$router->add('/envconfig/offline/role/cfg/buildpreviewhtml', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgHtmlPreviewCode',
))->setName('envconfig_roleCfgHtmlPreviewCode');

$router->add('/envconfig/offline/role/cfg/param/addnode', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgParamsAddNode',
))->setName('envconfig_roleCfgParamsAddNode');

$router->add('/envconfig/offline/role/cfg/type/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeList',
))->setName('envconfig_cfgTypeList');

$router->add('/envconfig/offline/role/cfg/type/keys', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeys',
))->setName('envconfig_cfgTypeKeys');

$router->add('/envconfig/offline/role/cfg/type/keyvals', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyVals',
))->setName('envconfig_cfgTypeKeyVals');

$router->add('/envconfig/offline/role/cfg/param/edithtmlcode', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgEditHtmlCode',
))->setName('envconfig_roleCfgEditHtmlCode');

$router->add('/envconfig/offline/role/cfg/param/addhtmlcode', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgAddHtmlCode',
))->setName('envconfig_roleCfgAddHtmlCode');

$router->add('/envconfig/offline/role/cfg/param/nodeupdate', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgNodeUpdate',
))->setName('envconfig_roleCfgNodeUpdate');

$router->add('/envconfig/offline/role/cfg/param/nodedelete', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgNodeDelete',
))->setName('envconfig_roleCfgNodeDelete');

$router->add('/envconfig/offline/params/type/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeAdd',
))->setName('envconfig_cfgTypeAdd');

$router->add('/envconfig/offline/params/key/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyAdd',
))->setName('envconfig_cfgTypeKeyAdd');

$router->add('/envconfig/offline/params/val/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyValsAdd',
))->setName('envconfig_cfgTypeKeyValsAdd');

$router->add('/envconfig/offline/params/type/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeEdit',
))->setName('envconfig_cfgTypeEdit');

$router->add('/envconfig/offline/params/key/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyEdit',
))->setName('envconfig_cfgTypeKeyEdit');

$router->add('/envconfig/offline/params/val/edit', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyValsEdit',
))->setName('envconfig_cfgTypeKeyValsEdit');

$router->add('/envconfig/offline/params/type/deletebyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeDeleteByIds',
))->setName('envconfig_cfgTypeDeleteByIds');

$router->add('/envconfig/offline/params/key/deletebyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyDeleteByIds',
))->setName('envconfig_cfgTypeKeyDeleteByIds');

$router->add('/envconfig/offline/params/val/deletebyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'cfgTypeKeyValsDeleteByIds',
))->setName('envconfig_cfgTypeKeyValsDeleteByIds');

$router->add('/envconfig/offline/role/cfg/usablelist', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgUsableList',
))->setName('envconfig_roleCfgUsableList');

$router->add('/envconfig/offline/role/cfg/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgFileAdd',
))->setName('envconfig_roleCfgFileAdd');

$router->add('/envconfig/offline/role/cfg/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgFileUpdate',
))->setName('envconfig_roleCfgFileUpdate');

$router->add('/envconfig/offline/role/cfg/build', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgFileBuild',
))->setName('envconfig_roleCfgFileBuild');

$router->add('/envconfig/offline/role/cfg/server/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'getCfgServerList',
))->setName('envconfig_getCfgServerList');

$router->add('/envconfig/offline/cfg/send', array(
    'controller'  =>  'envconfig',
    'action'      =>  'deployCfgFile',
))->setName('envconfig_deployCfgFile');

$router->add('/envconfig/offline/cfg/deletebyids', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleCfgFileDelete',
))->setName('envconfig_roleCfgFileDelete');

$router->add('/envconfig/offline/app/command/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'getCommandList',
))->setName('envconfig_getCommandList');

$router->add('/envconfig/offline/app/command/type/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'getCommandTypeList',
))->setName('envconfig_getCommandTypeList');

$router->add('/envconfig/offline/app/command/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'addAppCommand',
))->setName('envconfig_addAppCommand');

$router->add('/envconfig/offline/app/command/del', array(
    'controller'  =>  'envconfig',
    'action'      =>  'deleteAppCommand',
))->setName('envconfig_deleteAppCommand');

$router->add('/envconfig/offline/app/command/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'updateAppCommand',
))->setName('envconfig_updateAppCommand');

$router->add('/envconfig/offline/role/app/command/list', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandList',
))->setName('envconfig_roleAppCommandList');

$router->add('/envconfig/offline/role/app/command/add', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandadd',
))->setName('envconfig_roleAppCommandadd');

$router->add('/envconfig/offline/role/app/command/del', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommanddel',
))->setName('envconfig_roleAppCommanddel');

$router->add('/envconfig/offline/role/app/command/update', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandUpdate',
))->setName('envconfig_roleAppCommandUpdate');

$router->add('/envconfig/offline/role/app/command/avaliblelist', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandAvalibleList',
))->setName('envconfig_roleAppCommandAvalibleList');

$router->add('/envconfig/offline/role/app/command/info', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandInfo',
))->setName('envconfig_roleAppCommandInfo');

$router->add('/envconfig/offline/role/app/command/deploy', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandDeploy',
))->setName('envconfig_roleAppCommandDeploy');

$router->add('/envconfig/offline/role/app/command/deploysend', array(
    'controller'  =>  'envconfig',
    'action'      =>  'roleAppCommandDeploySend',
))->setName('envconfig_roleAppCommandDeploySend');
