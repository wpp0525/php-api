<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 推广创意信息 服务类
 *
 * @author flash.guo
 *
 */
class SemCreativeBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'sem_creative';//对应数据库表
    const PRIMARY_KEY = 'creativeId'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加推广创意信息
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新推广创意信息
     * @param $id 编号
     * @param $data 更新数据
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function update($id, $data, $platform = 1) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id . ' AND platform = ' . $platform;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除推广创意信息
     * @param $id 编号
     * @param $platform 所属平台，默认1为百度
     * @return bool|mixed
     */
    public function delete($id, $platform = 1) {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id . ' AND platform = ' . $platform;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取推广创意信息
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序
     * @return array|mixed
     */
    public function getCreativeList($where_condition, $limit = NULL, $columns = NULL, $order = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取推广创意信息总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getCreativeTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条推广创意信息
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneCreative($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条推广创意信息
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