<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Components\ApiClient;

/**
 * lmm_travels库 游记内容 服务类
 *
 * @author mac.zhao
 *
 */
class TrTravelContentDataService extends DataServiceBase {

	const TABLE_NAME = 'tr_travel_content'; //对应数据库表
    
    private $baseUri = 'http://php-api.lvmama.com/';
	
    /**
     * 
     * 
     * @author mac.zhao
     * 
     * @param unknown $content
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
	
	public function getContentByTravelid( $travelid ) {
		$sql = 'SELECT id, title, content FROM ' . self::TABLE_NAME . ' WHERE travel_id = ' . $travelid;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
}