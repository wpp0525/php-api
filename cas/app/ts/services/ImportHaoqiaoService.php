<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Component\Pinyintransform;

class ImportHaoqiaoService implements DaemonServiceInterface
{

    public $n = 0;

    /**
     * [__construct description]
     * @author lixiumeng
     * @datetime 2017-11-15T14:07:10+0800
     * @param    [type]                   $di [description]
     */
    public function __construct($di)
    {
        $this->di     = $di;
        $this->db     = $this->di->get('cas')->getDbServer('dbvst');
        $this->redis  = $this->di->get('cas')->getRedis();
        $this->pinyin = new Pinyintransform();
    }

    public function shutdown($timestamp = null, $flag = null)
    {

    }

    public function process($timestamp = null, $flag = null)
    {
        $this->matchZh(); // 匹配中文
        $this->matchEn(); // 匹配英文
        $this->import(); // 导入剩余数据
    }

    /**
     * [getLine description]
     * @author lixiumeng
     * @datetime 2017-11-15T14:07:20+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function getFileInfo($params)
    {
        $filename = empty($params[0]) ? "/tmp/haoqiao.csv" : $params[0];
        $csvInfo  = file_get_contents($filename);

        $res   = explode("\n", $csvInfo);
        $lines = [];

        foreach ($res as $value) {
            $lines[] = explode(',', $value);
        }
        return $lines;
    }

    /**
     * 匹配中文
     * @author lixiumeng
     * @datetime 2017-11-15T17:10:32+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function matchZh($params)
    {
        $res        = $this->getFileInfo($params);
        $this->n    = 0;
        $table      = 'biz_district';
        $conditions = ['table' => $table, 'where' => "cancel_flag = 'Y' and foreign_flag = 'Y'"];
        $dbInfos    = $this->searchDistrict($conditions, true); //所有的国外的地区
        $foreigns   = [];

        // 遍历国外的信息
        foreach ($dbInfos as $r) {
            // 4000多国外信息
            $r['district_name'] = trim($r['district_name']);
            // 查询所属国家
            $key = "district:tree:" . $r['district_id'];

            $info = $this->redis->hgetall($key);

            if (!empty($info['country'])) {
                $unique_key = $info['country'] . ":" . $r['district_name'];

                $foreigns[$unique_key] = $r;
            } else {
                echo $r['district_name'] . "没有找到所属国家\n";
            }
        }
        // 遍历好巧的数据
        foreach ($res as $v) {
            if (empty($v[4])) {
                continue;
            }
            $unique_key = trim($v[1]) . ":" . trim($v[4]);

            if (!empty($foreigns[$unique_key])) {
                // 数据库中有该地区
                $upInfo = ['en_name' => $v[5], 'update_time' => time()];
                $where  = 'district_id = ' . $foreigns[$unique_key]['district_id'];
                $this->updateDistrict($table, $upInfo, $where);
                unset($foreigns[$unique_key]);
            } else {
                $data = implode(',', $v);
                $this->export('/tmp/no_match_zh_haoqiao.csv', $data);
            }

            $this->n += 1;
            echo $this->n . " done\n";
        }

        foreach ($foreigns as $n) {
            $data = implode(',', $n);
            $this->export('/tmp/no_match_zh_db.csv', $data);
        }

        echo "well done\n";
    }

    /**
     * 匹配英文
     * @author lixiumeng
     * @datetime 2017-11-15T17:10:44+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function matchEn($params)
    {
        $res     = $this->getFileInfo($params);
        $this->n = 0;
        $table   = 'biz_district';
        // 好巧的数据
        foreach ($res as $v) {
            if (empty($v[5])) {
                continue;
            }
            $conditions = [
                'table' => $table,
                'where' => 'en_name = "' . trim($v[5]) . '"',
            ];
            $rt     = $this->searchDistrict($conditions, true);
            $upInfo = [];
            $match  = false; // 是否匹配到

            if (!empty($rt)) {
                foreach ($rt as $value) {
                    $parents_info = $this->redis->hgetall("district:tree:" . $value['district_id']);
                    $country_name = !empty($parents_info['country']) ? $parents_info['country'] : "";
                    if ($value['en_name'] == $v[5] && $country_name == $v[1]) {
                        // 匹配到了
                        $upInfo = ['district_name' => trim($v[4]), 'update_time' => time()];
                        $where  = 'district_id = ' . $value['district_id'];
                        $this->updateDistrict($table, $upInfo, $where);
                        $match = true;
                    } else {
                        echo "数据库中的[" . $value['district_name'] . "]的国家和地区与好巧不完全匹配\n";
                        $this->export("/tmp/error_haoqiao.log", json_encode($v, true));
                    }
                }
            }

            if (!$match) {
                // 没匹配到
                $msg     = implode(",", $v);
                $outFile = "/tmp/import_district.csv";
                $this->export($outFile, $msg);
            }
            $this->n += 1;
            echo $this->n . " done\n";
        }
        echo "well done\n";
    }

    /**
     * 导入
     * @author lixiumeng
     * @datetime 2017-11-15T17:10:56+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function import($params)
    {
        $file    = "/tmp/import_district.csv";
        $rs      = $this->getFileInfo($params);
        $this->n = 0;
        foreach ($rs as $v) {
            $metas = $this->getMetaInfo($v);
            if (empty($metas)) {
                continue;
            }

            $r = [
                'parent_id'        => $metas['parent_id'],
                'district_type'    => $metas['type'],
                'district_code'    => '',
                'district_name2'   => '',
                'district_name'    => $metas['district_name'],
                'pinyin'           => $metas['pinyin'],
                'cancel_flag'      => 'Y',
                'short_pinyin'     => $metas['short_pinyin'],
                'url_pin_yin'      => $metas['pinyin'],
                'province_name'    => '',
                'province_pin_yin' => '',
                'city_name'        => '',
                'city_pin_yin'     => '',
                'hotel_num'        => 0,
                'district_alias'   => '',
                'en_name'          => $metas['en_name'],
                'local_lang'       => 'hq',
                'foreign_flag'     => 'Y',
                'update_time'      => time(),
            ];

            $this->db->insert('biz_district', array_values($r), array_keys($r));
            $this->n += 1;
            echo $this->n . " done\n";
        }

        echo "well,done";
    }

    /**
     * 获取地区部分信息
     * @author lixiumeng
     * @datetime 2017-11-16T11:09:28+0800
     * @param    [type]                   $a [description]
     * @return   [type]                      [description]
     */
    public function getMetaInfo($a)
    {
        $metas = [];
        // 获取parentId
        $sql         = "select district_id from biz_district where district_name = '{$a[1]}'";
        $countryInfo = $this->db->fetchOne($sql, \PDO::FETCH_ASSOC);
        if (!empty($countryInfo)) {
            $parentId = $countryInfo['district_id'];
        } else {
            return false;
        }
        // 获取拼音
        $pinyin = $this->pinyin->pinyin(explode("/", $a[4])[0]);
        // 获取短拼音
        //
        $short_pinyin = $this->pinyin->pinyin(explode("/", $a[4])[0], true);
        // foreach (str_split($a[4], 3) as $v) {
        //     $short_arr[] = str_split($this->pinyin->pinyin($v))[0];
        // }

        // $short_pinyin = implode('', $short_arr);

        $metas = [
            'parent_id'     => $parentId,
            'type'          => 'CITY',
            'district_name' => $a[4],
            'pinyin'        => $pinyin,
            'short_pinyin'  => $short_pinyin,
            'en_name'       => $a[5],
        ];
        return $metas;
    }

    /**
     * 查找行政区数据
     * @author lixiumeng
     * @datetime 2017-11-15T15:07:35+0800
     * @param    [type]                   $conditions [description]
     * @return   [type]                               [description]
     */
    public function searchDistrict($conditions, $all = false)
    {
        $sql = "select district_id,district_name,en_name from {$conditions['table']} where {$conditions['where']}";
        if ($all) {
            return $this->db->fetchAll($sql, \PDO::FETCH_ASSOC);
        }
        return $this->db->fetchOne($sql, \PDO::FETCH_ASSOC);
    }

    /**
     * 更新行政区数据
     * @author lixiumeng
     * @datetime 2017-11-15T15:07:43+0800
     * @return   [type]                   [description]
     */
    public function updateDistrict($table, $upInfo, $where)
    {
        $this->db->update($table, array_keys($upInfo), array_values($upInfo), $where);
    }

    /**
     * 导出文件
     * @author lixiumeng
     * @datetime 2017-11-15T15:07:53+0800
     * @param    [type]                   $filename [description]
     * @param    [type]                   $data     [description]
     * @return   [type]                             [description]
     */
    public function export($filename, $data)
    {
        file_put_contents($filename, $data . "\n", FILE_APPEND);
    }
}
