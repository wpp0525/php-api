<?php

/**
 * 权限控制器
 * 
 * @author flash.guo
 *
 */
class PermissionController extends ControllerBase {
    private $func_base_svc;
    private $staff_base_svc;
    private $staff_role_svc;
    private $role_func_svc;

    private $sys_core;
    public function initialize() {
        parent::initialize();
        $this->func_base_svc = $this->di->get('cas')->get('func_base_service');
        $this->staff_base_svc = $this->di->get('cas')->get('staff_base_service');
        $this->staff_role_svc = $this->di->get('cas')->get('staff_role_service');
        $this->role_func_svc = $this->di->get('cas')->get('role_func_service');

        $this->sys_core = $this->di->get('cas')->get('sct_system_core');
    }
	
	/**
	 * 权限列表
	 */
	public function getAction() {
        $type = intval($this->request->get('auth_type'));
		if ($type == 0) { //角色
			$perms = array();
        	$roleid = intval($this->request->get('auth_id'));
	        if(empty($roleid)) {
	        	$this->_errorResponse(DATA_NOT_FOUND,'角色不存在');
	        	return;
	        }
		    $role_funcs = $this->role_func_svc->getFuncByRoleid($roleid);
	        foreach ($role_funcs as $role_func) {
	        	$perms[$role_func['function_id']] = explode(",", $role_func['permissions']);
	        }
		}
        $this->jsonResponse(array('results' => $perms));
	}
	
	/**
	 * 用户权限列表
	 */
	public function staffAction() {
        $staffid = intval($this->request->get('staffid'));
	    $staff_info = $this->staff_base_svc->getOneById($staffid);
        if(empty($staff_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'用户不存在');
        	return;
        }
	    $staff_roles = $this->staff_role_svc->getRoleByStaffid($staff_info['id']);
		$perms = $roleids = array();
	    foreach ($staff_roles as $staff_role) {
			$roleids[] = $staff_role['role_id'];
	    }
	    $role_funcs = $this->role_func_svc->getFuncByRoleid($roleids);
	    foreach ($role_funcs as $role_func) {
	    	$perms[$role_func['function_key']]['function_key'] = $role_func['function_key'];
	    	$permissions = isset($perms[$role_func['function_key']]['permissions']) ? $perms[$role_func['function_key']]['permissions'] : array();
	    	$perms[$role_func['function_key']]['permissions'] = array_unique(array_merge_recursive(explode(",", $role_func['permissions']), $permissions));
	    }
        $this->jsonResponse(array('results' => $perms));
	}
	
