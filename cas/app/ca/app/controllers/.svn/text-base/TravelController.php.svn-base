<?php

use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\PageviewsDataService;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 游记 控制器
 * 
 * @author mac.zhao
 * 
 */
class TravelController extends ControllerBase {
	
	private $redis;
	
// 	protected $beanstalk;
	
	private $dest_travel_svc;
	
	public function initialize() {
		parent::initialize();
		$this->travelsvc = $this->di->get('cas')->get('trip-data-service');
		$this->dest_travel_svc=$this->di->get('cas')->get('dest_travel_service');
		$this->redis = $this->di->get('cas')->getRedis();
// 		$this->beanstalk = $this->di->get('cas')->getBeanstalk();
	}
	
	/**
	 * 新建:游记信息|数据接口 post.travel.info
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "title=第一个行程test" http://ca.lvmama.com/travel/info-create/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param title | 标题
	 * 
	 */
	public function createInfoAction() {
// 	    $now = time();
	    
// 	    // 插入游记 - 本方法暂时不用
// 	    $data = array();

// 	    if($this->title) {
// 	        $data['title'] = $this->title;
// 	    }
	    
//         if(!empty($data)) {
// 	       $travelid = $this->travelsvc->insert($data);
//         }
	    
// 	    // 草稿
// 	    // 用户保存为草稿 redis 记录
// 	    $this->redis->hSet(RedisDataService::REDIS_EDIT_TRIPID, $travelid, $now);
	    
	    
	    
	    
	    // MQ中插入任务，通过ES接口，从游记内容中提取出目的地列表
// 		$data = array(
// 			'id' => $this->travelid,
// 			'content' => $this->content,
// 		);

		$data = array(
			'id' => 2, //游记ID
		);
		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_DEST)->put(json_encode($data));
		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_SENSITIVEWORD)->put(json_encode($data));
	    
	    
	    $this->_successResponse();
	}
	
	/**
	 * 更新:指定游记信息|数据接口 put.travel.info
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "travelid=116611&status=4" http://ca.lvmama.com/travel/info-update/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @example curl -i -X POST -d "travelid=65182&delStatus=1" http://ca.lvmama.com/travel/info-update/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param travelid | 游记ID | NOT NULL
	 * @param title | 标题
	 * @param audit | 审核状态: 99-已发布, 1-待审核, 2-退稿
	 * @param userStatus | 用户状态: 99-正常, 1-草稿, 2-删除
	 * 
	 */
	public function updateInfoAction() {
	    $now = time();
	    
	    // 更新游记 - 暂时不走更新逻辑，业务层自己处理更新逻辑，本方法只缓存状态
	    $data = array();

	    if($this->title) {
	        $data['title'] = $this->title;
	    }

	    if($this->status) {
	        $data['main_status'] = $this->status;
	    }

	    if($this->delStatus) {
	        $data['del_status'] = $this->delStatus;
	    }

        if(!empty($data)) {
// 	       $this->travelsvc->update($this->travelid, $data);
        }
	    
	    // 审核通过游记，系统随机浏览数、评论数、点赞数
	    if(in_array($this->status, array(4, 99))) {
	        $rkey = RedisDataService::REDIS_AUDIT_TRIPID . date('Ymd', time());
	        $this->redis->sAdd($rkey, $this->travelid);
	        if($this->redis->ttl($rkey) == -1) {
	            $this->redis->expire($rkey, 20 * 24 * 60 * 60); //缓存20天
	        }
	    }
	    
	    // 草稿
	    if(in_array($this->status, array(0, 2))) { // 用户保存为草稿 redis 记录
	        $this->redis->hSet(RedisDataService::REDIS_EDIT_TRIPID, $this->travelid, $now);
	    }
	    else if(in_array($this->status, array(1, 3, 4, 99))) { // 草稿变发布后 删除key REDIS_EDIT_TRIPID
	        $this->redis->hDel(RedisDataService::REDIS_EDIT_TRIPID, $this->travelid);
	    }
	    
	    $this->_successResponse();
	}

    public function destTravelSingleAction(){
        $dest_id=$this->dest_id;
        $forcedb=$this->forcedb?$this->forcedb:false;
        $page_num=$this->pn;
        $page_size=$this->ps?$this->ps:10;
        $limit=intval($this->limit);
        $result=$this->getTravelByDestId($dest_id,$forcedb,$limit?$limit:array('page_num'=>intval($page_num),'page_size'=>intval($page_size)));
        $this->jsonResponse($result);
    }

    /**
     * new dest 行程
     */
    public function destNewDestTravelSingleAction(){

        $dest_id = $this->dest_id;
        $forcedb = $this->forcedb?$this->forcedb:false;
        $limit = intval($this->size);

        $result = $this->dest_travel_svc->getTListByDestId($dest_id, $limit);
        if($result && is_array($result)){
            foreach($result as $key => $res){
                $temp = $this->dest_travel_svc->getTravelViewTotalByTravelId($res['travel_id']);
//                echo json_encode($result);die;
//                var_dump($temp);die;
                if($temp && is_array($temp)){
                    $result[$key]['dest_ids'] = $temp;
                }
            }
        }

        $this->jsonResponse($result);

    }


    public function destTravelMultAction(){
        $dest_id_str=$this->dest_id;
        $forcedb=$this->forcedb?$this->forcedb:false;
        $page_num=$this->pn;
        $page_size=$this->ps?$this->ps:10;
        $limit=intval($this->limit);
        if($dest_id_str){
            $dest_ids=explode(',',$dest_id_str);
        }
        if($dest_ids){
            foreach($dest_ids as $id){
                $result[$id]=$this->getTravelByDestId($id,$forcedb,$limit?$limit:array('page_num'=>intval($page_num),'page_size'=>intval($page_size)));
            }
        }
        $this->jsonResponse($result);
    }

    public function destTravelViewNumAction(){
        $travel_id=$this->travel_id;
        $forcedb=$this->forcedb?$this->forcedb:false;
        $redis_key=RedisDataService::REDIS_TRAVEL_VIEWNUM.$travel_id;
        $result=0;
        if(!$forcedb){
            $result=$this->redis_svc->dataGet($redis_key);
            $result=intval($result);
        }
        if(!$result){
            $result=$this->dest_travel_svc->getTravelViewTotalByTravelId($travel_id);
            if($result){
                $result=count($result);
                $ttl=$this->redisConfig['ttl']['lvyou_travel_viewnum']?$this->redisConfig['ttl']['lvyou_travel_viewnum']:null;
                $this->redis_svc->dataSet($redis_key,$result,$ttl);
            }
        }
        $this->jsonResponse($result);
    }
    private function getTravelByDestId($dest_id,$forcedb=null,$page=null){
        if(is_array($page)){
            $page_key=":page_num:".$page['page_num'].":size:".$page['page_size'];
        }else{
            $page_key="limit:".$page;
        }
        $redis_key=RedisDataService::REDIS_DEST_TRAVEL.$dest_id.$page_key;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->getTravelList($redis_key);
        }
        if(!$result){
            $result=$this->dest_travel_svc->getListByDestId($dest_id,$page);
            if($result){
                $ttl=$this->redisConfig['ttl']['lvyou_dest_travel_list']?$this->redisConfig['ttl']['lvyou_dest_travel_list']:null;
                $this->redis_svc->insertTravelList($redis_key,$result,$ttl);
            }
        }
        return $result;
    }

    private function getTravelIdsByTravelId($travel_id,$forcedb=null){
        $redis_key=RedisDataService::REDIS_TRAVEL_VIEWIDS.$travel_id;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->getTravelList($redis_key);
        }
        if(!$result){
            $result=$this->dest_travel_svc->getTravelViewTotalByTravelId($travel_id);
            if($result) {
                $ttl=$this->redisConfig['ttl']['lvyou_dest_travel_list']?$this->redisConfig['ttl']['lvyou_dest_travel_list']:null;
                $this->redis_svc->insertTravelList($redis_key,$result,$ttl);
            }
        }
        return $result;
    }

    public function  destTravelViewIdsAction(){
        $travel_id=$this->travel_id;
        $forcedb=$this->forcedb?$this->forcedb:false;
        $result=$this->getTravelIdsByTravelId($travel_id,$forcedb);
        $this->jsonResponse($result);
    }

    /**
     * 保存远程图片
     */
    public function saveImageAction(){

        $image_url = $this->image_url;
        $id = $this->id;
        $type = $this->type;
        $cr = $this->cr;

        if(!$image_url || intval($id) <= 0 || !$type)
            $this->jsonResponse(array('error' => 1,'message' => '缺少必要参数'));
        $image_string = $this->curl($image_url);
        $file_path = '/tmp/' . time() . '.jpg';
        $res = file_put_contents($file_path,$image_string);
        if(!$res)
            $this->jsonResponse(array('error' => 1,'message' => 'save faild'));

        $upload_res = $this->UploadImage2Pic($file_path, $id, $type, intval($cr));
        if($upload_res['error'])
            $this->jsonResponse(array('code' =>'100000','msg' => '失败','data' => $upload_res['data']));
        $this->jsonResponse(array('code' => '200','msg' => '成功','data' => $upload_res['data']));
    }


    /**
     * 将图片上传到图库
     * @param string $image_path
     * @param int $id
     * @param $type
     * @param int $cr
     * @return array|string
     */
    private function UploadImage2Pic($image_path = '',$id = 0,$type = '', $cr = 0){
        $java_upload_url = 'http://piclib.lvmama.com/photo-back/photo/photo/uploadImgAndSave.do';
        $post_fields = array(
            'photoType' => $type,
            'photoSecondType' => $type,
            'photoName' => $id,
            'photoCopyrightId' => $cr,
            'isPHP_PC' => 'true',
            'photoAutoCut' => 1,
            'imagePath' => $image_path,
        );
        list($width, $height, $type, $attr) = getimagesize($image_path);
        $result = $this->curl($java_upload_url, 'POST', $post_fields);

        if (isset($result['success']) && $result['success']) {
            $return = array('url' => $result['url'],'width' => $width);
            return array('error' => '0','msg' => 'SUCCESS','data' => $return);
        }
        return array("error" => "1", 'msg' => 'upload image to pic faild');
    }

    private function curl($url, $method = 'GET', $postfields = array(), $headers = array()) {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, 'CAS API SERVICE' . ' ' . '1.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ci, CURLOPT_TIMEOUT, 120);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    $postfields['imagePath'] = new CURLFile($postfields['imagePath']);
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'PUT':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
                }
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
            default:
                curl_setopt($ci, CURLOPT_POST, FALSE);
                if (!empty($postfields)) {
                    $url = $url . "?" . http_build_query($postfields);
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        $string = curl_exec($ci);
        curl_close($ci);
        //过滤非标准json格式字符
        $aBP = strpos($string, '[');//数组符号第一个位置
        $oBP = strpos($string, '{');//对象符号第一个位置
        //如果都不存在有这两个符号，表示非json数据，直接返回原始数据
        if ($aBP === false && $oBP === false) {
            $data = $string;
        } else {
            $aEP = strrpos($string, ']');//数组符号最后一个位置
            $oEP = strrpos($string, '}');//对象符号最后一个位置
            //否则,如果只存在{，那么只返回对象部分数据
            if ($aBP === false) {
                $jsonData = substr($string, $oBP, ($oEP - $oBP + 1));
            } elseif ($oBP === false) {
                //如果只存在[,那么只返回数组部分数据
                $jsonData = substr($string, $aBP, ($aEP - $aBP + 1));
            } else {
                //[和{都存在，那么比较位置大小，取值最小的
                $bP = min($aBP, $oBP);
                $eP = ($bP == $aBP) ? $aEP : $oEP;
                $jsonData = substr($string, $bP, ($eP - $bP + 1));
            }
            $data = json_decode($jsonData, true);
            //超时或者无效接口，直接返回错误信息
            if (isset($data['error']) && $data['error'] && isset($data['status']) && !$data['status']) return array('error' => 'api timeout error');
            //判断是否json数据,非json数据，返回获取到的字符串
            if ($data === null) $data = $string;
        }
        return $data;
    }





}
