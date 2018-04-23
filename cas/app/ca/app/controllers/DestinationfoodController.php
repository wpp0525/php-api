<?php

/**
 * 美食基本信息管理
 */
class DestinationfoodController extends ControllerBase
{

    /**
     * @var \Lvmama\Cas\Service\FoodDestService
     */
    private $food_dest;

    public function initialize()
    {
        parent::initialize();
        $this->foodsrv   = $this->di->get('cas')->get('destination_food_service');
        $this->dest      = $this->di->get('cas')->get('destination-data-service');
        $this->food      = $this->di->get('cas')->get('food-data-service');
        $this->food_dest = $this->di->get('cas')->get('food_dest_service');
    }

    /**
     * 添加美食
     * @author lixiumeng
     * @datetime 2017-11-30T11:40:54+0800
     */
    public function addAction()
    {
        $food_name = $this->request->get('food_name');
        $data      = ['food_name' => $food_name];
        $rs        = $this->foodsrv->add($data);
        $this->_successResponse($rs);
    }

    /**
     * 编辑美食
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
     * 删除美食
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
     * 美食列表
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
        $rs = $this->foodsrv->getItem(" * ", "food_id = " . $id);
        $this->_successResponse($rs);
    }

    /**
     * [getFoodTypesAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:36:04+0800
     * @return   [type]                   [description]
     */
    public function getFoodTypesAction()
    {
        $type = $this->request->get('type');
        $rs   = $this->foodsrv->getSubjectTypes($type);
        $this->_successResponse($rs);
    }

    /**
     * [getFoodRelationTypeAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:36:01+0800
     * @return   [type]                   [description]
     */
    public function getFoodRelationTypeAction()
    {
        $food_id     = $this->request->get('food_id');
        $object_type = $this->request->get('object_type');
        $rs          = $this->foodsrv->getSubjectRealationTypes($food_id, $object_type);
        $this->_successResponse($rs);
    }

    /**
     * [setFoodTypeAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:57+0800
     */
    public function setFoodTypeAction()
    {
        $id         = $this->request->get('id');
        $subject_id = $this->request->get('subject_id');
        $type       = 'food';
        // 先清空已设定的标签
        $this->foodsrv->clearSubjectType($id,$type);
        foreach (explode(',', $subject_id) as $sid) {
            $rs = $this->foodsrv->addSubjectType($id, $sid, $type);
        }
        $this->_successResponse($rs);
    }

    /**
     * [getFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:54+0800
     * @return   [type]                   [description]
     */
    public function getFoodDestAction()
    {
        $food_id = $this->request->get('food_id');
        $rs      = $this->food_dest->getFoodDest($food_id);

        $this->_successResponse($rs);
    }

    /**
     * [addFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:50+0800
     */
    public function addFoodDestAction()
    {
        $dests = $this->request->get('dest_id');
        $id    = $this->request->get('food_id');
        if(is_numeric($id)){
            $tmp = $this->food_dest->getList(array('food_id' => '='.$id),'ly_food_dest',null,'dest_id');
            $exists_dest_id = array();
            foreach($tmp as $row){
                $exists_dest_id[] = $row['dest_id'];
            }
        }
        foreach (explode(',', $dests) as $key => $value) {
            if(in_array($value,$exists_dest_id)) continue;
            $data = [
                'food_id' => $id,
                'dest_id' => $value,
            ];
            $rs = $this->food_dest->add($data);
        }
        $this->_successResponse($rs);
    }

    /**
     * [delFoodDestAction description]
     * @author lixiumeng
     * @datetime 2017-12-11T16:35:46+0800
     * @return   [type]                   [description]
     */
    public function delFoodDestAction()
    {
        $id = $this->request->get('id');
        $rs = $this->food_dest->del($id);
        $this->_successResponse($rs);
    }

