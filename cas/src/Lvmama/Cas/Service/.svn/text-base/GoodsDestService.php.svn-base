<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;

class GoodsDestService extends DataServiceBase
{
    const TABLE_NAME  = 'ly_goods_dest'; //对应数据库表
    const PRIMARY_KEY = 'id';

    /**
     * 获取商品的目的地
     * @author lixiumeng
     * @datetime 2017-12-07T14:49:58+0800
     * @return   [type]                   [description]
     */
    public function getGoodsDest($id, $dest_type)
    {
        $sql = "select id,dest_id,goods_id,goods_name,dest_name,goods_seq,dest_seq,dest_type,dest_parent_name from ly_goods_dest where goods_id = {$id}";
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
        $sql  = "select goods_name from ly_goods where goods_id  = {$data['goods_id']}";
        $food = $this->query($sql);

        $data['dest_name']         = !empty($dest['dest_name']) ? $dest['dest_name'] : '';
        $data['goods_name']        = !empty($food['goods_name']) ? $food['goods_name'] : '';
        $data['goods_seq']         = 0;
        $data['goods_status']      = 99;
        $data['dest_parent_id']    = !empty($dest['parent_id']) ? $dest['parent_id'] : '';
        $data['dest_parents']      = !empty($dest['parents']) ? $dest['parents'] : '';
        $data['dest_parent_names'] = !empty($dest['parent_names']) ? $dest['parent_names'] : '';
        $data['dest_parent_name']  = !empty($dest['parent_name']) ? $dest['parent_name'] : '';
        $data['dest_children']     = !empty($dest['children']) ? $dest['children'] : '';
        $data['dest_type']         = !empty($dest['dest_type']) ? $dest['dest_type'] : '';
        $data['parent']            = $dest['type'] == 'SHOP' ? $data['dest_parent_id'] : 0;
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
