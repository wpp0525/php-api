<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 推广关键词信息 服务类
 *
 * @author flash.guo
 *
 */
class SemKeywordBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_keyword';//对应数据库表
    const PRIMARY_KEY = 'keywordId'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    private $rel_tables = array(
        'adgroup' => array(
            'table' => 'sem_adgroup',
            'key' => 'adgroupId',
            'foreign_key' => 'adgroupId',
        ),
        'campaign' => array(
            'table' => 'sem_campaign',
            'key' => 'campaignId',
            'foreign_key' => 'campaignId',
        ),
        'account' => array(
            'table' => 'sem_account',
            'key' => 'userId',
            'foreign_key' => 'userId',
        )
    );

    /**
     * 添加推广关键词信息
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        if(!isset($data['createTime'])){
            $data['createTime'] = time();
        }
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新推广关键词信息
     * @param $id 编号
     * @param $data array 更新数据
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function update($id, $data, $platform = 1) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id . ' AND platform = ' . $platform;
        if(!isset($data['updateTime'])){
            $data['updateTime'] = time();
        }
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 批量保存
     * @param $data
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function saveBatch($data, $platform = 1) {
        foreach ($data as $k => $d){
            $data[$k]['updateTime'] = time();
            $data[$k]['platform'] = $platform;
        }
        return $this->save($data, self::TABLE_NAME);
    }

    /**
     * 删除推广关键词信息
     * @param $id 编号
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function delete($id, $platform = 1) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id . ' AND platform = ' . $platform;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取推广关键词信息
     * @param $where_condition array 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getKeywordList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取推广关键词信息总数
     * @param $where_condition array 查询条件
     * @return array|mixed
     */
    public function getKeywordTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条推广关键词信息
     * @param $where_condition array 查询条件
     * @param $columns string 
     * @return bool|mixed
     */
    public function getOneKeyword($where_condition, $columns = null){
        $data=$this->getOne($where_condition, self::TABLE_NAME, $columns);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条推广关键词信息
     * @param $id 编号
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function getOneById($id, $platform = 1) {
        $where_condition=array(self::PRIMARY_KEY => "=".$id, "platform" => "=".$platform);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * 获取关键词相关全数据
     * @param $condition
     * @param null $columns
     * @param array $joins
     * @param null $limit
     * @param null $order
     * @param int $ttl
     * @return bool|mixed
     * @author libiying
     */
    public function getFullKeywordList($condition, $columns = null, $joins = array('adgroup' ,'campaign' ,'account'), $limit = null, $order = null, $ttl = 3600){
        $key = md5(json_encode(array($condition, $columns, $joins, $limit, $order)));

        $result = json_decode($this->redis->get($key), true);
        if(!$result){
            $where_str = $this->initWhere($condition);
            $columns_str = $columns ? $columns : '*';
            $joins_str = '';
            foreach ($joins as $join){
                if(isset($this->rel_tables[$join])){
                    $joins_str .= $this->initJoin($this->rel_tables[$join], self::TABLE_NAME);
                }
            }
            $limit_str = $limit ? " LIMIT $limit" : '';
            $order_str = $order ? " ORDER BY $order" : '';

            $sql = "SELECT " . $columns_str . " FROM " . self::TABLE_NAME . $joins_str . $where_str . $limit_str . $order_str;
            $result = $this->query($sql, 'All');

            $this->redis->set($key, json_encode($result));
            $this->redis->expire($key, $ttl);
        }

        return $result?$result:false;
    }
}