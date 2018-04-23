<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 关键字参数存储表
 *
 * @author dirc.wang
 *
 */
class VartypeNavigationService extends DataServiceBase {

	const TABLE_NAME = 'seo_vartype_navigation'; // 对应数据库表
  private $params = array();

/**
 * get kvars info information
 * @param  array  $data condition just '='
 * @return array       result
 */
  public function info(array $data=array() ,array $dataout=array(), $limit = 0 ,$offset = false ,$order = false){
    $this->params = array();
    $lim = ($limit > 0)?(' limit '.$limit):'';
    $offs = ($offset)?(' offset '.$offset.' '):'';
    $orde = ($order)?(' order by '.$order.' '):'';
    $sql = 'SELECT '.$this->buildShere($dataout).' FROM ' . self::TABLE_NAME . $this->buildWhere($data) . $orde.  $lim . $offs;
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
       if($dataout){
         $sub = array();
         foreach($dataout as $x){
           $sub[$x] = $robot[$x];
         }
         array_push($out, $sub);
       }else{
         array_push($out, $robot);
       }
    }
    return $out;
  }

  public function insert(array $data = array()){
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = 'INSERT INTO ' . self::TABLE_NAME . '('. $this->buildInsert($data) .') VALUES (' . $this->buildInsertData($data) . ')';
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $id = $connection->lastInsertId();
        $connection->commit();
        return $id;
    } catch (Exception $e) {
        // 如果发现异常，回滚操作
        $connection->rollback();
        return FALSE;
    }
  }

/**
 * update table
 * @param  array  $data set sql
 * @param  array  $where just '='
 * @return bool      result
 */
  public function update(array $data = array(), array $where = array()){
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = 'UPDATE ' . self::TABLE_NAME . $this->buildSetData($data) . $this->buildWhere($where);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        // 如果发现异常，回滚操作
        $connection->rollback();
        return FALSE;
    }
  }

  public function del(array $data = array()){
    if(!$data || !count($data))
      return FALSE;
    $connection = $this->getAdapter();
    $this->params = array();
    $sql = "DELETE FROM " . self::TABLE_NAME . $this->buildWhere($data);
    try {
        $connection->begin();
        $connection->execute($sql, $this->params);
        $connection->commit();
        return TRUE;
    } catch (Exception $e) {
        $connection->rollback();
        return FALSE;
    }
  }

  public function getsubnav($mids){
    $in = ' WHERE `belong_id` in (' . implode(",", $mids) .')';
    $sql = "SELECT * FROM ". self::TABLE_NAME . $in . "ORDER BY `sort`";
    $result = $this->getAdapter()->query($sql, $this->params);
    $out = array();
    while ($robot = $result->fetch()) {
      foreach($robot as $x => $x_val){
        if(is_numeric($x))
          unset($robot[$x]);
      }
      array_push($out, $robot);
    }
    return $out;
  }

  private function runExceptSelect($sql, array $params = array()){
    $connection = $this->getAdapter();
    try {
        $connection->begin();
        $connection->execute($sql, $params);
        $connection->commit();
        return true;
    } catch (Exception $e) {
        // 如果发现异常，回滚操作
        $connection->rollback();
        return false;
    }
  }

//sub function
  public function buildWhere($data){
    $out = array();
    foreach($data as $x => $_val){
      $this->params['W'.$x] = $_val;
      array_push($out, $x.'=:W'.$x);
    }
    if(count($data)>0)
      return ' where '.implode(' and ', $out).' ';
    return '';
  }

  public function buildval($data){
    $out = array();
    foreach($data as $x => $x_val){
      $out[':'.$x] = $x_val;
    }
    return $out;
  }

  public function buildShere($data){
    $out = array();
    foreach($data as $x){
      array_push($out, '`' . $x . '`');
    }
    if(count($data)>0)
      return ' '.implode(',', $out).' ';
    return '*';
  }

  public function buildInsert($data){
    $out = array();
    foreach($data as $x => $x_val){
      array_push($out, '`' . $x . '`');
    }
    if(count($data)>0)
      return ' '.implode(',', $out).' ';
    return ' ';
  }

  public function buildInsertData($data){
    $out = array();
    foreach($data as $x => $x_val){
      $this->params['I' . $x] = $x_val;
      array_push($out, ':I' . $x);
    }
    if(count($data)>0)
      return ' '.implode(',', $out).' ';
    return ' ';
  }

  public function buildSetData($data){
    $out = array();
    foreach($data as $x => $x_val){
      $this->params['S' . $x] = $x_val;
      array_push($out, '`' . $x . '` =:S' . $x);
    }
    if(count($data)>0)
      return ' SET '.implode(',', $out).' ';
    return ' ';
  }
	
}
