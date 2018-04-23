<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Components\ApiClient;

/**
 * lmm_travels库 游记 服务类
 *
 * @author mac.zhao
 *
 */
class TrTravelDataService extends DataServiceBase {

	const TABLE_NAME = 'tr_travel'; //对应数据库表
    
    private $baseUri = 'http://php-api.lvmama.com/';

	/**
	 * 获取指定游记ID的游记列表
     * 
     * @author mac.zhao
     * 
     * @return recommend_status 推荐状态: 1-不推荐, 2-推荐
	 */
	public function listTravelById($ids) {
		$sql = 'SELECT id, recommend_status FROM ' . self::TABLE_NAME . ' WHERE id IN (' . implode(',', $ids) . ')';
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * 获取指定游记ID游记信息
     * 
     * @author mac.zhao
	 *
	 */
	public function get($id) {
		$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $id;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	
    /**
     * 获取游记内容中涉及的目的地
     * 
     * @author mac.zhao
     * 
     * @param string $content
     * @return Ambigous <>|string
     */
	public function getDestByContent( $content ) {
		$this->client = new ApiClient($this->baseUri);
		$params = array(
		    'content' => $content,
		);
		$cookies = array(
		);
	    $res = $this->client->exec('es/article', $params, $cookies, 'POST');
	    if(!empty($res) && $res['error'] == 0) {
	        return $res['data'];
	    }
	    else {
	        return '';
	    }
	}

	/**
	 * 获取时间段内游记列表
	 * 
	 * @author mac.zhao
	 *
	 */
	public function getTripsByInterval($startTime, $endTime) {
		$sql = 'SELECT trip_id, title, uid, modify_time FROM ' . self::TABLE_NAME . ' WHERE user_status = 1 AND modify_time >= ' . $startTime . ' AND modify_time <= ' . $endTime;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
}