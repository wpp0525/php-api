<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 问答问题表
 *
 * @author win.shenxiang
 *        
 */
class QaQuestionDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_question';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	const PRIMARY_KEY='id';

	/**
     * @param $id
     * @return array
     */
    public function getById($id){
        $where_condition = array('id' => "=".$id);
        $data = $this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:array();
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
        $redis_key = RedisDataService::REDIS_QA_SQL.md5($sql).':'.($one ? 1 : 0);
        $rs = json_decode($this->redis->get($redis_key),true);
        if(!is_array($rs)){
            $result = $this->getAdapter()->query($sql);
            $result->setFetchMode(\PDO::FETCH_ASSOC);
            $rs = $one ? $result->fetch() : $result->fetchAll();
            $this->redis->setex($redis_key,rand(1800,7200),json_encode($rs));
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


    public function getCommonQuestion($tag1, $tag2, $page = 1, $limit = 10){

        if(is_array($tag1)){
            $where1 = " qtr1.`tag_id` in ('".implode("', '", $tag1)."') ";
        }else{
            $where1 = " qtr1.`tag_id` = '{$tag1}' ";
        }

        if(is_array($tag2)){
            $where2 = " qtr2.`tag_id` in ('".implode("', '", $tag2)."') ";
        }else{
            $where2 = " qtr2.`tag_id` = '{$tag2}' ";
        }

        $begin = ($page - 1) * $limit;
        $limit_str = " LIMIT {$begin}, {$limit}";

        $order = " ORDER BY qtr1.`question_id` DESC ";

        $select_str = "SELECT q.`id`, q.`content` AS qcontent, aa.`content` AS acontent, qtr1.`tag_id` AS bu, qtr2.`tag_id`, aa.`admin_id` ";
//        $select_str = "SELECT * ";
        $select_count = "SELECT count(*) AS itemCount ";

        $sql_temp = " FROM `qa_question_tag_rel` AS qtr1 ".
            " LEFT JOIN `qa_question_tag_rel` AS qtr2 ON qtr1.`question_id` = qtr2.`question_id` ".
            " LEFT JOIN `qa_question` AS q ON qtr1.`question_id` = q.`id` ".
            " LEFT JOIN `qa_admin_answer` AS aa ON qtr1.`question_id` = aa.`question_id` ".
            " WHERE q.`del_status` = 0 AND aa.`status` = 1 AND {$where1} AND {$where2} ";

        $sql = $select_str.$sql_temp.$order.$limit_str;
        $count_sql = $select_count.$sql_temp;

//        echo $sql;die;
        $res['list'] = $this->getRsBySql($sql);
        $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);

        $itemCount = $count_res['itemCount'];
        $res['pages'] = array(
            'itemCount' => $itemCount,
            'pageCount' => ceil($itemCount / $limit),
            'page' => $page,
            'pageSize' => $limit
        );

        return $res;

    }


    /**
     * 产品问答 for cms - 问题管理 - 问题审核列表
     * @param array $search
     * @param int $tag_id
     * @param int $product_id
     * @param string|array $limit
     * @return mixed
     * @author liuhongfei
     */
    public function getProductQuestionCheckData($search = array(), $tag_id = 0, $product_id = 0, $limit = '15'){

        $select = "q.`id`, q.`uid`, q.`username`, q.`content`, q.`update_time`,q.`auditor_id`,q.`main_status`";
        $rel_table = '';

        if($tag_id){
            $rel_table = "INNER JOIN `qa_question_tag_rel` AS qtr  ON qtr.`question_id` = q.`id` ";
            $search['qtr.tag_id'] = "= {$tag_id}";
        }

        if($product_id){
            if(strpos($product_id, '|')){
                $temp = explode('|', $product_id);
                $search['qpr.product_id'] = "IN (".implode(', ',$temp).")";
            }else{
                $search['qpr.product_id'] = "= {$product_id}";
            }
        }

        $where_str = $this->parseWhereCondition($search);
        $where_str = str_replace("update_time_end", "update_time", $where_str);
        $limit_condition = $this->parseLimitCondition($limit);

        $sql = "SELECT {$select} FROM `qa_question` AS q INNER JOIN `qa_question_product_rel` AS qpr  ON qpr.`question_id` = q.`id` {$rel_table}WHERE {$where_str} ORDER BY q.`update_time` DESC {$limit_condition['condition_str']}";
        $data['list'] = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if(count($limit_condition) > 1){
            $count_sql = "SELECT count(*) AS itemCount FROM `qa_question` AS q INNER JOIN `qa_question_product_rel` AS qpr  ON qpr.`question_id` = q.`id` {$rel_table}WHERE {$where_str}";
            $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);
            $itemCount = $count_res['itemCount'];
            $data['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount/$limit_condition['pageSize']),
                'page' => $limit_condition['page'],
                'pageSize' => $limit_condition['pageSize']
            );
        }

        return $data;

    }







    /**
     * 问答社区 for cms - 问题管理 - 问题审核列表
     * @param $search
     * @param $tag_id
     * @param $dest_id
     * @param $limit
     * @return mixed
     * 若出现慢查询，考虑更改为
     * SELECT q.`id`, q.`uid`, q.`username`, q.`title`, q.`content`, q.`update_time`, q.`main_status`
     *          FROM `qa_question` AS q,`qa_question_dest_rel`  AS qdr
     *          WHERE  q.`del_status` = 0 AND q.`title` <> '' AND q.`main_status` > 1 AND qdr.`question_id` = q.`id`
     *          ORDER BY q.`update_time` DESC LIMIT 0,15
     * @author liuhongfei
     */
    public function getCommunityQuestionCheckData($search = array(), $tag_id = 0, $dest_id = 0, $limit = '15'){

        $select = "q.`id`, q.`uid`, q.`username`, q.`title`, q.`content`, q.`update_time`, q.`main_status`";

        $rel_table = array();
        if($tag_id){
            $rel_table[] = "LEFT JOIN `qa_question_tag_rel` AS qtr  ON qtr.`question_id` = q.`id` ";
            $search['qtr.tag_id'] = "= {$tag_id}";
        }

        if($dest_id){
            $select .= ", qdr.`dest_id`";
            $rel_table[] = "LEFT JOIN `qa_question_dest_rel` AS qdr  ON qdr.`question_id` = q.`id` ";
            $search['qdr.dest_id'] = "= {$dest_id}";
        }

        $where_str = $this->parseWhereCondition($search);
        $where_str = str_replace("update_time_end", "update_time", $where_str);
        $limit_condition = $this->parseLimitCondition($limit);

        $join_str = '';
        if($rel_table){
            foreach($rel_table as $table_str){
                $join_str .= $table_str;
            }
        }

        $sql = "SELECT {$select} FROM `qa_question` AS q {$join_str}WHERE {$where_str} ORDER BY q.`update_time` DESC {$limit_condition['condition_str']}";
//        echo $sql; die;
        $data['list'] = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if(count($limit_condition) > 1){
            $count_sql = "SELECT count(*) AS itemCount FROM `qa_question` AS q {$join_str}WHERE {$where_str}";
            $count_res = $this->getAdapter()->fetchOne($count_sql, \PDO::FETCH_ASSOC);
            $itemCount = $count_res['itemCount'];
            $data['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount/$limit_condition['pageSize']),
                'page' => $limit_condition['page'],
                'pageSize' => $limit_condition['pageSize']
            );
        }

        return $data;

    }

    /**
     * 组成 where 条件
     * @param array $where_array
     * @return string
     * @author liuhongfei
     */
    private function parseWhereCondition($where_array = array()){
        if(empty($where_array) || !is_array($where_array)) return '1';
        $where_arr = array();
        foreach($where_array as $key => $value){
            if(strpos($key, '.')){
                $a_key = explode('.', $key);
                $ak0 = $a_key[0];
                $ak1 = $a_key[1];
                $where_arr[] = " {$ak0}.`{$ak1}` {$value}";
            }else{
                $where_arr[] = " `{$key}` {$value}";
            }
        }
        $where_condition = is_array($where_arr) ? implode(' AND',$where_arr) : '1';
        return $where_condition;
    }

    /**
     * 组成 limit 条件
     * @param array|string $limit
     * @return array
     * @author liuhongfei
    */
    private function parseLimitCondition($limit = '15'){
        if(is_array($limit)){
            $limit['pageSize'] = isset($limit['pageSize']) ? $limit['pageSize'] : '10';
            $limit['page'] = isset($limit['page']) ? $limit['page'] : '1';
            $offset = ($limit['page'] - 1) * $limit['pageSize'];
            $limit['condition_str'] = "LIMIT {$offset}, {$limit['pageSize']}";
        }else{
            $limit['condition_str'] = "LIMIT {$limit}";
        }
        return $limit;
    }


}