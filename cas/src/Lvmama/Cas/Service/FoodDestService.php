<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class FoodDestService extends DataServiceBase
{
    const TABLE_NAME  = 'ly_food_dest'; //对应数据库表
    const PRIMARY_KEY = 'id';

    /**
     * 获取美食的目的地
     * @author lixiumeng
     * @datetime 2017-12-07T14:49:58+0800
     * @return   [type]                   [description]
     */
    public function getFoodDest($food_id, $dest_type)
    {
        $sql = "select id,dest_id,food_id,food_name,dest_name,food_seq,dest_seq,dest_type,dest_parent_name from ly_food_dest where food_id = {$food_id}";
        $rs  = $this->query($sql, 'All');
        if (!empty($rs)) {
            $status = true;
            $data   = $rs;
            $msg    = 'success';
        } else {
            $status = false;
            $data   = $rs;
            $msg    = 'error';
        }
        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }
    /**
     * 添加美食
     * @author lixiumeng
     * @datetime 2017-11-29T16:39:09+0800
     * @param    array                    $data [description]
     */
    public function add(array $data = [])
    {
        $sql  = "select * from ly_destination where dest_id = {$data['dest_id']}";
        $dest = $this->query($sql);
        $sql  = "select food_name from ly_food where food_id  = {$data['food_id']}";
        $food = $this->query($sql);

        $data['dest_name']         = !empty($dest['dest_name']) ? $dest['dest_name'] : '';
        $data['food_name']         = !empty($food['food_name']) ? $food['food_name'] : '';
        $data['food_seq']          = 0;
        $data['food_status']       = 99;
        $data['dest_parent_id']    = !empty($dest['parent_id']) ? $dest['parent_id'] : '';
        $data['dest_parents']      = !empty($dest['parents']) ? $dest['parents'] : '';
        $data['dest_parent_names'] = !empty($dest['parent_names']) ? $dest['parent_names'] : '';
        $data['dest_parent_name']  = !empty($dest['parent_name']) ? $dest['parent_name'] : '';
        $data['dest_children']     = !empty($dest['children']) ? $dest['children'] : '';
        $data['dest_type']         = !empty($dest['dest_type']) ? $dest['dest_type'] : '';
        $data['parent']            = $dest['type'] == 'RESTAURANT' ? $data['dest_parent_id'] : 0;
        $data['dest_seq']          = 0;

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
     * 删除
     * @author lixiumeng
     * @datetime 2017-11-30T11:28:22+0800
     * @param    integer                  $id [description]
     * @return   [type]                       [description]
     */
    public function del($id = 0)
    {
        $where = 'id = ' . intval($id);
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

}
