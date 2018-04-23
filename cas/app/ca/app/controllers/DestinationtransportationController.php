<?php

class DestinationtransportationController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->trans_srv = $this->di->get('cas')->get('destination_transportation_service');
    }

    /**
     * 添加
     * @author lixiumeng
     * @datetime 2017-12-25T14:06:12+0800
     */
    public function addAction()
    {
        $data = $this->request->get('data');
        $data = json_decode($data, true);
        $this->_successResponse($this->trans_srv->insert($data));
    }

    /**
     * 编辑
     * @author lixiumeng
     * @datetime 2017-12-25T14:06:22+0800
     * @return   [type]                   [description]
     */
    public function editAction()
    {
        $id   = $this->request->get('id');
        $data = json_decode($this->request->get('data'), true);
        $this->_successResponse($this->trans_srv->update($id, $data));
    }

    /**
     * 删除
     * @author lixiumeng
     * @datetime 2017-12-25T14:06:31+0800
     * @return   [type]                   [description]
     */
    public function delAction()
    {
        $id = $this->request->get('id');
        $this->_successResponse($this->trans_srv->del($id));
    }

    /**
     * 列表
     * @author lixiumeng
     * @datetime 2017-12-04T14:15:10+0800
     * @return   [type]                   [description]
     */
    public function listAction()
    {
        $searchCondition = $this->request->get();
        $limit           = array();
        if (isset($searchCondition['page_num'])) {
            $limit['page_num'] = $searchCondition['page_num'];
            unset($searchCondition['page_num']);
        }
        if (isset($searchCondition['page_size'])) {
            $limit['page_size'] = $searchCondition['page_size'];
            unset($searchCondition['page_size']);
        }
        $this->_successResponse($this->trans_srv->search($searchCondition, $limit));
    }

    /**
     * 获取单条数据
     * @author lixiumeng
     * @datetime 2017-12-04T14:23:55+0800
     * @return   [type]                   [description]
     */
    public function itemAction()
    {
        $id = $this->request->get('id');
        $rs = $this->trans_srv->getItem(" * ", "trans_id = " . $id);
        $this->_successResponse($rs);
    }
}
