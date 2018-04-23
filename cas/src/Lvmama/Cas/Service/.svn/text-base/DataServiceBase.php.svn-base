<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\DiInterface;
use Phalcon\Db\AdapterInterface;

use Lvmama\Cas\Component\RedisAdapter;
use Lvmama\Cas\Component\BeanstalkAdapter;
use Lvmama\Cas\Component\MasterSlaveDbAdapter;
use Lvmama\Common\Filesystem\Adapters\LocalAdapter;
use Phalcon\Security;
use Lvmama\Common\Utils\Misc;
use Phalcon\Db\RawValue;

/**
 * 数据服务类基类
 *
 * @author mac.zhao
 *        
 */
class DataServiceBase {
	
	/**
	 * @var DiInterface
	 */
	protected $di = null;
	
	/**
	 * @var AdapterInterface
	 */
	protected $adapter = null;
	
	/**
	 * @var RedisAdapter
	 */
	protected $redis = null;

	/**
	 * @var BeanstalkAdapter
	 */
	protected $beanstalk = null;
	/**
	 * @var bool  数据库重连
	 */
	protected $reconnect = false;
	
	/**
	 * 
	 * @param DiInterface $di
	 * @param AdapterInterface $adapter 数据提供
	 * @param RedisAdapter $redis
	 * @param BeanstalkAdapter $beanstalk
	 */
	public function __construct($di, $adapter, $redis = null, $beanstalk = null) {
		$this->di = $di;
		$this->adapter = $adapter;
		$this->redis = $redis;
		$this->beanstalk = $beanstalk;
	}
	
	/**
	 * 设定城市
	 * 
	 * @param string $location
	 * @return \Hwj\Cas\Service\DataServiceBase
	 */
	public function setLocation($location) {
		$this->location = $location;
		return $this;
	}
	
	/**
	 * 设定数据连接是否重连
	 * 
	 * @param boolean $reconnect
	 */
	public function setReconnect($reconnect = true) {
		$this->reconnect = $reconnect;
	}

	/**
	 * 手动断开链接
	 *
	 * @param boolean $reconnect
	 */
	public function disconnect() {
		if ($this->adapter instanceof LocalizeDbAdapter) {
			return $this->adapter->close();
		}else{
			return true;
		}
	}
	
	/**
	 * @return MasterSlaveDbAdapter
	 */
	protected function getAdapter($location = null, $reconnect = false) {
		if ($this->adapter instanceof LocalizeDbAdapter) {
			return $this->adapter->getAdapter($location ?: $this->location, $reconnect | $this->reconnect);
		}
		if ($reconnect || $this->reconnect)
			$this->adapter->connect();
		return $this->adapter;
	}
	
	/**
	 * @return DiInterface
	 */
	protected function getDI() {
		return $this->di;
	}
	
	/**
	 * @return RedisAdapter
	 */
	protected function getRedis() {
		return $this->redis;
	}
	
	/**
	 * @return BeanstalkAdapter
	 */
	protected function getBeanstalk() {
		return $this->beanstalk;
	}
	
	/**
	 * Redis是否连接
	 *
	 * @return boolean
	 */
	protected function isRedisConnected() {
		return !is_null($this->redis)
				&& $this->redis->isConnected();
	}
	
	/**
	 * Beanstalk是否连接
	 *
	 * @return boolean
	 */
	protected function isBeanstalkConnected() {
		return !is_null($this->beanstalk)
				&& $this->beanstalk->getConnection()->isServiceListening();
	}
	
    public function isTableExist($table_name){
		$sql="SELECT table_name FROM information_schema.TABLES WHERE table_name ='{$table_name}'";
		$resutl=$this->query($sql);
		return $resutl?true:false;
	}
	/**
	 * @purpose 根据查询条件获取某表某数据的总量
	 * @param array $where_condition
	 * @param null $table_name
	 * @return bool|mixed|null|string
	 */
	public function getTotalBy($where_condition=array(),$table_name=null){
        $where_str=$this->initWhere($where_condition);
		$sql="SELECT COUNT(1) AS num FROM ".$table_name.$where_str;
        $result=$this->query($sql);
		return $result['num']?$result['num']:false;

	}

