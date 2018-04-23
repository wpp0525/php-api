<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\RedisAdapter;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 游记机器人计时器 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class TimerWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var RedisAdapter
	 */
	private $redis;

	public function __construct($di) {
		$this->redis = $di->get('cas')->getRedis();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
	    $now = time();
	    $today = date('Y-m-d H:i:s', $now);
	    $hour = date('H', $now);
	    $minite = date('i', $now);
	    $second = date('s', $now);
	    
	    //当日6点时间戳
	    $startTime = strtotime(date('Y-m-d 06:00:00', $now));
	    
	    //当日23点时间戳
	    $endTime = strtotime(date('Y-m-d 23:00:00', $now));
	    
	    //当日23:59:59时间戳
	    $expireTime = strtotime(date('Y-m-d 23:59:59', $now));
	    
	    if($hour == '00' && $minite == '00' && $second == '00') {
            for($d = 6; $d >= 0; $d--) {
                $rkey = RedisDataService::REDIS_AUDIT_TRIPID . date('Ymd', $now - $d * 86400);
                $tripids = $this->redis->sMembers($rkey);
                foreach ($tripids as $tripid) {
                    $this->redis->hSet(RedisDataService::REDIS_ROBOT_TIMER, $tripid, rand($startTime, $endTime));
                    $this->redis->expireAt(RedisDataService::REDIS_ROBOT_TIMER, $expireTime);
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
}