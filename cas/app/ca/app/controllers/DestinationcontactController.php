<?php

use Lvmama\Common\Utils\Filelogger;

class DestinationcontactController extends ControllerBase
{
    private $contact_srv;

    public function initialize()
    {
        parent::initialize();
        $this->contact_srv = $this->di->get('cas')->get('destination_contact_service');
    }

    public function addAction()
    {
        $data = $this->request->get('data');
        $rs = $this->contact_srv->insert($data);

        $this->_successResponse($rs);
    }

    public function updateAction()
    {
        $id = $this->request->get('id');
        $data = $this->request->get('data');

        $rs = $this->contact_srv->update($id, $data);
        $this->_successResponse($rs);
    }

    public function getOneAction()
    {
        $dest_id = $this->request->get('dest_id');
        if(empty($dest_id)){
            $this->_errorResponse(100010,'缺少参数');
        }
        $whereCondition = 'dest_id = ' . $dest_id;
        $result = $this->contact_srv->getOne($whereCondition);
        $this->_successResponse($result);
    }

}