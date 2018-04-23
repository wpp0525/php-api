<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 问答系统-问题敏感词过滤 Worker服务类
 *
 * @author mac.zhao
 *        
 */
class QuestionsensitivewordWorkerService implements DaemonServiceInterface {
	
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
		$this->sensitiveWordSVC = $di->get('cas')->get('sensitive-word-data-service');
		$this->sensitiveWordSVC->setReconnect(true);
		
		$this->redis = $di->get('cas')->getRedis();
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
	}
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function process($timestamp = null, $flag = null) {

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
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
	 */
	public function shutdown($timestamp = null, $flag = null) {
		// nothing to do
	}
	
	protected function _validateSensitiveWord($word) {
        $sensitiveWordArr = array();
        
        $sensitiveWord = $this->sensitiveWordSVC->validateSensitiveWord($word);
        
        if(!empty($sensitiveWord)) {
            $sensitiveWordArr = explode(',', $sensitiveWord);
            foreach ($sensitiveWordArr as $k => $v) {
                $sensitiveWordArr[$k] = substr($v, 1, -1);
            }
        }
	   
        return $sensitiveWordArr;
	}
}