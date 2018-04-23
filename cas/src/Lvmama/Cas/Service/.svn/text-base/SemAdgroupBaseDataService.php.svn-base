<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 推广单元信息 服务类
 *
 * @author flash.guo
 *
 */
class SemAdgroupBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_adgroup';//对应数据库表
    const PRIMARY_KEY = 'adgroupId'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加推广单元信息
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
     * 更新推广单元信息
     * @param $id 编号
     * @param $data 更新数据
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
     * 删除推广单元信息
     * @param $id 编号
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function delete($id, $platform = 1) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id . ' AND platform = ' . $platform;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取推广单元信息
     * @param $where_condition array 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getAdgroupList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取推广单元信息总数
     * @param $where_condition array 查询条件
     * @return array|mixed
     */
    public function getAdgroupTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条推广单元信息
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneAdgroup($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条推广单元信息
     * @param $id 编号
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function getOneById($id, $platform = 1) {
        $where_condition=array(self::PRIMARY_KEY => "=".$id, "platform" => "=".$platform);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
}