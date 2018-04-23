<?php

namespace Lvmama\Cas\Service;

class MoAttachmentsService extends DataServiceBase
{
    const TABLE_NAME = 'mo_attachments'; //对应数据库表
    const PRIMARY_KEY = 'attachment_id';//对应主键，如果有

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

}
