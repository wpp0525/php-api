<?php

use Lvmama\Common\Utils\Filelogger;

class MonthrecController extends ControllerBase
{
    private $monthrec;

    public function initialize()
    {
        parent::initialize();
        $this->monthrec = $this->di->get('cas')->get('monthrec_service');
    }

    public function addAction()
    {
        $request = $this->request->get();
        $data = array(
            'dest_id' => $request['dest_id'],
            'status' => $request['status'],
            'type' => $request['type'],
            'month' => $request['month'],
        );
        $rs = $this->monthrec->insert($data);

        $this->_successResponse($rs);
    }

    public function updateAction()
    {
        $id = $this->request->get('id');
        $data = $this->request->get('data');

        $rs = $this->monthrec->update($id, $data);
        $this->_successResponse($rs);
    }

    public function getTotalAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->monthrec->getTotal($where_condition);
        $this->_successResponse($result);
    }

    public function getListAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->monthrec->getListData($where_condition, null, '*', 'id ASC');
        $this->_successResponse($result);
    }
}