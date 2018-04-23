<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Service\TripStatisticsDataService;
use Lvmama\Cas\Service\CommentDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class TripstatisticsWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var TripStatisticsDataService
	 */
	private $datasvc;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;
	
	private $config;

	public function __construct($di) {
		$this->datasvc = $di->get('cas')->get('trip-statistics-data-service');
		$this->datasvc->setReconnect(true);
		
		$this->travelsvc = $di->get('cas')->get('tr-travel-data-service');
		$this->travelsvc->setReconnect(true);
		
		$this->travelBonusService = $di->get('cas')->get('travel-bonus-data-service');
		$this->travelBonusService->setReconnect(true);
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {

		if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRIP_STATISTICS)->ignore('default')->reserve()) {
			try {
				if ($job_data = json_decode($job->getData(), true)) {
				    // 更新数量
				    $trip = $this->datasvc->get($job_data['id']);
					$data = array();
            	    switch($job_data['type']) {
            	        case TripStatisticsDataService::PV_INIT:
            	            $data['hits_init'] = $trip['hits_init'] + $job_data['number'];
            	            break;
            	        case TripStatisticsDataService::PV_REAL:
            	            $data['hits_real'] = $trip['hits_real'] + $job_data['number'];
            	            break;
            	        case TripStatisticsDataService::LIKE_INIT:
            	            $data['praise_init'] = $trip['praise_init'] + $job_data['number'];
            	            break;
            	        case TripStatisticsDataService::LIKE_REAL:
            	            $data['praise_real'] = $trip['praise_real'] + $job_data['number'];
            	            break;
            	        case TripStatisticsDataService::COMMENT:
            	            $data['comment_num'] = $trip['comment_num'] + $job_data['number'];
            	            break;
            	        default:
            	            break;
            	    }
            	    
            	    if(!empty($data)) {
					   $this->datasvc->update($job_data['id'], $data);
            	    }
					
                    // 统计数量
					$trip = $this->datasvc->get($job_data['id']);
					
					if($trip['is_comment'] == 0 
					    && $trip['hits_init'] + $trip['hits_real'] >= 3000 
					    && $trip['praise_init'] + $trip['praise_real'] >= 50
					    && $trip['comment_num'] > 0) {
					        
				        $travel = $this->travelsvc->get($job_data['id']);
				        $travelBonus = $this->travelBonusService->get($job_data['id']);
				        
					        
//                 	    $template = '#username#，您的本篇游记现在人气爆棚，具体数据如下：<br/>
//                                      #interval#，被浏览#pagview#次，收集到#like#个赞，留下了#comment#条评论，有#share#人进行了分享。<br/>
//                                      另外，您还赚到了#money#元的驴游宝奖金哦。<br/>
//                                      感谢您的努力付出和对我们的支持，谢谢。';
                	    $template = '#username#，您的本篇游记现在人气爆棚，具体数据如下：<br/>
                                     #interval#，被浏览#pagview#次，收集到#like#个赞，留下了#comment#条评论';
                	    
				        $keys = array('#username#', '#interval#', '#pagview#', '#like#', '#comment#');
				        $values = array($travel['username'], date('Y年m月d日', $travel['create_time']) . '-' . date('Y年m月d日', time()), $trip['hits_init'] + $trip['hits_real'], $trip['praise_init'] + $trip['praise_real'], $trip['comment_num']);
                	    
                	    // 分享数
				        if($travel['count_share'] > 0) {
				            $template .= '，有#share#人进行了分享。';
				            $keys[] = '#share#';
				            $values[] = $travel['count_share'];
				        }
				        else {
				            $template .= '。';
				        }
				        
				        $template .= '<br/>';
				        
				        // 收益
				        if($travelBonus && $travelBonus['amt'] > 0) {
				            $template .= '另外，您还赚到了#money#元的驴游宝奖金哦。<br/>';
				            $keys[] = '#money#';
				            $values[] = $travelBonus['amt'];
				        }
				        
				        $template .= '感谢您的努力付出和对我们的支持，谢谢。';

//                 	    $keys = array('#username#', '#interval#', '#pagview#', '#like#', '#comment#', '#share#', '#money#');
//                 	    $values = array($travel['username'], date('Y年m月d日', $travel['create_time']) . '-' . date('Y年m月d日', time()), $trip['hits_init'] + $trip['hits_real'], $trip['praise_init'] + $trip['praise_real'], $trip['comment_num'], $travel['count_share'], $travelBonus ? $travelBonus['amt'] : 0);
                	    $content = str_replace($keys, $values, $template);

					    $data = array(
					        'uid' => '3428a92f4c3190a3014c45535e8d40df',
					        'username' => '驴妈妈游记小编',
					        'channel' => 'trip',
					        'object_type' => 'trip',
					        'obj_type_p_id' => $trip['trip_id'], // 游记ID
					        'object_id' => $trip['trip_id'], // 子类型ID
// 					        'create_time' => time(),
					        'ip' => '127.0.0.1',
					        'source' => 'PC',
					        'memo' => $content,
					        'valid' => 'Y',
					        'status' => '99',
					    );
					    
					    $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRIP_COMMENT)->put(json_encode($data), 1024, rand(1, 3600));
					}
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