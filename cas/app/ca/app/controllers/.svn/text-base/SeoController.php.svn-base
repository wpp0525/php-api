<?php

/**
 * SEO接口
 *
 * @author win.sx
 *
 */
class SeoController extends ControllerBase {

	/**
	 * @var \Lvmama\Cas\Service\DestinationDataService
	 */
	private $dest = null;

	/**
	 * @var \Lvmama\Cas\Service\MoRecommendDataService
	 */
	private $recom = null;

	/**
	 * @var \Lvmama\Cas\Service\CombinationDataService
	 */
	private $combination = null;

	/**
	 * @var \Lvmama\Cas\Service\DistSignDataService
	 */
	private $dist_sign = null;

    protected $manual_url;

	public function initialize(){
		parent::initialize();
		$this->dest = $this->di->get('cas')->get('dest_old_service');
		$this->recom = $this->di->get('cas')->get('mo-recommend-data-service');
		$this->combination = $this->di->get('cas')->get('combination-data-service');
		$this->dist_sign = $this->di->get('cas')->get('old_dist_sign_service');
		$this->category = $this->di->get('cas')->get('seo_category_service');
		$this->crawler_url = $this->di->get('cas')->get('seo_crawler_url_service');
		$this->keyword_url = $this->di->get('cas')->get('seo_keyword_url_service');
		$this->keyword_url_related = $this->di->get('cas')->get('seo_keyword_url_related_service');
		$this->manual_crawler = $this->di->get('cas')->get('seo_manual_crawler_service');
		$this->manual_url = $this->di->get('cas')->get('seo_manual_url_service');
	}


