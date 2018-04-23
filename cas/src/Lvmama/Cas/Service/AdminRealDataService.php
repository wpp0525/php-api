<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * cms后台管理员ID与real对应关系
 *
 * @author sx
 *        
 */
class AdminRealDataService extends DataServiceBase {
	const TABLE_NAME = 'cr_admin';//对应数据库表
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	public function getAdminReal($admin_id){
		if(!is_numeric($admin_id)) return '';
		$realName = $this->redis->get(RedisDataService::REDIS_CORE_ADMIN_REALNAME.$admin_id);
		if(!$realName){
			$sql = 'SELECT `real_name` FROM '.self::TABLE_NAME.' WHERE `admin_id` = '.$admin_id;
			$rs = $this->getRsBySql($sql,true);
			$realName = isset($rs['real_name']) ? $rs['real_name'] : '';
			$this->redis->set(RedisDataService::REDIS_CORE_ADMIN_REALNAME.$admin_id,$realName,86400);
		}
		return $realName;
	}
}