<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-7-14
 * Time: 下午2:00
 */
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

class QaAnswerCommentDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_answer_comment';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	const PRIMARY_KEY='id';

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
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
	}

    // 添加
    public function insert($data) {
        $is_ok = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
        if($is_ok){
            $id = $this->getAdapter()->lastInsertId();
            return $id;
        }else{
            return false;
        }
    }

    // 更新
    public function update($id, $data) {
        $whereCondition = 'id = ' . $id;
        $is_ok = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
        if($is_ok){
            return $id;
        }else{
            return false;
        }
    }

    /**
     * 查询回答审核列表
     * @param $search
     * @param $limit
     * @author liuhongfei
     */
    public function getAnswerCommentCheckData($search, $limit){

        $select = "id, answer_id, uid, username, commented_username,commented_uid, content, auditor_id, main_status, del_status, update_time ";

        $where_str = $this->parseWhereStrCondition($search);
        $where_str = str_replace("update_time_end", "update_time", $where_str);
        $limit_condition = $this->parseLimitCondition($limit);

        $sql = "SELECT {$select}FROM `qa_answer_comment` WHERE {$where_str} ORDER BY `update_time` DESC {$limit_condition['condition_str']}";
        $data['list'] = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if(count($limit_condition) > 1){
            $count_sql = "SELECT count(*) AS itemCount FROM `qa_answer_comment` WHERE {$where_str}";
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
    private function parseWhereStrCondition($where_array = array()){
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