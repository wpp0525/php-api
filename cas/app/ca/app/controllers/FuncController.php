<?php

/**
 * 功能控制器
 * 
 * @author flash.guo
 *
 */
class FuncController extends ControllerBase {
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
	 * 功能组详情
	 */
	public function getGroupAction() {
        $id = intval($this->request->get('id'));
        $group_info = $this->func_base_svc->getOneById($id);
        if(empty($group_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'功能组不存在');
        	return;
        }
        $group = array();
        $group['id'] = $group_info['function_id'];
        $group['group_key'] = $group_info['function_key'];
        $group['group_name'] = $group_info['function_name'];
        $group['sort_num'] = $group_info['sort_num'];
        $this->jsonResponse(array('result' => $group));
	}
	
	/**
	 * 功能组新增
	 */
	public function createGroupAction() {
        $group = array();
        $group['function_key'] = $this->request->getPost('group_key');
        $group['function_name'] = $this->request->getPost('group_name');
        $group['sort_num'] = intval($this->request->getPost('sort_num'));
        $group['create_time'] = $group['update_time'] = time();
        if(!empty($group['function_key']) && !empty($group['function_name'])) $result = $this->func_base_svc->insert($group);
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能组新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 功能组更新
	 */
	public function updateGroupAction() {
        $id = intval($this->request->getPost('id'));
        $group = array();
        $group['function_name'] = $this->request->getPost('group_name');
        $group['sort_num'] = $this->request->getPost('sort_num');
        $group['update_time'] = time();
        if(!empty($group['function_name'])) $result = $this->func_base_svc->update($id, $group);
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能组更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 功能组删除
	 */
	public function deleteGroupAction() {
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
        	$condition['group_id'] = "=" . $id;
        	$condition['status'] = "=1";
        	$functions = $this->func_base_svc->getFuncList($condition);
        	if (!empty($functions)) {
	        	$this->_errorResponse(OPERATION_FAILED,'请先删除该功能组下的功能，再来删除该功能组');
	        	return;
	        }
        	$result = $this->func_base_svc->delete($id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能组删除失败');
        	return;
        }
        $this->role_func_svc->delFuncByFuncid($id);
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 功能列表
	 */
	public function getAction() {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
	        $func_info = $this->func_base_svc->getOneById($id);
	        if(empty($func_info)) {
	        	$this->_errorResponse(DATA_NOT_FOUND,'功能不存在');
	        	return;
	        }
	        $func_info['actions'] = json_decode($func_info['actions']);
	        $this->jsonResponse(array('results' => array($id => $func_info)));
	        return;
		}
        $function_tree = $condition = array();
        $condition['group_id'] = "=0";
        $condition['status'] = "=1";
        $id = intval($this->request->get('id'));
        !empty($id) && $condition['function_id'] = "=" . $id;
        $groups = $this->func_base_svc->getFuncList($condition);
        if(empty($groups)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'功能信息不存在');
        	return;
        }
        foreach ($groups as $group) {
        	$function_tree[$group['function_id']]['id'] = $group['function_id'];
        	$function_tree[$group['function_id']]['group_name'] = $group['function_name'];
        	$function_tree[$group['function_id']]['group_key'] = $group['function_key'];
        	$function_tree[$group['function_id']]['sort_num'] = $group['sort_num'];
        	$functions = $condition = array();
        	$condition['group_id'] = "=" . $group['function_id'];
        	$condition['status'] = "=1";
        	$functions = $this->func_base_svc->getFuncList($condition, NULL, "*", "parent_id");
        	foreach ($functions as $function) {
	        	$function['actions'] = json_decode($function['actions']);
        		if ($function['parent_id'] > 0) {
        			$function_tree[$group['function_id']]['functions'][$function['parent_id']]['functions'][$function['function_id']] = $function;
        		} else {
        			$function_tree[$group['function_id']]['functions'][$function['function_id']] = $function;
        		}
        	}
        }
        $this->jsonResponse(array('results' => $function_tree));
	}
	
	/**
	 * 功能新增
	 */
	public function createAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->func_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 功能更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $function_id = intval($post['function_id']);
        unset($post['function_id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->func_base_svc->update($function_id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 功能删除
	 */
	public function deleteAction() {
        $function_id = intval($this->request->get('function_id'));
        if(!empty($function_id)) {
        	$condition['parent_id'] = "=" . $function_id;
        	$condition['status'] = "=1";
        	$functions = $this->func_base_svc->getFuncList($condition);
        	if (!empty($functions)) {
	        	$this->_errorResponse(OPERATION_FAILED,'请先删除该功能下的子功能，再来删除该功能');
	        	return;
	        }
        	$result = $this->func_base_svc->delete($function_id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'功能信息删除失败');
        	return;
        }
        $this->role_func_svc->delFuncByFuncid($function_id);
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 用户功能列表
     * update by liu @2017.05.21
     *  1.增加注释
	 */
	public function staffAction() {

        // 查找用户是否存在
        $staffid = intval($this->request->get('staffid'));
	    $staff_info = $this->staff_base_svc->getOneById($staffid);

        if(empty($staff_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'用户不存在');
        	return;
        }

        // 查找用户组
	    $staff_role = $this->staff_role_svc->findRoleByStaffid($staff_info['id']);
//        echo json_encode($staff_role); die;

	    $role_methods = $this->role_func_svc->getMethodByRoleid($staff_role['role_id']);

        $staff_funcs = $this->role_func_svc->getMethodByStaffid($staffid);

//        echo json_encode($staff_funcs); die;
        $allow_action = array_unique(array_merge(array_diff($role_methods, $staff_funcs['N']),$staff_funcs['Y']));

        $role_funcs = $this->sys_core->findMenuByAllow($allow_action);

        if($role_funcs['menu'] && is_array($role_funcs['menu'])){
            $i = 1;
            $function_tree = array();
            foreach ($role_funcs['menu'] as $role_func) {

                if($role_func['group_id'] == 0 && $role_func['parent_id'] == 0){

                    $temp = array();
                    $temp['id'] = $role_func['menu_id'];
                    $temp['group_name'] = $role_func['menu_name'];
                    $temp['group_key'] = $role_func['menu_key'];
                    $temp['sort_num'] = $role_func['sort_num'];

                    $function_tree[$i] = $temp;
                    foreach ($role_funcs['menu'] as $role_func2) {
                        if($role_func2['group_id'] == $role_func['menu_id']){
                            $function_tree[$i]['functions'][] = $role_func2;
                        }
                    }

                }
                $i++;

            }

        }


        $this->jsonResponse(array('result' => $function_tree, 'methods' => $role_funcs['perm']));
	}
}

