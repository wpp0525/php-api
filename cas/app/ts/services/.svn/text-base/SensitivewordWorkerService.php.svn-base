<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 敏感词过滤 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class SensitivewordWorkerService implements DaemonServiceInterface {
	
	/**
	 * @var RedisAdapter
	 */
	private $redis;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;
	
	private $config;

	public function __construct($di) {
		$this->sensitiveWordDS = $di->get('cas')->get('sensitive-word-data-service');
		$this->sensitiveWordDS->setReconnect(true);
        
        $this->trTravelContentDS = $di->get('cas')->get('tr-travel-content-data-service');
        $this->trTravelContentDS->setReconnect(true);
		
		$this->redis = $di->get('cas')->getRedis();
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {
	}
	
	/**
	 * 问答系统-问题敏感词过滤
	 * 
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processQuestion($timestamp = null, $flag = null) {

		if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_QA_QUESTION)->ignore('default')->reserve()) {
			try {
				if ($job_data = json_decode($job->getData(), true)) {
				    
				    $titleSensitiveWord = $this->_validateSensitiveWord($job_data['title']);
				    $contentSensitiveWord = $this->_validateSensitiveWord($job_data['content']);
				    
				    $sensitiveWord = array_merge($titleSensitiveWord, $contentSensitiveWord);
				    
				    $keys = array('{id}');
				    $values = array($job_data['id']);
				    $rkey = str_replace($keys, $values, RedisDataService::REDIS_QA_QUESTION_INFO);
				    
				    $question = array(
				        'id' => $job_data['id'],
				        'title' => $job_data['title'],
				        'content' => $job_data['content'],
				        'sensitiveWord' => json_encode($sensitiveWord),
				        'time' => time(),
				    );
				    
        	        $this->redis->hMSet($rkey, $question);
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
	 * 游记-敏感词过滤
	 * 
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processTravel($timestamp = null, $flag = null) {

		if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_SENSITIVEWORD)->ignore('default')->reserve()) {
			try {
				$job_json_data = $job->getData();
				if ($job_data = json_decode($job_json_data, true)) {
				    if($job_data['id']) {
				        $contents = $this->trTravelContentDS->getContentByTravelid($job_data['id']);
				    
    				    foreach($contents as $content) {
        				    $titleSensitiveWord = $this->_validateSensitiveWord($content['title']);
        				    $contentSensitiveWord = $this->_validateSensitiveWord($content['content']);
        				    
        				    $sensitiveWord = array_merge($titleSensitiveWord, $contentSensitiveWord);
        				    
        				    $keys = array('{travelid}', '{id}');
        				    $values = array($job_data['id'], $content['id']);
        				    $rkey = str_replace($keys, $values, RedisDataService::REDIS_TRAVEL_CONTENT);

        				    $travelContent = array(
        				        'sensitiveWord' => json_encode($sensitiveWord),
        				    );
        	                $this->redis->hMSet($rkey, $travelContent);
    				    }
					}

					$this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRAVEL_QUICK_CHECK_LIST)->put($job_json_data);
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
	
	protected function _validateSensitiveWord($word) {
        $sensitiveWordArr = array();
        
        $sensitiveWord = $this->sensitiveWordDS->validate($word);
        
        if(!empty($sensitiveWord)) {
            $sensitiveWordArr = explode(',', $sensitiveWord);
            foreach ($sensitiveWordArr as $k => $v) {
                $sensitiveWordArr[$k] = substr($v, 1, -1);
            }
        }
	   
        return $sensitiveWordArr;
	}

	/**
	 * 更新 Beanstalk 中符合条件的游记的状态
	 *
	 */
	private function updateTravelMainStatusFromBeanstalk()
	{
		$curr_job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRAVEL_QUICK_CHECK_LIST)->ignore('default')->reserve();
		if ($curr_job) {
			try {
				$job_data = json_decode($curr_job->getData(), true);
				if ($job_data) {
					if ($this->isValid($job_data['travel_id'], $job_data['uid']))
						$this->updateTravelMainStatus($job_data['travel_id']);
				}
				$this->beanstalk->delete($curr_job);
				unset($job_data);
			} catch (\Exception $ex) {
				echo $ex->getMessage() . ',' . $ex->getTraceAsString() . '\r\n';
			}
		}
		unset($curr_job);
	}

	/**
	 * 从CMS后台获取配置的快速审核用户ID
	 * @return array
	 */
	private function getQuickCheckIdsByConfig()
	{
		$params = array(
			'columns' => '`value`',
			'where' => "`object_type` = 'trip' AND `key` = 'quick_check_ids'",
		);
		$result = $this->configuredatasvc->getRsBySql($params);
		return !empty($result) ? explode(',', $result['0']['value']) : array();
	}

	/**
	 * 判断游记是否符合快速审核条件
	 * @param int $travel_id
	 * @param int $uid
	 * @return bool
	 */
	private function isValid($travel_id = 0, $uid = 0)
	{
		if (!$travel_id || !$uid)
			return false;

		$travel_num = $this->getValidTravelNumByUid($uid);

		$has_sensitive = $this->hasSensitive($travel_id);

		if ($has_sensitive)
			return false;
		if (in_array($uid, $this->config_id_arr))
			return true;
		if ($travel_num >= 2)
			return true;
		return false;
	}

	/**
	 * 返回用户的通过并显示的游记数
	 * @param $uid
	 * @return int
	 */
	private function getValidTravelNumByUid($uid)
	{
		$new_travel_count_sql = "SELECT COUNT(*) AS count FROM `tr_travel` tr LEFT JOIN `tr_travel_ext` tre ON `tr`.`id` = `tre`.`travel_id` WHERE `tr`.`id` > '90338' AND tre.`main_status` = '4' AND tre.`del_status` = '0' AND tr.`uid` = '{$uid}'";
		$new_travel_count_res = $this->traveldatasvc->querySql($new_travel_count_sql);
		$new_travel_count = $new_travel_count_res['list']['0']['count'];
		if ($new_travel_count >= 2)
			return $new_travel_count;

		$params = array(
			'table' => 'ly_trip',
			'select' => 'COUNT(*) AS count',
			'where' => array('deleted' => 'N', 'source' => array('!=', 'ADMIN'), 'verify' => '99', 'finished' => 'Y', 'user_status' => '99', 'uid' => $uid),
		);
		$old_travel_count_res = $this->tripdatasvc->select($params);
		$old_travel_count = $old_travel_count_res['list']['0']['count'];

		return intval($new_travel_count) + intval($old_travel_count);
	}

	/**
	 * 判断章节内容是否有敏感词
	 * @param $travel_id
	 * @return bool
	 */
	private function hasSensitive($travel_id)
	{
		$params = array(
			'table' => 'travel_content',
			'select' => 'id',
			'where' => array('travel_id' => $travel_id),
		);
		$travel_content_res = $this->traveldatasvc->select($params);
		if (empty($travel_content_res['list']))
			return true;
		foreach ($this->getRow($travel_content_res['list']) as $row) {
			$redis_data = $this->redis->hgetall("tr:travel:" . $travel_id . ":content:" . $row['id']);
			$sensitive_array = json_decode($redis_data['sensitiveWord']);
			if (!empty($sensitive_array))
				return true;
		}
		return false;
	}

	/**
	 * 更新游记状态
	 * @param string $travel_id_str
	 * @param int $main_status
	 * @return bool
	 */
	private function updateTravelMainStatus($travel_id_str = '', $main_status = '5')
	{
		$params = array(
			'table' => 'travel_ext',
			'where' => "`travel_id` IN ('{$travel_id_str}')",
			'data' => array('main_status' => $main_status),
		);
		$res = $this->traveldatasvc->update($params);
		if (isset($res['error']) && !$res['error'])
			return true;
		return false;
	}
}