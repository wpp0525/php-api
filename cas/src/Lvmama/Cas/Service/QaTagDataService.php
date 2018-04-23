<?php
/**
 * 问答标签表
 * User: liuhongfei
 * Date: 16-6-14
 * Time: 上午11:49
 */
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
class QaTagDataService extends DataServiceBase {

    const TABLE_NAME = 'qa_tag';
    const PRIMARY_KEY='id';

    /**
     * @param $id
     * @return bool|mixed
     */
    public function getById($id){
        $where_condition = array('id' => "=".$id);
        $data = $this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @param $cate_id
     * @return bool|mixed
     */
    public function getByCateId($cate_id){
        $where_condition = array('category_id'=>"=".$cate_id);
        $data = $this->getList($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

	/**
	 * 根据分类取得标签ID (上一个方法传给getList的第三个参数不传将导致异常)
	 * @param $category_id
	 * @return mixed|array
	 */
	public function getIdByCategoryId($category_id){
		$sql = 'SELECT id FROM ' . self::TABLE_NAME . ' WHERE category_id = ' . $category_id.' AND `status` = 1';
		$tag_id = array();
		$tag = $this->getRsBySql($sql);
		if(!$tag) return $tag_id;
		foreach($tag as $v){
			$tag_id[] = $v['id'];
		}
		return $tag_id;
	}
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
		$redis_key = RedisDataService::REDIS_QA_CATE_TAG . md5($sql) . ':' . ($one ? 1 : 0);
		$rs = json_decode($this->redis->get($redis_key),true);
		if(!is_array($rs)){
			$result = $this->getAdapter()->query($sql);
			$result->setFetchMode(\PDO::FETCH_ASSOC);
			$rs = $one ? $result->fetch() : $result->fetchAll();
			$this->redis->setex($redis_key,rand(28800,86400),json_encode($rs));
		}
		return $rs;
	}
	/**
	 * 添加
	 * 
	 */
	public function insert($data) {
	    if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){
	        return $id;
	    }
	}
	
	/**
	 * 更新
	 * 
	 */
	public function update($id, $data) {
	    $whereCondition = 'id = ' . $id;
	    if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
	        return $id;
	    }
	}
}