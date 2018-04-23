<?php
namespace Lvmama\Cas\Component;

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

use Lvmama\Common\Utils\ArrayUtils;

/**
 * 主从数据提供类
 *
 * @author mac.zhao
 *        
 */
class MasterSlaveDbAdapter extends DbAdapter {
	
	private $_slaves = array();
	
	private $_masla_mode = false; // 是否主从模式
	
	private $_force_master = false;

	/**
	 * @return MasterSlaveDbAdapter
	 */
	private function getSlave($conid = null) {
		return count($this->_slaves)
				? $this->_slaves[rand(0, count($this->_slaves)-1)] // TODO: 随机?
				: $this;
	}
	
	/**
	 * @see \Phalcon\Db\Adapter\Pdo::__construct()
	 */
	public function __construct(array $descriptor) {
    	try {
    		if (array_key_exists('master', $descriptor) && array_key_exists('slaves', $descriptor)) {
    			$this->_masla_mode = true;
    			parent::__construct($descriptor['master']);
    			foreach ($descriptor['slaves'] as $slave) {
    				$this->_slaves[] = new DbAdapter($slave);
    			}
    		} elseif (array_key_exists('master', $descriptor)) {
    			parent::__construct($descriptor['master']);
    		} else {
    			parent::__construct($descriptor);
    		}
    	} catch (\Exception $ex) {
			echo "Can't connect to MySQL! Cause:" . $ex->getMessage() . " \r\n";
    	}
	}
	
	/**
	 * @see \Phalcon\Db\Adapter\Pdo::connect()
	 */
	public function connect($descriptor = null) {
		parent::connect($descriptor);
		foreach ($this->_slaves as $slave) {
			$slave->connect($descriptor);
		}
	}
	
	/**
	 * 设置连接参数
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value) {
		parent::execute("SET {$key}={$value};");
		foreach ($this->_slaves as $adapter) {
			$adapter->execute("SET {$key}={$value};");
		}
	}
	
	/**
	 * 强制切换Master
	 * 
	 * @param string $force
	 * @return \Hwj\Cas\Component\MasterSlaveDbAdapter
	 */
	public function forceMaster($force = true) {
		$this->_force_master = $this->_masla_mode ? $force : true;
		return $this;
	}
	
	/**
	 * @see \Phalcon\Db\AdapterInterface::query()
	 */
	public function query($sqlStatement, $placeholders = null, $dataTypes = null) {
    	//添加throw机制 add by guoqiya
		try {
//		    echo $sqlStatement;
			return ($this->_masla_mode && !$this->_force_master)
				? $this->getSlave()->query($sqlStatement, $placeholders, $dataTypes)
				: parent::query($sqlStatement, $placeholders, $dataTypes);
		} catch (\PDOException $e) {
    		if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
    			$this->connect();
    			try {
					return ($this->_masla_mode && !$this->_force_master)
						? $this->getSlave()->query($sqlStatement, $placeholders, $dataTypes)
						: parent::query($sqlStatement, $placeholders, $dataTypes);
    			} catch (\PDOException $e) {
    				throw $e;
    			}
    		}
			throw $e;
		}
	}
	
	/**
	 * @see \Phalcon\Db\AdapterInterface::insert()
	 * 
	 * @return integer lastInsertId();
	 */
	public function insert($table, array $values, $fields = null, $dataTypes = null) {
    	//添加自动重连机制 add by guoqiya
    	try {
			if ($result = parent::insert($table, $values, $fields, $dataTypes))
				return parent::lastInsertId();
			else 
				return $result;
    	} catch (\PDOException $e) {
    		if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
    			$this->connect();
    			try {
					if ($result = parent::insert($table, $values, $fields, $dataTypes))
						return parent::lastInsertId();
					else 
						return $result;
    			} catch (\PDOException $e) {
    				throw $e;
    			}
    		}
			throw $e;
    	}
	}
	
	/**
	 * @see \Phalcon\Db\Adapter::insertAsDict()
	 * 
	 * @return integer lastInsertId();
	 */
	public function insertAsDict($table, $data, $dataTypes = null) {
		return $this->insert($table, array_values($data), array_keys($data), $dataTypes);
	}
	
	public function update($table, $fields, $values, $whereCondition = null, $dataTypes = null) {
    	//添加自动重连机制 add by guoqiya
    	try {
			return parent::update($table, $fields, $values, $whereCondition, $dataTypes);
    	} catch (\PDOException $e) {
    		if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
    			$this->connect();
    			try {
					return parent::update($table, $fields, $values, $whereCondition, $dataTypes);
    			} catch (\PDOException $e) {
    				throw $e;
    			}
    		}
			throw $e;
    	}
	}
	
	public function delete($table, $whereCondition = NULL, $placeholders = NULL, $dataTypes = NULL) {
    	//添加自动重连机制 add by guoqiya
    	try {
			return parent::delete($table, $whereCondition, $placeholders, $dataTypes);
    	} catch (\PDOException $e) {
    		if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
    			$this->connect();
    			try {
					return parent::delete($table, $whereCondition, $placeholders, $dataTypes);
    			} catch (\PDOException $e) {
    				throw $e;
    			}
    		}
			throw $e;
    	}
	}

}