    /**
     * 取得目的地的美食列表(美食概述、美食图片、链接、名称、菜系和描述)
     * @param int $dest_id
     * @param int $page
     * @param int $pageSize
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/food/getFoodList
     */
    public function getFoodListAction()
    {
        $dest_id  = isset($this->dest_id) ? $this->dest_id : 0;
        $page     = isset($this->page) && is_numeric($this->page) ? (int) $this->page : 1;
        $pageSize = isset($this->pageSize) && is_numeric($this->pageSize) ? (int) $this->pageSize : 10;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        $data      = array();
        $dest_data = $this->dest->getDestById($dest_id);
        //美食概述
        //$food_summary = $this->food->getSummary($dest_data);
        //美食列表
        $total = $this->food->getDestFoodNum($dest_data);
        if ($total) {
            $total     = (int) $total;
            $totalPage = ceil($total / $pageSize);
            $page      = $page < 1 ? 1 : $page;
            $page      = $page > $totalPage ? $totalPage : $page;
            $result    = $this->food->getRecommendFood($dest_id, array('page' => $page, 'pageSize' => $pageSize));
            if (count($result) < $pageSize) {
                $foodIds = UCommon::parseId($result, 'food_id');
                $result  = array_merge($result, $this->food->getDestHaveFood($dest_data, array('page' => $page, 'pageSize' => $pageSize - count($result)), $foodIds));
            }
            $tmp  = array();
            $list = array();
            foreach ($result as $k => $v) {
                $v['memo'] = strip_tags($v['memo']);
                if ($v['have_img']) {
                    $list[] = $v;
                } else {
                    $tmp[$k] = $v;
                }
            }
            foreach ($tmp as $k => $v) {
                $list[] = $v;
            }
            $data['list']  = $list;
            $data['pages'] = array('itemCount' => $total, 'pageCount' => $totalPage, 'page' => $page, 'pageSize' => $pageSize);
        }
        //$data['summary'] = $food_summary;
        //美食列表
        $this->_successResponse($data);
    }
    /**
     * 根据美食ID取得美食的详细信息
     * @param int food_id 美食ID
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/food/getFoodDetail
     */
    public function getFoodDetailAction()
    {
        $food_id = isset($this->food_id) && is_numeric($this->food_id) ? $this->food_id : 0;
        if (!$food_id) {
            $this->_errorResponse(10002, '请传入正确的food_id');
        }

        $food_data = $this->food->get($food_id);
        if ($food_data) {
            //菜系和封面图及图片总量
            $subject                 = $this->di->get('cas')->get('mo-subject');
            $image                   = $this->di->get('cas')->get('dest_image_service');
            $food_type               = $subject->getSubjectName(array($food_id));
            $food_data['memo']       = isset($food_data['memo']) ? strip_tags($food_data['memo']) : '';
            $food_data['caixi']      = isset($food_type[$food_id]) ? $food_type[$food_id] : array();
            $food_image_count        = $image->getImageNumById($food_id, 'food');
            $food_image              = $image->getCoverByObject($food_id, 'food');
            $food_data['imageTotal'] = $food_image_count ? $food_image_count : 0;
            $food_data['image']      = $food_image ? $food_image : '';
        }
        $this->_successResponse($food_data);
    }

