<?php
/**
 * 产品目的地关系
 */
namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class DestProductRelV2Service extends DataServiceBase
{

    const TABLE_NAME = 'dest_product_rel_v2';

    /**
     * @purpose 插入数据
     * @param $data   数据
     * @return array
     * @throws \Exception
     */
    public function insert($data)
    {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * @purpose 更新数据
     * @param $product_id
     * @param $data   数据
     * @return array
     * @throws \Exception
     */
    public function update($product_id, $data)
    {
        $condition = 'product_id = ' . $product_id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $condition);
    }

    public function updateByWhere($where, $data)
    {
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);
    }

    public function getAllByParams($params = array())
    {

        // 初始数据
        $init_params = array(
            'table'  => '',
            'select' => '*',
            'join'   => array(),
            'where'  => '1',
            'order'  => '',
            'group'  => '',
        );

        $params = array_merge($init_params, $params);

        // 组成join条件
        $join = '';
        if ($params['join']) {
            $join = implode(', ', $params['join']);
        }

        $sql = "SELECT {$params['select']} FROM {$params['table']} {$join} WHERE {$params['where']}";
//        return $sql;

        // group by
        if ($params['group']) {
            $sql .= ' GROUP BY ' . $params['group'];
        }

        // order
        if ($params['order']) {
            $sql .= " ORDER BY {$params['order']}";
        }

//        return $sql;
        $data = $this->getAdapter()->fetchAll($sql, \PDO::FETCH_ASSOC);

        if ($data && is_array($data)) {
            return $data;
        } else {
            return array();
        }

    }

    public function getDestProductRelByProductId($product_id)
    {
        $data      = array();
        $condition = array('product_id' => $product_id);
        $data      = $this->getList($condition, self::TABLE_NAME, $limit, $columns, $order);

        return $data;
    }
}
