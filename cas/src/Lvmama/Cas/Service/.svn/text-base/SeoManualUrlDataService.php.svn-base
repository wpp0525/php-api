<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * lmm_seo manual_url表相关数据
 *
 * @author win.shenxiang
 *
 */
class SeoManualUrlDataService extends DataServiceBase {

	const TABLE_NAME = 'seo_manual_url'; //对应数据库表
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

	public function getRsBySql($sql,$one = false)
	{

		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}

    /**
     * 获取关键词列表
     * @method getManualList
     * @param  array        $condition 条件
     * @param  int        $page      当前页
     * @param  int        $pageSize  分页数
     * @return array      array(
     *         					'total' => 100,//总数
     *         					'data'  => array()，//结果数组
     *                          )
     *
     */
	public function getManualList($condition,$page,$pageSize)
	{
		$fromTable		= self::TABLE_NAME." as m ";
		$categoryTable = "seo_category as c ";
		$fields 		= 'm.id, m.url, m.keyword, m.max_match_times, m.status, m.crawl_status,m.category_id ,c.category ';
		$fieldsCount	= 'count(1) as cnt ';
		$sql 			= "SELECT ".$fields." FROM ".$fromTable." LEFT JOIN ".$categoryTable." ON m.category_id = c.id ";
		$sqlCount 		= "SELECT ".$fieldsCount." FROM ".$fromTable." LEFT JOIN ".$categoryTable." ON m.category_id = c.id ";
		$where 			= array();
		$whereStr = '';
		if(!empty($condition)){
			if(isset($condition['url'])) {
				$where[] = "m.url= '".$condition['url']."'";
			}

			if(isset($condition['keyword'])) {
				$where[] = "m.keyword= '".$condition['keyword']."'";
			}

			if(isset($condition['type'])) {
				$where[] = "m.category_id= ".$condition['type'];
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

		if(!empty($data)){
			foreach ($data as $value) {
				$maxCount = $this->getCrawlerCountByKeywordId($value['id']);
				$value['max_count'] = $maxCount['cnt'];
				$result['data'][]   = $value;

			}
		}
        return $result;
	}

	/**
	 * 根据关键词id获取此关键抓取到url的总数
	 * @method getCrawlerCountByKeywordId
	 * @param  int     $keywordId keyword_id
	 * @return array     array('cnt'=>1 //总数)
	 */
	public function getCrawlerCountByKeywordId($keywordId)
	{
		$sql	= "";
		$table	= "seo_keyword_url";
		$where  = "keyword_id = ".$keywordId;
		$fields = "count(*) as cnt";
		$sql   .= "SELECT ".$fields." FROM ".$table." WHERE ".$where." group by keyword_id";
		$result = $this->getAdapter()->fetchOne($sql);
		return $result;
	}

	/**
	 * 根据id批量删除 关键词
	 * @method delManualByIds
	 * @param  array         $ids 关键词id数组:array(1,2,3)
	 * @return int            删除的行数
	 */
	public function delManualByIds($ids)
    {
        $sql    = "";
        $where  = "id in (" .$ids.")";
        $sql   .= "DELETE FROM `seo_manual_url` WHERE ".$where;
        $this->getAdapter()->execute($sql);
        $affect = $this->getAdapter()->affectedRows();
        return $affect;
    }


	/**
	 * 新增关键词
	 * @method addManual
	 * @param  array    $data 关键词数组： array(
	 *                        		'categoryId' => , //分类id
	 *                        		'channelId'  => , //一级分类id
	 *                        		'keyword'	 => , //关键词
	 *                        		'url'		 => , //关键词url
	 *                        		'maxMatchTimes' => , //最大匹配次数
	 *                        )
	 */
	public function addManual($data)
	{
		$sql   = "";
		$table = self::TABLE_NAME;
		$fields= " (category_id, channel_id, url, keyword, max_match_times, create_time)";
		$categoryId = $data['categoryId'];
		$keyword 	= $data['keyword'];
		$url		= $data['url'];
		$channelId	= $data['channelId'];
		$match		= $data['maxMatchTimes'];
		$createTime	= date("Y-m-d H:i:s",time());

		$values = "(".$categoryId.",".$channelId.",'".$url."','".$keyword."',".$match.",'".$createTime."')";

		$sql   .= "INSERT INTO ".$table.$fields." VALUES ".$values;

		$this->getAdapter()->execute($sql);
		$newId = $this->getAdapter()->lastInsertId();

		if(!$newId){
			return $this->getAdapter()->getErrorInfo();
		}
		$this->pushKeywordToRedis("baidu:url", $keyword, $newId);
		return $newId;
	}

	/**
	 * 根据id获取关键词的信息
	 * @method getManualInfoById
	 * @param  int            $manualId id
	 * @return array                      关键词信息
	 */
	public function getManualInfoById($manualId)
	{
        if(empty($manualId)) return false;

		$sql    = "";
		$where  = " id =".$manualId;
		$sql   .= "SELECT id, category_id, channel_id, url, keyword, max_match_times, status from seo_manual_url WHERE".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		return $result;
	}

	/**
	 * 更新关键词
	 * @method updateManual
	 * @param  array       $where 条件数组
	 * @param  array       $data  更新数据数组
	 * @return int           影响的行数
	 */
	public function updateManual($where, $data)
	{
		$tableName 		 = self::TABLE_NAME;
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

	/**
	 * 根据数组插入关键词
	 * @method insertManual
	 * @param  array       $data array(
	 *                           	array(),
	 *                           	array(),
	 *                           	...
	 *                           )
	 * @return int  插入的条数
	 */
	public  function  insertManual($data)
    {
        $tableName =  self::TABLE_NAME;
        $fields    =implode(',', array_keys(current($data)));
        $values = "";
		$end = end($data);
	    reset($data);
        foreach ($data as $key => $value){
            $values .= "( '";
            $values .= implode("','", array_values($value));
            $values .="')";
            if($value != $end){
                $values .=",";
            }
        }
        $sql	   = "INSERT INTO ".$tableName."(".$fields." ) VALUES ". $values;
        $this->getAdapter()->execute($sql);
		$this->pushUploadKeywordsToRedis($this->getAdapter()->lastInsertId(), $this->getAdapter()->affectedRows());
        return $this->getAdapter()->affectedRows();
    }

	/**
	 * 判断关键词是否唯一
	 * @param $category_id
	 * @param $url
	 * @param $keyword
	 * @return bool 存在的时候返回true, 不存在返回false
	 */
	public function checkManual($category_id, $url, $keyword)
	{
		$sql    = "";
		$where  = " category_id =".$category_id." AND url='".$url."' AND keyword='".$keyword."'";
		$sql   .= "SELECT id from seo_manual_url WHERE ".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		if (empty($result)){
			return false;
		}
		return true;
	}
	
	/**
	 * @param $lastId
	 * @param $count
	 */
	public  function pushUploadKeywordsToRedis($lastId, $count)
	{
		$tableName =  self::TABLE_NAME;
		$sql    = "";
		$endId = $lastId+$count-1;
		$where  = " id between ".$lastId." AND " . $endId;
		$sql   .= "SELECT id, keyword FROM ". $tableName ." WHERE  ".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		if ($result){
			foreach ($result as $v) {
				$this->pushKeywordToRedis("baidu:url", $v['keyword'], $v['id']);
			}
		}
		return;
	}
	
	/**
	 * @param $key
	 * @param $val
	 * @param $keyId
	 */
	public function pushKeywordToRedis($key, $val,$keyId)
	{
		$keyword = "site:lvmama.com ".$val;
		$data =array();
		$data['wd']   = $keyword;
		$data['soul'] = $keyId;
		$data['pn']   = 0;
		$data['cl']   = 3;
		$data['rn']   = 50;
		$url = "http://www.baidu.com/s?".http_build_query($data);
		$this->di->get('redis')->lpush($key,$url);
		return;
	}
}
