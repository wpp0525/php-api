<?php

/**
 * 商品基本信息管理
 */
class DestinationgoodsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->foodsrv    = $this->di->get('cas')->get('destination_goods_service');
        $this->food       = $this->di->get('cas')->get('food-data-service');
        $this->goods_dest = $this->di->get('cas')->get('goods_dest_service');
    }

    /**
     * 添加商品
     * @author lixiumeng
     * @datetime 2017-11-30T11:40:54+0800
     */
    public function addAction()
    {
        $goods_name = $this->request->get('goods_name');
        $data       = ['goods_name' => $goods_name];
        $rs         = $this->foodsrv->add($data);
        $this->_successResponse($rs);
    }

    /**
     * 编辑商品
     * @author lixiumeng
     * @datetime 2017-12-04T14:14:50+0800
     * @return   [type]                   [description]
     */
    public function editAction()
    {
        $data = $this->request->get();
        $rs   = $this->foodsrv->edit($data['id'], json_decode($data['info'], true));

        $this->_successResponse($rs);
    }

    /**
     * 删除商品
     * @author lixiumeng
     * @datetime 2017-12-04T14:15:03+0800
     * @return   [type]                   [description]
     */
    public function delAction()
    {
        $id = $this->request->get('id');
        $rs = $this->foodsrv->del($id);
        $this->_successResponse($rs);
    }

    /**
     * 商品列表
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
        $this->_successResponse($this->foodsrv->search($searchCondition, $limit));
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
        $rs = $this->foodsrv->getItem(" * ", "goods_id = " . $id);

        $this->_successResponse($rs);
    }

    /**
     * [getFoodTypesAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:36:04+0800
     * @return   [type]                   [description]
     */
    public function getGoodsTypesAction()
    {
        $type          = $this->request->get('type');
        $this->foodsrv = $this->di->get('cas')->get('destination_food_service');
        $rs            = $this->foodsrv->getSubjectTypes($type);
        $this->_successResponse($rs);
    }

    /**
     * [getFoodRelationTypeAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:36:01+0800
     * @return   [type]                   [description]
     */
    public function getGoodsRelationTypeAction()
    {
        $goods_id      = $this->request->get('goods_id');
        $object_type   = $this->request->get('object_type');
        $this->foodsrv = $this->di->get('cas')->get('destination_food_service');
        $rs            = $this->foodsrv->getSubjectRealationTypes($goods_id, $object_type);
        $this->_successResponse($rs);
    }

    /**
     * [setFoodTypeAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:57+0800
     */
    public function setGoodsTypeAction()
    {
        $id            = $this->request->get('id');
        $subject_id    = $this->request->get('subject_id');
        $type          = 'goods';
        $this->foodsrv = $this->di->get('cas')->get('destination_food_service');
        // 先清除已设置的数据
        $this->foodsrv->clearSubjectType($id,$type);
        foreach (explode(',', $subject_id) as $sid) {
            $rs = $this->foodsrv->addSubjectType($id, $sid, $type);
        }
        $this->_successResponse($rs);
    }

    /**
     * [addFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:50+0800
     */
    public function addGoodsDestAction()
    {
        $dests = $this->request->get('dest_id');
        $id    = $this->request->get('id');
        if(is_numeric($id)){
            $tmp = $this->goods_dest->getList(array('goods_id' => '='.$id),'ly_goods_dest',null,'dest_id');
            $exists_dest_id = array();
            foreach($tmp as $row){
                $exists_dest_id[] = $row['dest_id'];
            }
        }
        foreach (explode(',', $dests) as $key => $value) {
            if(in_array($value,$exists_dest_id)) continue;
            $data = [
                'goods_id' => $id,
                'dest_id'  => $value,
            ];
            $rs = $this->goods_dest->add($data);
        }
        $this->_successResponse($rs);
    }

    /**
     * [delFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:46+0800
     * @return   [type]                   [description]
     */
    public function delGoodsDestAction()
    {
        $id = $this->request->get('id');
        $rs = $this->goods_dest->del($id);
        $this->_successResponse($rs);
    }

    /**
     * [getFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:54+0800
     * @return   [type]                   [description]
     */
    public function getGoodsDestAction()
    {
        $id = $this->request->get('id');
        $rs = $this->goods_dest->getGoodsDest($id);

        $this->_successResponse($rs);
    }

}