	/**
	 * 权限设置
	 */
	public function authorizeAction() {
		$post = $this->request->getPost();
		$type = intval($post['auth_type']);
		if ($type == 0) { //角色
			$funcs = $funcids = $perms = $permids = array();
	        $roleid = intval($post['auth_id']);
	        if(empty($roleid)) {
	        	$this->_errorResponse(DATA_NOT_FOUND,'角色不存在');
	        	return;
	        }
		    $role_funcs = $this->role_func_svc->getFuncByRoleid($roleid);
	        foreach ($role_funcs as $role_func) {
	        	$funcs[$role_func['id']] = $role_func['function_id'];
	        	$perms[$role_func['function_id']] = $role_func['permissions'];
	        }
	        $post['permissions'] = json_decode($post['permissions'], true);
	        foreach ($post['permissions'] as $permission) {
	        	$funcids[$permission['funcid']] = $permission['funcid'];
	        	$permids[$permission['funcid']] = trim($permids[$permission['funcid']].",".$permission['permissions'], ",");
	        }
	        $result = true;
	        $newfuncids = array_diff($funcids, $funcs);
			$func = array();
	        foreach ($newfuncids as $funcid) {
	        	$func['role_id'] = $roleid;
	        	$func['function_id'] = intval($funcid);
	        	$func['permissions'] = $permids[$funcid];
	        	$func['create_time'] = $func['update_time'] = time();
	        	$func['creator_id'] = intval($post['creator_id']);
	        	$func['creator_name'] = $post['creator_name'];
	        	$func['create_ip'] = $post['create_ip'];
		        $result = $this->role_func_svc->insert($func);
	        }
	        $updfuncids = array_intersect($funcids, $funcs);
			$func = array();
	        foreach ($updfuncids as $funcid) {
	        	if ($perms[$funcid] == $permids[$funcid]) continue;
	        	$id = array_search($funcid, $funcs);
	        	$func['role_id'] = $roleid;
	        	$func['function_id'] = intval($funcid);
	        	$func['permissions'] = $permids[$funcid];
	        	$func['update_time'] = time();
		        $result = $this->role_func_svc->update($id, $func);
	        }
	        $oldfuncids = array_diff($funcs, $funcids);
	        foreach ($oldfuncids as $funcid) {
		        $result = $this->role_func_svc->delFuncByRoleid($roleid, intval($funcid));
	        }
		}
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'权限设置失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

    /**
     * 或许可选择权限的
     */
    public function getFunctionAction(){

        $cdata = $this->sys_core->getDataByConditionSrt('cms_action_controller', '*', " `comment` != `controller` ");
        $adata = $this->sys_core->getDataByConditionSrt('cms_action', '*', " `action_name` != `method` ");

        $return = array();
        $key = 0;
        if(!empty($cdata) && is_array($cdata) && !empty($adata) && is_array($adata)){

            foreach($cdata as $cd){

                $return[$key]['controller'] = $cd;
                foreach($adata as $ad){
                    if($ad['class_name'] == $cd['controller']){
                        if($ad['permission_status'] == 'S'){
                            $init_ad = $ad;
                            $params = json_decode($ad['permission_params'], true);
                            foreach($params as $temp_p){
                                $temp_a = $init_ad;
//                            var_dump($temp_p); die;
                                $afterid = implode('-',array_values($temp_p['parameters']));
                                $temp_a['id'] = $temp_a['id'].'-'.$afterid;
                                $temp_a['action_name'] = $temp_p['permissionName'].'('.$temp_a['action_name'].')';

                                $return[$key]['method'][] = $temp_a;
                                unset($temp_a);
                            }
                        }else{
                            $return[$key]['method'][] = $ad;
                        }
                    }
                }
                $key++;
            }

        }

        $this->jsonResponse(array('result' => $return));
    }

    public function getRolePermissionAction(){

        $roleId = intval($this->request->getPost('role_id'));

        $cdata = $this->sys_core->getDataByConditionSrt('cms_role_controller', 'controller_id, controller', " `role_id` = '{$roleId}' ");

        $adata = $this->sys_core->getDataByConditionSrt('cms_role_permission', 'action_id, action_method, controller_id, controller_name, params_permission', " `role_id` = '{$roleId}' ");

        $cdata = $cdata && is_array($cdata) ? $cdata : array();
        $adata = $adata && is_array($adata) ? $adata : array();

        $this->jsonResponse(array('controller' => $cdata, 'method' => $adata));
    }


    public function getStaffPermissionAction(){

        $staffId = intval($this->request->getPost('staff_id'));

        $data = $this->sys_core->getDataByConditionSrt('cms_staff_permission', 'action_id, action_status, params_permission', " `staff_id` = '{$staffId}' ");

        $data = $data && is_array($data) ? $data : array();

        $this->jsonResponse(array('method' => $data));

    }


    public function saveStaffPermissionsAction(){

        $methods = unserialize($this->request->getPost('method'));

        $staffId = intval($this->request->getPost('staffId'));
        $creator_id = intval($this->request->getPost('creator_id'));
        $creator_name = trim($this->request->getPost('creator_name'));
        $create_ip = intval($this->request->getPost('create_ip'));

        $all_a_data = $all_c_data = array();
        $controller_data = $this->sys_core->getDataByConditionSrt('cms_action_controller', '*', " `comment` != `controller` ");
        if($controller_data && is_array($controller_data)){
            foreach($controller_data as $controller){
                $all_c_data[$controller['controller']] = $controller['id'];
            }
        }
        unset($all_cdata);
        $action_data = $this->sys_core->getDataByConditionSrt('cms_action', '*', " `action_name` != `method` ");
        if($action_data && is_array($action_data)){
            foreach($action_data as $action){
                $all_a_data[$action['id']] = $action;
            }
        }
        unset($action_data);

        $error = 0;

        // 获取已有数据
        $aids = $del_aids = $astatus = array();
        $adatas = $this->sys_core->getDataByConditionSrt('cms_staff_permission', 'id, action_id, action_status, params_permission', " `staff_id` = '{$staffId}' ");
        if($adatas && is_array($adatas)){
            foreach($adatas as $adata){
//                $aids[$adata['id']] = $adata['action_id'];
                $astatus[$adata['id']] = $adata['action_status'];
                if($adata['params_permission']){
                    $del_aids[$adata['action_id'].'-'.$adata['params_permission']] = $adata['id'];
                }else{
                    $del_aids[$adata['action_id']] = $adata['id'];
                }
            }
        }unset($adatas);

        if($methods && is_array($methods)){
            if($methods){
                foreach($methods as $method){

                    $method_id = $method['action_id'];
                    $post_data = array();
                    $post_data['creator_id'] = intval($creator_id);
                    $post_data['creator_name'] = $creator_name;
                    $post_data['create_ip'] = $create_ip;
                    $post_data['action_status'] = $method['action_status'];

                    $key = $method_id;
                    if(!empty($method['params_permission'])){
                        $post_data['params_permission'] = $method['params_permission'];
                        $key = $method_id.'-'.$post_data['params_permission'];
                    }

                    if(isset($del_aids[$key])){
                        // 写入数据库
                        $post_data['update_time'] = time();
                        $result = $this->sys_core->operateDataById('cms_staff_permission', $post_data, $del_aids[$key]);
                        unset($del_aids[$key]);
                    }else{
                        $c_name = $all_a_data[$method_id]['class_name'];
                        $post_data['staff_id'] = $staffId;
                        $post_data['action_id'] = $method_id;
                        $post_data['action_method'] = $all_a_data[$method_id]['method'];
                        $post_data['controller_id'] = $all_c_data[$c_name];
                        $post_data['controller_name'] = $c_name;
                        $post_data['create_time'] = time();
                        $result = $this->sys_core->operateDataById('cms_staff_permission', $post_data);

                        unset($del_aids[$key]);

                        if(!$result){
                            $error = '1';
                        }
                    }
                }
            }
        }

        // 删除数据
        if($del_aids){
            $params = array(
                'table' => 'cms_staff_permission',
                'where' => " id IN ('".implode("', '", $del_aids)."')",
            );
            $result = $this->sys_core->deleteData($params);
        }

        if(!empty($error)) {
            $this->_errorResponse('500','权限设置失败');
            return;
        }
        $this->jsonResponse(array('code' => 200));


    }

    public function saveRolePermissionsAction(){

        $controllers = unserialize($this->request->getPost('controller'));
        $methods = unserialize($this->request->getPost('method'));

        $roleId = intval($this->request->getPost('roleId'));

        $creator_id = intval($this->request->getPost('creator_id'));
        $creator_name = trim($this->request->getPost('creator_name'));
        $create_ip = intval($this->request->getPost('create_ip'));

        $all_a_data = $all_c_data = array();

        $all_cdata = $this->sys_core->getDataByConditionSrt('cms_action_controller', '*', " `comment` != `controller` ");
        if($all_cdata && is_array($all_cdata)){
            foreach($all_cdata as $acd){
                $all_c_data[$acd['id']] = $acd;
            }
        }
        unset($all_cdata);

        $all_adata = $this->sys_core->getDataByConditionSrt('cms_action', '*', " `action_name` != `method` ");
        if($all_adata && is_array($all_adata)){
            foreach($all_adata as $aad){
                $all_a_data[$aad['id']] = $aad;
            }
        }
        unset($all_adata);

        $error = 0;

        // 获取已有数据
        $cids = $aids = $del_cids = $del_aids = array();
        $cdata = $this->sys_core->getDataByConditionSrt('cms_role_controller', 'id, controller_id', " `role_id` = '{$roleId}' ");
        if($cdata && is_array($cdata)){
            foreach($cdata as $cd){
                $cids[$cd['id']] = $cd['controller_id'];
                $del_cids[$cd['controller_id']] = $cd['id'];
            }
        }unset($cdata);

        $adata = $this->sys_core->getDataByConditionSrt('cms_role_permission', 'id, action_id, params_permission', " `role_id` = '{$roleId}' ");
        if($adata && is_array($adata)){
            foreach($adata as $ad){
                $aids[$ad['id']] = $ad['action_id'];
                if($ad['params_permission']){
                    $aids[$ad['id']] = $ad['action_id'].'-'.$ad['params_permission'];
                    $del_aids[$ad['action_id'].'-'.$ad['params_permission']] = $ad['id'];
                }else{
                    $aids[$ad['id']] = $ad['action_id'];
                    $del_aids[$ad['action_id']] = $ad['id'];
                }
            }
        }unset($adata);

        if($methods && is_array($methods)){
            foreach($methods as $method_key => $method){

                if($controllers && is_array($controllers)){
                    if(in_array($method_key, $controllers)){
                        continue;
                    }
                }

                foreach($method as $met_id){

                    $post_data = array();
                    if(in_array($met_id, $aids)){
                        // 写入数据库
                        $post_data['update_time'] = time();
                        $result = $this->sys_core->operateDataById('cms_role_permission', $post_data, $del_aids[$met_id]);
                        unset($del_aids[$met_id]);
                    }else{

                        $tmp_key = $met_id;
                        if(strpos($met_id, '-')){
                            $tmp_array = explode('-', $met_id, 2);
                            $tmp_key = $tmp_array[0];
                            $tmp_parmas = $tmp_array[1];
                        }

                        if($all_a_data[$tmp_key]){
                            $post_data['role_id'] = $roleId;
                            $post_data['action_id'] = $tmp_key;
                            $post_data['action_method'] = $all_a_data[$tmp_key]['method'];
                            $post_data['controller_id'] = $method_key;
                            $post_data['controller_name'] = $all_a_data[$tmp_key]['class_name'];

                            if($tmp_parmas){
                                $post_data['params_permission'] = $tmp_parmas;
                                unset($tmp_parmas);
                            }

                            $post_data['create_time'] = time();
                            $post_data['creator_id'] = intval($creator_id);
                            $post_data['creator_name'] = $creator_name;
                            $post_data['create_ip'] = $create_ip;

                            // 写入数据库
                            $result = $this->sys_core->operateDataById('cms_role_permission', $post_data);
                            if(!$result){
                                $error = '1';
                            }
                        }

                    }

                }

            }
        }

        // 处理controller
        foreach($controllers as $controller){
            $post_data = array();
            if(in_array($controller, $cids)){
                // 写入数据库
                $post_data['update_time'] = time();
                $result = $this->sys_core->operateDataById('cms_role_controller', $post_data, $del_cids[$controller]);
                unset($del_cids[$controller]);
            }else{

                if($all_c_data[$controller]){
                    $post_data['role_id'] = $roleId;
                    $post_data['controller_id'] = $controller;
                    $post_data['controller'] = $all_c_data[$controller]['controller'];
                    $post_data['create_time'] = time();
                    $post_data['creator_id'] = intval($creator_id);
                    $post_data['creator_name'] = $creator_name;
                    $post_data['create_ip'] = $create_ip;

                    // 写入数据库
                    $result = $this->sys_core->operateDataById('cms_role_controller', $post_data);

                    if(!$result){
                        $error = '1';
                    }

                }

            }
        }

        // 删除数据
        if($del_aids){
            $params = array(
                'table' => 'cms_role_permission',
                'where' => " id IN ('".implode("', '", $del_aids)."')",
            );
            $result = $this->sys_core->deleteData($params);
        }

        if($del_cids){
            $params = array(
                'table' => 'cms_role_controller',
                'where' => " id IN ('".implode("', '", $del_cids)."')",
            );
            $result = $this->sys_core->deleteData($params);
        }

        if(!empty($error)) {
            $this->_errorResponse('500','权限设置失败');
            return;
        }
        $this->jsonResponse(array('code' => 200));

    }

    public function staffAllowAction(){
        $staffid = intval($this->request->getPost('staffid'));
        $url = trim($this->request->getPost('route'));
        $permission_params = trim($this->request->getPost('perm_params'));

        if($url == 'auth|logout' || $url == 'auth|login' || $url == 'index|index'){
            $this->jsonResponse(1);
            return;
        }elseif(!$staffid || !$url){
            $this->jsonResponse(-1);
            return;
        }
        $result = $this->sys_core->isAllowPermission($staffid, $url, $permission_params);

        $result = $result > 0 ? 1 : -1;

        $this->jsonResponse($result);
    }

}

