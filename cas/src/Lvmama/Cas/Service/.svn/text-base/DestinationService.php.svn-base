<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Component\Pinyintransform;

class DestinationService extends DataServiceBase
{
    const TABLE_NAME = 'ly_destination'; //对应数据库表
    const PRIMARY_KEY = 'dest_id';

    public function edit($id = 0, $data = [])
    {
        $where = 'dest_id  = ' . intval($id);

        $data['modify_time'] = time();

        if (!empty($data['dest_name'])) {
            $this->py = new Pinyintransform();
            $data['short_pinyin'] = empty($data['short_pinyin']) ? $this->py->pinyin($data['dest_name'], true) : $data['short_pinyin'];
            $data['pinyin'] = empty($data['pinyin']) ? $this->py->pinyin($data['dest_name']) : $data['pinyin'];
        }

        $rs = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $where);

        if ($rs) {
            $status = true;
            $msg = '更新成功';
        } else {
            $status = false;
            $msg = '更新失败';
        }

        return [
            'status' => $status,
            'msg' => $msg,
        ];
    }
}
