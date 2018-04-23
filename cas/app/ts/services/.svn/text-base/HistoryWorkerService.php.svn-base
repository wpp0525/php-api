<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\MsgDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

/**
 * 历史数据处理 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class HistoryWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var MsgDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

	public function __construct($di) {
		$this->tripsvc = $di->get('cas')->get('trip-data-service');
		$this->tripsvc->setReconnect(true);

		$this->redis = $di->get('cas')->getRedis();
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	public function process($timestamp = null, $flag = null) {
	    
	}
	
	/**
	 * 针对游记草稿历史数据，发送通知消息
	 * 
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processMsg($timestamp = null, $flag = null) {
	    $now = time();
	    // 第一次统计时范围为：7日内草稿游记进Redis
	    $trips = $this->tripsvc->getTripsByInterval($now - 7 * 86400, $now);
	    foreach($trips as $trip) {
	       $this->redis->hSet(RedisDataService::REDIS_EDIT_TRIPID, $trip['trip_id'], $trip['modify_time']);
	    }

	    // 第一次统计时范围为：消息发送日前8-23天的草稿游记
	    $trips = $this->tripsvc->getTripsByInterval($now - 29 * 86400, $now - 8 * 86400);
	    foreach($trips as $trip) {
	        $template = '您的草稿游记《#title#》还差一点就能发布了，让我们去 <a target="_blank" href="http://www.lvmama.com/trip/edit/#id#">http://www.lvmama.com/trip/edit/#id#</a> 把它完成吧。';
    	    $keys = array('#title#', '#id#');
    	    $values = array($trip['title'], $trip['trip_id']);
    	    $content = str_replace($keys, $values, $template);
            
            $data = array(
                'aid' => '0',
                'uid' => $trip['uid'],
                'subject' => '草稿游记提醒',
                'message' => $content,
                'type' => 'DRAFT_TRAVEL_NOTICE_7', // 通知
            );
			// 第1天内随机通知历史草稿用户
            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_MSG)->put(json_encode($data), 1024, rand(1, 86400));
	    }

	    // 第一次统计时范围为：消息发送日前30-60天的草稿游记（再更久远的游记，继续写的可能性很低很低了）
	    $trips = $this->tripsvc->getTripsByInterval($now - 60 * 86400, $now - 30 * 86400);
	    foreach($trips as $trip) {
	        $template = '您一个月前创建的游记《#title#》还在角落默默等待着你，请不要冷落它，去 <a target="_blank" href="http://www.lvmama.com/trip/edit/#id#">http://www.lvmama.com/trip/edit/#id#</a> 完成，让更多的人能欣赏你的精彩旅程吧。';
    	    $keys = array('#title#', '#id#');
    	    $values = array($trip['title'], $trip['trip_id']);
    	    $content = str_replace($keys, $values, $template);
	    
	        $data = array(
	            'aid' => '0',
	            'uid' => $trip['uid'],
	            'subject' => '草稿游记提醒',
	            'message' => $content,
	            'type' => 'DRAFT_TRAVEL_NOTICE_30', // 通知
	        );
	        $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_MSG)->put(json_encode($data), 1024, rand(86400, 2 * 86400)); // 第2天内随机通知历史草稿用户
	    }
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
}