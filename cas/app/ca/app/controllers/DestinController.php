<?php

/**
 * 新版目的地控制器
 * 
 * @author flash.guo
 *
 */
class DestinController extends ControllerBase {
    private $dest_base_svc;
    private $dest_rel_svc;
    private $dest_type;
    private $dest_mark;
    public function initialize() {
        parent::initialize();
        $this->dest_base_svc = $this->di->get('cas')->get('destin_base_service');
        $this->dest_rel_svc = $this->di->get('cas')->get('destin_rel_service');
		$this->dest_type = array(
				'CONTINENT' => '大洲',
				'SPAN_COUNTRY' => '跨国家地区',
				'COUNTRY' => '国家',
				'SPAN_PROVINCE' => '跨州省地区',
				'PROVINCE' => '州省',
				'SPAN_CITY' => '跨城市地区',
				'CITY' => '城市/直辖市/特区',
				'SPAN_COUNTY' => '跨区县地区',
				'COUNTY' => '区/县',
				'SPAN_TOWN' => '跨乡镇地区',
				'TOWN' => '乡镇/街道',
				'SCENIC' => '景区',
				'VIEWSPOT' => '景点',
				'RESTAURANT' => '餐厅',
				'SHOP' => '购物地',
				'HOTEL' => '酒店',
				'SCENIC_ENTERTAINMENT' => '娱乐点'
		);
		$this->dest_mark = array(
				'PROVINCE_CITY' => '省会城市',
				'MUNICIPALITY_CITY' => '直辖市',
				'CANTON_DISTRICT' => '特别行政区',
				'OTHER' => '其他',
		);
    }
	
	/**
	 * 目的地类型
	 */
	public function typeAction() {
        $this->jsonResponse($this->dest_type);
	}
	
	/**
	 * 目的地标识
	 */
	public function markAction() {
        $this->jsonResponse($this->dest_mark);
	}
	
	/**
	 * 目的地详情
	 */
	public function infoAction() {
        $id = intval($this->request->get('id'));
        $destname = trim($this->request->get('destname'));
        $desttype = trim($this->request->get('desttype'));
        $distid = intval($this->request->get('distid'));
        $conditions = array();
        !empty($id) && $conditions['dest_id'] = "=" . $id;
        !empty($destname) && $conditions['dest_name'] = "='" . $destname . "'";
        !empty($desttype) && $conditions['dest_type'] = "='" . $desttype . "'";
        !empty($distid) && $conditions['district_id'] = "=" . $distid;
        !empty($conditions) && $dest_info = $this->dest_base_svc->getOneDest($conditions);
        if(empty($dest_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'目的地信息不存在');
        	return;
        }
	    $dest_info['relations'] = $this->dest_rel_svc->getRelByDestid($dest_info['dest_id']);
        $this->jsonResponse(array('results' => $dest_info));
	}
	
	/**
	 * 上级区域
	 */
	public function parentAction() {
        $loop = $this->request->get('loop');
        $ids = trim($this->request->get('ids'), ",");
        $ids = implode(",", array_map("intval", explode(",", $ids)));
        $conditions = array();
        if (!empty($ids)) {
        	$conditions['dest_id'] = " IN(" . $ids .")";
        	$dest_info = $this->dest_base_svc->getDestList($conditions);
        }
        if(empty($dest_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'目的地信息不存在');
        	return;
        }
        $parents = "";
        $destarr = array();
        foreach ($dest_info as $dest) {
        	$parents[$dest['dest_id']] = $dest['dest_name']."(".$this->dest_type[$dest['dest_type']].")";
			if (empty($destarr[$dest['dest_id']])) {
	        	$destarr[$dest['dest_id']] = array();
        		$destarr[$dest['dest_id']]['parent_id'] = $dest['parent_id'];
	        	$destarr[$dest['dest_id']]['dest_name'] = $dest['dest_name'];
	        	$destarr[$dest['dest_id']]['dest_type'] = $dest['dest_type'];
			}
        	loop_start:
			if (!empty($loop) && !empty($dest['parent_id'])) {
				if (empty($destarr[$dest['parent_id']])) {
        			$parent = $this->dest_base_svc->getOneDest(array('dest_id' => "=" . $dest['parent_id']));
        			$destarr[$dest['parent_id']] = array();
        			$destarr[$dest['parent_id']]['parent_id'] = $parent['parent_id'];
        			$destarr[$dest['parent_id']]['dest_name'] = $parent['dest_name'];
        			$destarr[$dest['parent_id']]['dest_type'] = $parent['dest_type'];
				} else {
					$parent = $destarr[$dest['parent_id']];
				}
        		$parents[$dest['dest_id']] .= "--".$parent['dest_name']."(".$this->dest_type[$parent['dest_type']].")";
        		if (!empty($parent['parent_id']) && $parent['parent_id'] <> $dest['parent_id']) {
        			$dest['parent_id'] = $parent['parent_id'];
        			goto loop_start;
        		}
			}
        }
        unset($destarr);
        $this->jsonResponse(array('results' => $parents));
	}
	
	/**
	 * 目的地列表
	 */
	public function listAction() {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $order = $this->request->get('order');
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);
        $dest_info = $this->dest_base_svc->getDestList($condition, $limit, NULL, $order);
        if(empty($dest_info)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'目的地信息不存在');
        	return;
        }
        $total_records = $this->dest_base_svc->getDestTotal($condition);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array('results' => $dest_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}
	
	/**
	 * 目的地新增
	 */
	public function addAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->dest_base_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'目的地信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 目的地更新
	 */
	public function updateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->dest_base_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'目的地信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
	
	/**
	 * 设置次父级目的地
	 */
	public function addRelAction() {
		$post = $this->request->getPost();
		$rel = $rels = array();
        $destid = intval($post['id']);
        if(empty($destid)) {
        	$this->_errorResponse(DATA_NOT_FOUND,'目的地信息不存在');
        	return;
        }
	    $dest_rels = $this->dest_rel_svc->getRelByDestid($destid);
        foreach ($dest_rels as $dest_rel) {
        	$rels[] = $dest_rel['parent_id'];
        }
        $relids = empty($post['relids']) ? array() : array_map("intval", explode(",", $post['relids']));
        $newrelids = array_diff($relids, $rels);
        $result = true;
        foreach ($newrelids as $relid) {
        	$rel['dest_id'] = $destid;
        	$rel['parent_id'] = intval($relid);
        	$rel['update_time'] = time();
	        $result = $this->dest_rel_svc->insert($rel);
        }
        $oldrelids = array_diff($rels, $relids);
        foreach ($oldrelids as $relid) {
	        $result = $this->dest_rel_svc->delRelByDestid($destid, intval($relid));
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'设置次父级目的地失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}
}

