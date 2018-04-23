<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Component\Pinyintransform;
use Lvmama\Cas\Service\DataServiceBase;

class DestinationFoodService extends DataServiceBase
{
    const TABLE_NAME  = 'ly_food'; //对应数据库表
    const PRIMARY_KEY = 'food_id';

    /**
     * 添加美食
     * @author lixiumeng
     * @datetime 2017-11-29T16:39:09+0800
     * @param    array                    $data [description]
     */
    public function add(array $data = [])
    {
        $this->py = new Pinyintransform();
        if (empty($data) || empty($data['food_name'])) {
            return [
                'status' => false,
                'msg'    => '美食名称不能为空',
            ];
        }

        $data['create_time']  = $data['modify_time']  = time();
        $data['pinyin']       = $this->py->pinyin($data['food_name']);
        $data['short_pinyin'] = $this->py->pinyin($data['food_name'], true);

        $id = $this->getAdapter()->insertAsDict(self::TABLE_NAME, $data);

        if ($id) {
            $status = true;
            $msg    = '添加成功';
        } else {
            $status = false;
            $msg    = '添加失败';
        }
        return ['status' => $status, 'msg' => $msg, 'data' => $id];
    }

    /**
     * [edit description]
     * @author lixiumeng
     * @datetime 2017-11-30T11:16:42+0800
     * @param    integer                  $id   [description]
     * @param    array                    $data [description]
     * @return   [type]                         [description]
     */
    public function edit($id = 0, $data = [])
    {
        $where = 'food_id  = ' . intval($id);

        $data['modify_time'] = time();

        if (!empty($data['food_name'])) {
            $this->py             = new Pinyintransform();
            $data['short_pinyin'] = empty($data['short_pinyin']) ? $this->py->pinyin($data['food_name'], true) : $data['short_pinyin'];
            $data['pinyin']       = empty($data['pinyin']) ? $this->py->pinyin($data['food_name']) : $data['pinyin'];
        }

        $rs = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);

        if ($rs) {
            $status = true;
            $msg    = '更新美食成功';
        } else {
            $status = false;
            $msg    = '更新美食失败';
        }

