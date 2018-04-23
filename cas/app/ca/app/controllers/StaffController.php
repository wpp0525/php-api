<?php

/**
 * 管理员控制器
 *
 * @author flash.guo
 *
 */
class StaffController extends ControllerBase {
    private $staff_base_svc;
    private $staff_role_svc;
    public function initialize() {
        parent::initialize();
        $this->staff_base_svc = $this->di->get('cas')->get('staff_base_service');
        $this->staff_role_svc = $this->di->get('cas')->get('staff_role_service');
    }

	/**
	 * 登录
	 */
	public function loginAction() {
        $username = trim($this->request->get('username'));
        $password = trim($this->request->get('password'));
        $password = $password == "" ? $password : md5($password);
        $staff_info = $this->staff_base_svc->getOneByUsername($username);

        if(empty($staff_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'用户名或密码不正确');
        	return;
        }else{
            if($staff_info['salt'] && $staff_info['password2']){
                $newps = md5($password.$staff_info['salt']);
                if($staff_info['password2'] <> $newps){
                    $this->_errorResponse(DATA_NOT_FOUND,'用户名或密码不正确');
                    return;
                }
            }elseif($staff_info['password'] <> $password){
                $this->_errorResponse(DATA_NOT_FOUND,'用户名或密码不正确');
                return;
            }
        }

//        if(empty($staff_info) || $staff_info['password'] <> $password) {
//        	$this->_errorResponse(DATA_NOT_FOUND,'用户名或密码不正确');
//        	return;
//        }

        $this->jsonResponse(array('result' => $staff_info));
	}

	/**
	 * 管理员详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $username = trim($this->request->get('username'));
        $conditions = array();
        !empty($id) && $conditions['id'] = "=" . $id;
        !empty($username) && $conditions['username'] = "='" . $username . "'";
        $staff_info = $this->staff_base_svc->getOneStaff($conditions);
        if(empty($staff_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'会员信息不存在');
        	return;
        }
	    $staff_info['roles'] = $this->staff_role_svc->getRoleByStaffid($staff_info['id']);
        $this->jsonResponse(array('results' => $staff_info));
	}

	/**
	 * 管理员列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);

        $staff_info = $this->staff_base_svc->getStaffList($condition, $limit);
        if(empty($staff_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'会员信息不存在');
        	return;
        }
        $total_records = $this->staff_base_svc->getStaffTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $staff_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 管理员新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
            $result = $this->staff_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'会员信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 管理员更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
//        echo json_encode($post);die;
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        isset($post['password']) && $post['password'] = md5($post['password']);
        isset($post['password2']) && $post['password2'] = md5($post['password2']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->staff_base_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'会员信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

    /**
     * 管理员绑定角色
     * @20170527 by liu
     * 管理员和角色绑定为一对一
     */
    public function bindRoleAction(){
        $post = $this->request->getPost();
        $role = $delRoles = array();
        $staffId = intval($post['id']);
        if(empty($staffId)) {
            $this->_errorResponse(DATA_NOT_FOUND,'会员信息不存在');
            return;
        }
        $roleId = intval($post['role_id']);
        if(empty($roleId)) {
            $this->_errorResponse(DATA_NOT_FOUND,'角色信息不存在');
            return;
        }

        $staff_roles = $this->staff_role_svc->getRoleByStaffid($staffId);
        if($staff_roles && is_array($staff_roles)){
            foreach ($staff_roles as $staff_role) {
                if($staff_role['role_id'] == $roleId){
                    $roleId = false;
                    $return = $staff_role['id'];
                }else{
                    $result = $this->staff_role_svc->delRoleByStaffid($staffId, $staff_role['role_id']);
                    if(empty($result)) {
                        $this->_errorResponse(OPERATION_FAILED,'设置管理员角色失败');
                        return;
                    }
                }
            }
        }

        if($roleId){
            $role['staff_id'] = $staffId;
            $role['role_id'] = intval($roleId);
            $role['create_time'] = time();
            $role['creator_id'] = intval($post['creator_id']);
            $role['creator_name'] = $post['creator_name'];
            $role['create_ip'] = $post['create_ip'];
            $result2 = $this->staff_role_svc->insert($role);
            $this->jsonResponse(array('result' => $result2));
        }else{
            $this->jsonResponse(array('result' => $return));
        }

    }

    /**
	 * 设置管理员角色
	 */
	public function addRoleAction() {
		$post = $this->request->getPost();
		$role = $roles = array();
        $staffid = intval($post['id']);
        if(empty($staffid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'会员信息不存在');
        	return;
        }
	    $staff_roles = $this->staff_role_svc->getRoleByStaffid($staffid);
        foreach ($staff_roles as $staff_role) {
        	$roles[] = $staff_role['role_id'];
        }
        $roleids = empty($post['roleids']) ? array() : explode(',', $post['roleids']);
        if(empty($roleids)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'角色信息不存在');
        	return;
        }
        $newroleids = array_diff($roleids, $roles);
        $result = true;
        foreach ($newroleids as $roleid) {
        	$role['staff_id'] = $staffid;
        	$role['role_id'] = intval($roleid);
        	$role['create_time'] = time();
        	$role['creator_id'] = intval($post['creator_id']);
        	$role['creator_name'] = $post['creator_name'];
        	$role['create_ip'] = $post['create_ip'];
	        $result = $this->staff_role_svc->insert($role);
        }
        $oldroleids = array_diff($roles, $roleids);
        foreach ($oldroleids as $roleid) {
	        $result = $this->staff_role_svc->delRoleByStaffid($staffid, intval($roleid));
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置管理员角色失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}


    public function getStaffsByRoleAction(){

        $role_id = intval($this->request->get('role_id'));

        $staffs = $this->staff_role_svc->getStaffsByRole($role_id);

        if(empty($staffs)) {
            $this->_errorResponse('40404','查询失败');
            return;
        }
        $this->jsonResponse(array('result' => $staffs));

    }

    /**
     * 根据条件获取用户信息列表
     *
     * @author jianghu
     */
    public function getUserInfoListByConditionAction()
    {
        $condition = $this->request->get('condition');
        $condition = json_decode($condition, true);

        $staff_info = $this->staff_base_svc->getStaffList($condition);
        if(empty($staff_info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'会员信息不存在');
            return;
        }
        $this->jsonResponse($staff_info);
    }
}
