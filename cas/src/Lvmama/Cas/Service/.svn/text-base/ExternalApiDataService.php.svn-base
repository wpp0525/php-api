<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon as UCommon;
use Lvmama\Common\Components\ApiClient as ApiClient;

/**
 * 调用外部API
 *
 * @author win.sx
 *
 */
class ExternalApiDataService extends DataServiceBase {
	public function __construct($di, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->redis = $redis;
		$this->beanstalk = $beanstalk;
		$this->client = new ApiClient('');
	}

	/**
	 * 调取外部接口的数据
	 * @param $url 需要调取的URL
	 * @param array $param 参数键值对
	 * @param array $cacahe
	 * @return array
	 * @author shenxiang
	 */
	public function getResult($key = '',$param = array(),$method = 'GET',$cacahe = array('enable'=>true,'expire' => 7200)){
		$this->configure = $this->di->get('cas')->get('configure-data-service');
		$url = $this->configure->getConfigureByKey($key);
		if(!$url) return array('error'=>'lose api flag!');
		if($param){
			$keys = array_keys($param);
			$values = array_values($param);
			foreach($keys as $index=>$key){
				$keys[$index] = '{$'.$key.'}';
			}
			$url = str_replace($keys,$values,$url);
		}
		//参数验证
		if(preg_match('/\{\$(\w+)\}/',$url)) return array('error'=>'lose parameter!');
		$tmp = explode('?',$url);
		if(isset($tmp[1])){
			parse_str($tmp[1], $param);
		}
		if($cacahe['enable']){//使用缓存的到缓存中看看是否已经有
			$cache_id = RedisDataService::REDIS_EXTERNAL_API_KEY.md5($url);
			$result = $this->redis->get($cache_id);
			if(!$result){
				$result = $this->client->external_exec($tmp[0],$param,array(),$method);
				$this->redis->setex($cache_id,$cacahe['expire'],json_encode($result));
				return $result;
			}
			return json_decode($result,true);
		}
		$result = $this->client->external_exec($tmp[0],$param,array(),$method);
		return $result;
	}
}