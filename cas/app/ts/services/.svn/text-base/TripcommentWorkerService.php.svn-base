<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

/**
 * 游记数据统计 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class TripcommentWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var CommentDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;
	
	private $config;

	public function __construct($di) {
		$this->datasvc = $di->get('cas')->get('comment-data-service');
		$this->datasvc->setReconnect(true);

		$this->travelsvc = $di->get('cas')->get('trip-data-service');
		$this->travelsvc->setReconnect(true);
		
		$this->tripstatisticsdatasvc = $di->get('cas')->get('trip-statistics-data-service');
		$this->tripstatisticsdatasvc->setReconnect(true);
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
		if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRIP_COMMENT)->ignore('default')->reserve()) {
			try {
				if ($job_data = json_decode($job->getData(), true)) {
				    $job_data['create_time'] = time();
					$this->datasvc->insert($job_data);
					
					// 状态变为已评论 临时方案
					if($job_data['uid'] == '3428a92f4c3190a3014c45535e8d40df') {
    					$data = array(
    					    'is_comment' => 1,
    					);
    					$this->tripstatisticsdatasvc->update($job_data['obj_type_p_id'], $data);
					}
					
            		// 增加评论数 - 目前数据库层面通过trigger出发统计
//             		$data = array(
//             			'id' => $job_data['obj_type_p_id'],
//             			'type' => TripStatisticsDataService::COMMENT,
//             			'number' => 1,
//             		);
//             		$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->put(json_encode($data));


					$template = '#username#对您发表的《#title#》进行了评论，点击 <a target="_blank" href="http://www.lvmama.com/trip/show/#id#">http://www.lvmama.com/trip/show/#id#</a> 进行查看';
					
					// 读库
                	$travel = $this->travelsvc->get($job_data['obj_type_p_id']);
                	
            	    $keys = array('#username#', '#title#', '#id#');
            	    $values = array($job_data['username'], $travel['title'], $travel['trip_id']);
            	    $content = str_replace($keys, $values, $template);
					
					$data = array(
					    'aid' => '0',
					    'uid' => $travel['uid'],
					    'subject' => '游记被评论',
					    'message' => $content,
					    'type' => 'COMMENTED_TRIP_NOTICE', // 通知
					);
					$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_MSG)->put(json_encode($data), 1024, rand(1, strtotime(date('Y-m-d 23:59:59', time())) - time())); // 当前时间到今日23:59随机发送
				}
				unset($job_data);
// 				$this->beanstalk->delete($job);
			} catch (\Exception $ex) {
				echo $ex->getMessage() . ";" . $ex->getTraceAsString() . "\r\n";
			}
			$this->beanstalk->delete($job);
		}
		unset($job);
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
}