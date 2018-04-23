<?php

use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\PageviewsDataService;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;

/**
 * 游记 控制器
 * 
 * @author mac.zhao
 * 
 */
class TripController extends ControllerBase {
	
	private $redis;
	
// 	private $beanstalk;
	
	private $tripsvc;
	
	private $dest_relation_svc;
	
	public function initialize() {
		parent::initialize();
		$this->tripsvc = $this->di->get('cas')->get('trip-data-service');
		$this->redis = $this->di->get('cas')->getRedis();
// 		$this->beanstalk = $this->di->get('cas')->getBeanstalk();
		$this->dest_relation_svc = $this->di->get('cas')->get('dest_relation_service');
	}

	/**
	 * 游记标签获取
	 *
	 * @author zhta
	 *
	 */
	public function getTripTagAction() {
		$new_array=array();
		if($this->trip_id){
			$trip_tags=$this->tripsvc->getTagByTrip($this->trip_id);
			if(!empty($trip_tags)){
				foreach($trip_tags as $k=>$v){
					$new_array[$v["object_id"]][]=array("tag_id"=>$v["tag_id"],"tag_name"=>$v["tag_name"]);
				}
			}
		}
		$this->_successResponse($new_array);
	}

    /**
     * 新老游记判断
     *
     * @author zhta
     *
     */
    public function checkTripAction() {
        $ids=$this->trip_id;
        $old_trips=$this->tripsvc->checkTrip($ids);
        $new_trips=explode(",",$ids);
        $new_array=array();
        if(!empty($old_trips)){
            foreach($old_trips as $k=>$v){
                $trips[]=$v["trip_id"];
            }
            foreach($new_trips as $kk=>$vv){
                if(in_array($vv,$trips)){
                    $new_array[$vv]="old";
                }else{
                    $new_array[$vv]="new";
                }
            }
        }
        $this->_successResponse($new_array);
    }

	/**
	 * 新建:游记信息|数据接口 post.trip.info
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "tripid=1&title=第一个行程test&audit=99" http://ca.lvmama.com/trip/info-create/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param tripid 游记ID (NOT NULL)
	 * @param title 标题
	 * @param audit 审核状态: 99-已发布, 1-待审核, 2-退稿
	 * @param userStatus 用户状态: 99-正常, 1-草稿, 2-删除
	 * 
	 */
	public function createInfoAction() {
	    $now = time();
	    // 更新游记
	    $data = array();
	    if($this->title) {
	        $data['title'] = $this->title;
	    }
	    if($this->audit) {
	        $data['verify'] = $this->audit;
	    }
	    if($this->userStatus) {
	        $data['user_status'] = $this->userStatus;
	    }
        if(!empty($data)) {
	       $this->tripsvc->update($this->tripid, $data);
        }
	    // 审核通过游记，系统随机浏览数、评论数、点赞数
	    if($this->audit == 99) {
	        $rkey = RedisDataService::REDIS_AUDIT_TRIPID . date('Ymd', time());
	        $this->redis->sAdd($rkey, $this->tripid);
	        if($this->redis->ttl($rkey) == -1) {
	            $this->redis->expire($rkey, 20 * 24 * 60 * 60); //缓存20天
	        }
	    }
	    // 草稿
	    if($this->userStatus == 1) { // 用户保存为草稿 redis 记录
	        $this->redis->hSet(RedisDataService::REDIS_EDIT_TRIPID, $this->tripid, $now);
	    }
	    else if(in_array($this->userStatus, array(99, 2))) { // 草稿变发布后 删除key REDIS_EDIT_TRIPID
	        $this->redis->hDel(RedisDataService::REDIS_EDIT_TRIPID, $this->tripid);
	    }
	    $this->_successResponse();
	}
	
	/**
	 * 更新:指定游记信息|数据接口 put.trip.info
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "tripid=1&title=第一个行程test&audit=99" http://ca.lvmama.com/trip/info-update/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param tripid 游记ID (NOT NULL)
	 * @param title 标题
	 * @param audit 审核状态: 99-已发布, 1-待审核, 2-退稿
	 * @param userStatus 用户状态: 99-正常, 1-草稿, 2-删除
	 * 
	 */
	public function updateInfoAction() {
	    $now = time();
	    // 更新游记
	    $data = array();
	    if($this->title) {
	        $data['title'] = $this->title;
	    }
	    if($this->audit) {
	        $data['verify'] = $this->audit;
	    }
	    if($this->userStatus) {
	        $data['user_status'] = $this->userStatus;
	    }
        if(!empty($data)) {
	       $this->tripsvc->update($this->tripid, $data);
        }
	    // 审核通过游记，系统随机浏览数、评论数、点赞数
	    if($this->audit == 99) {
	        $rkey = RedisDataService::REDIS_AUDIT_TRIPID . date('Ymd', time());
	        $this->redis->sAdd($rkey, $this->tripid);
	        if($this->redis->ttl($rkey) == -1) {
	            $this->redis->expire($rkey, 20 * 24 * 60 * 60); //缓存20天
	        }
	    }
	    // 草稿
	    if($this->userStatus == 1) { // 用户保存为草稿 redis 记录
	        $this->redis->hSet(RedisDataService::REDIS_EDIT_TRIPID, $this->tripid, $now);
	    }
	    else if(in_array($this->userStatus, array(99, 2))) { // 草稿变发布后 删除key REDIS_EDIT_TRIPID
	        $this->redis->hDel(RedisDataService::REDIS_EDIT_TRIPID, $this->tripid);
	    }
	    $this->_successResponse();
	}

	public function setTripDataAction(){
		//$this->redis->flushdb();
		$trip_data=$this->tripsvc->getTripAll();
		$this->redis_svc->setTripList($trip_data,RedisDataService::REDIS_TRIP_HASH);
	}

	public function  getTripListAction(){
		 $dest_id=$this->dest_id;
		 $page_num=intval($this->pn);
		 $page_size=intval($this->ps)?intval($this->ps):10;
		 $limit=intval($this->limit);
		 $trip_list=array();
		 $trip_ids=$this->dest_relation_svc->getTripIdsByDestId($dest_id);
		 if($trip_ids){
			 foreach($trip_ids as $trip_id){
				 $trip_ids_array[]=$trip_id['trip_id'];
			 }
			 $trip_ids_array=array_unique($trip_ids_array);
			 foreach($trip_ids_array as $item){
				 $trip_list[]=$this->redis_svc->getTripData(RedisDataService::REDIS_TRIP_HASH.$item);
			 }
			 if($trip_list){
				foreach($trip_list as $key=>$row){
					if($row){
						if($row['elite']=='Y'){
							$trip_list_elite[]=$row;
						}else{
							$trip_list_normal[]=$row;
						}
					}
				}
				$trip_list_elite=Misc::array_sort($trip_list_elite,'publish_time','DESC');
				 $trip_list_normal=Misc::array_sort($trip_list_normal,'publish_time','DESC');
				 $result=array_merge($trip_list_elite,$trip_list_normal);
				 if($result){
                     $total=count($result);
                     if($page_num){
                         $res=array_slice($result,($page_num-1)*$page_size,$page_size);
                         $this->jsonResponse(array('total'=>$total,'list'=>$res));
                     }else{
                         $res=array_slice($result,0,$limit);
                         $this->jsonResponse(array('total'=>$total,'list'=>$res));
                     }
                 }
			 }
		 }
	}
}
