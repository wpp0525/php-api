<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * lmm_seo crawler_url表相关数据
 *
 * @author win.shenxiang
 *
 */
class SeoCrawlerUrlDataService extends DataServiceBase {

	const TABLE_NAME = 'seo_crawler_url'; //对应数据库表
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
	 * URl 列表
	 * @method getCrawlList
	 * @param  array       $condition 条件：array('url'=>?)
	 * @param  int        $page      当前页
	 * @param  int        $pageSize  分页数
	 * @return array      array(
	 *         					'total' => 100,//总数
	 *         					'data'  => array()，//结果数组
	 *                          )
	 */
	public function getCrawlList($condition,$page,$pageSize)
	{
		$fromTable		= self::TABLE_NAME." as c ";
		$fields 		= 'c.id, c.title, c.url ';
		$fieldsCount	= 'count(1) as cnt ';
		$sql 			= "SELECT ".$fields." FROM ".$fromTable;
		$sqlCount 		= "SELECT ".$fieldsCount." FROM ".$fromTable ;
		$where 			= array();
		$whereStr = '';
		if(!empty($condition)){
			if(isset($condition['url'])) {
				$where[] = "c.url= '".$condition['url']."'";
			}

			if(isset($condition['keyword'])) {
				$where[] = "c.title= '".$condition['keyword']."'";
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
}
