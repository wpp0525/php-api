<?php

/**
 * 操作日志控制器
 * 
 * @author flash.guo
 *
 */
class LoggingController extends ControllerBase {
    private $log_base_svc;
    public function initialize() {
        parent::initialize();
        $this->log_base_svc = $this->di->get('cas')->get('log_base_service');
    }
	
	/**
	 * 角色详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $conditions = array();
        !empty($id) && $conditions['id'] = "=" . $id;
        $log_info = $this->log_base_svc->getOneLog($conditions);
        if(empty($log_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'操作日志信息不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $log_info));
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
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);
        $log_info = $this->log_base_svc->getLogList($condition, $limit);
        if(empty($log_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'操作日志信息不存在');
        	return;
        }
        $total_records = $this->log_base_svc->getLogTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $log_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	
	/**
	 * 用户操作日志新增
	 */
	public function staffAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = time();
        	$result = $this->log_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'操作日志信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 操作日志删除
	 */
	public function deleteAction() {
        $log_id = intval($this->request->get('log_id'));
        if(!empty($log_id)) $result = $this->log_base_svc->delete($log_id);
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'操作日志信息删除失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
}

