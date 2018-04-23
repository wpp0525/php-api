<?php

use Lvmama\Common\Utils\Filelogger;

class MosubjectController extends ControllerBase
{
    private $mo_subject;
    private $mo_subject_relation;

    public function initialize()
    {
        parent::initialize();
        $this->mo_subject = $this->di->get('cas')->get('mo_subject_service');
        $this->mo_subject_relation = $this->di->get('cas')->get('mo_subject_relation_service');
    }

    public function addAction()
    {
        $data = $this->request->get('data');
        $rs = $this->mo_subject_relation->insert($data);

        $this->_successResponse($rs);
    }

    public function updateAction()
    {
        $id = $this->request->get('id');
        $data = $this->request->get('data');

        $rs = $this->mo_subject_relation->update($id, $data);
        $this->_successResponse($rs);
    }

    public function deleteAction()
    {
        $id = $this->request->get('id');

        $rs = $this->mo_subject_relation->delete($id);
        $this->_successResponse($rs);
    }

    public function deleteByAction()
    {
        $where_condition = $this->request->get('where_condition');

        $rs = $this->mo_subject_relation->deleteBy($where_condition);
        $this->_successResponse($rs);
    }

    public function updateByAction()
    {
        $where_condition = $this->request->get('where_condition');
        $data = $this->request->get('data');

        $rs = $this->mo_subject_relation->updateBy($where_condition, $data);
        $this->_successResponse($rs);
    }

    public function getTotalAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->mo_subject_relation->getTotal($where_condition);
        $this->_successResponse($result);
    }

    public function getOneAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->mo_subject_relation->getItem($where_condition);
        $this->_successResponse($result);
    }

    public function getListAction()
    {
        $where_condition = $this->request->get('where_condition');
        $order = $this->request->get('order');
        $result = $this->mo_subject_relation->getListData($where_condition, null, '*', $order);
        $this->_successResponse($result);
    }


    public function getSubjectListAction()
    {
        $where_condition = $this->request->get('where_condition');
        $order = $this->request->get('order');
        $result = $this->mo_subject->getListData($where_condition, null, '*', $order);
        $this->_successResponse($result);
    }
}