<?php

/**
 * 管理员控制器
 * 
 * @author libiying
 *
 */
class AdsController extends ControllerBase {

    /**
     * @var \Lvmama\Cas\Service\Ads\AdsBannerDataService
     */
    private $ads_banner_svc;

    /**
     * @var \Lvmama\Cas\Service\Ads\AdsZoneDataService
     */
    private $ads_zone_svc;

    /**
     * @var \Lvmama\Cas\Service\Ads\AdsCampaignDataService
     */
    private $ads_campaign_svc;

    /**
     * @var \Lvmama\Cas\Service\Ads\AdsPropertyDataService
     */
    private $ads_property_svc;

    public function initialize() {
        parent::initialize();
        $this->ads_banner_svc = $this->di->get('cas')->get('ads_banner_service');
        $this->ads_zone_svc = $this->di->get('cas')->get('ads_zone_service');
        $this->ads_campaign_svc = $this->di->get('cas')->get('ads_campaign_service');
        $this->ads_property_svc = $this->di->get('cas')->get('ads_property_service');
    }


	/**
     * 广告列表
	 */
	public function bannerListAction() {
        $condition = [];
        $page = $this->page;
        $page_size = $this->pageSize;
        $limit = array('page_num' => $page, 'page_size' => $page_size);
        $banners = $this->ads_banner_svc->getBannerList($condition, $limit);
        if(empty($banners)) {
        	$this->_errorResponse(DATA_NOT_FOUND, '广告信息不存在');
        	return;
        }
        $total = $this->ads_banner_svc->getTotal($condition);
        $this->jsonResponse(array('results' => $banners, 'total' => intval($total), 'page' => $page, 'pageSize' => $page_size));
	}

    /**
     * 广告(包括详情)
     *
     * @param id int 广告id
     * @param show_detail int 是否显示广告详情，如果广告类型为 img，则detail为一个对象，反之为数组
     * @param show_property int 是否显示属性详情
     */
    public function bannerAction(){
        $id = $this->id;
        $show_detail = $this->show_detail;
        $show_property = $this->show_property;
        $condition['id = '] = $id;
        $banner = $this->ads_banner_svc->getBanner($condition, null, $show_detail, $show_property);
        if(empty($banner)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位信息不存在');
            return;
        }
        $this->jsonResponse($banner);
    }

    /**
     * 广告详情
     */
    public function bannerDetailAction(){
        $id = $this->id;
        $banner_id = $this->banner_id;
        if($id){
            $condition['id = '] = $id;
            $detail = $this->ads_banner_svc->getBannerDetail($condition, null, true);
        }elseif($banner_id){
            $condition['banner_id = '] = $banner_id;
            $detail = $this->ads_banner_svc->getBannerDetail($condition);
        }
        if(empty($detail)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位信息不存在');
            return;
        }
        $this->jsonResponse($detail);
    }


    /**
     * 广告位属性
     */
    public function bannerPropertyAction(){
        $id = $this->id;
        $property = $this->ads_banner_svc->getBannerProperty($id);
        if(empty($property)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位属性不存在');
            return;
        }
        $this->jsonResponse($property);
    }