	/**
	 * @purpose 根据查询条件获取单条数据
     * @param $where_condition
	 * @param $table_name
	 * @return bool|mixed
	 * @throws \Exception
	 */
	public  function getOne($where_condition,$table_name,$columns=null){
        $where_str=$this->initWhere($where_condition);
        $columns=$columns?$columns:' * ';
        $sql="SELECT ".$columns." FROM ".$table_name.$where_str;
		$result=$this->query($sql);
		return $result?$result:false;

	}

	/**
	 * @purpose 根据条件获取列表数据
     * @param $where_condition
     * @param $limit
	 * @return $this|bool
	 * @throws \Exception
	 */
	public function getList($where_condition,$table_name,$limit=null,$columns=null,$order=null){
        $where_str=$this->initWhere($where_condition);
        $columns=$columns?$columns:' * ';
		$limit_str = '';
        if(!empty($limit)){
            if(is_array($limit)){
                $limit_str=" LIMIT ".($limit['page_num']-1)*$limit['page_size']." , ".$limit['page_size'];
            }else{
                $limit_str=' LIMIT '.$limit;
            }
        }
		$order_str = '';
        if($order!==null){
        	$order_str=' ORDER BY '.$order;
        }
        $sql="SELECT ".$columns.' FROM '.$table_name.$where_str.$order_str.$limit_str;
        $result=$this->query($sql,'All');

		return $result?$result:false;
	}

	/**
	 * 获取多对多的记录
	 *
	 * @param string|array $condition	查询条件，请写条件字段的全称，如：tableName.columnName
	 * @param array $relation	关联关系
		array(
			'leftTable', 'leftTableId',
			'relationTable', 'relationTableLeftTableId', 'relationTableRightTableId',
			'rightTable', 'rightTableId'
		)
	 * @param null $columns
	 * @param string $fetch_module
	 * @param null $limit
	 * @param null $order
	 * @return array|boolean
	 * @author libiying
	 */
	public function getMany2Many($condition, $relation, $columns = null, $fetch_module = 'All', $order = null, $limit = null, $group = null){
		$where_str = $this->initWhere($condition);
		$relation_pattern = array(
			'leftTable', 'leftTableId',
			'relationTable', 'relationTableLeftTableId', 'relationTableRightTableId',
			'rightTable', 'rightTableId'
		);
		$rel = array();
		foreach ($relation as $key => $r){
			$rel[$relation_pattern[$key]] = $r;
		}
		$columns = $columns ? $columns : $rel['rightTable'] . '.*';
		$order_str = '';
		if($order){
			$order_str = " ORDER BY " . $order;
		}
		$limit_str = '';
		if($limit){
			$limit_str = " LIMIT " . $limit;
		}
		$group_str = '';
		if($group){
			$group_str = " GROUP BY " . $group;
		}

		$sql = "SELECT " . $columns . " FROM " . $rel['leftTable']
			. " INNER JOIN " . $rel['relationTable'] . " ON " . $rel['leftTable'] . "." . $rel['leftTableId'] . " = " . $rel['relationTable'] . "." . $rel['relationTableLeftTableId']
			. " INNER JOIN " . $rel['rightTable'] . " ON " . $rel['relationTable'] . "." . $rel['relationTableRightTableId'] . " = " . $rel['rightTable'] . "." . $rel['rightTableId']
			. $where_str . $group_str . $order_str . $limit_str;
		$result = $this->query($sql, $fetch_module);
		return $result ? $result : false;
	}

	/**
	 * @param $condition
	 * @param $table_name
	 * @return bool|mixed
	 */
	public function deleteFrom($condition, $table_name){
		$where_str = $this->initWhere($condition);

		$sql = "DELETE FROM " . $table_name . $where_str;
		$result = $this->query($sql);
		return $result ? $result : false;
	}
	/**
	 * 使用参数绑定方式预处理执行更新操作,特点是不用关心大段数据中特殊字符的问题
	 *
	 * @param $sql 需要预处理的SQL,形如:INSERT INTO segment(`id`,`segment_name`,`createtime`) VALUES (:id,:segment_name,:createtime) ON DUPLICATE KEY UPDATE `segment_name` = VALUES(`segment_name`),`createtime` = VALUES(`createtime`)
	 * @param $params 绑定的参数(如果第三个参数为false则是一维数组,否则为二维数组),形如array (array (':id' => '1',':segment_name' => '主描述',':createtime' => '2017-07-18 20:51:46'))
	 * @param $batch 是否为批量执行,此参数影响 $params的数组维度
	 */
	public function execute($sql,$params = array(),$batch = false){
		$this->getAdapter()->forceMaster();
		$sth = $this->getAdapter()->prepare($sql);
		if($batch){
			try{
				$this->beginTransaction();
				foreach($params as $param){
					$sth->execute($param);
				}
				$this->commit();
			}catch (\PDOException $e){
				$this->rollBack();
				var_dump($e->getMessage());
				return false;
			}finally{
				return true;
			}
		}
		return $sth->execute($params);
	}

