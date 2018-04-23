<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * 主站用户 服务类
 *
 * @author mac.zhao
 *        
 */
class UserDataService extends DataServiceBase {
    
    private $baseUri = 'http://login.lvmama.com/nsso/';
	
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
	}
	
	/**
	 * 通过lvsessionid获取uid
	 * 
	 */
	public function getUidBySession($lvsessionid) {
		$this->client = new ApiClient($this->baseUri);
		$params = array(
		);
		$cookies = array(
		    'lvsessionid' => $lvsessionid,
		);
	    $res = $this->client->exec('ajax/getUserNo.do', $params, $cookies, 'POST');
	    if(!empty($res) && $res['success'] == 1) {
	        return $res['result'];
	    }
	    else {
	        return '';
	    }
	}
}