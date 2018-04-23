<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * 主站敏感词 服务类
 *
 * @author mac.zhao
 *        
 */
class SensitiveWordDataService extends DataServiceBase {
    
    
    /**
     * 基础连接
     * 
     * @var unknown
     * 
     * http://super.lvmama.com/super_back/phpValidateSensitiveWords.do?content={$content}
     */
    private $baseUri = 'http://super.lvmama.com/';
	
	/**
	 * 
	 * @param DiInterface $di
	 * @param AdapterInterface $adapter 数据提供
	 * @param RedisAdapter $redis
	 * @param BeanstalkAdapter $beanstalk
	 */
	public function __construct($di, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->redis = $redis;
		$this->beanstalk = $beanstalk;
		$this->client = new ApiClient($this->baseUri);
	}
	
	/**
	 * 校验敏感词
	 * 
	 */
	public function validate($content) {
		$prams = array(
		    'content' => urlencode($content),
		);
	    $res = $this->client->exec('super_back/phpValidateSensitiveWords.do', $prams, array(), 'GET');
	    if(!empty($res) && $res['success'] == 1 && isset($res['msg'])) {
	        return $res['msg'];
	    }
	    else {
	        return '';
	    }
	}
}