    /**
     * 根据目的地查询餐厅列表
     * @param dest_id 目的地ID
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return json
     * @author shenxiang
     * @example curl -i -X GET http://ca.lvmama.com/food/getRestaurantList?dest_id=100
     */
    public function getRestaurantListAction()
    {
        $dest_id  = isset($this->dest_id) ? $this->dest_id : 0;
        $page     = isset($this->page) && is_numeric($this->page) ? (int) $this->page : 1;
        $pageSize = isset($this->pageSize) && is_numeric($this->pageSize) ? (int) $this->pageSize : 10;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        $data   = $this->dest->getDestById($dest_id);
        $result = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $page, 'pageSize' => $pageSize));
        if ($data) {
            //餐厅列表
            $scenicviewspot = $this->di->get('cas')->get('scenicviewspot-data-service');
            $rec_view       = $scenicviewspot->getRecommendDestByDestid(
                $dest_id,
                'RESTAURANT',
                'RESTAURANT',
                0,
                '',
                2,
                array('page' => $page, 'pageSize' => $pageSize)
            );
            //无图的往后排
            foreach ($rec_view as $k => $v) {
                if (!trim($v['img_url'])) {
                    unset($rec_view[$k]);
                }
            }
            $result = $this->dest->packagingDests($rec_view, $data, 'RESTAURANT', array('page' => $page, 'pageSize' => $pageSize));
            if (is_array($result)) {
                //加上均价、地址、图片、最多7个推荐菜
                $result['list'] = isset($result['list']) && $result['list'] ? $this->dest->getRestaurantDetail($result['list']) : array();
            }
            $tmp = array();
            foreach ($result['list'] as $v) {
                if (isset($v['parent_id'])) {
                    unset($v['parent_id']);
                    unset($v['en_name']);
                    unset($v['cancel_flag']);
                    unset($v['range']);
                    unset($v['star']);
                    unset($v['abroad']);
                    unset($v['ent_sight']);
                    unset($v['count_want']);
                    unset($v['g_longitude']);
                    unset($v['g_latitude']);
                    unset($v['longitude']);
                    unset($v['latitude']);
                    unset($v['have_image']);
                }
                $tmp[] = $v;
            }
            $result['list'] = $tmp;
        }
        $this->_successResponse($result);
    }

    /**
     * 根据美食推荐餐厅(出参:推荐餐厅列表[餐厅ID,餐厅名称，图片，地址，推荐菜列表])
     * @param int $food_id 美食ID
     * @param int $page 页码
     * @param int $pageSize 每页显示条数
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/food/getRestaurantFromFood?food_id=141
     * @author shenxiang
     */
    public function getRestaurantFromFoodAction()
    {
        $food_id  = isset($this->food_id) ? $this->food_id : 0;
        $page     = isset($this->page) && is_numeric($this->page) ? (int) $this->page : 1;
        $pageSize = isset($this->pageSize) && is_numeric($this->pageSize) ? (int) $this->pageSize : 10;
        if ($pageSize > 30) {
            $this->_errorResponse(10002, '请传入正确的pageSize,整数且30以内');
        }

        if (!$food_id || !is_numeric($food_id)) {
            $this->_errorResponse(10003, '请传入正确的food_id');
        }

        $rest = $this->food->getRestByFood($food_id, $page, $pageSize);
        if (isset($rest['list']) && count($rest['list'])) {
            $rest['list'] = $this->dest->getRestaurantDetail($rest['list'], 'food_item');
        }
        $this->_successResponse($rest);
    }
    /**
     * 根据美食取图片列表 (出参:美食图片列表URL)
     * @param int $food_id 美食ID
     * @param int $page 页码
     * @param int $pageSize 每页显示条数
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/food/getImagesFromFood?food_id=181
     * @author shenxiang
     */
    public function getImagesFromFoodAction()
    {
        $food_id  = isset($this->food_id) && is_numeric($this->food_id) ? $this->food_id : 0;
        $page     = isset($this->page) && is_numeric($this->page) ? (int) $this->page : 1;
        $pageSize = isset($this->pageSize) && is_numeric($this->pageSize) ? (int) $this->pageSize : 10;
        if (!$food_id) {
            $this->_errorResponse(10002, '请传入正确的food_id');
        }

        if ($pageSize > 10) {
            $this->_errorResponse(10002, '每次pageSize不能大于10');
        }

        $result      = array('list' => array(), 'pages' => array('itemCount' => 0, 'pageCount' => 0, 'page' => $page, 'pageSize' => $pageSize));
        $image       = $this->di->get('cas')->get('dest_image_service');
        $image_count = $image->getImageNumById($food_id, 'food');
        if ($image_count) {
            $result['pages']['itemCount'] = $image_count;
            $result['pages']['pageCount'] = ceil($image_count / $pageSize);
            $limit                        = (($result['pages']['pageCount'] - 1) * $pageSize) . ',' . $pageSize;
            $image_list                   = $image->getImgById($food_id, $limit, 'food');
            $result['list']               = $image_list ? $image_list : array();
        }
        $this->_successResponse($result);
    }
    /**
     * 根据目的地ID取详情
     * @param int $dest_ids 目的地ID集合(用半角逗号隔开)
     * @return json
     * @example curl -i -X GET http://ca.lvmama.com/food/getDestsByIds?dest_ids=100,87,82
     * @author shenxiang
     */
    public function getDestsByIdsAction()
    {
        $dest_ids = isset($this->dest_ids) ? $this->dest_ids : 0;
        $result   = array();
        foreach (explode(',', $dest_ids) as $dest_id) {
            if ($dest_id && is_numeric($dest_id)) {
                $result[] = $this->dest->getDestById($dest_id);
            }
        }
        $this->_successResponse($result);
    }
    /**
     * 根据目的地ID取得指定美食的主题
     * @param int dest_id
     * @return string json
     * @author shenxiang
     * @example curl -i -X GET http://ca.lvmama.com/food/getTheme?dest_id=100
     */
    public function getThemeAction()
    {
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        $dest_data = $this->dest->getDestById($dest_id);
        //美食概述
        $food_summary = $this->food->getSummary($dest_data);
        $themes       = $this->food->getThemes($dest_data);
        $this->_successResponse(array('summary' => $food_summary, 'list' => $themes, 'dest_name' => $dest_data['dest_name']));
    }
    /**
     * 根据美食ID取得所属菜系
     * @param string food_id
     * @return string json
     * @author shenxiang
     * @example curl -i -X GET http://ca.lvmama.com/food/getCaixi?food_id=181,50
     */
    public function getCaixiAction()
    {
        $food_id = isset($this->food_id) ? $this->food_id : '';
        if (!$food_id) {
            return array();
        }

        $tmp = explode(',', $food_id);
        $ids = array();
        foreach ($tmp as $v) {
            if ($v && is_numeric($v)) {
                $ids[] = $v;
            }
        }
        $subject = $this->di->get('cas')->get('mo-subject');
        $result  = $subject->getSubjectName($ids);
        $this->_successResponse($result);
    }

    /**
     * [getFestinationList 获取目的地列表
     * @author lixiumeng
     * @datetime 2017-12-14T11:34:19+0800
     * @return   [type]                   [description]
     */
    public function destinationListAction()
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
        $this->_successResponse($this->dest->search($searchCondition, $limit));
    }

}
