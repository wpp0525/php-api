<?php

class EliteimageController extends ControllerBase
{

    private $img_srv;

    public function initialize()
    {
        parent::initialize();
        $this->img_srv = $this->di->get('cas')->get('elite_image_service');
    }

    public function addAction()
    {
        $data = $this->request->get();
        $rt = $this->img_srv->insert(json_decode($data['info'], true));
        $id = $rt['result'];
        $this->_successResponse($id);
    }

    public function editAction()
    {
        $data = $this->request->get();
        $this->_successResponse($this->img_srv->update($data['id'], json_decode($data['info'], true)));
    }

    public function editByAction()
    {
        $data = $this->request->get();
        $this->_successResponse($this->img_srv->updateBy($data['where'], json_decode($data['info'], true)));
    }

    /**
     * 解除关联
     * @author lixiumeng
     * @datetime 2017-12-21T14:38:36+0800
     * @return   [type]                   [description]
     */
    public function delAction()
    {
        $id = $this->request->get('id');
        $this->_successResponse($this->img_srv->del($id));
    }

    public function getImageAction()
    {
        $image_id    = $this->request->get('image_id');
        $object_type = $this->request->get('object_type');
        $page_num    = $this->request->get('page_num');
        $page_size   = $this->request->get('page_size');
        $object_id   = $this->request->get('object_id');
        $img_url     = $this->request->get('img_url');
        $title       = $this->request->get('title');
        $order       = $this->request->get('order');

        $conditions = [
            'image_id'    => $image_id,
            'object_type' => $object_type,
            'object_id'   => $object_id,
            'img_url'     => $img_url,
            'title'       => $title,
            'order'       => $order,
        ];

        $limit = [
            'page_size' => !empty($page_size) ? $page_size : 10,
            'page_num'  => !empty($page_num) ? $page_num : 1,
        ];

        $this->_successResponse($this->img_srv->searchImg($conditions, $limit));
    }

    public function itemAction()
    {
        $id = $this->request->get('id');
        $rs = $this->img_srv->getItem(" * ", "image_id = " . $id);
        $this->_successResponse($rs);
    }

    public function getCountAction()
    {
        $object_id = $this->request->get('object_id');
        $object_type = $this->request->get('object_type');
        $cover = $this->request->get('cover');
        $conditions = [
            'object_type' => $object_type,
            'object_id' => $object_id,
            'cover' => $cover,
        ];
        $this->_successResponse($this->img_srv->count($conditions));
    }


    /**
     * 迁移 - POI 页面图片接口
     * @params dest_id 必选
     * @params num 必选
     */
    public function destIndexImageListAction(){
        $dest_id=intval($this->dest_id);
        $num=intval($this->num);
        $elite_img=$this->img_srv->getImgById($dest_id,$num);
        $this->jsonResponse($elite_img);
    }







}
