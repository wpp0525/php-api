<?php

/**
 * 角色控制器
 * 
 * @author flash.guo
 *
 */
class RoleController extends ControllerBase {
    private $role_base_svc;
    private $staff_role_svc;
    private $role_func_svc;
    public function initialize() {
        parent::initialize();
        $this->role_base_svc = $this->di->get('cas')->get('role_base_service');
        $this->staff_role_svc = $this->di->get('cas')->get('staff_role_service');
        $this->role_func_svc = $this->di->get('cas')->get('role_func_service');
    }
	
	/**
	 * 角色详情
	 */
	public function infoAction() {
        $role_id = intval($this->request->get('role_id'));
        $role_name = trim($this->request->get('role_name'));
        $conditions = array();
        !empty($role_id) && $conditions['role_id'] = "=" . $role_id;
        !empty($role_name) && $conditions['role_name'] = "='" . $role_name . "'";
        $role_info = $this->role_base_svc->getOneRole($conditions);
        if(empty($role_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'角色信息不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $role_info));
	}
	
	/**
	 * 角色列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $condition = json_decode($condition, true);
        $page_size = $page_size ? $page_size : 10;
        $current_page = $current_page ? $current_page : 1;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $role_info = $this->role_base_svc->getRoleList($condition, $limit);
        if(empty($role_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'角色信息不存在');
        	return;
        }
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $role_info));
        	return;
        }
        $total_records = $this->role_base_svc->getRoleTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $role_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	
	/**
	 * 角色新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->role_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'角色信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 角色更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $role_id = intval($post['role_id']);
        unset($post['role_id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->role_base_svc->update($role_id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'角色信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 角色删除
	 */
	public function deleteAction() {
        $role_id = intval($this->request->get('role_id'));
        if(!empty($role_id)) $result = $this->role_base_svc->delete($role_id);
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'角色信息删除失败');
        	return;
        }
        $this->staff_role_svc->delRoleByRoleid($role_id);
        $this->role_func_svc->delFuncByRoleid($role_id);
        $this->jsonResponse(array('result' => $result));
	}

    public function getIdByNameAction(){

        $role_name = trim($this->request->getPost('role_name'));
        if(!empty($role_name)) $result = $this->role_base_svc->getOneByName($role_name);
        if(empty($result) || empty($result['role_id'])) {
            $this->_errorResponse(OPERATION_FAILED, '没有数据');
            return;
        }
        $this->jsonResponse(array('roleId' => $result['role_id']));

    }



}

