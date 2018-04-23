<?php

namespace Lvmama\Cas\Service\Ads;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * 广告系统：广告位服务类
 *
 * @author sx
 *        
 */
class AdsCampaignDataService extends DataServiceBase {

	const TABLE_NAME = 'ad_campaign';//对应数据库表
	const PRIMARY_KEY = 'id';

	/**
	 * 添加
	 *
	 */
	public function insert($data) {
		if($data){
			$data['create_time'] = time();
			$data['update_time'] = time();
		}
		return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
	}

	/**
	 * 更新
	 *
	 */
	public function update($id, $data) {
		$whereCondition = self::PRIMARY_KEY . ' = ' . $id;
		return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
	}

	public function getCampaign($condition, $columns = null){
		return $this->getOne($condition, self::TABLE_NAME, $columns);
	}

	public function getCampaignList($condition, $limit = null, $columns = null, $order = null){
		return $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);
	}

	public function deleteCampaign($id){
		$condition = array(
			'id = ' => $id,
		);
		return $this->deleteFrom($condition, self::TABLE_NAME);
	}

	/**
	 * 当选
	 */
	public function elected($id, $zone_id, $type, $campaign_cycle){
		$whereCondition = "zone_id = $zone_id AND status = 'reign'";
		$res = $this->getOne($whereCondition, self::TABLE_NAME, 'id');
		if($res){//如果有在任的广告，则让其离任
			$old_id = $res['id'];
			$this->update($old_id, array('status' => 'failed', 'reign_end_time' => time(), 'update_time' => time()));
		}

		$data['update_time'] = time();
		$data['type'] = $type;
		$data['reign_start_time'] = time();
		$data['reign_end_time'] = time() + $campaign_cycle;
		$data['status'] = 'reign';
		return $this->update($id, $data);
	}

	/**
	 * 系统推荐
	 *
	 * @param $zone_id
	 * @param $campaign_cycle
	 * @return bool|void
	 */
	public function autoElected($zone_id, $campaign_cycle){
		$condition = 'ad_zone.id = ' . $zone_id . ' AND ad_banner.is_del = 0';
		$relation = array(
			'ad_zone', 'id',
			'ad_campaign', 'zone_id', 'banner_id',
			'ad_banner', 'id'
		);
		$max = $this->getMany2Many($condition, $relation, 'ad_campaign.*', null, 'total_weight desc', 1);
		if(!$max){
			return false;
		}
		return $this->elected($max['id'], $zone_id, 'auto', $campaign_cycle);
	}

	/**
	 * 根据条件查询记录总数
	 *
	 * @param $condition
	 * @return bool|mixed|null|string
	 */
	public function getTotal($condition){
		return $this->getTotalBy($condition, self::TABLE_NAME);
	}
}