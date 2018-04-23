<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\RedisAdapter;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

/**
 * 游记机器人 Worker服务类
 * 
 * 浏览 点赞 评论 通知
 *
 * @author mac.zhao
 *        
 */
class RobotWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var CommentDataService
	 */
	private $datasvc;
	
	/**
	 * @var RedisAdapter
	 */
	private $redis;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

	public function __construct($di) {
		$this->travelsvc = $di->get('cas')->get('trip-data-service');
		$this->travelsvc->setReconnect(true);

		$this->trTravelDS = $di->get('cas')->get('tr-travel-data-service');
		$this->trTravelDS->setReconnect(true);
		
		$this->travelCommentTemplateService = $di->get('cas')->get('travel-comment-template-data-service');
		$this->travelCommentTemplateService->setReconnect(true);
		
		$this->vestUserService = $di->get('cas')->get('vest-user-data-service');
		$this->vestUserService->setReconnect(true);
		
		$this->redis = $di->get('cas')->getRedis();
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processPV($timestamp = null, $flag = null) {
	    $now = time();
	    $hour = date('H', $now);
	    $minite = date('i', $now);
	    $second = date('s', $now);
	    
	    if($hour == '00' && $minite == '00' && $second == '00') {
            $matchids = $tripids = array();
	        list($matchids, $tripids) = $this->_getMatchTripids($now, 9);

	        if(!empty($matchids)) {
    	        $recommends = $this->_getRecommends($matchids);
    	        
                foreach ($tripids as $tripid => $day) {
    	            $data = array(
                        'id' => $tripid,
                        'type' => TripStatisticsDataService::PV_INIT,
                        'number' => 0,
    	            );
    	            
    	            if(in_array($day, array(1, 2, 3, 4))) {
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(1200, 2000);
    	                }
    	                else {
    	                    $data['number'] = rand(400, 700);
    	                }
    	            }
    	            else if(in_array($day, array(5, 6, 7))) {
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(600, 1000);
    	                }
    	                else {
    	                    $data['number'] = rand(100, 300);
    	                }
    	            }
    	            else if(in_array($day, array(8, 9, 10))) { // 精华 8-10 天，add rand 100-400
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(100, 400);
    	                }
    	            }
    
    	            if($data['number'] != 0) {
    	                $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data), 1024, rand(1, 14400)); // 0 - 4点随机插入数据
    	            }
    	            
                }
	        }
	    }
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processLike($timestamp = null, $flag = null) {
	    $now = time();
	    $hour = date('H', $now);
	    $minite = date('i', $now);
	    $second = date('s', $now);
	    
	    if($hour == '00' && $minite == '00' && $second == '00') {
            $matchids = $tripids = array();
	        list($matchids, $tripids) = $this->_getMatchTripids($now, 9);

	        if(!empty($matchids)) {
    	        $recommends = $this->_getRecommends($matchids);
    	        
                foreach ($tripids as $tripid => $day) {
    	            $data = array(
                        'id' => $tripid,
                        'type' => TripStatisticsDataService::LIKE_INIT,
                        'number' => 0,
    	            );
    	            
    	            if(in_array($day, array(1, 2, 3, 4))) {
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(15, 30);
    	                }
    	                else {
    	                    $data['number'] = rand(5, 10);
    	                }
    	            }
    	            else if(in_array($day, array(5, 6, 7))) {
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(7, 14);
    	                }
    	                else {
    	                    $data['number'] = rand(1, 4);
    	                }
    	            }
    	            else if(in_array($day, array(8, 9, 10))) { // 精华 8-10 天，add rand 100-400
    	                if($recommends[$tripid] == '2') {
    	                    $data['number'] = rand(1, 6);
    	                }
    	            }
    
    	            if($data['number'] != 0) {
    	                $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data), 1024, rand(1, 14400)); // 0 - 4点随机插入数据
    	            }
    	            
                }
	        }
	    }
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
	    $now = time();

	    //当日23点时间戳
	    $endTime = strtotime(date('Y-m-d 23:00:00', $now));
	    
	    //当日23:59:59时间戳
	    $expireTime = strtotime(date('Y-m-d 23:59:59', $now));
	    
	    $timers = $this->redis->hGetall(RedisDataService::REDIS_ROBOT_TIMER);
	    foreach ($timers as $tripid => $timer) {
	        if($timer <= $now) {
	            $commentCount = $this->redis->hExists(RedisDataService::REDIS_ROBOT_COUNTER, $tripid) ? $this->redis->hGet(RedisDataService::REDIS_ROBOT_COUNTER, $tripid) : 0;
				
				// 读库
            	$travel = $this->trTravelDS->get($tripid);
            	
	            if($commentCount < ($travel['recommend_status'] == '1' ? rand($commentCount, 2) : rand($commentCount > 1 ? $commentCount : 1, 10))) { // 普通游记当日评论小于 当前count-2 随机，精华游记当日评论小于 当前count-10 随机，继续评论
// 	            if($commentCount < 10 && $commentCount < rand($commentCount > 1 ? $commentCount : 1, 10)) { // 普通游记当日评论小于 当前count-2 随机，精华游记当日评论小于 当前count-10 随机，继续评论
    	            
    	            // 向 BEANSTALK_TRIP_COMMENT 队列插入 robot comment 数据 start
    	            $user = $this->vestUserService->get(rand(1, 5627));
    	            $data = array(
    	                'uid' => $user['uid'],
    	                'username' => $user['username'],
    	                'channel' => 'trip',
    	                'object_type' => 'trip',
    	                'obj_type_p_id' => $tripid, // 游记ID
    	                'object_id' => $tripid, // 子类型ID
//     	                'create_time' => time(),
    	                'ip' => '127.0.0.1',
    	                'source' => 'PC',
    	                'memo' => $this->travelCommentTemplateService->get(rand(1, 354)),
    	                'valid' => 'Y',
    	                'status' => '99',
    	            );
    	            
    	            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_COMMENT)->put(json_encode($data), 1024, rand(1, 3600)); // 1小时之内随机发送
    	            // 向 BEANSTALK_TRIP_COMMENT 队列插入 robot comment 数据 end
    	            
    	            // 记录当日 robot comment counter
                    $this->redis->hIncrBy(RedisDataService::REDIS_ROBOT_COUNTER, $tripid, 1);
                    $this->redis->expireAt(RedisDataService::REDIS_ROBOT_COUNTER, $expireTime);
    	            
    	            // 生成下次 robot comment timer
    	            $this->redis->hSet(RedisDataService::REDIS_ROBOT_TIMER, $tripid, rand($timer, $endTime));
	            }
	            else {
	                // 删除该游记当日timer counter, 该游记今日评论结束
	                $this->redis->hDel(RedisDataService::REDIS_ROBOT_TIMER, $tripid);
	                $this->redis->hDel(RedisDataService::REDIS_ROBOT_COUNTER, $tripid);
	            }
	        }
	    }
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processMsg($timestamp = null, $flag = null) {
	    $now = time();
	    $hour = date('H', $now);
	    $minite = date('i', $now);
	    $second = date('s', $now);
	    
	    if($hour == '00' && $minite == '00' && $second == '00') {
    	    $tripids = $this->redis->hGetall(RedisDataService::REDIS_EDIT_TRIPID);
    	    foreach($tripids as $tripid => $editTime) {
    	        $days = floor(($now - $editTime) / 86400);

                if($days == 7 || $days == 30) {
    	            if($days == 7) {
    	                $aid = '0';
    	                $type = 'DRAFT_TRAVEL_NOTICE_7';
    	                $template = '您的草稿游记《#title#》还差一点就能发布了，让我们去 <a target="_blank" href="http://www.lvmama.com/trip/edit/#id#">http://www.lvmama.com/trip/edit/#id#</a> 把它完成吧。';
    	            }
    	            else {
    	                $aid = '0';
    	                $type = 'DRAFT_TRAVEL_NOTICE_30';
    	                $template = '您一个月前创建的游记《#title#》还在角落默默等待着你，请不要冷落它，去 <a target="_blank" href="http://www.lvmama.com/trip/edit/#id#">http://www.lvmama.com/trip/edit/#id#</a> 完成，让更多的人能欣赏你的精彩旅程吧。';
    	            }
    				// 读库
                	$travel = $this->travelsvc->get($tripid);
                	
            	    $keys = array('#title#', '#id#');
            	    $values = array($travel['title'], $travel['trip_id']);
            	    $content = str_replace($keys, $values, $template);
    	            
    	            $data = array(
    	                'aid' => $aid,
    	                'uid' => $travel['uid'],
    	                'subject' => '草稿游记提醒',
    	                'message' => $content,
    	                'type' => $type, // 通知
    	            );
    	            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_MSG)->put(json_encode($data), 1024, rand(1, 86400));
    	            
    	            if($days == 30) {
                        $this->redis->hDel(RedisDataService::REDIS_EDIT_TRIPID, $tripid);
    	            }
    	        }
    	    }
	    }
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
	
	protected function _getMatchTripids($endTime, $days) {
	    $matchids = $tripids = array();
	    for($d = $days; $d >= 0; $d--) {
	        $rkey = RedisDataService::REDIS_AUDIT_TRIPID . date('Ymd', $endTime - $d * 86400); // 按照此顺序，同一ID，多次审核通过，最后一次时间覆盖前面时间
	        $ids = $this->redis->sMembers($rkey);
	        foreach ($ids as $id) {
	            $tripids[$id] = $d + 1;
	            $matchids[] = $id;
	        }
	    }
	    return array($matchids, $tripids);
	}
	
	protected function _getRecommends($ids) {
        // 读库
        $travels = $this->trTravelDS->listTravelById($ids);
        $recommends = array();
        foreach($travels as $value) {
            $recommends[$value['id']] = $value['recommend_status'];
        }
        return $recommends;
	}
}