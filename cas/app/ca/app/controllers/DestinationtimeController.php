<?php

use Lvmama\Common\Utils\Filelogger;

class DestinationtimeController extends ControllerBase
{
    private $time_srv;

    public function initialize()
    {
        parent::initialize();
        $this->time_srv = $this->di->get('cas')->get('destination_time_service');
    }

    public function addAction()
    {
        $data = $this->request->get('data');
        $rs = $this->time_srv->insert($data);

        $this->_successResponse($rs);
    }

    public function updateAction()
    {
        $id = $this->request->get('id');
        $data = $this->request->get('data');

        $rs = $this->time_srv->update($id, $data);
        $this->_successResponse($rs);
    }

    public function deleteAction()
    {
        $id = $this->request->get('id');

        $rs = $this->time_srv->delete($id);
        $this->_successResponse($rs);
    }

    public function getTotalAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->time_srv->getTotal($where_condition);
        $this->_successResponse($result);
    }

    public function getOneAction()
    {
        $id = $this->request->get('id');
        $result = $this->time_srv->getItem($id);
        $this->_successResponse($result);
    }

    public function getListAction()
    {
        $where_condition = $this->request->get('where_condition');
        $order = $this->request->get('order');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $page_size = $page_size ? $page_size : 10;
        $current_page = $current_page ? $current_page : 1;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);

        $list = $this->time_srv->getListData($where_condition, $limit, '*', $order);
        $total = $this->time_srv->getTotal($where_condition);
        $total_pages = $total ? intval(($total - 1) / $page_size + 1) : 0;

        $this->_successResponse(array('list' => $list, 'total' => $total, 'current_page' => $current_page, 'total_pages' => $total_pages));
    }

}