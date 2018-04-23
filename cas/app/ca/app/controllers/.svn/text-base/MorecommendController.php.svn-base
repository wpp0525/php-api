<?php

use Lvmama\Common\Utils\Filelogger;

class MorecommendController extends ControllerBase
{
    private $recommend;
    private $recommend_block;

    public function initialize()
    {
        parent::initialize();
        $this->recommend = $this->di->get('cas')->get('mo_recommend_service');
        $this->recommend_block = $this->di->get('cas')->get('mo_recommend_block_service');
    }

    public function addImageAction()
    {
        $data = $this->request->get('data');
        $rs = $this->recommend->insert($data);

        $this->_successResponse($rs);
    }

    public function updateImageAction()
    {
        $id = $this->request->get('id');
        $data = $this->request->get('data');

        $rs = $this->recommend->update($id, $data);
        $this->_successResponse($rs);
    }

    public function deleteImageAction()
    {
        $id = $this->request->get('id');

        $rs = $this->recommend->delete($id);
        $this->_successResponse($rs);
    }

    public function getImageItemAction()
    {
        $id = $this->request->get('id');
        $result = $this->recommend->getItem($id);
        $this->_successResponse($result);
    }

    public function getImageListAction()
    {
        $where_condition = $this->request->get('where_condition');
        $order = $this->request->get('order');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $page_size = $page_size ? $page_size : 10;
        $current_page = $current_page ? $current_page : 1;
        $limit = array('page_num' => $current_page, 'page_size' => $page_size);

        $list = $this->recommend->getListData($where_condition, $limit, '*', $order);
        $total = $this->recommend->getTotal($where_condition);
        $total_pages = $total ? intval(($total - 1) / $page_size + 1) : 0;

        $this->_successResponse(array('list' => $list, 'total' => $total, 'current_page' => $current_page, 'total_pages' => $total_pages));
    }

    public function addBlockAction()
    {
        $data = $this->request->get('data');
        $rs = $this->recommend_block->insert($data);

        $this->_successResponse($rs);
    }

    public function updateBlockAction()
    {
        $block_id = $this->request->get('block_id');
        $data = $this->request->get('data');

        $rs = $this->recommend_block->update($block_id, $data);
        $this->_successResponse($rs);
    }

    public function getBlockAction()
    {
        $where_condition = $this->request->get('where_condition');
        $result = $this->recommend_block->getOne($where_condition);
        $this->_successResponse($result);
    }

    public function saveSeqsAction()
    {
        $seqs = $this->request->get('seqs');
        if (empty($seqs)) $this->_errorResponse(10001, '排序值参数无效');
        $data = json_decode($seqs, true);
        $params = array();
        foreach ($data as $id => $seq) {
            if (!is_numeric($seq)) continue;
            $params[] = array(':seq' => $seq, ':id' => $id);
        }
        $sql = 'UPDATE mo_recommend SET seq = :seq WHERE id = :id';
        $this->_successResponse($this->recommend->execute($sql, $params, true));
    }
}