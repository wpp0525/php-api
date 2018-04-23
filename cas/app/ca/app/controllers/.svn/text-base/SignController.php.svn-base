<?php

/**
 * 交通点控制器
 * 
 * @author flash.guo
 *
 */
class SignController extends ControllerBase {
    private $dist_sign_svc;
    private $dist_sign_type;
    public function initialize() {
        parent::initialize();
        $this->dist_sign_svc = $this->di->get('cas')->get('dist_sign_service');
		$this->dist_sign_type = array(
				'2000' => '地标',
				'2001' => '商圈',
				'2002' => '景区',
				'2003' => '飞机场',
				'2004' => '火车站',
				'2005' => '长途汽车站',
				'2006' => '地铁'
		);
    }
	
	/**
	 * 区域级别
	 */
	public function typeAction() {
        $this->jsonResponse($this->dist_sign_type);
	}
	
	/**
	 * 交通点详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $signname = trim($this->request->get('signname'));
        $signtype = trim($this->request->get('signtype'));
        $enname = intval($this->request->get('enname'));
        $distid = intval($this->request->get('distid'));
        $conditions = array();
        !empty($id) && $conditions['sign_id'] = "=" . $id;
        !empty($signname) && $conditions['sign_name'] = "='" . $signname . "'";
        !empty($signtype) && $conditions['sign_type'] = "='" . $signtype . "'";
        !empty($enname) && $conditions['en_name'] = "='" . $enname . "'";
        !empty($distid) && $conditions['district_id'] = "=" . $distid;
        !empty($conditions) && $dist_info = $this->dist_sign_svc->getOneDistsign($conditions);
        if(empty($dist_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'交通点信息不存在');
        	return;
        }
        $this->jsonResponse(array('results' => $dist_info));
	}
	
	/**
	 * 交通点列表
	 */
	public function listAction() {
        $order = $this->request->get('order');
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $order = $order ? $order : "sign_id DESC";
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);
        $dist_info = $this->dist_sign_svc->getDistsignList($condition, $limit, "*", $order);
        if(empty($dist_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'交通点信息不存在');
        	return;
        }
        $total_records = $this->dist_sign_svc->getDistsignTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $dist_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	
	/**
	 * 交通点新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->dist_sign_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'交通点信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 交通点更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->dist_sign_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'交通点信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
}

