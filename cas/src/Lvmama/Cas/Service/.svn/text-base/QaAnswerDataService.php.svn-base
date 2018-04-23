<?php
namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 用户回答主表
 *
 * @author win.shenxiang
 *        
 */
class QaAnswerDataService extends DataServiceBase {
	
	const TABLE_NAME = 'qa_answer';//对应数据库表
	
	const BEANSTALK_TUBE = '';
	
	const BEANSTALK_TRIP_MSG = '';

	const PV_REAL = 2;
	
	const LIKE_INIT = 3;

	const PRIMARY_KEY='id';

	/**
     * 虎哥版select增强版 -- new name -> getByParams
     * @param array $params
     * @return array
     */
    public function getByParams($params = array()){
        $init_params = array(
            'table' =>'',
            'select' => '*',
            'where' => '1',
            'order' => '',
            'group' => '',
            'limit' => '',
            'page' => false
        );

        $params = array_merge($init_params, $params);
        $table_name = $params['table']?$params['table']:self::TABLE_NAME;

        $where_arr = $this->parseWhereCondition($params['where']);
        $params['where'] = is_array($where_arr) ? implode(' AND ',$where_arr['where']) : '1';

        $sql = "SELECT {$params['select']} FROM {$table_name} WHERE {$params['where']}";

        if($params['order'])
            $sql .= " ORDER BY {$params['order']}";

        if($params['group'])
            $sql .= " GROUP BY {$params['group']}";

        if($params['page']) {
            $params['page']['pageSize'] = $params['page']['pageSize'] ? $params['page']['pageSize'] : '10';
            $params['page']['page'] = $params['page']['page'] ? $params['page']['page'] : '1';
            $offset = ($params['page']['page'] - 1) * $params['page']['pageSize'];
            $sql .= " LIMIT {$offset},{$params['page']['pageSize']}";
        }elseif ($params['limit']) {
            $limit_arr = explode(',', $params['limit']);
            if (count($limit_arr) == 1)
                $params['limit'] = '0,' . $limit_arr[0];
            else
                $params['limit'] = $limit_arr[0] . ',' . $limit_arr[1];
            $sql .= " LIMIT {$params['limit']}";
        }

        if(isset($where_arr['param']))
            $result = $this->getAdapter()->fetchAll($sql,'',$where_arr['param']);
        else
            $result = $this->getAdapter()->fetchAll($sql);
        $data = array();
        $data['list'] = $result;

        if($params['page']){
            $count_sql = "SELECT count(*) as itemCount FROM {$table_name} WHERE {$params['where']}";
            if(isset($where_arr['param']))
                $count_res = $this->getAdapter()->fetchOne($count_sql,$where_arr['param']);
            else
                $count_res = $this->getAdapter()->fetchOne($count_sql);
            $itemCount = $count_res['itemCount'];
            $data['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $params['page']['pageSize']),
                'page' => $params['page']['page'],
                'pageSize' => $params['page']['pageSize']
            );
        }
        return $data;
    }

    /**
     * 虎哥版
     * @param array $where
     * @return array|string
     */
    private function parseWhereCondition($where = array()){
        if(empty($where) || !is_array($where)) return '1';
        $res = array();
        foreach($where as $key => $value){
            $tmp = ':' . $key;
            $res['where'][] = "`{$key}` = {$tmp}";
            $res['param'][$key] = $value;
        }
        return $res;
    }

    /**
     * 回答 insert Or update
     * @param $data     要更新或插入数据
     * @param int $key      回复id
     * @return array
     */
    public function  operateAnswer($data, $key = 0){

        if(!$this->getAdapter()->tableExists(self::TABLE_NAME))
            return array('error' => 1,'result' => '数据表不存在！');

        if(empty($data))
            return array('error' => 1,'result' => '无数据可操作！');

        // $key 如果有值的话 就是修改 如果没有值的话 就是新增
        if($key > 0){
            $init_data = array(
                'status' => 0,
                'update_time' => time()
            );
            $params = array_merge($init_data, $data);
            $where = 'id = ' . $key;
            $complete = $this->getAdapter()->update(self::TABLE_NAME, array_keys($params), array_values($params), $where);
            if($complete){
                $id = strval($key);
            }
        }else{
            $init_data = array(
                'create_time' => time(),
                'update_time' => time()
            );
            $params = array_merge($init_data, $data);
            $complete = $this->getAdapter()->insert(self::TABLE_NAME, array_values($params), array_keys($params));
            if($complete){
                $id = $this->getAdapter()->lastInsertId();
            }
        }

        if($id){
            $result = array('error' => 0, 'result' => $id);
        }else{
            $result = array('error' => 1, 'result' => '更新数据失败，稍后重试！');
        }
        return $result;
    }

    public function checkAnswerStatus($ans_id, $status){
        if(!$this->getAdapter()->tableExists(self::TABLE_NAME))
            return array('error' => 1,'result' => '数据表不存在！');

        if(!$ans_id)
            return array('error' => 1,'result' => 'answer_id不能为空！');

        $allow_status = array(1, 2, 3);
        if(!in_array($status, $allow_status))
            return array('error' => 1,'result' => 'status不是合法的审核状态值！');

        $param = array(
            'status' => $status
        );

        $where = 'id = ' . $ans_id;
        $complete = $this->getAdapter()->update(self::TABLE_NAME, array_keys($param), array_values($param), $where);

        if($complete){
            return array('error' => 1,'result' => '审核完成！');
        }else{
            return array('error' => 1,'result' => '操作失败，稍后重试');
        }
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
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $one ? $result->fetch() : $result->fetchAll();
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
     * 查询回答审核列表
     * @param $search
     * @param $limit
     * @author liuhongfei
     */
    public function getCommunityAnswerCheckData($search, $limit){

        $select = "id, question_id, uid, username, content, auditor_id, main_status, del_status, update_time ";

        $where_str = $this->parseWhereStrCondition($search);
        $where_str = str_replace("update_time_end", "update_time", $where_str);
        $limit_condition = $this->parseLimitCondition($limit);

        $sql = "SELECT {$select}FROM `qa_answer` WHERE {$where_str} ORDER BY `update_time` DESC {$limit_condition['condition_str']}";
        $data['list'] = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if(count($limit_condition) > 1){
            $count_sql = "SELECT count(*) AS itemCount FROM `qa_answer` WHERE {$where_str}";
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

    //
    public function getAnswerTop5($time_begin = '', $time_end = '', $top_num = 5){

        $time_begin = mktime(0,0,0,date('m'),date('d')-7, date('Y'));
        $time_end = mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        $sql = "SELECT COUNT(*) AS top ,`uid` FROM `qa_answer` ".
            "WHERE `del_status`=0 AND `main_status`=5 AND `update_time` > {$time_begin}  AND `update_time` < {$time_end} ".
            "GROUP BY `uid` ORDER BY top DESC LIMIT 5 ";
        $data = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        return $data;
    }

    public function getTotalUserNum(){
        $sql = "SELECT COUNT(DISTINCT uid) as totaluser FROM `qa_answer`";
        $data = $this->getAdapter()->fetchOne($sql, \PDO::FETCH_ASSOC);
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