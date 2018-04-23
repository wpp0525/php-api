<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 频道页变量 服务类
 *
 * @author gubuchun
 *
 */
class ChTempChannelVariableService extends DataServiceBase
{

    const TABLE_NAME = 'ch_template_channel_variable';//对应数据库表
    const PRIMARY_KEY = 'channel_id'; //对应主键，如果有

    /**
     * @purpose 添加频道页变量
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data)
    {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * @purpose 更新频道页变量
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data)
    {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * @purpose 删除频道页变量
     * @param $id 编号
     * @return bool|mixed
     */
    public function delete($id)
    {
        $whereCondition = 'variable_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取频道页变量
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getVarList($where_condition, $limit = NULL)
    {
        $data = $this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data ? $data : false;
    }

    /**
     * @purpose 根据条件获取一条频道页变量
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneVar($where_condition)
    {
        $data = $this->getOne($where_condition, self::TABLE_NAME);
        return $data ? $data : false;
    }

    /**
     * @purpose 根据主键获取一条频道页变量
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id)
    {
        $where_condition = array('variable_id' => "=" . $id);
        $data = $this->getOne($where_condition, self::TABLE_NAME);
        return $data ? $data : false;
    }

    /**
     * @purpose 根据频道ID获取一条频道页变量
     * @param $cid 频道ID
     * @return bool|mixed
     */
    public function getOneByCid($cid)
    {
        if (!$cid) return false;
        $where_condition = array('channel_id' => "=" . $cid);
        $base_data = $this->getOne($where_condition, self::TABLE_NAME);
        return $base_data ? $base_data : false;
    }

    /**
     * @purpose 根据频道ID删除一条频道页变量
     * @param $cid 频道ID
     * @param $varname 变量名称
     * @return bool|mixed
     */
    public function delVarByCid($cid, $varname = '')
    {
        if (empty($cid)) return false;
        $where_condition = 'channel_id =' . $cid;
        if (!empty($varname)) $where_condition .= " AND variable_name = '" . $varname . "'";
        return $this->getAdapter()->delete(self::TABLE_NAME, $where_condition);
    }

    /**
     * @purpose 根据频道ID删除频道页变量
     * @param $cid 频道ID
     * @return bool|mixed
     */
    public function delAllVarByCid($cid)
    {
        $whereCondition = 'channel_id = ' . intval($cid);
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }
}
