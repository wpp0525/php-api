<?php

namespace Lvmama\Cas\Service;

class DestinationContactService extends DataServiceBase
{
    const TABLE_NAME = 'ly_contact'; //对应数据库表
    const PRIMARY_KEY = 'contact_id';

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
     * @purpose 根据条件获取一条数据
     * @param $whereCondition array|string 查询条件
     * @return array|mixed
     */
    public function getOne($whereCondition)
    {
        return parent::getOne($whereCondition, self::TABLE_NAME);
    }

}
