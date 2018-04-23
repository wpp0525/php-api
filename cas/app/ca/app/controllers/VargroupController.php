<?php

/**
 * 大目的地变量组控制器
 *
 * @author flash.guo
 *
 */
class VargroupController extends ControllerBase {
    private $seo_var_grp_svc;
    public function initialize() {
        parent::initialize();
        $this->seo_var_grp_svc = $this->di->get('cas')->get('seo_variable_group_service');
    }

	/**
	 * 变量组详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $mdlname = trim($this->request->get('mdlname'));
        $conditions = array();
        !empty($id) && $conditions['group_id'] = "=" . $id;
        !empty($mdlname) && $conditions['group_name'] = "='" . $mdlname . "'";
        !empty($conditions) && $mdl_info = $this->seo_var_grp_svc->getOneGroup($conditions);
        if(empty($mdl_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'变量组不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $mdl_info));
	}

	/**
	 * 变量组列表
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
        $group_info = $this->seo_var_grp_svc->getGroupList($condition, $limit, $columns, $order);
        if(empty($group_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'变量组不存在');
        	return;
        }
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $group_info));
        	return;
        }
        $total_records = $this->seo_var_grp_svc->getGroupTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $group_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 变量组新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->seo_var_grp_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'变量组新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 变量组更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->seo_var_grp_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'变量组更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 变量组删除
	 */
	public function deleteAction() {
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
        	$result = $this->seo_var_grp_svc->delete($id);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'变量组删除失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
}