	/**
	 * @purpose 执行查询
	 * @param $sql
	 * @param string $module
	 * @return bool|mixed
	 * @modify 增加insert ,update ,delete 原生SQL的支持 hongwuji  10/24/2016
	 */
	public function query($sql,$module=null){
		/*
		$result=$this->getAdapter()->query($sql);
		$type = substr($sql,0,6);
		if(in_array(strtolower($type),array('insert','update','delete'))) {return 'success';}
		*/
		$type = substr($sql,0,6);
		if(in_array(strtolower($type),array('insert','update','delete'))) {
			$this->getAdapter()->forceMaster();
			$this->getAdapter()->query($sql);
			return 'success';
		}
		$result=$this->getAdapter()->query($sql);
        if (!empty($result)) {
			$result->setFetchMode(\PDO::FETCH_ASSOC);
			$result = ($module=='All')?$result->fetchAll():$result->fetch();
        }
		return $result?$result:false;
	}

	/**
	 * 批量保存（存在则更新，不存在则插入），目前支持50条
	 * @param $data
	 * @param $table_name
	 * @return bool|mixed
	 * @author libiying
	 */
	protected function save($data, $table_name){
		if(!isset($data[0])){
			return false;
		}
		if(count($data) > 50){
			return false;
		}
		$keys = array_keys($data[0]);

		$insert_into = ' INSERT INTO ' .  $table_name . '(`' . implode('`,`', $keys) . '`)';

		$values_arr = array();
		foreach ($data as $d){
			foreach ($d as $k => $v){
				$d[$k] = addslashes($v);
			}
			$values_arr[] = "'" . implode("','", $d) . "'";
		}
		$values = " VALUES (" . implode("),(", $values_arr) . ")";

		$update_arr = array();
		foreach ($keys as $key){
			$update_arr[] = "`" . $key . "` = VALUES(`" . $key . "`)";
		}
		$update = " ON DUPLICATE KEY UPDATE " . implode(",", $update_arr);

		$sql = $insert_into . $values . $update ;
		$result = $this->getAdapter()->execute($sql);

		return $result ? $result : false;
	}

    protected function initWhere($where_condition){
        if(!$where_condition) return '';
        if(is_array($where_condition)){
            foreach($where_condition as $key=>$row){
                $where_arr[]=$key.$row;
            }
            $where_str=" WHERE ".implode(' AND ',$where_arr);
        }else{
            $where_str=" WHERE ".$where_condition;
        }
        return $where_str;
    }

	protected function initJoin($rel, $table, $rel_mode = 'LEFT JOIN'){
		if(!$rel || !$table) return '';
		$join_str = " $rel_mode " . $rel['table'] . " ON " . $rel['table'] . "." . $rel['key'] . "= $table ." . $rel['foreign_key'];

		return $join_str;
	}

	public function initPage($page){
		if($page){
			if(!is_array($page) && is_int($page)){
				$limit_str="  LIMIT ".$page;
			}elseif(is_array($page)){
				$limit_str=" LIMIT ".($page['page_num']-1)*$page['page_size']." , ".$page['page_size'];
			}else{
				$limit_str='LIMIT '.$page;
			}
		}else{
			$limit_str='';
		}
		return $limit_str;
	}
	public function lastInsertId(){
		return $this->getAdapter()->lastInsertId();
	}
	public function beginTransaction(){
		$this->getAdapter()->begin();
	}
	public function commit(){
		$this->getAdapter()->commit();
	}
	public function rollBack(){
		$this->getAdapter()->rollBack();
	}
}