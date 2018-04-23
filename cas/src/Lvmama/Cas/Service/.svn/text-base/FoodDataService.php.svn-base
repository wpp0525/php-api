<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils;
use Phalcon\Mvc\Model;
use Lvmama\Common\Utils\UCommon as UCommon;

/**
 * 美食类
 *
 * @author win.shenxiang
 *
 */
class FoodDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_food';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	const EXPIRE_TIME = 86400;
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
		$redis_key = str_replace('{id}',$id,RedisDataService::REDIS_FOOD_INFO);
		$result = $this->redis->hGetAll($redis_key);
		if(!$result) {
			$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE food_id = ' . $id;
			$sth = $this->getAdapter()->query($sql);
			$sth->setFetchMode(\PDO::FETCH_ASSOC);
			$result = $sth->fetch();
			if($result){
				$this->redis->hmset($redis_key,$result);
				$this->redis->expire($redis_key,self::EXPIRE_TIME);
			}else{
				$result = array();
			}
		}
		return $result;
	}
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	/**
	 * 取得目的地下的推荐美食
	 * @param $data
	 * @param $limit
	 * @param string $search_name
	 * @return bool|mixed|void
	 */
	public function getRecommendFood($dest_id = 0,$limit = '',$search_name=''){
		if(!$dest_id || !is_numeric($dest_id)) return false;
		$limits = $limit ? (is_array($limit) ? 'LIMIT '.(($limit['page'] - 1) * $limit['pageSize']).','.$limit['pageSize'] : ' LIMIT '.$limit) : '';
		$food_name = $search_name ? " AND food_name LIKE '%".$search_name."%'" : '';
		$sql="SELECT f.food_id,f.food_name,f.memo,f.img_url,(CASE f.img_url WHEN '' THEN 0 ELSE 1 END) AS have_img FROM ly_food_recommend  fr LEFT JOIN ly_food f on fr.food_id=f.food_id WHERE f.`status`=99 AND fr.`dest_id`={$dest_id}{$food_name} ORDER BY fr.seq ASC,have_img DESC,f.food_id ASC {$limits}";
		$redis_key = str_replace('{sql}',md5($sql),RedisDataService::REDIS_DEST_RECOMMEND_FOOD);
		$redis_data = $this->redis->get($redis_key);
		if($redis_data === false){
			$return = $this->getRsBySql($sql);
			$this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
		}else{
			$return = json_decode($redis_data,true);
		}
		return $return;
	}
	/**
	 * 取得目的地下的美食数量
	 * @param $data
	 * @param string $search_name
	 * @return number
	 */
	public function getDestFoodNum($data,$search_name=''){
		if(!$data) return false;
		$food_name = '';
		if($search_name){
			$food_name = " AND food_name LIKE '%".$search_name."%'";
		}
		$sql="SELECT count(*) AS count FROM (SELECT * FROM ly_food_dest WHERE food_status=99 AND (`dest_parents` LIKE '".$data['parents'].",%' OR dest_parents='".$data['parents']."')".$food_name."  GROUP BY food_id) a";
		$redis_key = str_replace('{sql}' , md5($sql),RedisDataService::REDIS_DEST_FOOD_NUM);
		$return = $this->redis->get($redis_key);
		if($return === false){
			$num = $this->getRsBySql($sql);
			$return = isset($num[0]['count']) ? $num[0]['count'] : 0;
			$this->redis->setex($redis_key,self::EXPIRE_TIME,$return);
		}
		return $return;
	}
	/**
	 * @param $data
	 * @param $limit
	 * @param string $rec_ids
	 *
	 * @param string $search_name
	 * @return array|bool
	 * 根据目的地ID获取所属美食
	 */
	public function getDestHaveFood($data,$limit,$rec_ids='',$search_name=''){
		if(!$data) return false;
		$limits = '';
		$not_in = $rec_ids ? " AND a.food_id NOT IN (".$rec_ids.")" : '';
		$food_name = $search_name ? " AND a.food_name LIKE '%".$search_name."%'" : '';
		if($limit){
			if(is_array($limit)){
				$limits = " LIMIT ".($limit['page']-1)*$limit['pageSize'].", ".$limit['pageSize'];
			}else{
				$limits = " LIMIT ".$limit;
			}
		}
		$sql = "SELECT a.food_id,a.food_name,b.memo,b.img_url,(CASE b.img_url WHEN '' THEN 0 ELSE 1 END) AS have_img FROM ly_food_dest AS a INNER JOIN ly_food AS b ON a.food_id = b.food_id AND a.`food_status`=99 AND (a.dest_parents LIKE '".$data['parents'].",%'  OR a.dest_parents='".$data['parents']."')".$not_in.$food_name." GROUP BY a.food_id ORDER BY a.food_seq ASC,have_img DESC,a.food_id ASC".$limits;
		$redis_key = str_replace('{sql}' , md5($sql),RedisDataService::REDIS_DEST_FOOD_DATA);
		$redis_data = $this->redis->get($redis_key);
		if($redis_data === false){
			$return = $this->getRsBySql($sql);
			$this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
		}else{
			$return = json_decode($redis_data,true);
		}
		return $return;
	}
	/**
	 * 根据目的地取得美食概述
	 * @param array $data
	 * @return string
	 * @author shenxiang
	 */
	public function getSummary($data = 0){
		if(!$data || !is_numeric($data['dest_id'])) return array();
		$redis_key = str_replace('{dest_id}',$data['dest_id'],RedisDataService::REDIS_DEST_FOOD_SUMMARY);
		$return = $this->redis->get($redis_key);
		if($return === false){
			$sth = $this->getAdapter()->prepare("SELECT text FROM ly_data WHERE `dest_id` = {$data['dest_id']} AND `status` = 99 AND `cate_id` = (SELECT cate_id FROM ly_category WHERE parent_id IN (SELECT cate_id FROM ly_category WHERE `code` = 'food') AND `code` = 'food_summary')");
			$sth->setFetchMode(\PDO::FETCH_ASSOC);
			$sth->execute();
			$cate = $sth->fetch();
			$return = isset($cate['text']) ? strip_tags($cate['text']) : $data['dest_name'].'美食品类丰富，独具特色，令人垂涎欲滴';
			$this->redis->setex($redis_key,self::EXPIRE_TIME,$return);
		}
		return $return;
	}
	/**
	 * 获取指定目的地美食标签
	 * @param $data 目的地基本信息
	 * @param $page 页码
	 * @param $pageSize 每页显示条数
	 * @return array
	 */
	public function getThemes($data = array()){
		if(!$data || !isset($data['dest_id']) || !isset($data['parents'])) return array();
		$redis_key = str_replace('{dest_id}',$data['dest_id'],RedisDataService::REDIS_DEST_FOOD_THEME);
		$redis_data = $this->redis->get($redis_key);
		if($redis_data === false) {
			$rs = $this->getRsBySql("SELECT subject_id,subject_name,COUNT(subject_id) AS num FROM (SELECT  subject_id,subject_name  FROM `ly_food_dest_subject_relation_view` WHERE (dest_parents LIKE '{$data['parents']},%' OR `dest_parents`='{$data['parents']}') AND `status`=99 AND `object_type`='food' AND `channel`='lvyou' GROUP BY food_id,subject_id) tm GROUP BY tm.subject_id ORDER BY num DESC");
			$return = $rs ? $rs : array();
			$this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
		}else{
			$return = json_decode($redis_data,true);
		}
		return $return;
	}

	public function GetRestBestFood($rest_id){
		$result = $this->getRsBySql('SELECT food_name FROM ly_food_dest WHERE dest_id = '.$rest_id.' LIMIT 7');
		$tmp = array();
		if($result){
			foreach($result as $v){
				$tmp[] = $v['food_name'];
			}
		}
		return $tmp ? implode(',',$tmp) : '';
	}

	/**
	 * 根据美食取餐厅
	 * @param $food_id
	 */
	public function getRestByFood($food_id = 0,$page = 1,$pageSize = 10){
		if(!$food_id) return array();
		$total = $this->getRsBySql('SELECT COUNT(id) AS c FROM ly_food_dest WHERE `dest_type`=\'RESTAURANT\' AND food_status = 99 AND `food_id`= '.$food_id,true);
		$num = isset($total['c']) ? intval($total['c']) : 0;
		$totalPage = ceil($num / $pageSize);
		$start = ($page - 1) * $pageSize;
		$sql = 'SELECT food_id,dest_id,dest_name,food_name,parent FROM ly_food_dest WHERE `dest_type`=\'RESTAURANT\' AND food_status = 99 AND `food_id`= '.$food_id.' LIMIT '.$start.','.$pageSize;
		$redis_key = str_replace('{sql}',md5($sql),RedisDataService::REDIS_DEST_RESTAURANT_OF_FOOD);
		$redis_data = $this->redis->get($redis_key);
		if($redis_data === false) {
			$rs = $this->getRsBySql($sql);
			$return = $rs ? array('list' => $rs,'pages' => array('itemCount' => $num,'pageCount' => $totalPage,'page' => $page,'pageSize' => $pageSize)) : array('list' => array(),'pages' => array('itemCount' => $num,'pageCount' => $totalPage,'page' => $page,'pageSize' => $pageSize));
			$this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
		}else{
			$return = json_decode($redis_data,true);
		}
		return $return;
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