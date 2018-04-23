<?php

/**
 * 对象坐标控制器
 * 
 * @author flash.guo
 *
 */
class CoordController extends ControllerBase {
    private $coord_base_svc;
    public function initialize() {
        parent::initialize();
        $this->coord_base_svc = $this->di->get('cas')->get('coord_base_service');
    }
	
	/**
	 * 对象坐标详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $object_id = intval($this->request->get('object_id'));
        $object_type = trim($this->request->get('object_type'));
        $coord_type = trim($this->request->get('coord_type'));
        $conditions = array();
        !empty($id) && $conditions['coord_id'] = "=" . $id;
        !empty($object_id) && $conditions['object_id'] = "=" . $object_id;
        !empty($object_type) && $conditions['object_type'] = "='" . $object_type . "'";
        !empty($object_type) && $conditions['coord_type'] = "='" . $coord_type . "'";
        !empty($conditions) && $coord_info = $this->coord_base_svc->getOneCoord($conditions);
        if(empty($coord_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'对象坐标信息不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $coord_info));
	}
	
	/**
	 * 对象坐标列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $order = $this->request->get('order');
        $condition = json_decode($condition, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);
        $coord_info = $this->coord_base_svc->getCoordList($condition, $limit, NULL, $order);
        if(empty($coord_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'对象坐标信息不存在');
        	return;
        }
        $total_records = $this->coord_base_svc->getCoordTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $coord_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	
	/**
	 * 对象坐标新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->coord_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'对象坐标信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 对象坐标更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->coord_base_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'对象坐标信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
}

