<?php

namespace Lvmama\Cas\Service;

/**
 * 目的地关联游记服务类
 */
class DestinationTripRelDataService extends DataServiceBase
{

    const TABLE_NAME = 'dest_have_trips';//对应数据库表
    const PRIMARY_KEY = 'id'; //对应主键，如果有

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
     * 根据主键删除一条数据
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
     * @param $where_condition array|string 查询条件
     * @return array|mixed
     */
    public function getItem($where_condition)
    {
        return $this->getOne($where_condition, self::TABLE_NAME);
    }

    public function getDesttripInfoById($dest_id, $limit = NULL)
    {
        $limit_str = '';
        if (!empty($limit)) {
            if (is_array($limit)) {
                $limit_str = " LIMIT " . ($limit['page_num'] - 1) * $limit['page_size'] . " , " . $limit['page_size'];
            } else {
                $limit_str = ' LIMIT ' . $limit;
            }
        }
        $sql = "select d.*,b.dest_name from dest_have_trips as d left join biz_dest as b on d.have_dest_id = b.dest_id where d.direct_dest_id = " .
            $dest_id . " order by have_dest_id ASC" . $limit_str;
        $list = $this->query($sql, 'All');

        $sql = "select count(1) as num from dest_have_trips as d left join biz_dest as b on d.have_dest_id = b.dest_id where d.direct_dest_id = " . $dest_id;
        $total = $this->query($sql);
        $total = $total ? $total['num'] : 0;
        $result = array('list' => $list, 'total' => $total);

        return $result;
    }
}