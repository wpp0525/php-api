<?php

use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;

/**
 * 游记 控制器
 * 
 * @author mac.zhao
 * 
 */
class DesttdkController extends ControllerBase {
	
	private $redis;

	public function initialize() {
		parent::initialize();
		$this->redis = $this->di->get('cas')->getRedis();
	}

    /**
     * 获取tdk
     * http://www.lvmama.com/pet_topic/tdk/queryTDK.do?key={$key}&destId={$destid}
     */
    public function getDestTdkAction(){

        $key = $this->tdk_key;
        $destId = $this->dest_id;
        $current = $this->current;

        if(!$key || !$destId) $this->_successResponse(array());
        $redis_key = str_replace('{tdk_key}', $key, RedisDataService::REDIS_NEW_DEST_TDK_KEY);

        $header = $this->redis_svc->dataHgetall($redis_key);
        if(!$header){
            $header = $this->api('http://www.lvmama.com/pet_topic/tdk/queryTDK.do?key='.$key.'&destId='.$destId);
            if($header && is_array($header)){
                $this->redis_svc->dataHmset($redis_key, $header, 14400);
            }else{
                $this->_successResponse(array());
            }
        }

        $redis_dest = str_replace('{dest_id}', $destId, RedisDataService::REDIS_NEW_DEST_TDK_DEST);
        $initSettings = array(
            'year' => date('Y',time()),
            'month' => date('n',time()),
            'day' => date('j',time()),
            'current' => $current
        );

        // 获取所有的变量
        $vars = $search = $repat = array();
        foreach($header as $val){
            preg_match_all('/\{\$(\w+)\}/i', $val, $var);
            foreach($var[1] as $value){
                $vars[] = $value;
            }
        }
        $vars = array_unique($vars);
        // 确定正则与被替换者
        foreach($vars as $var){
            if(!isset($initSettings[$var])) $initSettings[$var] = '';
            $repat[$var] = $initSettings[$var];
            $search[$var] = '{$'.$var.'}';
        }
        // 替换
        foreach($header as $key=>$val){
            $val = str_replace($search,$repat,$val);
            $header[$key] = $val;
        }

        $this->redis_svc->dataHmset($redis_dest, $header, 14400);

        $this->_successResponse($header);
    }


}
