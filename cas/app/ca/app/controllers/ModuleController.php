<?php

/**
 * 模块控制器
 *
 * @author flash.guo
 *
 */
class ModuleController extends ControllerBase {
    private $seo_mdl_svc;
    private $seo_mdl_var_svc;
    public function initialize() {
        parent::initialize();
        $this->seo_mdl_svc = $this->di->get('cas')->get('seo_module_service');
        $this->seo_mdl_var_svc = $this->di->get('cas')->get('seo_module_variable_service');
    }

	/**
	 * 模块详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $mdlname = trim($this->request->get('mdlname'));
        $conditions = array();
        !empty($id) && $conditions['module_id'] = "=" . $id;
        !empty($mdlname) && $conditions['module_name'] = "='" . $mdlname . "'";
        !empty($conditions) && $mdl_info = $this->seo_mdl_svc->getOneModule($conditions);
        if(empty($mdl_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模块不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $mdl_info));
	}

	/**
	 * 模块列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns = trim($this->request->get('columns'));
        $order = trim($this->request->get('order'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order = $order ? $order : null;
        $module_info = $this->seo_mdl_svc->getModuleList($condition, $limit, $columns, $order);
        if(empty($module_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模块不存在');
        	return;
        }
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $module_info));
        	return;
        }
        $total_records = $this->seo_mdl_svc->getModuleTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $module_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 模块新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_mdl_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模块新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模块更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_mdl_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模块更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模块删除
	 */
	public function deleteAction() {
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
        	$result = $this->seo_mdl_svc->delete($id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模块删除失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 获取模块所有变量
	 */
	public function getVarAction() {
        $mid = intval($this->request->get('mid'));
		$order = $this->request->get('order');
        if (empty($mid)) {
	        $this->_errorResponse(DATA_NOT_FOUND,'模块变量不存在');
	        return;
		}
		$condition['module_id'] = "=".$mid;
		$limit = NULL;
		$columns = NULL;
		$order = $order ? urldecode($order) : NULL;
		$module_vars = $this->seo_mdl_var_svc->getVarList($condition,$limit,$columns,$order);
		if(empty($module_vars)) {
			$this->_errorResponse(DATA_NOT_FOUND,'模块变量不存在');
			return;
		}
		$this->jsonResponse(array('results' => $module_vars));
	}

	/**
	 * 模块变量新增
	 */
	public function addVarAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_mdl_var_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模块变量新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 模块变量更新
	 */
	public function updateVarAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_mdl_var_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'模块变量更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	/**
	 * 清空模块变量
	 */
	public function emptyVarAction(){
		$post = $this->request->getPost();
		$mid = isset($post['mid']) ? intval($post['mid']) : 0;
		$result = $this->seo_mdl_var_svc->delVarByMid($mid);
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'清空模块变量失败');
        	return;
        } else {
	        $this->seo_mdl_svc->update($mid, array('update_time' => time()));
        }
		$this->jsonResponse(array('result' => $result));
	}

	/**
	 * 设置模块变量
	 */
	public function setVarAction() {
		$post = $this->request->getPost();
		$var = $vars = $varids = $varnames = $varcnts = array();
        $mid = intval($post['mid']);
        if(empty($mid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'模块不存在');
        	return;
        }
		$condition['module_id'] = "=".$mid;
		$module_vars = $this->seo_mdl_var_svc->getVarList($condition);
        foreach ($module_vars as $module_var) {
        	$vars[] = $module_var['variable_name'];
        	$varids[$module_var['variable_name']] = $module_var['variable_id'];
        }
        $vardatas = empty($post['vars']) ? array() : json_decode($post['vars'], true);
        foreach ($vardatas as $vardata) {
        	$varnames[] = $vardata['variable_name'];
        	$varcnts[$vardata['variable_name']] = $vardata;
        }
        $result = true;
        $bothvarnames = array_intersect($varnames, $vars);
        foreach ($bothvarnames as $varname) {
        	$varid = $varids[$varname];
        	$var['module_id'] = $mid;
        	$var['variable_name'] = trim($varname);
        	$var['max_count'] = intval($varcnts[$varname]['max_count']);
          $var['variable_des'] = trim($varcnts[$varname]['variable_des']);
          $var['variable_default'] = trim($varcnts[$varname]['variable_default']);
          $var['group_id'] = trim($varcnts[$varname]['group_id']);
        	$var['update_time'] = time();
	        $result = $this->seo_mdl_var_svc->update($varid, $var);
        }
        $newvarnames = array_diff($varnames, $vars);
        foreach ($newvarnames as $varname) {
        	$var['module_id'] = $mid;
        	$var['variable_name'] = trim($varname);
          $var['variable_des'] = trim($varcnts[$varname]['variable_des']);
          $var['variable_default'] = trim($varcnts[$varname]['variable_default']);
        	$var['max_count'] = intval($varcnts[$varname]['max_count']);
          $var['group_id'] = intval($varcnts[$varname]['group_id']);
        	$var['create_time'] = $var['update_time'] = time();
	        $result = $this->seo_mdl_var_svc->insert($var);
        }
        $oldvarnames = array_diff($vars, $varnames);
        foreach ($oldvarnames as $varname) {
	        $result = $this->seo_mdl_var_svc->delVarByMid($mid, trim($varname));
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置模块变量失败');
        	return;
        } else {
	        $this->seo_mdl_svc->update($mid, array('update_time' => time()));
        }
        $this->jsonResponse(array('result' => $result));
	}
}
