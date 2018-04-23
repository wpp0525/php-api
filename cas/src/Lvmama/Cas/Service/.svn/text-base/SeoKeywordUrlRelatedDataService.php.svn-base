<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * lmm_seo keyword_url_related表相关数据
 *
 * @author win.shenxiang
 *
 */
class SeoKeywordUrlRelatedDataService extends DataServiceBase {

	const TABLE_NAME = 'seo_keyword_url_related'; //对应数据库表
    /**
     * @purpose 插入数据
     * @param $data   数据
     * @param $table_name  详情表表名
     * @return array
     * @throws \Exception
     */
    public function insert($data,$table_name){
        $table_name=$this->dest_type?$this->dest_type:$table_name;
        $is_exist=$this->isTableExist($table_name);
        if($is_exist){
            if($id = $this->getAdapter()->insert($table_name, array_values($data), array_keys($data)) ){

            }
            $result = array('error'=>0, 'result'=>$id);
            return $result;
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }
     public function delete($table,$where_condition){
        if($id=$this->getAdapter()->delete($table,$where_condition)){
        }
     }
    /**
     * @param $where_condition
     * @param $data
     * @param $talbe_name
     */
    public function update($where_condition, $data,$talbe_name) {
        if($id = $this->getAdapter()->update($talbe_name, array_keys($data), array_values($data), $where_condition) ) {}
    }
	public function getRsBySql($sql,$one = false){
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}
	/**
	 * 获取关键词URL与爬取结果中相关联URL内的关联关系
	 * @param $url 关键词URL
	 * @param $category_id 分类ID
	 * @return array
	 */
    public function getUrlRelateKeywordLinks($url,$category_id = 0){
        if(!$url) return array();
		$redis_key = RedisDataService::REDIS_SEO_D_KEY.$url.'category'.$category_id;
		$redis_result = $this->redis->get($redis_key);
		if(!$redis_result || $redis_result == 'false'){
			//先取一条来获取返回的最大条数
			$tmp = $this->query("SELECT display_limit,rule FROM ".self::TABLE_NAME." WHERE url = '{$url}' LIMIT 1");
			$pageSize = empty($tmp['display_limit']) ? 30 : intval($tmp['display_limit']);
			$result = array();
			$ids = array();
			//查询条件
			$where = ' WHERE url = \''.$url.'\'';
			if($category_id){
				//如果category_id是parent_id为0时,把属于它的子分类ID查出来
				$tmp_category = $this->query('SELECT id FROM seo_category WHERE parent_id = '.$category_id,'All');
				//没有子ID的情况
				if(empty($tmp_category)){
					$where .= ' AND channel_id = '.$category_id;
				}else{
					$cids = array($category_id);//包括本身
					foreach($tmp_category as $row){
						$cids[] = $row['id'];
					}
					$where .= ' AND channel_id IN( '.implode(',',$cids) . ')';
				}
			}
			//先查询出符合条件的id集合,计算出总量,限制查800条,防止量太大
			$tmp_ids = $this->query('SELECT id FROM seo_keyword_url'.$where.' LIMIT 800','All');
			$count = empty($tmp_ids) ? 0 : count($tmp_ids);
			//如果没有查到数据,直接返回空数组
			if($count <= 0) return $result;
			//如果查询出来的条数大于需要的条数,则在总条数中随机返回需要的条数
			if($count > $pageSize){
				$rand_rs = array_rand(range(0,$count-1),$pageSize);
				foreach($rand_rs as $k){
					$ids[] = $tmp_ids[$k]['id'];
				}
				$where = ' WHERE id IN('.implode(',',$ids).')';
			}
			$result = $this->query('SELECT id,keyword_id,keyword_url,keyword_title FROM seo_keyword_url'.$where,'All');
			$this->redis->set($redis_key,json_encode($result));
			$this->redis->expire($redis_key,86400);
		}else{
			$result = json_decode($redis_result,true);
		}
        return $result;
    }

	/**
	 * 获取管理url列表
	 * @method getRelation
	 * @param  array      $condition
	 * @param  int      $page
	 * @param  int      $pageSize
	 * @return array
	 */
	public function getRelation($condition,$page,$pageSize)
	{
		$fromTable		= self::TABLE_NAME;

		$fields 		= 'related_id, related_title,relation_url';
		$fieldsCount	= 'count(1) as cnt ';
		$sql 			= "SELECT ".$fields." FROM ".$fromTable ;
		$sqlCount 		= "SELECT ".$fieldsCount." FROM ".$fromTable ;
		$where 			= array();
		$whereStr = '';
		if(!empty($condition)){
			if(isset($condition['urlId'])) {
				$where[] = "url_id= '".$condition['urlId']."'";
			}
		}

		if (!empty($where)) {
			$whereStr = implode(" AND ",$where);
			$sql      .= " WHERE ".$whereStr;
			$sqlCount.= " WHERE ".$whereStr;
		}


		$total = $this->getAdapter()->fetchOne($sqlCount);

		$limit = $this->initPage(
			array(
				'page_num'  => $page,
				'page_size' => $pageSize
			)
		);

		$sql .= $limit;

		$data = $this->getAdapter()->fetchAll($sql);

		$result  		 = array();
		$result['total'] = $total['cnt'];
		$result['data']  = $data;

		return $result;
	}


	/**
	 * 获取url 关联的url列表
	 * @method getCrawlUrl
	 * @param  array      $condition 条件
	 * @param  int      $page      当前页
	 * @param  int      $pageSize  分页数
	 * @return array                  array('total'=>?，//数据总数,'data'=>? //结果集)
	 */
	public function getCrawlUrl($condition,$page,$pageSize)
	{
		$fromTable		=  self::TABLE_NAME ;
		$fields 		= ' * , GROUP_CONCAT(related_id) as relation_str ';
		$fieldsCount	= 'count(1) as cnt ';
		$sql 			= "SELECT ".$fields." FROM ".$fromTable;
		$where 			= array();
		$whereStr = '';
		if(!empty($condition)){

			if(isset($condition['id'])) {
				$where[] = " id= '".$condition['id']."'";
			}

			if(isset($condition['url'])) {
				$where[] = " url= '".$condition['url']."'";
			}

			if(isset($condition['keyword'])) {
				$where[] = "related_title= '".$condition['keyword']."'";
			}
		}

		if (!empty($where)) {
			$whereStr = implode(" AND ",$where);
			$sql      .= " WHERE ".$whereStr;

		}
		$sql .= " GROUP BY url_id ORDER BY id DESC ";

		$sqlCount 		= "SELECT ".$fieldsCount." FROM (". $sql ." ) as c";
		if ($whereStr) {
			$sqlCount.= " WHERE ".$whereStr;
     	}
		$total = $this->getAdapter()->fetchOne($sqlCount);


		$limit = $this->initPage(
			array(
				'page_num'  => $page,
				'page_size' => $pageSize
			)
		);

		$sql .= $limit;

		$data = $this->getAdapter()->fetchAll($sql);

		$result  		 = array();
		$result['total'] = $total['cnt'];
		$result['data']  = $data;

		return $result;
	}

	/**
	 * 根据id获取url关系信息
	 * @method getUrlInfoById
	 * @param  int         $dataId
	 * @return array
	 */
	public function getUrlInfoById($dataId)
	{
		$fromTable		=  self::TABLE_NAME ;
		$fields 		= ' * ';
		$whereStr 		= 'id='.$dataId;
		$sql 			= "SELECT ".$fields." FROM ".$fromTable." WHERE ".$whereStr;
		$data = $this->getAdapter()->fetchAll($sql);
		return $data;
	}

	/**
	 * 更新记录信息
	 * @method updateUrlRelation
	 * @param  array            $where array(id=>?)
	 * @param  array            $data  array(rule=>?)
	 * @return int                   affectedRows
	 */
	public function updateUrlRelation($where, $data)
	{
		$tableName = self::TABLE_NAME;
		$wheres = "";
		$datas  = "";
		foreach ($where as $key => $value){
			$wheres .= "`".$key."`='".$value."'";
			if($value != end($where)){
				$wheres .=" AND ";
			}
		}
		foreach ($data as $k => $v){
			$datas .="`".$k."`='".$v."'";
			if($v != end($data)){
				$datas .=" , ";
			}
		}
		$sql = "UPDATE ".$tableName." SET ".$datas." WHERE ".$wheres;

		$this->getAdapter()->execute($sql);
		return $this->getAdapter()->affectedRows();
	}
}