	/**
	 * 广告新增
	 */
	public function bannerAddAction() {
		$post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->ads_banner_svc->insert($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'广告信息新增失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

    /**
     * 广告详情新增
     */
    public function bannerDetailAddAction() {
        $post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->ads_banner_svc->insertDetail($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'广告详情信息新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }
	
	/**
	 * 广告更新
	 */
	public function bannerUpdateAction() {
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->ads_banner_svc->update($id, $post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'广告信息更新失败');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

    /**
     * 广告更新
     */
    public function bannerDetailUpdateAction() {
        $post = $this->request->getPost();
        $id = intval($post['id']);

        if(!$id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：ID');
            return;
        }

        unset($post['id'], $post['api']);
        if(!empty($post)) {
            $post['update_time'] = time();
            $result = $this->ads_banner_svc->updateDetail($id, $post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '广告详情信息更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 删除广告详情
     */
    public function bannerDetailDelAction(){
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['api']);
        if(!empty($post)) {
            $result = $this->ads_banner_svc->deleteDetail($id);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '广告详情信息删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 广告位列表
     */
    public function zoneListAction() {
        $condition = [];
        $page = $this->page;
        $page_size = $this->pageSize;
        $limit = array('page_num' => $page, 'page_size' => $page_size);
        $zones = $this->ads_zone_svc->getZoneList($condition, $limit);
        if(empty($zones)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位信息不存在');
            return;
        }
        $total = $this->ads_zone_svc->getTotal($condition);
        $this->jsonResponse(array('results' => $zones, 'total' => intval($total), 'page' => $page, 'pageSize' => $page_size));
    }

    /**
     * 广告位
     */
    public function zoneAction(){
        $id = $this->id;
        $show_property = $this->show_property;

        $condition['id = '] = $id;
        $zone = $this->ads_zone_svc->getZone($condition, null, $show_property);
        if(empty($zone)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位信息不存在');
            return;
        }
        $this->jsonResponse($zone);
    }

    /**
     * 广告位新增
     */
    public function zoneAddAction() {
        $post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->ads_zone_svc->insert($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'广告位信息创建失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 广告位更新
     */
    public function zoneUpdateAction() {
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
            $post['update_time'] = time();
            $result = $this->ads_zone_svc->update($id, $post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'广告位信息更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 广告位属性
     */
    public function zonePropertyAction(){
        $id = $this->id;
        $property = $this->ads_zone_svc->getZoneProperty($id);
        if(empty($property)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位属性不存在');
            return;
        }
        $this->jsonResponse($property);
    }

    /**
     * 广告位竞选记录
     */
    public function zoneCampaignAction(){
        $id = $this->id;
        $campaign = $this->ads_zone_svc->getZoneCampaign($id);
        if(empty($campaign)) {
            $this->_errorResponse(DATA_NOT_FOUND, '广告位竞选记录不存在');
            return;
        }
        $this->jsonResponse($campaign);
    }

    /**
     * 获取竞选记录
     */
    public function campaignAction(){
        $zone_id = $this->zone_id;
        $status = $this->status;

        if($zone_id){
            $condition['zone_id = '] = $zone_id;
        }
        if($status){
            $condition['status  = '] = "'$status'";
        }
        $campaign = $this->ads_campaign_svc->getCampaign($condition);
        if(empty($campaign)) {
            $this->_errorResponse(DATA_NOT_FOUND, '竞选记录不存在');
            return;
        }
        $this->jsonResponse($campaign);
    }

    /**
     * 删除竞选
     */
    public function campaignDelAction(){
        $post = $this->request->getPost();
        $campaign_id = intval($post['campaign_id']);
        if(!$campaign_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：campaign_id'); return;
        }

        $result = $this->ads_campaign_svc->deleteCampaign($campaign_id);
        if(!$result){
            $this->_errorResponse(OPERATION_FAILED, '关联属性删除失败'); return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 系统生成竞选记录
     */
    public function campaignRecommendAction(){
        $post = $this->request->getPost();
        $zone_id = intval($post['zone_id']);

        if(!$zone_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：zone_id'); return;
        }

        $campaigns = array();
        //查询zone-property
        $properties = $this->ads_zone_svc->getZoneProperty($zone_id);
        //查询与zone-property有关联的banner
        foreach ($properties as $property){
            $banners = $this->ads_banner_svc->getBannerByProperty($property['id']);
            if($banners){
                foreach ($banners as $banner){
                    if(isset($campaigns[$banner['id']])){
                        //计算总权值
                        $campaigns[$banner['id']]['total_weight'] += $property['weight'];
                        continue;
                    }
                    $campaigns[$banner['id']] = array(
                        'banner_id' => $banner['id'],
                        'zone_id' => $zone_id,
                        'type' => 'auto',
                        'total_weight' => $property['weight'],
                        'status' => 'failed',
                    );
                }
            }
        }
        //新增竞选列表
        if(!$campaigns){
            $this->_errorResponse(DATA_NOT_FOUND, '系统没有匹配到符合条件的广告！请关联属性后重试！'); return;
        }
        foreach ($campaigns as $data){
            $exist = $this->ads_campaign_svc->getCampaign(array(
                'zone_id = ' => $data['zone_id'],
                'banner_id = ' => $data['banner_id'],
            ));
            if($exist){
                $this->ads_campaign_svc->update($exist['id'], $data);
            }else{
                $this->ads_campaign_svc->insert($data);
            }
        }
        $this->jsonResponse($campaigns);
    }

    /**
     *获取当选记录，包括广告信息，和广告位信息
     */
    public function campaignReignAction(){
        $zone_id = $this->zone_id;
        $match_url = urldecode($this->match_url);
        //如果是匹配URL
        $campaign = null;
        $banner = null;
        $zone = null;
        if($match_url){
            $campaign = $this->ads_campaign_svc->getCampaignList(array(
                'zone_id = ' => $zone_id,
            ));
            $banner_ids = array();
            foreach ($campaign as $c){
                $banner_ids[] = $c['banner_id'];
            }
            if(count($banner_ids) > 0){
                $banner = $this->ads_banner_svc->getBanner(array(
                    'id in ' => '(' . implode(',', $banner_ids) . ')',
                    'match_url <> ' => "''",
                    'locate' => '(match_url,"' . $match_url . '")', //使用locate()函数进行反向模糊匹配
                ));
            }
        }
        if(!$campaign || !$banner){
            $campaign = $this->ads_campaign_svc->getCampaign(array(
                'zone_id = ' => $zone_id,
                'status = ' => '"reign"',
            ));
            if($campaign){
                $banner = $this->ads_banner_svc->getBanner(array(
                    'id = ' => $campaign['banner_id'],
                ));
            }
        }
        $zone = $this->ads_zone_svc->getZone(array(
            'id = ' => $zone_id,
        ));
        if(!$campaign){
            $this->_errorResponse(DATA_NOT_FOUND, '竞选记录不存在');
            return;
        }
        if(!$banner){
            $this->_errorResponse(DATA_NOT_FOUND, '当选的广告记录不存在');
            return;
        }
        if(!$zone){
            $this->_errorResponse(DATA_NOT_FOUND, '广告位记录不存在');
            return;
        }

        $this->jsonResponse(array(
//            'campaign' => $campaign,
            'zone' => $zone,
            'banner' => $banner,
        ));
    }

    /**
     * 广告位当选，分为两种：manual:手动指定;auto:系统算法指定
     */
    public function zoneElectionAction(){
        $post = $this->request->getPost();
        $id = intval($post['id']);
        $zone_id = intval($post['zone_id']);
        $type = $post['type'];
        $campaign_cycle = $post['campaign_cycle'];

        if(!in_array($type, array('auto', 'manual'))){
            $this->_errorResponse(PARAMS_ERROR, '参数type只能为 auto|manual'); return;
        }
        if($type == 'manual' && !$id){
            $this->_errorResponse(PARAMS_ERROR, '当type为manual时，id必须'); return;
        }

        if($type == 'auto'){
            $result = $this->ads_campaign_svc->autoElected($zone_id, $campaign_cycle);
        }else if($type == 'manual'){
            $result = $this->ads_campaign_svc->elected($id, $zone_id, $type, $campaign_cycle);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'广告任选失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 属性
     */
    public function propertyAction(){
        $id = $this->id;
        $condition['id = '] = $id;
        $property = $this->ads_property_svc->getProperty($condition);
        if(empty($property)) {
            $this->_errorResponse(DATA_NOT_FOUND, '属性信息不存在');
            return;
        }
        $this->jsonResponse($property);
    }

    /**
     * 属性列表
     */
    public function propertyListAction(){
        $page = $this->page;
        $page_size = $this->pageSize;
        $id_not_in = $this->id_not_in;
        $condition = '';

        if($id_not_in){
            $condition .= ' id NOT IN (' . $id_not_in . ')';
        }
        $limit = array('page_num' => $page, 'page_size' => $page_size);
        $properties = $this->ads_property_svc->getPropertyList($condition, $limit);
        if(empty($properties)) {
            $this->_errorResponse(DATA_NOT_FOUND, '属性信息不存在');
            return;
        }
        $total = $this->ads_property_svc->getTotal($condition);
        $this->jsonResponse(array('results' => $properties, 'total' => intval($total), 'page' => $page, 'pageSize' => $page_size));
    }

    /**
     * 属性新增
     */
    public function propertyAddAction() {
        $post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->ads_property_svc->insert($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '属性创建失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 属性更新
     */
    public function propertyUpdateAction() {
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
            $post['update_time'] = time();
            $result = $this->ads_property_svc->update($id, $post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '属性更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 关联属性
     */
    public function propertyRelationAddAction(){
        $post = $this->request->getPost();
        $type = trim($post['type']);
        $type_id = intval($post['type_id']);
        $property_id = trim($post['property_id']); //可以为逗号分隔的id字符串

        if(!$type){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：type'); return;
        }
        if(!in_array($type, array('zone', 'banner'))){
            $this->_errorResponse(PARAMS_ERROR, '参数type只能为 zone|banner'); return;
        }
        if(!$type_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：type_id'); return;
        }
        if(!$property_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：property_id'); return;
        }

        $ids = explode(',', $property_id);
        $result = array();
        foreach ($ids as $id){
            $res = $this->ads_property_svc->buildPropertyRelation($type, $type_id, $id);
            if($res){
                $result[] = $res;
            }
        }

        $result = implode(',', $result);
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '属性关联失败，请确认关联属性是否已存在');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 删除广告位关联属性
     */
    public function propertyRelationDelAction(){
        $post = $this->request->getPost();
        $type = isset($post['type']) ? trim($post['type']) : '';
        $type_id = isset($post['type_id']) ? intval($post['type_id']) : 0;
        $property_id = isset($post['property_id']) ? intval($post['property_id']) : 0;
        if(!$type){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：type'); return;
        }
        if(!in_array($type, array('zone', 'banner'))){
            $this->_errorResponse(PARAMS_ERROR, '参数type只能为 zone|banner'); return;
        }
        if(!$type_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：type_id'); return;
        }
        if(!$property_id){
            $this->_errorResponse(PARAMS_ERROR, '缺少参数：property_id'); return;
        }

        $result = $this->ads_property_svc->deletePropertyRelation($type, $type_id, $property_id);
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '关联属性删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

}