	/**
	 * 取得目的地的周边攻略
	 * @param int $dest_id
	 * @param string $dest_type
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getAroundSeoLinks
	 */
	public function getAroundSeoLinksAction(){

		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		$dest_type = isset($this->dest_type) ? $this->dest_type : '';
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = $this->dest->getAroundSeoLinks($this->dest->getDestById($dest_id));
		$this->_successResponse($data);
	}
	/**
	 * 取得目的地的攻略推荐
	 * @param int $dest_id
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getRecomGuide
	 */
	public function getRecomGuideAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = $this->dest->getRecomGuide($this->dest->getDestById($dest_id));
		$this->_successResponse($data);
	}
	/**
	 * 取得目的地的友情链接
	 * @param int $dest_id
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getSeoOutLink
	 */
	public function getSeoOutLinkAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		$type = isset($this->type) ? $this->type : '';
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');
		$this->dest = $this->di->get('cas')->get('dest_old_service');
		$this->_successResponse($this->dest->getSeoOutLink($dest_id,$type));
	}
	/**
	 * 取得目的地的精选链接
	 * @param int $dest_id
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getSeoInLink
	 */
	public function getSeoInLinkAction(){
		$dest_id = isset($this->dest_id) ?  $this->dest_id : 0;
		$type = isset($this->type) ? $this->type : '';
		if(!$dest_id || !is_numeric($dest_id)){
			$this->_errorResponse(10002, '请传入正确的dest_id');
		}

		$data = $this->dest->getSeoInLink($dest_id, $type);
		$this->_successResponse($data);
	}
	/**
	 * 取得目的地的相关导航
	 * @param int $dest_id
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getDestTags
	 */
	public function getDestTagsAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		if(!$dest_id || !is_numeric($dest_id)){
			$this->_errorResponse(10002,'请传入正确的dest_id');
		}

		$dest = $this->dest->getDestById($dest_id);
		$data = $this->dest->getDestTags($dest);
		$this->_successResponse($data);
	}
	/**
	 * 取得目的地的热门景点SEOLINKS
	 * @param int $dest_id
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getDestTags
	 */
	public function getHotSeoLinksAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		if(!$dest_id || !is_numeric($dest_id)){
			$this->_errorResponse(10002,'请传入正确的dest_id');
		}

		$data = $this->dest->getHotSeoLinks($dest_id);
		$this->_successResponse($data);
	}

	/*  seo底部改版接口 */
	/**
	 * 周边目的地
	 *  参数：dest_id（目的地Id），limit（最大数量）
	 *  返回：around（周边旅游），local（当地旅游）
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getAroundDest?dest_id=100
	 * @author libiying
	 */
	public function getAroundDestAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		$limit = isset($this->limit) ? $this->limit : 20;
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = array();
		$dest = $this->dest->getDestById($dest_id);
		switch($dest['dest_type']){
			case 'CONTINENT'://大洲
			case 'SPAN_COUNTRY'://跨国家地区
				break;
			case 'COUNTRY'://国家
			case 'SPAN_PROVINCE'://跨州省地区
			case 'PROVINCE'://州省
			case 'SPAN_CITY':
			case 'CITY':
				$data['around'] = $this->dest->getSameLevelDest($dest, $limit);
				$data['local'] = $this->dest->getNextLevelDest($dest, $limit);
				break;
			case 'SPAN_COUNTY':
			case 'COUNTY':
				$data['around'] = $this->dest->getSameLevelDest($dest, $limit);
				break;
			default:
				$parent = $this->dest->getParentDest($dest['parent_id'], 'CITY');
				if($parent){
					$data['around'] = $this->dest->getSameLevelDest($parent, $limit);
				}
				break;
		}

		$this->_successResponse($data);
	}

	/**
	 * 热门目的地
	 *  参数：dest_id（目的地Id），limit（最大数量）
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getHotDest?dest_id=100
	 * @author libiying
	 */
	public function getHotDestAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		$limit = isset($this->limit) ? $this->limit : 20;
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = array();
		$dest = $this->dest->getDestById($dest_id);
		switch($dest['dest_type']){
			case 'CONTINENT'://大洲
			case 'SPAN_COUNTRY'://跨国家地区
				break;
			case 'COUNTRY'://国家
			case 'SPAN_PROVINCE'://跨州省地区
			case 'PROVINCE'://州省
				$data['hottest'] = $this->dest->getNextLevelDest($dest, $limit);
				break;
			default:
				$data['hottest'] = $this->dest->getSameLevelDest($dest, $limit);
				break;
		}

		//当月热推
		$season_ids = array();
		$tmp_ids = $this->recom->getRecommendDestIds('lvyou_in_current', array('国内当季'));
		foreach ($tmp_ids as $tmp_id){
			$season_ids[] = $tmp_id['object_id'];
		}
		//主题游玩
		$condition = array(
			'identity = ' => '"lvyou_themes_play"',
			'parent_id <>' => '0',
		);
		$tmp_blocks = $this->recom->getRecommendBlocks($condition, 1, 'seq ASC');
		if(count($tmp_blocks) > 0){
			$tmp_ids = $this->recom->getRecommendDestIds('lvyou_themes_play', array($tmp_blocks[0]['name']));
			foreach ($tmp_ids as $tmp_id){
				$season_ids[] = $tmp_id['object_id'];
			}
		}
		//筛选类型为地区的目的地
		$data['season'] = $this->dest->getListByIds($season_ids, array('stage = ' => 1), 20, null, 'count_been DESC');

		$this->_successResponse($data);
	}

	/**
	 * 热门景点
	 *  参数：dest_id（目的地Id），limit（最大数量）
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getHotSpot?dest_id=100
	 * @author libiying
	 */
	public function getHotSpotAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		$limit = isset($this->limit) ? $this->limit : 20;
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = array();
		$data['hottest'] = $this->dest->getHotSeoLinks($dest_id);

		//当月热推
		$season_ids = array();
		$tmp_ids = $this->recom->getRecommendDestIds('lvyou_in_current', array('国内当季'));
		foreach ($tmp_ids as $tmp_id){
			$season_ids[] = $tmp_id['object_id'];
		}
		//主题游玩
		$condition = array(
			'identity = ' => '"lvyou_themes_play"',
			'parent_id <>' => '0',
		);
		$tmp_blocks = $this->recom->getRecommendBlocks($condition, 1, 'seq ASC');
		if(count($tmp_blocks) > 0){
			$tmp_ids = $this->recom->getRecommendDestIds('lvyou_themes_play', array($tmp_blocks[0]['name']));
			foreach ($tmp_ids as $tmp_id){
				$season_ids[] = $tmp_id['object_id'];
			}
		}
		//筛选类型为景点的目的地
		$data['season'] = $this->dest->getListByIds($season_ids, array('dest_type = ' => "'VIEWSPOT'"), 20, null, 'count_been DESC');

		$this->_successResponse($data);
	}

	/**
	 * 相关导航
	 *  参数：dest_id（目的地Id）
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getNavigation?dest_id=100
	 * @author libiying
	 */
	public function getNavigationAction(){
		$dest_id = isset($this->dest_id) ? $this->dest_id : 0;
		if(!$dest_id || !is_numeric($dest_id)) $this->_errorResponse(10002,'请传入正确的dest_id');

		$data = array();
		$dest = $this->dest->getDestById($dest_id);
		$data['dest'] = $dest;

		//旅游大全（前一版的相关导航）
		$data['tourInfo'] = $this->dest->getDestTags($dest);

		//PK导航
		if(!in_array($dest['dest_type'], array('CONTINENT', 'SPAN_COUNTRY', 'COUNTRY', 'SPAN_PROVINCE', 'PROVINCE'))){
			//省级别及其以上没有pk数据
			$province_pk = $this->dest->getSameLevelDest($dest, 3); //所属的其他同级别
			$d = null;
			if($dest['dest_type'] == 'CITY' || $dest['dest_type'] == 'SPAN_CITY'){
				$d = $this->dest->getParentDest($dest['parent_id'], 'COUNTRY');//(全国的)其他同级别
			}else{
				$d = $this->dest->getParentDest($dest['parent_id'], 'PROVINCE');//(同省的)其他同级别
				if(!$d){
					$d = $this->dest->getParentDest($dest['parent_id'], 'CITY');//(同城的)其他同级别
				}
			}
			$abroad_pk = $this->dest->getDestsByPid($d, $dest['dest_type'], false, false, 3);

			//剔除重复
			$pk = array();
			foreach ($province_pk as $province){
				$tag = false;
				foreach ($abroad_pk as $abroad){
					if($province['dest_id'] == $abroad['dest_id']){
						$tag = true; break;
					}
				}
				if(!$tag){
					$pk[] = $province;
				}
			}
			//剔除本身
			$pk = array_merge($pk, $abroad_pk);
			foreach ($pk as $p){
				if($dest_id != $p['dest_id']){
					$data['guideInfo']['pk'][] = $p;
				}
			}
		}
		//点到点导航（poi交通点到poi景点）
		$traffic = array();
		//获取推荐景点
		$recom_dests = $this->dest->getRecommendDest($dest_id, 'VIEWSPOT', 2);
		//获取交通点
		$condition = array(
			"dest_type NOT IN " => "('LANDMARK','BUSINESS_AREA','SCENIC')",
			"parents LIKE " => "'" .$dest['parents'] . ",%'"
		);
		$recom_signs = $this->dist_sign->getDistsignList($condition, 4, null, "count_been DESC", 'ly_district_sign');
		//构造
		foreach ($recom_signs as $key => $sign){
			if(isset($recom_dests[$key])){
				$traffic[$key]['from_id'] = $sign['dest_id'];
				$traffic[$key]['from_name'] = $sign['dest_name'];
				$traffic[$key]['to_id'] = $recom_dests[$key]['dest_id'];
				$traffic[$key]['to_name'] = $recom_dests[$key]['dest_name'];
			}
		}
		$data['guideInfo']['traffic'] = $traffic;
		//聚合词
		$data['guideInfo']['combination'] = $this->combination->getTopicByDestId($dest_id, "id desc", 10);

		$this->_successResponse($data);

	}
	/***********************************/

	/**
	 * 取得lmm_seo中URL相关的链接
	 * @param string $url
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getUrlRelateLinks
	 */
	public function getUrlRelateLinksAction(){
		$url = isset($this->u) ? urldecode($this->u) : '';
		$category_id = isset($this->category_id) ? $this->category_id : 0;
		if(!$url || !filter_var($url, FILTER_VALIDATE_URL)){
			$this->_errorResponse(10002,'请传入正确的url');
		}
		$data = $this->keyword_url->getUrlRelateLinks($url,$category_id);
		$this->_successResponse($data);
	}
	/**
	 * 取得lmm_seo中URL关键字相关的链接
	 * @param string $url
	 * @return json
	 * @example curl -i -X GET http://ca.lvmama.com/seo/getUrlRelateKeywordLinks
	 */
	public function getUrlRelateKeywordLinksAction(){
		$url = isset($this->u) ? urldecode($this->u) : '';
		$category_id = isset($this->category_id) ? $this->category_id : 0;
		if(!$url || !filter_var($url, FILTER_VALIDATE_URL)){
			$this->_errorResponse(10002,'请传入正确的url');
		}
		$data = $this->keyword_url_related->getUrlRelateKeywordLinks($url,$category_id);
		$this->_successResponse($data);
	}


	public function tAction(){
		//删除度假内链中的废旧数据
		$type = isset($_GET['type']) ? $_GET['type'] : '';
		$host = isset($_GET['host']) ? $_GET['host'] : '172.20.8.106';
		$port = isset($_GET['port']) ? $_GET['port'] : '6379';
		if(!$type) die('done');

		if($type == 'reset_seo'){
			$this->crawler_url->delete('seo_crawler_url','1 = 1');
			$this->crawler_url->delete('seo_keyword_url','1 = 1');
			$this->crawler_url->delete('seo_keyword_url_related','1 = 1');
			$this->crawler_url->delete('seo_manual_crawler','1 = 1');
			$this->crawler_url->getRsBySql('UPDATE seo_manual_url SET crawl_status = 0');
			echo 'execute done';
		}
		if($type == 'flushall_redis'){
			try{
				$redis = new Redis();
				$redis->connect($host,$port);
				var_dump($redis->flushAll());
			}catch (Exception $e){
				var_dump($e);
			}
		}
	}

	/**
	 * 关键词类别
	 * @method manualListAction
	 * @return array           结果集：array(
	 *         		'error'		=> 0,
	 *         		'result'	=>$data
	 *         )
	 */
	public function manualListAction()
	{
		$condition = array();
	    if($this->request->get('keyword')){
			$condition['keyword'] = $this->request->get('keyword');
        }

		if($this->request->get("u") ){
		     $url =  $this->request->get("u");
            //url正则
            $regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
            if(!preg_match($regix,$url)){
                $this->_errorResponse(10002,'请传入正确的url');
            }
			$condition['url']  = $url;
		}
		if( ($this->request->get("type") != null)  ){
			$condition	['type'] = intval($this->request->get("type"));
		}
		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}

	    $data = $this->manual_url->getManualList($condition,$page,$pageSize);

        $this->_successResponse($data);
	}

	/**
	 * 关键词删除
	 * @method manualDelAction
	 * @return array          array('error'=>0,'result'=>data)
	 */
	public function manualDelAction()
    {
        if(empty($this->request->get('ids'))){
            $this->_errorResponse(10003,'id不能为空');
        }
        $ids = $this->request->get('ids');
        $affect = $this->manual_url->delManualByIds($ids);

        $data = array(
            'affect' => $affect
        );
        $this->_successResponse($data);
    }

	/**
	 * 关键词分类
	 * @method manualCategoryAction
	 * @return array              array('error'=>0,'result'=>data)
	 */
	public function manualCategoryAction()
	{
		$data = $this->category->getAllCategory();

		$this->_successResponse($data);
	}

	/**
	 * 新增关键词
	 * @method manualAddAction
	 * @return array         array('error'=>0,'result'=>data)
	 */
	public function manualAddAction()
	{

		$post = $this->request->getPost();
		if(empty($post['keyword']) || empty($post['u']) || empty($post['categoryId']) || empty($post['channelId'])) {
			$this->_errorResponse(10004,'请填写完整参数');
		}
		$maxMatchTimes = 30;
		if(!empty($post['maxMatchTimes'])){
			$maxMatchTimes = intval($post['maxMatchTimes']);
		}
		$data = array();
		$data['keyword']	 = $post['keyword'];
		$data['url']		 = $post['u'];
		$data['categoryId']  = $post['categoryId'];
		$data['channelId']	 = $post['channelId'];
		$data['maxMatchTimes'] = $maxMatchTimes;
		$result = $this->manual_url->addManual($data);
		$this->_successResponse($result);

	}

	/**
	 * 关键词信息
	 * @method manualInfoAction
	 * @return array            array('error'=>0,'result'=>data //关键词信息)
	 */
	public function manualInfoAction()
	{
		$manualId = $this->request->get('id');
		if(!intval($manualId)){
			$this->_errorResponse(10004,'请填写完整参数');
		}
		$manualInfo = $this->manual_url->getManualInfoById($manualId);
		$this->_successResponse($manualInfo);
	}

	/**
	 * 更新关键词
	 * @method manualUpdateAction
	 * @return array              array('error'=>0,'result'=>data //影响行数)
	 */
	public function manualUpdateAction()
	{

		$where = array();
		$data  = array();

		if($this->request->get('id') == null){
			$this->_errorResponse(10004,'请填写完整参数');
		}

		$manualId = $this->request->get('id');
		if(!intval($manualId)){
			$this->_errorResponse(10004,'请填写完整参数');
		}
		if($this->request->get('maxMatchTimes') != null ){
			$data['max_match_times'] = $this->request->get('maxMatchTimes');
		}
		if($this->request->get('status') != null ){
			$data['status'] = $this->request->get('status');
		}

		$where['id'] = $manualId;

		$affect = $this->manual_url->updateManual($where, $data);

		$this->_successResponse($affect);
	}

	/**
	 * 导入关键词
	 * @method manualUploadAction
	 * @return array             array('error'=>0,'result'=>data //影响行数)
	 */
	public function manualUploadAction()
	{
	   if($this->request->get('data') == null){
           $this->_errorResponse(10004,'请填写完整参数');

       }
        $data = $this->request->getPost('data');
        $result = $this->manual_url->insertManual(json_decode($data,true));
        $this->_successResponse($result);

    }

	/**
	 * 抓取url的列表
	 * @method crawlListAction
	 * @return array          array('error'=>0,'result'=>data //url结果集)
	 */
	public function crawlListAction()
	{
		$condition = array();

		if($this->request->get('keyword')){
			$condition['keyword'] = $this->request->get('keyword');
		}

		if($this->request->get("u") ){
			$url =  $this->request->get("u");
			//url正则

			$regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
			if(!preg_match($regix,$url)){
				$this->_errorResponse(10002,'请传入正确的url');
			}
			$condition['url']  = $url;
		}

		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}
		$data = $this->crawler_url->getCrawlList($condition,$page,$pageSize);

		$this->_successResponse($data);
	}

	/**
	 * url 对应关键词关系
	 * @method crawlRelationAction
	 * @return array              array('error'=>0,'result'=>data //结果集)
	 */
	public function crawlRelationAction()
	{
		$condition = array();

		if($this->request->get('keyword')){
			$condition['keyword'] = $this->request->get('keyword');
		}

		if($this->request->get("u") ){
			$url =  $this->request->get("u");
			//url正则

			$regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
			if(!preg_match($regix,$url)){
				$this->_errorResponse(10002,'请传入正确的url');
			}
			$condition['url']  = $url;
		}

		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}
		$data = $this->keyword_url->getCrawlRelation($condition,$page,$pageSize);

		$this->_successResponse($data);
	}

	/**
	 * 抓取url之间关系
	 * @method crawlUrlAction
	 * @return array         array('error'=>0,'result'=>data //结果集)
	 */
	public function crawlUrlAction()
	{
		$condition = array();

		if($this->request->get('keyword')){
			$condition['keyword'] = $this->request->get('keyword');
		}

		if($this->request->get("u") ){
			$url =  $this->request->get("u");
			//url正则

			$regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
			if(!preg_match($regix,$url)){
				$this->_errorResponse(10002,'请传入正确的url');
			}
			$condition['url']  = $url;
		}

		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}

		$data = $this->keyword_url_related->getCrawlUrl($condition,$page,$pageSize);

		$this->_successResponse($data);
	}

	/**
	 * 分类列表
	 * @method categoryListAction
	 * @return array             array('error'=>0,'result'=>data //结果集)
	 */
    public function categoryListAction()
    {
        $condition = array();
        if($this->request->get('category') ){
            $condition['category'] = $this->request->get('category');
        }

		if($this->request->get('parentId') != null){
			$condition['parentId'] = $this->request->get('parentId');
		}

        $page = 1;
        if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
            $page = $this->request->get("current_page");
        }
        $pageSize =15;
        if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
            $pageSize = $this->request->get("pageSize");
        }

        $data = $this->category->getCategoryList($condition,$page,$pageSize);

        $this->_successResponse($data);
    }

	/**
	 * 新增分类
	 * @method categoryAddAction
	 * @return array array('error'=>0,'result'=>data //last id)
	 */
	public function categoryAddAction()
	{
		$data = array();
		if($this->request->get('parentId') == null){
			$this->_errorResponse(10002,'请传入父类参数');
		}
		if($this->request->get('category') == null){
			$this->_errorResponse(10002,'请传入分类');
		}
		if ($this->request->get('parentId') == 0){
			if($this->request->get("u")  == null ){
				$this->_errorResponse(10002,'请传入url');
			}
			$url =  $this->request->get("u");
			//url正则
			$regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
			if(!preg_match($regix,$url)){
				$this->_errorResponse(10002,'请传入正确的url');
			}
			$data['url']  		= $url;
		}
		$data['parentId']	= $this->request->get('parentId');
		$data['category']	= $this->request->get('category');
		$affect = $this->category->addCategory($data);

		$this->_successResponse($affect);
	}

	/**
	 * 空分类删除
	 * @method categoryDelAction
	 * @return array      array('error'=>0,'result'=>data //affect rows)
	 */
	public function categoryDelAction()
	{
		if(empty($this->request->get('id'))){
			$this->_errorResponse(10003,'id不能为空');
		}
		$categoryId = $this->request->get('id');
		$where = 'id='.$categoryId;
		$affect = $this->category->delCategory($where);

		$data = array(
			'affect' => $affect
		);
		$this->_successResponse($data);
	}

	/**
	 * 获取分类信息
	 * @method categoryInfoAction
	 * @return array            array('error'=>0,'result'=>data //category info data)
	 */
	public function categoryInfoAction()
	{
		$categoryId = $this->request->get('id');
		if(!intval($categoryId)){
			$this->_errorResponse(10004,'请填写完整参数');
		}
		$categoryInfo = $this->category->getCategoryInfoById($categoryId);
		$this->_successResponse($categoryInfo);
	}

	/**
	 * 更新分类信息
	 * @method categoryUpdateAction
	 * @return array
	 */
	public function categoryUpdateAction()
	{

		if($this->request->get('categoryId') == null){
			$this->_errorResponse(10002,'请传入完整参数');
		}

		if($this->request->get('category') == null){
			$this->_errorResponse(10002,'请传入分类');
		}
		$data = array();

		if ($this->request->get('parentId') == 0){
			if($this->request->get("u")  == null ){
				$this->_errorResponse(10002,'请传入url');
			}
			$url =  $this->request->get("u");
			//url正则
			$regix = '#(?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:]))#i';
			if(!preg_match($regix,$url)){
				$this->_errorResponse(10002,'请传入正确的url');
			}
			$data['url']  		= $url;
		}
		$data['category']	= $this->request->get('category');

		$where = array();
		$where['id'] = $this->request->get('categoryId');
		$affect = $this->category->updateCategory($where, $data);

		$this->_successResponse($affect);
	}
	
	/**
	 * 关键词对应的url列表
	 * @method manualCrawlerListAction
	 * @return array
	 */
	public function manualCrawlerListAction()
	{
		if($this->request->get('keywordId') == null){
			$this->_errorResponse(10002,'关键词不能为空');
		}
		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}
		
		$keywordId = $this->request->get('keywordId');
		$where = array();
		$where['keywordId'] = $keywordId;
		$data	   = $this->keyword_url->getUrlByKeyword($where, $page, $pageSize);
		$this->_successResponse($data);
	}
	
	/**
	 * url
	 * @method relationListAction
	 * @return array
	 */
	public function relationListAction()
	{
		if($this->request->get('keywordId') == null){
			$this->_errorResponse(10002,'关键词不能为空');
		}
		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}

		$keywordId = $this->request->get('keywordId');
		$where = array();
		$where['keywordId'] = $keywordId;
		#$data	   = $this->keyword_url->getUrlByKeyword($where, $page, $pageSize);
		 $data	   = $this->keyword_url->getManualInfoById($keywordId);
		$this->_successResponse($data);
	}

	/**
	 * URL管理的url列表
	 * @method relationUrlListAction
	 * @return array
	 */
	public function relationUrlListAction()
	{
		if($this->request->get('urlId') == null){
			$this->_errorResponse(10002,'url不能为空');
		}
		$page = 1;
		if($this->request->get("current_page") && is_numeric($this->request->get("current_page")) ){
			$page = $this->request->get("current_page");
		}
		$pageSize =15;
		if($this->request->get("pageSize") && is_numeric($this->request->get("pageSize")) ){
			$pageSize = $this->request->get("pageSize");
		}

		$urlId = $this->request->get('urlId');
		$where = array();
		$where['urlId'] = $urlId;
		$data	   = $this->keyword_url_related->getRelation($where, $page, $pageSize);

		$this->_successResponse($data);
	}

	/**
	 * relation_url info
	 * @method relationUrlInfoAction
	 * @return                 array
	 */
	public function relationUrlInfoAction()
	{
		if($this->request->get('id') == null){
			$this->_errorResponse(10002,'id不能为空');
		}
		$dataId = $this->request->get('id');

		$data = $this->keyword_url_related->getUrlInfoById($dataId);
		$this->_successResponse($data);
	}

	/**
	 * 更新url关联信息
	 * @method relationUrlUpdateAction
	 * @return int                  affectedRows
	 */
	public function relationUrlUpdateAction()
	{
		if($this->request->get('urlId') == null){
			$this->_errorResponse(10002,'urlId');
		}
		$urlId = $this->request->get('urlId');

		$data = array();
		$where= array();
		if($this->request->get('rule') != null){
			$data['rule'] = $this->request->get('rule');
		}

		if($this->request->get('displayLimit') !=null ){
			$data['display_limit'] = $this->request->get('displayLimit');
		}

		$where['url_id'] = $urlId;

		$affect = $this->keyword_url_related->updateUrlRelation($where, $data);
		$this->_successResponse($affect);
	}

	/**
	 * keyword_url 信息
	 * @method relationInfoAction
	 * @return array
	 */
    public function relationInfoAction()
    {
        if($this->request->get('id') == null){
            $this->_errorResponse(10002,'id不能为空');
        }
        $dataId = $this->request->get('id');

        $data = $this->keyword_url->getKeywordInfoById($dataId);
        $this->_successResponse($data);
    }

	/**
	 * 更新keyword_url
	 * @method relationUpdateAction
	 * @return int               affectedRows
	 */
    public function relationUpdateAction()
    {
        if($this->request->get('keywordId') == null){
            $this->_errorResponse(10002,'keywordId缺失');
        }
        $keywordId = $this->request->get('keywordId');

        $data = array();
        $where= array();
        if($this->request->get('rule') != null){
            $data['rule'] = $this->request->get('rule');
        }

        if($this->request->get('displayLimit') !=null ){
            $data['display_limit'] = $this->request->get('displayLimit');
        }

        $where['keyword_id'] = $keywordId;

        $affect = $this->keyword_url->updateRelation($where, $data);
        $this->_successResponse($affect);
    }
	
	/**
	 * 检查manual唯一性（category ,url ,keyword必须唯一）
	 */
    public function checkManualAction()
    {
	    if($this->request->get('categoryId') == null){
		    $this->_errorResponse(10002,'categoryId不存在');
	    }
	    if($this->request->get('u') == null){
		    $this->_errorResponse(10002,'u缺失');
	    }
	    if($this->request->get('keyword') == null){
		    $this->_errorResponse(10002,'keyword缺失');
	    }
	    $categoryId = $this->request->get("categoryId");
	    $url        = $this->request->get("u");
	    $keyword    = $this->request->get("keyword");
	    $result = $this->manual_url->checkManual($categoryId, $url, $keyword);
	    $this->_successResponse($result);
    }
	
}
