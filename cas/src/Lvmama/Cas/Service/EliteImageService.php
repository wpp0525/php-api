<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class EliteImageService extends DataServiceBase
{
    const TABLE_NAME = 'ly_elite_image'; //对应数据库表

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
        $whereCondition = 'image_id = ' . $id;
        if ($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition)) {
        }
        return array('error' => 0, 'result' => $id);
    }

    public function updateBy($whereCondition, $data)
    {
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
        $where = 'image_id = ' . intval($id);
        $rs    = $this->getAdapter()->delete(self::TABLE_NAME, $where);
        return array('error' => 0, 'result' => $rs);
    }

    /**
     * 获取目的地总图片数量 从老的图片库获取
     * @return bool|mixed|null|string
     */
    public function getDestAllImages()
    {
        $where_condition = array('object_type' => "=" . "'dest'");
        $total           = $this->getTotalBy($where_condition, self::TABLE_NAME);
        return $total;
    }

    public function getImageList($page = null)
    {
        if (!$page) {
            $sql = "select * from " . self::TABLE_NAME;
            return $this->query($sql, 'All');
        } else {
            return $this->getList(array(), self::TABLE_NAME, $page);
        }
    }

    public function getCoverByObject($object_id, $object_type = 'dest')
    {
        if (!$object_id) {
            return false;
        }

        $sql    = "SELECT img_url FROM " . self::TABLE_NAME . " WHERE  object_type='" . $object_type . "' AND  object_id=" . $object_id . " AND cover='Y' ";
        $result = $this->query($sql);
        return $result['img_url'];
    }

    /**
     * 目的地改版补全封面图
     * @param $object_id
     * @param string $object_type
     * @return bool
     */
    public function getCoverByDestId($object_id, $object_type = 'dest')
    {
        if (!$object_id) {
            return false;
        }

        $sql    = "SELECT img_url FROM " . self::TABLE_NAME . " WHERE object_type='" . $object_type . "' AND  object_id=" . $object_id . " ORDER BY cover ASC, seq ASC LIMIT 1";
        $result = $this->query($sql);
        return $result['img_url'];
    }

    //获取目的地的图片
    public function getListById($object_id, $object_type = 'dest', $pages = array())
    {
        if (!isset($pages['page']) || $pages['page'] < 1) {
            $pages['page'] = 1;
        }

        if (!isset($pages['pageSize']) || $pages['pageSize'] < 1 || $pages['pageSize'] > 50) {
            $pages['pageSize'] = 15;
        }

        $total         = $this->getImageNumById($object_id, $object_type);
        $totalPage     = ceil($total / $pages['pageSize']);
        $pages['page'] = $pages['page'] > $totalPage ? $totalPage : $pages['page'];
        $start         = ($pages['page'] - 1) * $pages['pageSize'];
        $sql           = "SELECT title,img_url FROM " . self::TABLE_NAME . " WHERE object_type = '{$object_type}' AND object_id = {$object_id} ORDER BY cover ASC LIMIT {$start},{$pages['pageSize']}";
        $list          = $this->query($sql, 'All');
        foreach ($list as $k => $v) {
            $list[$k]['trip_title']    = $v['title'];
            $list[$k]['segment_id']    = '';
            $list[$k]['original_time'] = '';
            $list[$k]['camera']        = '';
            $list[$k]['trip_id']       = 0;
            $list[$k]['memo']          = '';
            $list[$k]['longitude']     = '';
            $list[$k]['latitude']      = '';
            $list[$k]['praiseCount']   = '';
            $list[$k]['commentCount']  = '';
            $list[$k]['is_praise']     = '';
            $list[$k]['is_comment']    = '';
            $list[$k]['shareCount']    = '';
            $list[$k]['latitude']      = '';
        }
        $pages['pageCount'] = $totalPage;
        $pages['itemCount'] = (int) $total;
        return array('list' => $list, 'pages' => $pages);
    }
    public function getImageNumById($object_id, $object_type = 'dest')
    {
        if (!$object_id || !is_numeric($object_id)) {
            return 0;
        }

        $sql    = 'SELECT COUNT(1) AS num FROM ' . self::TABLE_NAME . ' WHERE object_type=\'' . $object_type . '\' AND object_id=' . $object_id;
        $result = $this->query($sql);
        return isset($result['num']) ? intval($result['num']) : 0;
    }

    public function getImgById($object_id, $num = 5, $object_type = 'dest')
    {
        if (!$object_id) {
            return array();
        }

        $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE object_type='" . $object_type . "' AND object_id=" . $object_id . " ORDER BY cover ASC LIMIT " . $num;
        return $this->query($sql, 'All');
    }

    public function getItem($fields = '', $where = '')
    {
        return $this->getOne($where, self::TABLE_NAME, $fields);
    }

    /**
     * @param $dest_id
     * @param int $limit
     * @return array
     * 根据目的地ID获取精选图
     */
    public function getDestEliteImgByDestid($dest_id, $limit = 0, $object_type = 'dest')
    {
        $data = $this->query("SELECT * FROM " . self::TABLE_NAME . " WHERE `object_id`={$dest_id} AND object_type='" . $object_type . "' ORDER BY seq ASC LIMIT " . $limit, 'All');
        foreach ($data as $key => $item) {
            $data[$key]['image'] = substr($item['img_url'], 1);
        }
        return $data ? $data : array();
    }

    public function getImgByIds($object_ids, $num = 5, $object_type = 'dest')
    {
        $image_list = array();

        $dest_ids_arr = explode(',', $object_ids);

        if (count($dest_ids_arr) == 1) {
            $image = $this->getImgById($dest_ids_arr[0], $num, $object_type);
            if (!empty($image)) {
                $image_list[$dest_ids_arr[0]] = $image;
            }
        } else {
            foreach ($dest_ids_arr as $item) {
                $image = $this->getImgById($item, $num, $object_type);
                if (!empty($image)) {
                    $image_list[$item] = $image;
                }
            }
        }

        return $image_list;
    }

    public function searchImg($condition = array(), $limit = array(), $fields = array())
    {
        $fields = "*";
        //防止设置pageSize太大拖垮库
        $limit['page_size'] = !empty($limit['page_size']) ? ($limit['page_size'] > 30 ? 30 : $limit['page_size']) : 15;
        $limit['page_num']  = !empty($limit['page_num']) ? $limit['page_num'] : 1;
        $where              = ' WHERE 1 = 1';
        if (!empty($condition['image_id'])) {
            $where .= ' AND image_id = ' . $condition['image_id'];
        }

        if (!empty($condition['img_url'])) {
            $where .= ' AND img_url LIKE \'%' . $condition['img_url'] . '%\'';
        }

        if (!empty($condition['title'])) {
            $where .= ' AND title LIKE \'%' . $condition['title'] . '%\'';
        }

        if (!empty($condition['object_id'])) {
            $where .= ' AND object_id = ' . $condition['object_id'];
        }

        if (!empty($condition['object_type'])) {
            $where .= ' AND object_type = \'' . $condition['object_type'] . "'";
        }

        $order = !empty($condition['order']) ? $condition['order'] . ',seq asc' : 'seq asc,image_id desc ';

        //获取符合条件的总条数
        $tmp   = $this->query('SELECT COUNT(image_id) AS c FROM ly_elite_image' . $where);
        $count = intval($tmp['c']);
        //总页码
        $totalPage = !empty($count) ? ceil($count / $limit['page_size']) : 1;

        $limit['page_num'] = $limit['page_num'] > $totalPage ? $totalPage : $limit['page_num'];
        $list              = $this->query('SELECT * FROM ly_elite_image' . $where . ' order by ' . $order . ' LIMIT ' . (($limit['page_num'] - 1) * $limit['page_size']) . ',' . $limit['page_size'], 'All');

        return array('list' => $list, 'count' => $count, 'page_num' => $limit['page_num'], 'page_size' => $limit['page_size'], 'maxPage' => $totalPage);
    }

    public function count($condition = array())
    {
        $where = ' WHERE 1 = 1';
        if (!empty($condition['object_id'])) {
            $where .= ' AND object_id = ' . $condition['object_id'];
        }
        if (!empty($condition['object_type'])) {
            $where .= ' AND object_type =  \'' . $condition['object_type'] . "'";
        }
        if (!empty($condition['cover'])) {
            $where .= ' AND cover =  \'' . $condition['cover'] . "'";
        }
        $tmp = $this->query('SELECT COUNT(1) AS c FROM ly_elite_image' . $where);
        return $tmp ? intval($tmp['c']) : 0;
    }

}
