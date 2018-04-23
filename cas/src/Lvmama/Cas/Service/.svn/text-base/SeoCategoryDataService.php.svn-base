<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * lmm_seo category表相关数据
 *
 * @author win.shenxiang
 *
 */
class SeoCategoryDataService extends DataServiceBase {

	const TABLE_NAME = 'seo_category'; //对应数据库表
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
     * 根据域名取得频道ID
     * @param string $host
     * @return array
     * @author shenxiang
     */
    public function getChannel($url = ''){
        if(!$url) return array();
        $tmp = parse_url($url);
        if(!isset($tmp['host'])) return array();
        $redis_key = RedisDataService::REDIS_SEO_CATEGORY.$tmp['host'];
        $rs = $this->redis->hGetAll($redis_key);
		$prefix_url = $tmp['scheme'].'://'.$tmp['host'];
        if(!$rs){
            $sth = $this->getAdapter()->prepare('SELECT * FROM '.self::TABLE_NAME.' WHERE url = :url AND parent_id = 0');
            $sth->bindParam(':url',$prefix_url);
            $sth->setFetchMode(\PDO::FETCH_ASSOC);
            $sth->execute();
            $rs = $sth->fetch();
            $this->redis->hmset($redis_key,$rs);
            $this->redis->expire($redis_key,86400);
        }
        return $rs;
    }

	/**
	 * 获取分类的列表
	 * @method getCategoryList
	 * @param  array          $condition 条件：array('category'=>?,'parentId'=>?)
	 * @param  int          $page      当前页
	 * @param  int          $pageSize  分页数
	 * @return array          array('total'=>?，//数据总数,'data'=>? //结果集)
	 */
	public function getCategoryList($condition,$page,$pageSize)
	{
		$fromTable		= 'seo_category';

		$fields 		= 'id, category, url, parent_id ';
		$fieldsCount	= 'count(1) as cnt ';
		$sql 			= "SELECT ".$fields." FROM ".$fromTable ;
		$sqlCount 		= "SELECT ".$fieldsCount." FROM ".$fromTable ;
		$where 			= array();
		$whereStr = '';
		if(!empty($condition)){
			if(isset($condition['category'])) {
				$where[] = "category= '".$condition['category']."'";
			}

			if(isset($condition['parentId'])) {
				$where[] = "parent_id= '".$condition['parentId']."'";
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
		$newData = array();
		if(!empty($data)){
			foreach($data as $value){
				$children =  $this->getCategoryByParents($value['id']);
				if($children){
					$value['childrens'] = $children;
				}
				$newData[]=$value;
			}

		}

		$result['data']  = $newData;

		return $result;
	}

	/**
	 * 根据父类id获取子分类集合
	 * @method getCategoryByParents
	 * @param  int               $parentId 父id
	 * @return array              子类数据
	 */
	public function getCategoryByParents($parentId)
	{
		$sql    = "";
		$where  = " parent_id = ".$parentId;
		$sql   .= "SELECT id, category from seo_category WHERE".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		return $result;
	}

	/**
	 * 获取所有一级分类以及子类的集合
	 * @method getAllCategory
	 * @return array    array(
	 *         					'id'=>,
	 *         					'category'=>,
	 *         					'childrens'=>array() //一级分类所拥有的子类
	 *         				)
	 */
	public function getAllCategory()
	{
		$sql    = "";
		$where  = " parent_id = 0";
		$sql   .= "SELECT id, category from seo_category WHERE".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		foreach($result as $value){
			$children =  $this->getCategoryByParents($value['id']);

			if($children){
				$value['childrens'] = $children;
			}
			$data[]=$value;
		}

		return $data;
	}

	/**
	 * 删除分类
	 * @method delCategory
	 * @param  string      $where_condition
	 * @return int                       affect rows
	 */
	public function delCategory($where){

	   $this->getAdapter()->delete(self::TABLE_NAME,$where);

	   return $this->getAdapter()->affectedRows();
	}

	public function addCategory($data)
	{
		$sql   = "";
		$table = self::TABLE_NAME;
		$fields= " (parent_id, category, url)";

		$category   = $data['category'];
		$url		= $data['url'];
		$parentId	= $data['parentId'];

		$values = "('".$parentId."','".$category."','".$url."')";

		$sql   .= "INSERT INTO ".$table.$fields." VALUES ".$values;

		$this->getAdapter()->execute($sql);
		$newId = $this->getAdapter()->lastInsertId();

		if(!$newId){
			return $this->getAdapter()->getErrorInfo();
		}
		return $newId;
	}

	/**
	 * 根据id获取分类信息
	 * @method getCategoryInfoById
	 * @param  int              $categoryId 分类id
	 * @return array                          分类详细信息
	 */
	public function getCategoryInfoById($categoryId)
	{
		if(empty($categoryId)) return false;

		$sql    = "";
		$where  = " id =".$categoryId;
		$sql   .= "SELECT * from ".self::TABLE_NAME." WHERE".$where;
		$result = $this->getAdapter()->fetchAll($sql);
		return $result;
	}

	/**
	 * 更新分类信息
	 * @method updateCategory
	 * @param  array         $where 条件
	 * @param  array         $data  更新的数据
	 * @return int  affectedRows
	 */
	public function updateCategory($where, $data)
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
}
