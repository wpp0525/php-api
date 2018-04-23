<?php
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class DestinationTransportationService extends DataServiceBase
{

    const TABLE_NAME  = 'ly_transportation'; //对应数据库表
    const PRIMARY_KEY = 'trans_id';

    /**
     * 添加
     *
     */
    public function insert($data)
    {
        if ($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data))) {

        }
        $result = array('error' => 0, 'result' => $id);
        return $result;
    }
    /**
     * 更新
     *
     */
    public function update($id, $data)
    {
        $whereCondition = 'trans_id = ' . $id;
        if ($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition)) {
        }
        return array('error' => 0, 'result' => $id);
    }

    /**
     * 删除
     * @author lixiumeng
     * @datetime 2017-11-30T11:28:22+0800
     * @param    integer                  $id [description]
     * @return   [type]                       [description]
     */
    public function del($id = 0)
    {
        $where = 'trans_id = ' . intval($id);
        $rs    = $this->getAdapter()->delete(self::TABLE_NAME, $where);
        return array('error' => 0, 'result' => $rs);
    }

    public function getItem($fields = '', $where = '')
    {
        return $this->getOne($where, self::TABLE_NAME, $fields);
    }

    public function search($condition = array(), $limit = array(), $fields = array())
    {
        if (empty($fields)) {
            $fields = array('trans_id', 'dest_id', 'type', 'trans_name', 'memo', 'tips', 'status', 'seq', 'preseted');
        }

        //防止设置pageSize太大拖垮库
        $limit['page_size'] = isset($limit['page_size']) && is_numeric($limit['page_size']) ? ($limit['page_size'] > 30 ? 30 : $limit['page_size']) : 15;
        $limit['page_num']  = isset($limit['page_num']) && is_numeric($limit['page_num']) ? $limit['page_num'] : 1;
        $where              = ' WHERE 1 = 1';
        if (!empty($condition['type'])) {
            $where .= ' AND type = \'' . $condition['type'] . "'";
        }

        if (!empty($condition['status'])) {
            $where .= ' AND status = ' . $condition['status'];
        }

        if (!empty($condition['dest_id'])) {
            $where .= ' AND dest_id = ' . $condition['dest_id'];
        }

        // 排序
        $order = !empty($condition['order']) ? $condition['order'] : ' trans_id asc';

        //获取符合条件的总条数
        $tmp   = $this->query('SELECT COUNT(trans_id) AS c FROM ly_transportation' . $where);
        $count = intval($tmp['c']);
        //总页码
        $totalPage         = ceil($count / $limit['page_size']);
        $limit['page_num'] = $limit['page_num'] > $totalPage ? $totalPage : $limit['page_num'];

        $rt_sql = 'SELECT `' . implode('`,`', $fields) . '` FROM ly_transportation' . $where . ' order by ' . $order . ' LIMIT ' . (($limit['page_num'] - 1) * $limit['page_size']) . ',' . $limit['page_size'];
        $list   = $this->query($rt_sql, 'All');

        return array('list' => $list, 'count' => $count, 'page_num' => $limit['page_num'], 'page_size' => $limit['page_size'], 'maxPage' => $totalPage);

    }

}
