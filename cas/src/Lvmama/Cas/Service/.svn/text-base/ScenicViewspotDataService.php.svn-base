<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Phalcon\Mvc\Model;

/**
 * 目的地类
 *
 * @author win.shenxiang
 *
 */
class ScenicViewspotDataService extends DataServiceBase {
	
	const TABLE_NAME = 'ly_scenic_viewspot';//对应数据库表
	
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
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE dest_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}
	/**
	 * 获取推荐目的地的ID
	 */
	public function getRecommendDestByDestid($dest_id,$rec_type,$dest_type,$limit = 0,$search_key='',$stage=0,$pages=array(),$condition='',$img_sort = 0) {
	    if(!$dest_id) return array();
        //判断要查询的目的地类型
        if( $dest_type ){
            if( is_array($dest_type) ){
                $typestr = '';
                foreach( $dest_type as $key=>$row ){
                    $typestr .= "'".$row."',";
                }
                $typestr = substr($typestr,0,-1);
                $dests_types = " AND  b.`dest_type` IN(".$typestr.") ";
            }else{
                if($dest_type == 'VIEWSPOT'){
                    $dests_types = "   AND  (b.`dest_type`='VIEWSPOT' OR (b.dest_type='SCENIC_ENTERTAINMENT' AND b.`ent_sight`='Y' )) ";
                }else if($dest_type == 'SCENIC_ENTERTAINMENT'){
                    $dests_types = "  AND  (b.`dest_type`='SCENIC_ENTERTAINMENT'  OR ( b.dest_type='VIEWSPOT' AND b.`ent_sight`='Y' ))";
                }else {
                    $dests_types = " AND   b.`dest_type` ='".$dest_type."' ";
                }
            }
        }else{
            $dests_types = '';
        }
        $limit = $limit ? ' LIMIT '.$limit : ($pages ? " LIMIT ".($pages['page'] - 1) * $pages['pageSize'].",".$pages['pageSize'] : '');
        $search_key = $search_key ? " AND b.dest_name LIKE '%{$search_key}%'" : '';
        $stages = $stage ? ' AND b.`stage`='.$stage : '';
        if($img_sort){
            $sql="SELECT distinct a.dest_id,b.dest_id,b.dest_name,b.dest_type,b.pinyin,b.url,b.img_url,b.count_been,b.parents,b.intro,b.stage,(CASE b.img_url WHEN '' THEN 0 ELSE 1 END) AS have_image FROM `ly_scenic_viewspot` AS a LEFT JOIN `ly_destination` AS b ON a.viewspot_id=b.dest_id WHERE b.cancel_flag='Y' AND b.`showed`='Y' AND a.recommend_type='".$rec_type."'".$stages.$dests_types." AND a.`dest_id`=".$dest_id." ".$search_key.$condition." ORDER BY a.seq ASC,have_image DESC,b.count_been DESC".$limit;
        }else{
            $sql="SELECT distinct a.dest_id,b.dest_id,b.dest_name,b.dest_type,b.pinyin,b.url,b.img_url,b.count_been,b.parents,b.intro,b.stage FROM `ly_scenic_viewspot` AS a LEFT JOIN `ly_destination` AS b ON a.viewspot_id=b.dest_id WHERE b.cancel_flag='Y' AND b.`showed`='Y' AND a.recommend_type='".$rec_type."'".$stages.$dests_types." AND a.`dest_id`=".$dest_id." ".$search_key.$condition." ORDER BY a.seq ASC,b.count_been DESC".$limit;
        }
		$redis_key = str_replace('{sql}',md5($sql),RedisDataService::REDIS_DEST_RECOMMEND_DATA);
		$data = $this->redis->get($redis_key);
		if($data === false) {
			$return = $this->getRsBySql($sql);
			$this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
		}else{
			$return = json_decode($data,true);
		}
        return $return;
	}
	public function getLists($dest_id = 0,$limit = 1){
		$sql = "SELECT recommend_id,viewspot_id,seq FROM ".self::TABLE_NAME." WHERE `recommend_type`='VIEWSPOT' AND  `status`=99 AND `dest_id`={$dest_id} ORDER BY seq ASC LIMIT {$limit}";
		return $this->getRsBySql($sql);
	}
	/**
	 * 添加
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
	 */
	public function update($id, $data) {
	    $whereCondition = 'trip_id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	    }
}



    /**
     * 获取推荐目的地的ID
     */
    public function getRecomDestTop($dest_id, $dest_type = '', $limit = 3, $recomType = '') {
        if(!$dest_id) return array();
        //判断要查询的目的地类型
        if( $dest_type ){
            if( is_array($dest_type) ){
                $typestr = '';
                foreach( $dest_type as $key=>$row ){
                    $typestr .= "'".$row."',";
                }
                $typestr = substr($typestr,0,-1);
                $dests_types = " AND  b.`dest_type` IN(".$typestr.") ";
            }else{
                if($dest_type == 'VIEWSPOT'){
                    $dests_types = "   AND  (b.`dest_type`='VIEWSPOT' OR (b.dest_type='SCENIC_ENTERTAINMENT' AND b.`ent_sight`='Y' )) ";
                }else if($dest_type == 'SCENIC_ENTERTAINMENT'){
                    $dests_types = "  AND  (b.`dest_type`='SCENIC_ENTERTAINMENT'  OR ( b.dest_type='VIEWSPOT' AND b.`ent_sight`='Y' ))";
                }else {
                    $dests_types = " AND   b.`dest_type` ='".$dest_type."' ";
                }
            }
        }else{
            $dests_types = '';
        }

        $recomTypeStr = "";
        if($recomType != ''){
            $recomTypeStr = " AND a.recommend_type = '$recomType' ";
        }

        $sql="SELECT distinct a.dest_id,b.dest_id,b.dest_name,b.dest_type,b.pinyin,b.url,b.img_url,b.count_been,b.parents,b.intro,b.stage ".
                "FROM `ly_scenic_viewspot` AS a ".
                "LEFT JOIN `ly_destination` AS b ON a.viewspot_id=b.dest_id ".
                "WHERE b.cancel_flag='Y' AND b.`showed`='Y' ".$dests_types." AND `stage` = 1 ".$recomTypeStr.
                "AND a.`dest_id`=".$dest_id." ORDER BY a.seq ASC,b.count_been DESC LIMIT ".$limit;
//return $sql;
        $redis_key = str_replace('{sql}',md5($sql),RedisDataService::REDIS_DEST_RECOMMEND_DATA);
        $data = $this->redis->get($redis_key);
        if($data === false) {
            $return = $this->getRsBySql($sql);
            $this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
        }else{
            $return = json_decode($data,true);
        }
        return $return;
    }




}