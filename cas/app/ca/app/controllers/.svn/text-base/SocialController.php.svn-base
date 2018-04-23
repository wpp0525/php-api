<?php

use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\PageviewsDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 社会化交互属性 控制器
 * 
 * @author mac.zhao
 * 
 * @example 浏览 Pageview 点赞 like 评论 comment 分享 share
 */
class SocialController extends ControllerBase {
	
	const PAGE_VIEW = 1;
	
	private $params;
	
	/**
	 * @var PageviewsDataService
	 */
	private $pageviewssvc;
	
	private $redis;
	
// 	private $beanstalk;
	
	public function initialize() {
		parent::initialize();
		$this->pageviewssvc = $this->di->get('cas')->get('pageviews-data-service');
		$this->likesvc = $this->di->get('cas')->get('like-data-service');
		$this->redis = $this->di->get('cas')->getRedis();
// 		$this->beanstalk = $this->di->get('cas')->getBeanstalk();
	}
	
	/**
	 * 获取:指定游记|浏览记录列表|数据接口 get.social.trip.pageview.list
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X GET http://ca.lvmama.com/social/trip/1/pageview-list/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 */
	public function listTripPageviewAction() {

	}
	
	/**
	 * 新建:指定游记|浏览记录|数据接口 post.social.trip.pageview
	 * 
	 * @author mac.zhao
	 * 
	 * @param tripid
	 * @param ip
	 * @param date default today
	 * @param source default PC
	 * 
	 * @example curl -i -X POST -d "tripid=1&ip=127.0.0.1&date=2015-12-22&source=PC&deviceid=1&browser=firefox" http://ca.lvmama.com/social/trip-pageview-create/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 */
	public function createTripPageviewAction() {
		$data = array(
			'channel'		=> 'trip',
			'object_type'	=> 'trip',
			'object_id'		=> $this->tripid,
			'ip'		=> $this->ip,
			'date'		=> $this->date,
			'create_time'		=> time(),
			'source'		=> $this->source,
			'device_id'		=> $this->deviceid,
			'browser'		=> $this->browser,
		);
		$this->pageviewssvc->insert($data);

		// 增加浏览数
		$data = array(
			'id' => $this->tripid,
			'type' => TripStatisticsDataService::PV_REAL,
			'number' => 1,
		);
		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data));
		
	    $this->_successResponse();
	}
	
	/**
	 * 新建:指定游记|点赞|数据接口 post.social.trip.like
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "tripid=1&uid=1&ip=127.0.0.1&source=PC" http://ca.lvmama.com/social/trip-like-create/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 */
	public function createTripLikeAction() {
		$data = array(
			'uid'		=> $this->uid,
			'username'		=> $this->uid,
			'channel'		=> 'trip',
			'object_type'	=> 'trip',
			'object_id'		=> $this->tripid,
			'create_time'		=> time(),
			'ip'		=> $this->ip,
			'source'		=> $this->source,
		);
		$this->likesvc->insert($data);

		// 增加喜欢数 - 目前数据库层面通过trigger触发统计
// 		$data = array(
// 			'id' => $this->tripid,
// 			'type' => TripStatisticsDataService::LIKE_REAL,
// 			'number' => 1,
// 		);
// 		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data));
		
	    $this->_successResponse();
	}
	
	/**
	 * 更新:指定游记|指定点赞|状态|数据接口 put.social.trip.like
	 * 
	 * @author mac.zhao
	 * 
	 * @example curl -i -X POST -d "tripid=1&uid=1&ip=127.0.0.1&status=1&source=PC" http://ca.lvmama.com/social/trip-like-update/json/lvmama/1432628954/df9c547fc34adad1820c9c93dfac5bc2
	 * 
	 * @param status 0-不喜欢 1-喜欢
	 */
	public function updateTripLikeAction() {
		$data = array(
			'canceled'		=> $this->status == 1 ? 'N' : 'Y',
			'ip'		=> $this->ip,
			'source'		=> $this->source,
		);
		$this->likesvc->updateByTripid($this->tripid, $this->uid, $data);

		// 更新喜欢数
		$data = array(
			'id' => $this->tripid,
			'type' => TripStatisticsDataService::LIKE_REAL,
			'number' => $this->status == 1 ? 1 : -1,
		);
		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data));
		
	    $this->_successResponse();
	}
}
