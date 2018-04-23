<?php

namespace Lvmama\Cas\Service;

class DestinationTimeService extends DataServiceBase
{
    const TABLE_NAME = 'ly_time'; //对应数据库表
    const PRIMARY_KEY = 'time_id';

    /**
     * 添加数据
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data)
    {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新数据
     * @param $id 编号
     * @param $data array 更新数据
     * @return bool|mixed
     */
    public function update($id, $data)
    {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除数据
     * @param $id 编号
     * @return bool|mixed
     */
    public function delete($id)
    {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取数据
     * @param $where_condition array|string 查询条件
     * @param $limit 查询条数
     * @param $columns 查询字段
     * @param $order 排序字段
     * @return array|mixed
     */
    public function getListData($where_condition, $limit = NULL, $columns = "*", $order = NULL)
    {
        $data = $this->getList($where_condition, self::TABLE_NAME, $limit, $columns, $order);
        return $data ? $data : false;
    }

    /**
     * @purpose 根据条件获取总数
     * @param $where_condition array|string 查询条件
     * @return array|mixed
     */
    public function getTotal($where_condition)
    {
        $data = $this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data ? $data : false;
    }

    /**
     * @purpose 根据条件获取一条数据
     * @param $id 编号
     * @return array|mixed
     */
    public function getItem($id)
    {
        $whereCondition = self::PRIMARY_KEY . ' = ' . $id;
        return $this->getOne($whereCondition, self::TABLE_NAME);
    }

}
