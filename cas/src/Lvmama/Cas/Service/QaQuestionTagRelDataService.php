<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 问答问题标签对应表
 *
 * @author win.shenxiang
 *        
 */
class QaQuestionTagRelDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_question_tag_rel';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	const PRIMARY_KEY='id';

	/**
     * 获取tag_id对应的question_id
     * @param $tid  tag_id  标签id
     * @param string $type  page|all    all全部 page按条件输出
     * @param int $perpage  每页条数
     * @param int $page 页码
     * @return array
     */
    public function getQidByTid($tid, $type='page', $perpage=10, $page=1){
        $where_condition = array('tag_id'=>"=".$tid);
        if($type == 'page'){
            $limit = array('page_num'=>$page, 'page_size'=>$perpage);
        }else{
            $limit = null;
        }
        $data = $this->getList($where_condition, self::TABLE_NAME, $limit, 'question_id');
        return $data?$data:array();
    }

    /**
     * 获取category_id对应的question_id
     * @param $cid  category_id 分类
     * @param string $type  page|all    all全部 page按条件输出
     * @param int $perpage  每页条数
     * @param int $page 页码
     * @return array
     */
    public function getQidByCid($cid, $type='page', $perpage=10, $page=1){
        // get tag_id
        $where_condition = array('category_id'=>"=".$cid);
        $data = $this->getList($where_condition, 'qa_tag', null, 'id');

        if($data){
            if(count($data)>1){
                $ids = '';
                foreach($data as $val){
                    if($val['id']) $ids .= $val['id'].',';
                }
                if(strlen($ids) > 0){
                    $where = array('tag_id'=>" in (".substr($ids,0,-1).")");
                }else{
                    return array();
                }
            }else{
                $where = array('tag_id'=>"=".$data[0]['id']);
            }
        }else{
            return array();
        }

        if($type == 'page'){
            $limit = array('page_num'=>$page, 'page_size'=>$perpage);
        }else{
            $limit = null;
        }

        // get ques_id
        $res = $this->getList($where,  self::TABLE_NAME, $limit, 'id');

        return $res;

    }
	/**
	 * 获取
	 * 
	 */
	public function get($id) {
	    $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE question_id = ' . $id;
	    $result = $this->getAdapter()->query($sql);
	    $result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}
	public function getRsBySql($sql,$one = false){
        $redis_key = RedisDataService::REDIS_QA_TAG_REL . md5($sql) . ':' . ($one ? 1 : 0);
        $rs = json_decode($this->redis->get($redis_key),true);
        if(!is_array($rs)){
            $result = $this->getAdapter()->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $rs = $one ? $result->fetch() : $result->fetchAll();
            $this->redis->setex($redis_key,rand(28800,86400),json_encode($rs));
        }
        return $rs;
	}
    public function getQidByTagid($tag_id = array()){
        $sql = 'SELECT question_id FROM ' . self::TABLE_NAME . ' WHERE tag_id IN('.implode(',',$tag_id).')';
        $question_id = array();
        $q = $this->getRsBySql($sql);
        if(!$q) return $question_id;
        foreach($q as $v){
            $question_id[] = $v['question_id'];
        }
        return $question_id;
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
    /**
     * 删除
     */
    public function delete($id){
        $sql = 'DELETE FROM' . self::TABLE_NAME . ' WHERE question_id = :question_id';
        $dbh = $this->getAdapter();
        $sth = $dbh->prepare($sql);
        $sth->bindValue(':question_id', $id, \PDO::PARAM_STR);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        return $sth->execute();
    }
    public function deleteTag($data = array()){
        if(!$data) return false;
        $dbh = $this->getAdapter();
        $where = '';
        foreach($data as $k=>$v){
            $where .= $k.' = :'.$k;
        }
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE '.$where;
        $sth = $dbh->prepare($sql);
        foreach($data as $k=>$v){
            $sth->bindValue(':'.$k, $v);
        }
        return $sth->exec();
    }
}