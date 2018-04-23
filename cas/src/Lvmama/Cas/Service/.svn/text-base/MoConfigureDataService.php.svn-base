<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * CMS通用模块的配置信息
 * @author win.shenxiang
 */
class MoConfigureDataService extends DataServiceBase {
	
	const TABLE_NAME = 'mo_configure';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;
	
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE segment_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($where = array()){
		$result = $this->getAdapter()->query("select {$where['columns']} FROM ".self::TABLE_NAME." WHERE {$where['where']}");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * 取得所有的通用配置
	 * @param $type 对象类型
	 * @return array
	 * @author shenxiang
	 */
	public function getConfigure($type = 'all'){
		$key = str_replace('{type}',$type,RedisDataService::REDIS_CONFIGURE_KEY);
		$result = $this->redis->hGetAll($key);
		if(!$result) {
			$result = $this->getRsBySql(array(
				'columns' => '`key`,`value`',
				'where' => "`object_type` = '{$type}'"
			));
			foreach($result as $v){
				$this->redis->hset($key,$v['key'],$v['value']);
			}
			$this->redis->expire($key,86400);
		}
		return $result;
	}
	/**
	 * 取得指定的通用模块的配置
	 * @key 配置的键值
	 * @type 类型
	 * @return string
	 * @author shenxiang
	 */
	public function getConfigureByKey($key = '',$type = 'all'){
		$hkey = str_replace('{type}',$type,RedisDataService::REDIS_CONFIGURE_KEY);
		if(!$this->redis->hlen($hkey)){
			$this->getConfigure($type);
		}
		$url = $this->redis->hget($hkey,$key);
		return $url ? $url : '';
	}
	/**
	 * 清除redis中的通用配置信息
	 * @type 类型
	 * @key 配置的键值(指定了需要删除的键名则精确删除该键名对应的值,否则删除该类型下的所有键值)
	 * @return bool
	 * @author shenxiang
	 */
	public function removeConfigure($type = 'all',$key = ''){
		$hkey = str_replace('{type}',$type,RedisDataService::REDIS_CONFIGURE_KEY);
		if($this->redis->hlen($hkey)){
			if($key){
				$this->redis->hdel($hkey,$key);
			}else{
				foreach($this->redis->hkeys($hkey) as $k){
					$this->redis->hdel($hkey,$k);
				}
			}
		}
		return true;
	}
	
	/**
	 * 添加
	 * 
	 */
	public function insert($data) {
	    if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){
// 	        $this->findOneBy(array('id'=>$id), self::TABLE_NAME, null, true);
// 	        return array('error'=>0, 'result'=>$id);
	    }
	    
		$result = array('error'=>0, 'result'=>$id);
		return $result;
	}
	
	/**
	 * 更新
	 * 
	 */
	public function update($id, $data) {
	    $whereCondition = 'trip_id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	    }
	}
}