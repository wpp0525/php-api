<?php

/**
 * 关联游记信息管理
 */
class DestinationtripController extends ControllerBase
{

    private $desttrip;

    private $destin;

    public function initialize()
    {
        parent::initialize();
        $this->desttrip = $this->di->get('cas')->get('destination_trips_rel_service');
        $this->destin = $this->di->get('cas')->get('destin_base_service');
    }

    public function addAction()
    {
        $have_dest_id = $this->request->get('have_dest_id');
        $direct_dest_id = $this->request->get('direct_dest_id');
        $data = array('have_dest_id' => $have_dest_id, 'direct_dest_id' => $direct_dest_id);
        $rs = $this->desttrip->insert($data);

        $this->_successResponse($rs);
    }

    public function deleteAction()
    {
        $id = $this->request->get('id');
        $rs = $this->desttrip->delete($id);

        $this->_successResponse($rs);
    }

    public function getOneAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->desttrip->getItem($where_condition);
        $this->_successResponse($result);
    }

    public function listAction()
    {
        $dest_id = $this->request->get('dest_id');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $page_size = $page_size ? $page_size : 10;
        $current_page = $current_page ? $current_page : 1;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);

        if (!is_numeric($dest_id)) {
            $this->_errorResponse(DATA_NOT_FOUND, '参数错误！');
            return;
        }

        $result = $this->desttrip->getDesttripInfoById($dest_id, $limit);
        $list = $result['list'];

        if (empty($list)) {
            $this->_errorResponse(DATA_NOT_FOUND, '列表为空！');
            return;
        }

        $total = $result['total'];
        $total_pages = $total ? intval(($total - 1) / $page_size + 1) : 0;

        $this->_successResponse(array('list' => $list, 'total' => $total, 'current_page' => $current_page, 'total_pages' => $total_pages));
    }

}