        return [
            'status' => $status,
            'msg'    => $msg,
        ];
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
        $where = 'food_id = ' . intval($id);
        $rs    = $this->getAdapter()->delete(self::TABLE_NAME, $where);
        if ($rs) {
            $status = true;
            $msg    = '删除成功';
        } else {
            $status = false;
            $msg    = '删除失败';
        }
        return [
            'status' => $status,
            'msg'    => $msg,
        ];
    }

    /**
     * 获取列表数据
     * @author lixiumeng
     * @datetime 2017-12-01T10:56:29+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function getAll($params)
    {
        $fileds = !empty($params['fields']) ? $params['fields'] : " * ";
        $order  = !empty($params['order']) ? $params['order'] : 'food_id DESC';
        $where  = !empty($params['where']) ? $params['where'] : "";
        // 如果有目的的, 则根据目的地进行筛选
        if (!empty($params['food_dest'])) {
            $db_vst = $this->di->get('cas')->getDbServer('dbvst');
            $sql    = "select distinct `food_id` from `ly_food_dest` where `dest_name` like '% . {$params['food_dest']}%'";
            $foods  = $db_vst->findAll($sql, Phalcon\Db::FETCH_ASSOC);
            if (!empty($foods)) {
                $foods_str = implode(',', array_column($foods, 'food_id'));
                $params['where'] .= " AND food_id in (" . $foods_str . ")";
            }
        }
        $limit = [
            'page_num'  => !empty($params['pagenum']) ? $params['pagenum'] : 0,
            'page_size' => !empty($params['pagesize']) ? $params['pagesize'] : 10,
        ];

        $list       = $this->getList($where, self::TABLE_NAME, $limit, $fileds, $order);
        $count_sql  = "select count(food_id) as count from ly_food where " . $where;
        $count_info = $this->getAdapter()->query($count_sql);
        $count      = !empty($count_info['count']) ? $count_info['count'] : 0;
        $totalPage  = ceil($count / $limit['page_size']);
        // 匹配目的地信息

        return array('list' => $list, 'count' => $count, 'page_num' => $limit['page_num'], 'page_size' => $limit['page_size'], 'maxPage' => $totalPage);
    }

    /**
     * 获取单条数据
     * @author lixiumeng
     * @datetime 2017-12-01T11:07:34+0800
     * @param    string                   $fields [description]
     * @param    string                   $where  [description]
     * @return   [type]                           [description]
     */
    public function getItem($fields = '', $where = '')
    {
        return $this->getOne($where, self::TABLE_NAME, $fields);
    }

    /**
     * 根据搜索条件获取菜品信息
     */
    public function search($condition = array(), $limit = array(), $fields = array())
    {
        if (empty($fields)) {
            $fields = array('food_id', 'food_name', 'food_type_id', 'status', 'img_url');
        }

        //防止设置pageSize太大拖垮库
        $limit['page_size'] = isset($limit['page_size']) && is_numeric($limit['page_size']) ? ($limit['page_size'] > 30 ? 30 : $limit['page_size']) : 15;
        $limit['page_num']  = isset($limit['page_num']) && is_numeric($limit['page_num']) ? $limit['page_num'] : 1;
        $where              = ' WHERE 1 = 1';
        if (!empty($condition['id'])) {
            $where .= ' AND food_id = ' . $condition['id'];
        }
        if (!empty($condition['status'])) {
            $where .= ' AND status = ' . $condition['status'];
        }
        if (!empty($condition['type'])) {
            $where .= ' AND type = \'' . $condition['type'] . '\'';
        }
        if (!empty($condition['name'])) {
            $where .= ' AND food_name LIKE \'%' . $condition['name'] . '%\'';
        }

        // 如果有目的的, 则根据目的地进行筛选
        if (!empty($condition['dest'])) {
            $sql   = "select distinct `food_id` from `ly_food_dest` where `parent` = 0 and `dest_name` like '%{$condition['dest']}%'";
            $foods = $this->query($sql, 'All');
            if (!empty($foods)) {
                $foods_str = implode(',', array_column($foods, 'food_id'));
                $where .= " AND food_id in (" . $foods_str . ")";
            } else {
                $where .= " AND food_id = 0";
            }
        }

        // 排序
        $order = !empty($condition['order']) ? $condition['order'] : ' food_id desc';

        //获取符合条件的总条数
        $tmp   = $this->query('SELECT COUNT(food_id) AS c FROM ly_food' . $where);
        $count = intval($tmp['c']);
        //总页码
        $totalPage         = ceil($count / $limit['page_size']);
        $limit['page_num'] = $limit['page_num'] > $totalPage ? $totalPage : $limit['page_num'];

        $rt_sql = 'SELECT `' . implode('`,`', $fields) . '` FROM ly_food' . $where . ' order by ' . $order . ' LIMIT ' . (($limit['page_num'] - 1) * $limit['page_size']) . ',' . $limit['page_size'];
        $list   = $this->query($rt_sql, 'All');

        // 补全目的信息
        $food_id_str = implode(',', array_column($list, 'food_id'));
        $dest_sql    = "select food_id,dest_name from ly_food_dest where parent = 0 and food_id in ({$food_id_str})";
        $rs          = $this->query($dest_sql, 'All');
        if (!empty($rs)) {
            $dest_arr = [];
            foreach ($rs as $k => $v) {
                $dest_arr[$v['food_id']][] = $v['dest_name'];
            }
        }

        // 补全菜系信息
        $caixi_sql = "select subject_id,object_id,subject_name from mo_subject_relation where channel = 'lvyou' and status = 99 and object_type = 'food' and object_id in ({$food_id_str})";
        $rt        = $this->query($caixi_sql, 'All');
        if (!empty($rt)) {
            $caixi_arr = [];
            foreach ($rt as $x => $y) {
                $caixi_arr[$y['object_id']][] = $y['subject_name'];
            }
        }

        foreach ($list as $m => $n) {
            if (!empty($dest_arr[$n['food_id']])) {
                $dests = implode(',', array_unique($dest_arr[$n['food_id']]));
            } else {
                $dests = '';
            }
            if (!empty($caixi_arr[$n['food_id']])) {
                $tags = implode(',', array_unique($caixi_arr[$n['food_id']]));
            } else {
                $tags = '';
            }
            $list[$m]['dests'] = $dests;
            $list[$m]['tags']  = $tags;
        }

        return array('list' => $list, 'count' => $count, 'page_num' => $limit['page_num'], 'page_size' => $limit['page_size'], 'maxPage' => $totalPage);
    }

    /**
     *获取菜品的所有菜系
     * @author lixiumeng
     * @datetime 2017-12-07T13:58:17+0800
     * @return   [type]                   [description]
     */
    public function getSubjectTypes($type)
    {
        $sql = "select * from mo_subject where cancel_flag = 'Y' and type ='{$type}'";
        $rs  = $this->query($sql, 'All');

        if (!empty($rs)) {
            $status = true;
            $msg    = 'success';
            $data   = $rs;

        } else {
            $status = false;
            $msg    = '未找到相关数据';
            $data   = [];
        }
        return [
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        ];
    }

    /**
     * 获取一个菜品的菜系
     * @author lixiumeng
     * @datetime 2017-12-11T15:12:52+0800
     * @return   [type]                   [description]
     */
    public function getSubjectRealationTypes($id, $object_type)
    {
        $sql = "select * from mo_subject_relation where channel = 'lvyou' and status = 99 and object_type = '{$object_type}'and object_id = {$id}";

        $rs = $this->query($sql, 'All');

        if (!empty($rs)) {
            $status = true;
            $msg    = 'success';
            $data   = $rs;
        } else {
            $status = false;
            $msg    = '';
            $data   = '';
        }
        return [
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        ];
    }

    /**
     * 设置菜品菜系(如果已有就不变没有增加)
     * @author lixiumeng
     * @datetime 2017-12-08T14:45:21+0800
     */
    public function addSubjectType($id, $subject_id, $object_type)
    {
        $sql = "select id from mo_subject_relation where channel = 'lvyou' and status = 99 and object_type = '{$object_type}'and object_id = {$id} and subject_id = {$subject_id}";
        $rt  = $this->query($sql);
        if (!empty($rt)) {
            return [
                'status' => false,
                'msg'    => '该记录已存在',
                'data'   => '',
            ];
        }
        // 查类型
        $sql     = "select subject_name,subject_pinyin from mo_subject where subject_id = {$subject_id}";
        $subject = $this->query($sql);

        if (!empty($subject)) {
            $data = [
                'subject_id'     => $subject_id,
                'subject_name'   => $subject['subject_name'],
                'subject_pinyin' => $subject['subject_pinyin'],
                'channel'        => 'lvyou',
                'object_type'    => $object_type,
                'object_id'      => $id,
                'main'           => 'N',
                'status'         => 99,
            ];
            $id = $this->getAdapter()->insertAsDict('mo_subject_relation', $data);

            if ($id) {
                $status = true;
                $msg    = '添加成功';
                $data   = $id;
            } else {
                $status = false;
                $msg    = '添加失败';
                $data   = '';
            }
        } else {
            $status = false;
            $msg    = '添加失败,没有找到项目';
            $data   = '';
        }
        return [
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        ];
    }

    /**
     * 清除某美食或者某商品的所有标签
     * @author lixiumeng
     * @datetime 2018-01-16T15:38:11+0800
     * @return   [type]                   [description]
     */
    public function clearSubjectType($object_id,$type)
    {
        $srv = $this->di->get('cas')->get('mo_subject_relation_service');
        $conditions = "object_id = {$object_id} and object_type = '{$type}'";
        return $srv->deleteBy($conditions);
    }

}
