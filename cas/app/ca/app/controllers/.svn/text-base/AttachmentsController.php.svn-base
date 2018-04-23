<?php

class AttachmentsController extends ControllerBase
{

    private $attachments;

    public function initialize()
    {
        parent::initialize();
        $this->attachments = $this->di->get('cas')->get('mo_attachments_service');
    }

    public function addAction()
    {
        $data = $this->request->get('post');
        $data = json_decode($data,true);
        $result = $this->attachments->insert($data);

        $this->_successResponse($result);
    }

}
