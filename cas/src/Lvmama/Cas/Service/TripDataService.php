<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 游记 服务类
 *
 * @author mac.zhao
 *
 */
class TripDataService extends DataServiceBase {

	const TABLE_NAME = 'ly_trip';//对应数据库表

	const BEANSTALK_TUBE = 'lvmama_trip_statistics';

	const BEANSTALK_TRIP_MSG = 'lvmama_trip_msg';

	const PV_REAL = 2;

	const LIKE_INIT = 3;

	private $expression_map = array(
		'EQ'    => ' = ',
		'NEQ'   => '<>',
		'GT'    => '>',
		'EGT'   => '>=',
		'LT'    => '<',
		'ELT'   => '<=',
		'LIKE'  => 'LIKE',
		'IN'    => 'IN',
	);

	/**
	 * 查询标签
	 *
	 */
	public function getTagByTrip($ids) {
		$sql='SELECT a.`object_id`,b.`tag_id`,b.`tag_name` FROM `ly_tag_item` a ,`ly_tag` b WHERE a.`tag_id`=b.`tag_id` AND a.`object_id` IN ('.$ids.') AND a.`object_type`="trip" AND b.`tag_type`="tag"';
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * 判断新老游记
	 *
	 */
	public function checkTrip($ids) {
		$sql='SELECT `trip_id` FROM `ly_trip` WHERE `trip_id` IN ('.$ids.')';
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * 获取
	 *
	 */
	public function listTravelById($ids) {
		$sql = 'SELECT trip_id, elite FROM ' . self::TABLE_NAME . ' WHERE trip_id IN (' . implode(',', $ids) . ')';
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	/**
	 * 获取
	 *
	 */
	public function get($id) {
		$sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE trip_id = ' . $id;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}

	/**
	 * 获取
	 *
	 */
	public function getTripsByInterval($startTime, $endTime) {
		$sql = 'SELECT trip_id, title, uid, modify_time FROM ' . self::TABLE_NAME . ' WHERE user_status = 1 AND modify_time >= ' . $startTime . ' AND modify_time <= ' . $endTime;
		$result = $this->getAdapter()->query($sql);
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
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
		$whereCondition = 'trip_id = ' . $id;
		if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
			return $id;
		}
	}

	public function getRsBySql($where = array()){
		$result = $this->getAdapter()->query("select {$where['columns']} FROM ".self::TABLE_NAME." WHERE {$where['where']}");
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetch();
	}

	/**
	 * 取得指定条件的列表
	 */
	public function getLists($trips = array()){
		if(!$trips) return array();
		$result = $this->getAdapter()->query('SELECT trip_id,title FROM '.self::TABLE_NAME.' WHERE trip_id IN('.implode(',',$trips).')');
		$result->setFetchMode(\PDO::FETCH_ASSOC);
		return $result->fetchAll();
	}

	public function getTripDestList($where_condition,$page){
		$result=$this->getList($where_condition,'ly_trip_dest',$page,'dest_id,trip_id');
		return $result;
	}

	public function getTraceList($page){
		if($page!==null){
			if(is_array($page)){
				$limit_str=" LIMIT ".($page['page_num']-1)*$page['page_size']." , ".$page['page_size'];
			}else{
				$limit_str=' LIMIT '.$page;
			}
		}
		$sql="SELECT dest_id,trip_id FROM ly_trace WHERE dest_id !=0 ORDER BY dest_id DESC ".$limit_str;
		$result=$this->query($sql,'All');
		return $result;
	}

	public function getTripAll(){
		$sql="SELECT trip_id,title,thumb,username,elite,init_hits,init_praise,day_count,publish_time,memo FROM ".self::TABLE_NAME." WHERE `verify`=99 AND `finished`='Y' AND `user_status`=99  AND `showed`='Y' AND `deleted`='N'";
		$reuslt=$this->query($sql,'All');
		return $reuslt;
	}

	public function select($params = array()){
		$init_params = array(
			'table' => '',
			'select' => '*',
			'where' => array(),
			'order' => '',
			'group' => '',
			'limit' => '',
			'page' => array()
		);
		$params = array_merge($init_params, $params);
		$table_name = $params['table'];
		if(!$this->getAdapter()->tableExists($table_name))
			return array('error' => '1','result' => '表未定义');

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
			$result = $this->getAdapter()->fetchAll($sql,\PDO::FETCH_ASSOC,$where_arr['param']);
		else
			$result = $this->getAdapter()->fetchAll($sql,\PDO::FETCH_ASSOC);
		$data = array();
		$data['list'] = $result;

		if($params['page']){
			$count_sql = "SELECT count(*) as itemCount FROM {$table_name} WHERE {$params['where']}";
			if(isset($where_arr['param']))
				$count_res = $this->getAdapter()->fetchOne($count_sql,\PDO::FETCH_ASSOC,$where_arr['param']);
			else
				$count_res = $this->getAdapter()->fetchAll($count_sql,\PDO::FETCH_ASSOC);

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
	 * 解析生成where条件
	 * @param array $where
	 * @return array|string
	 */
	private function parseWhereCondition($where = array()){
		if(empty($where) || !is_array($where)) return '1';
		$res = array();
		foreach($where as $key => $value){
			$tmp = ':' . $key;
			if(is_array($value)){
				$exp = strtoupper($value['0']);
				switch($exp){
					case '=':
					case '<>':
					case '!=':
					case '>':
					case '>=':
					case '<':
					case '<=':
						$res['where'][] = "`{$key}` {$exp} {$tmp}";
						$res['param'][$key] = $value['1'];
						break;
					case 'EQ':
					case 'NEQ':
					case 'GT':
					case 'EGT':
					case 'LT':
					case 'ELT':
					case 'LIKE':
						$res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$tmp}";
						$res['param'][$key] = $value['1'];
						break;
					case 'IN'://TODO PDO不支持直接绑定IN参数。在使用时确保IN中的数据安全
						$res['where'][] = "`{$key}` {$this->expression_map[$exp]} {$value['1']}";
						break;
					default:
						break;
				}
			}else {
				$res['where'][] = "`{$key}` = {$tmp}";
				$res['param'][$key] = $value;
			}
		}
		return $res;
	}

	/**
	 * 删除数据
	 * @param array $params
	 * @return array
	 */
	public function delete($params = array()){
		$init_params = array(
			'table' => '',
			'where' => array(),
		);
		$params = array_merge($init_params,$params);
		$table_name = $params['table'];
		if(!$this->getAdapter()->tableExists($table_name))
			return array('error' => '1','result' => '表未定义');
		if(empty($params['where']))
			return array('error' => '1','result' => '未设置删除条件');

		$data = $this->select($params);
		if(empty($data['list']))
			return array('error' => '1','result' => '未找到要删除的记录');

		$where = array();
		foreach($params['where'] as $key => $value){
			$where[] = $key . ' = ' . $value;
		}
		$where_sql = implode(' AND ',$where);
		$this->getAdapter()->delete($table_name, $where_sql);
		return array('error' => '0','result' => '删除成功');
	}

	/**
	 * 新增数据
	 * @param array $params
	 * @return array
	 */
	public function insertData($params = array()) {
		$init_params = array(
			'table' => '',
			'data' => array(),
		);
		$params = array_merge($init_params,$params);
		$table_name = $params['table'];
		if(!$this->getAdapter()->tableExists($table_name))
			return array('error' => '1','result' => '表未定义');

		if(empty($params)) return array('error' => '1', 'result' => '未设置插入的值');
		$id = $this->getAdapter()->insert($table_name, array_values($params['data']), array_keys($params['data']));
		if($id)
			return array('error' => '0','result' => $this->getAdapter()->lastInsertId());
		else
			return array('error' => '1', 'result' => '插入失败');
	}
}