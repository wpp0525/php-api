<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\UCommon as UCommon;

/**
 * 目的地类
 *
 * @author win.shenxiang
 *
 */
class DestinationDataService extends DataServiceBase
{

    const TABLE_NAME = 'ly_destination'; //对应数据库表

    const BEANSTALK_TUBE = '';

    const BEANSTALK_TRIP_MSG = '';

    const PV_REAL = 2;

    const LIKE_INIT = 3;

    const EXPIRE_TIME = 86400;

    /**
     * 获取
     *
     */
    public function get($id)
    {
        $sql    = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE dest_id = ' . $id;
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $result->fetch();
    }
    public function getRsBySql($sql, $one = false)
    {
        $result = $this->getAdapter()->query($sql);
        $result->setFetchMode(\PDO::FETCH_ASSOC);
        return $one ? $result->fetch() : $result->fetchAll();
    }
    public function getDestById($dest_id = 0)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            return array();
        }

        $key    = str_replace('{id}', $dest_id, RedisDataService::REDIS_DEST_INFO);
        $result = $this->redis->hGetAll($key);
        if (!$result) {
            $sql = "SELECT `dest_id`,`parent_id`,`district_id`,`district_name`,`dest_type`,`dest_name`,`en_name`,`pinyin`,`short_pinyin`,`letter`,`dest_alias`,`local_lang`,`parents`,`parent_name`,`parent_names`,`district_parent_id`,`district_parent_ids`,`district_parent_name`,`district_parent_names`,`stage`,`range`,`intro`,`star`,`abroad`,`url`,`heritage`,`ent_sight`,`img_url`,`count_want`,`count_been`,`coord_type`,`longitude`,`latitude`,`g_longitude`,`g_latitude`,`cancel_flag`,`protected_area` FROM " . self::TABLE_NAME . " WHERE showed = 'Y' AND cancel_flag = 'Y' AND dest_id = {$dest_id}";
            $sth = $this->getAdapter()->query($sql);
            $sth->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $sth->fetch();
            if ($result) {
                $this->redis->hmset($key, $result);
                $this->redis->expire($key, self::EXPIRE_TIME);
            } else {
                $result = array();
            }
        }
        return $result;
    }

    public function getDestListByIds($dest_ids = '')
    {
        $result = array();
        if (empty($dest_ids)) {
            return $result;
        }
        $key    = str_replace('{ids}', $dest_ids, RedisDataService::REDIS_DEST_LIST_INFO);
        $result = $this->redis->hGetAll($key);
        if (empty($result)) {
            $sql = "SELECT `dest_id`,`parent_id`,`district_id`,`district_name`,`dest_type`,`dest_name`,`en_name`,`pinyin`,`short_pinyin`,`letter`,`dest_alias`,`local_lang`,`parents`,`parent_name`,`stage`,`range`,`intro`,`star`,`abroad`,`url`,`heritage`,`ent_sight`,`img_url`,`count_want`,`count_been`,`longitude`,`latitude`,`g_longitude`,`g_latitude`,`cancel_flag`,`protected_area` FROM " . self::TABLE_NAME . " WHERE showed = 'Y' AND cancel_flag = 'Y' AND dest_id in ({$dest_ids})";
            $sth = $this->getAdapter()->query($sql);
            $sth->setFetchMode(\PDO::FETCH_ASSOC);
            $result = $sth->fetchAll();
            if (!empty($result)) {
                foreach ($result as &$item) {
                    $item['name'] = $item['dest_name'];
                    $item['type'] = $item['dest_type'];
                    unset($item['dest_name'], $item['dest_type']);
                }

                $this->redis->hmset($key, json_encode($result));
                $this->redis->expire($key, self::EXPIRE_TIME);
            }
        } else {
            $result = json_decode($result, true);
        }

        return $result;
    }

    public function getListByIds(array $dest_ids, array $condition = null, $limit = null, $columns = null, $order = null)
    {
        if (!$dest_ids) {
            return false;
        }
        $where['dest_id in']    = "(" . implode(',', $dest_ids) . ")";
        $where['cancel_flag ='] = "'Y'";
        $where['showed =']      = "'Y'";
        if ($condition) {
            $where = array_merge($where, $condition);
        }

        return $this->getList($where, self::TABLE_NAME, $limit, $columns, $order);
    }

    public function getListByViewSpots($viewspot_id = array())
    {
        if (!$viewspot_id) {
            return array();
        }

        $viewspots = implode(',', $viewspot_id);
        $sql       = "SELECT * FROM " . self::TABLE_NAME . " WHERE `dest_id` IN({$viewspots}) AND cancel_flag = 'Y' AND showed = 'Y' ORDER BY count_want DESC,count_been DESC";
        return $this->getRsBySql($sql);
    }
    /**
     * @param $rec_dest
     * @param $data
     * @param $dest_type
     * @param $pages
     * @param int $limit
     * @param string $search_name
     * @param int $stage
     * @return array
     * @purpose 根据目的地集合，进行推荐数据拼装以及去重操作
     */
    public function packagingDests(
        $rec_dest,
        $data,
        $dest_type,
        $pages,
        $limit = 0,
        $search_name = '',
        $stage = 0,
        $arrCondition = ''
    ) {
        //若没有推荐数据，直接返回查询出来的结果集
        if (!$rec_dest) {
            return $this->getDestsByPid($data, $dest_type, $pages, '', $limit, $search_name, $stage, $arrCondition);
        } else {
            $tmp_ids = array();
            foreach ($rec_dest as $v) {
                $tmp_ids[] = $v['dest_id'];
            }
            $rec_ids   = implode(',', $tmp_ids);
            $rec_count = count($rec_dest);
            if ($pages) {
                if ($pages['page'] == 1) {
                    if ($rec_count == $pages['pageSize']) {
                        $total           = $this->getDestNumParentid($data, $dest_type, $search_name); //该POI类型数量
                        $result['list']  = $rec_dest;
                        $page            = isset($pages['page']) ? $pages['page'] : 1;
                        $pageSize        = isset($pages['pageSize']) ? $pages['pageSize'] : 10;
                        $result['pages'] = array(
                            'itemCount' => $total ? intval($total) : 0,
                            'pageCount' => ceil($total / $pageSize),
                            'page'      => $page,
                            'pageSize'  => $pageSize,
                        );
                        return $result;
                    } else {
                        $country         = $this->getDestsByPid($data, $dest_type, $pages, $rec_ids, ($pages['pageSize'] - $rec_count), $search_name, $stage, $arrCondition);
                        $country['list'] = array_merge($rec_dest, $country['list']);
                        return $country;
                    }
                } else {
                    if ($rec_count == $pages['pageSize']) {
                        $result = $this->getDestsByPid($data, $dest_type, array('page' => ($pages['page'] - 1), 'pageSize' => $pages['pageSize']), $rec_ids, '', $search_name, $stage, $arrCondition);
                        return $result['list'];
                    } else {
                        $limit = (($pages['page'] - 2) * $pages['pageSize'] + ($pages['pageSize'] - $rec_count)) . ', ' . $pages['pageSize'];
                        return $this->getDestsByPid($data, $dest_type, $pages, $rec_ids, $limit, $search_name, $stage, $arrCondition);
                    }
                }
            } else {
                return $rec_count == $limit ? $rec_dest : array_merge($rec_dest, $this->getDestsByPid($data, $dest_type, $pages, ($limit - $rec_count), $search_name, $stage, $arrCondition));
            }
        }
    }
    /**
     * 获取目的地下某个类型目的地的数量
     * params $dest_id 要查询的某个目的地ID
     * params $dest_type 目的地类型
     */
    public function getDestNumParentid($data, $dest_type, $search_key = '', $condition = '')
    {
        if (!is_array($data)) {
            $data = $this->getDestByid($data);
        }
        if (!isset($data['dest_id']) || !isset($data['parents'])) {
            return 0;
        }

        $dests_types = '';
        //判断要查询的目的地类型
        if ($dest_type) {
            if (is_array($dest_type)) {
                $typestr     = "'" . implode("','", $dest_type) . "'";
                $dests_types = "`dest_type` IN(" . $typestr . ") ";
            } else {
                if ($dest_type == 'VIEWSPOT') {
                    $dests_types = " (`dest_type`='VIEWSPOT' OR (dest_type='SCENIC_ENTERTAINMENT' AND `ent_sight`='Y' )) ";
                } elseif ($dest_type == 'SCENIC_ENTERTAINMENT') {
                    $dests_types = " (`dest_type`='SCENIC_ENTERTAINMENT' OR ( dest_type='VIEWSPOT' AND `ent_sight`='Y' ))";
                } else {
                    $dests_types = "`dest_type` ='" . $dest_type . "' ";
                }
            }
        }
        if ($search_key) {
            $search_key = " AND dest_name LIKE  '%" . $search_key . "%'";
        }
        $rs = $this->getRsBySql("SELECT COUNT(dest_id) AS c FROM " . self::TABLE_NAME . " WHERE {$dests_types} AND `cancel_flag`='Y' AND `showed`='Y' AND `parents` LIKE ('{$data['parents']},%') {$search_key}{$condition}", true);
        return isset($rs['c']) ? $rs['c'] : 0;
    }

    /**
     * 根据目的地ID取得指南信息
     * @param int $dest_id
     * @return array
     */
    public function getSummaryById($dest_id = 0)
    {
        if (!$dest_id) {
            return array();
        }

        $key    = RedisDataService::REDIS_DEST_SUMMARY;
        $result = $this->redis->hGetAll($key . $dest_id);
        if ($result) {
            foreach ($result as $k => $v) {
                $result[$k] = $this->redis->hGetAll($key . $dest_id . ':' . $k);
            }
        } else {
            $result = $this->getRsBySql("SELECT title,text FROM ly_data WHERE `status`='99' AND `dest_id`='{$dest_id}' AND `text`!=''");
            foreach ($result as $k => $val) {
                foreach ($val as $t => $w) {
                    $this->redis->hset($key . $dest_id . ':' . $k, $t, $w);
                }
                $this->redis->hset($key . $dest_id, $k, $val);
            }
        }
        return $result;
    }
    /**
     * 取得指定目的地的推荐POI
     * @param int $dest_id
     * @param string $type
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getDestByType($dest_id = 0, $type = '', $page = 1, $pageSize = 15)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            return array();
        }

        $data = $this->getDestById($dest_id);
        if ($data['range'] != '0') {
            $tmp = $this->getRsBySql("SELECT `viewspot_id` FROM ly_scenic_viewspot WHERE `recommend_type`='{$type}' AND `dest_id`='{$data['dest_id']}' AND `status` = '99'");
            if (!$tmp) {
                return array();
            }

            $ids = array();
            foreach ($tmp as $v) {
                $ids[] = $v['viewspot_id'];
            }
            //统计符合条件的数量
            $tmp       = $result       = $this->getRsBySql("SELECT COUNT(`dest_id`) AS n FROM ly_destination WHERE `cancel_flag`='Y' AND `dest_id` IN(" . implode(',', $ids) . ")", true);
            $totalPage = ceil($tmp['n'] / $pageSize);
            if ($page < 1 || $page > $totalPage) {
                return array();
            }

            $start  = ($page - 1) * $pageSize;
            $result = $this->getRsBySql("SELECT dest_id,dest_name,en_name,dest_type,dest_alias,local_lang,pinyin,short_pinyin,parent_name,district_name,url,img_url FROM ly_destination WHERE `cancel_flag`='Y' AND `dest_id` IN(" . implode(',', $ids) . ") ORDER BY dest_id ASC LIMIT {$start},{$pageSize}");
        } else {
            //统计符合条件的数量
            $tmp       = $result       = $this->getRsBySql("SELECT COUNT(`dest_id`) AS n FROM ly_destination WHERE `cancel_flag`='Y' AND `dest_type`='{$type}' AND `parent_id`='{$data['parent_id']}' AND `stage`='2'", true);
            $totalPage = ceil($tmp['n'] / $pageSize);
            if ($page < 1 || $page > $totalPage) {
                return array();
            }

            $start  = ($page - 1) * $pageSize;
            $result = $this->getRsBySql("SELECT dest_id,dest_name,en_name,dest_type,dest_alias,local_lang,pinyin,short_pinyin,parent_name,district_name,url,img_url FROM ly_destination WHERE `cancel_flag`='Y' AND `dest_type`='{$type}' AND `parent_id`='{$data['parent_id']}' AND `stage`='2' ORDER BY dest_id ASC LIMIT {$start},{$pageSize}");
        }
        return $result;
    }
    /**
     * 根据目的地ID取得交通信息
     * @param dest_id 目的地ID
     * @param type 交通类型(AL-到达离开,LIC-本地/城际,SERV_CARD-交通卡券,SERV_OTHER-其它,POI目的地类型的交通)
     * @return array
     */
    public function getTransportByDest($dest_id = array(), $type = '')
    {
        if (!$dest_id) {
            return array();
        }

        $where = "dest_id IN(" . implode(',', $dest_id) . ") AND `status`='99'";
        if ($type) {
            $where .= " AND `type`='{$type}'";
        }

        $result = $this->getRsBySql("SELECT trans_id,dest_id,trans_name,memo,`type` FROM ly_transportation WHERE {$where} ORDER BY seq DESC,trans_id ASC");
        return $result;
    }
    /**
     * 根据目的地ID取得地址信息
     * @param int $dest_id
     * @return array
     */
    public function getAddressByDestId($dest_id = 0)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            return '';
        }

        $redis_key = str_replace('{dest_id}', $dest_id, RedisDataService::REDIS_DEST_ADDRESS);
        $result    = $this->redis->hGetAll($redis_key);
        if (!$result) {
            $addr   = $this->getRsBySql('SELECT `address`,`first` FROM ly_address WHERE dest_id = ' . $dest_id . ' AND `status` = 99', true);
            $result = isset($addr['address']) ? $addr : array();
            $this->redis->hmset($redis_key, $result);
            $this->redis->expire($redis_key, 86400);
        }
        return $result;
    }
    /**
     * 根据目的地取得联系方式
     * @param dest_id 目的ID
     * @return array
     */
    public function getContact($dest_id)
    {
        $result = $this->query("SELECT * FROM ly_contact WHERE `dest_id` = '{$dest_id}' AND `status` = '99'");
        return $result ? $result : array();
    }
    /**
     * 根据目的地ID取得营业时间
     * @param dest_id 目的ID
     * @return array
     */
    public function getSaleTime($dest_id)
    {
        $result = $this->query("SELECT * FROM ly_time WHERE `object_type` = 'SALE_TIME' AND `dest_id` = '{$dest_id}' AND `status` = '99' ORDER BY `first` DESC, `time_id` ASC", 'All');
        return $result ? $result : array();
    }
    /**
     * 获取目的地景区的开放时间和目的地简介
     */
    public function getBusinessHours($dest_id)
    {
        $data = $this->query("SELECT `start_time`, `end_time`, `memo`, `first` FROM ly_time WHERE `dest_id` = '{$dest_id}' AND ( object_type = 'sale_time' OR object_type = 'SALE_TIME' ) AND `status` = '99'  AND ( (`start_time` != '' AND `end_time` != '') OR `memo` != '' ) ORDER BY `first` DESC", 'All');
        return $data ? $data : array();
    }
    /**
     * 获取目的地简介
     */
    public function getSummaryText($dest_id, $cate_id)
    {
        $data = $this->query("select `text` FROM ly_data WHERE `dest_id` = '{$dest_id}' AND cate_id = {$cate_id} AND title = '目的地简介' AND `status` = 99 AND preseted = 'Y' AND showed = 'Y'");
        return isset($data['text']) ? $data['text'] : '';
    }
    /**
     * 根据目的地ID取得门票信息
     */
    public function getTicket($dest_id)
    {
        $result = $this->query("SELECT * FROM ly_ticket WHERE `dest_id` = '{$dest_id}' AND `status` = '99'", 'All');
        return $result ? $result : array();
    }
    /**
     * 根据目的地ID取得建议游玩时间
     */
    public function getSuggestTime($dest_id)
    {
        if (!$dest_id) {
            return array();
        }

        $result = $this->query("SELECT * FROM ly_suggest_time WHERE `dest_id` IN(" . implode(',', $dest_id) . ") AND `status` = '99'", 'All');
        return $result ? $result : array();
    }
    /**
     * 根据目的地ID属于此目的地的dest_id
     * @param int $dest_id
     * @return array
     */
    public function getSubDestIdByDestId($dest_id = 0)
    {
        if (!$dest_id || !is_numeric($dest_id)) {
            return array();
        }

        $data   = $this->getDestById($dest_id);
        $key    = RedisDataService::REDIS_DEST_PARENTS_DESTID;
        $result = $this->redis->sort($key . $dest_id);
        if (!$result) {
            $sub_id = array();
            if ($data['range'] != '0' && $data['parents']) {
                $poi_ids  = $this->getRsBySql("SELECT `dest_id` FROM ly_destination WHERE `showed` = 'Y' AND `cancel_flag`='Y' AND `parents` LIKE '{$data['parents']},%'");
                $sub_id[] = $data['dest_id'];
                foreach ($poi_ids as $v) {
                    $sub_id[] = $v['dest_id'];
                }
            } else {
                $sub_id[] = $data['dest_id'];
            }
            foreach ($sub_id as $id) {
                $this->redis->sadd($key . $dest_id, $id);
            }
            $result = $sub_id;
        }
        return $result;
    }
    /**
     * 获取指定poi的游记信息
     * @param array $poi_ids
     * @return array
     */
    public function getTripIdsByTrace($poi_ids = array())
    {
        if (!$poi_ids) {
            return array();
        }

        $trip_ids = $this->getRsBySql("SELECT trip_id FROM ly_trace WHERE `deleted`='N' AND `dest_id` IN(" . implode(',', $poi_ids) . ")");
        $tmp      = array();
        foreach ($trip_ids as $v) {
            $tmp[$v['trip_id']] = '';
        }
        $trip_ids = array_keys($tmp);
        return $trip_ids;
    }
    public function getTripSegmentList($trip_ids = array())
    {
        if (!$trip_ids) {
            return array();
        }

        $list = $this->getRsBySql("SELECT t.`trip_id`,t.`title` AS trip_title,s.segment_id,s.count_share FROM ly_trip AS t
INNER JOIN ly_segment AS s ON s.trip_id = t.trip_id
AND s.`type` = 'PICTURE'
AND s.verify = 99
AND s.deleted = 'N'
AND t.`trip_id` IN (" . implode(',', $trip_ids) . ")");
        $tmp = array();
        foreach ($list as $v) {
            $tmp[$v['segment_id']] = $v;
        }
        return $tmp;
    }
    public function getTripList($trip_ids = array())
    {
        if (!$trip_ids) {
            return array();
        }

        $trip_list = $this->getRsBySql("SELECT `trip_id`,`title` FROM ly_trip WHERE `trip_id` IN(" . implode(',', $trip_ids) . ")");
        $tmp       = array();
        foreach ($trip_list as $v) {
            $tmp[$v['trip_id']] = $v;
        }
        $trip_list = $tmp;
        return $trip_list;
    }
    public function getSegmentList($trip_ids = array())
    {
        if (!$trip_ids) {
            return array();
        }

        $segment_list = $this->getRsBySql("SELECT segment_id,trip_id,count_share FROM ly_segment WHERE `type`='PICTURE' AND `verify`='99' AND `deleted`='N' AND `trip_id` IN(" . implode(',', $trip_ids) . ")");
        $tmp          = array();
        foreach ($segment_list as $v) {
            $tmp[$v['segment_id']] = $v;
        }
        $segment_list = $tmp;
        return $segment_list;
    }
    public function getSegmentIds($trip_ids = array())
    {
        if (!$trip_ids) {
            return array();
        }

        $segment_list = $this->getRsBySql("SELECT segment_id FROM ly_segment WHERE `type`='PICTURE' AND `verify`='99' AND `deleted`='N' AND `trip_id` IN(" . implode(',', $trip_ids) . ")");
        $tmp          = array();
        foreach ($segment_list as $v) {
            $tmp[] = $v['segment_id'];
        }
        return $tmp;
    }
    public function getlySPicture($segment_ids = array(), $pages = array())
    {
        if (!$segment_ids) {
            return array();
        }

        if (!isset($pages['page'])) {
            $pages['page'] = 1;
        }

        if (!isset($pages['pageSize'])) {
            $pages['pageSize'] = 15;
        }

        //取得符合条件的数量
        $item = $this->getRsBySql("SELECT COUNT(segment_id) AS n FROM ly_s_picture WHERE `segment_id` IN(" . implode(',', $segment_ids) . ")", true);
        $pics = array(
            'pages' => array(
                'itemCount' => intval($item['n']),
                'pageCount' => ceil($item['n'] / $pages['pageSize']),
                'page'      => $pages['page'],
                'pageSize'  => $pages['pageSize'],
            ),
            'list'  => $this->getRsBySql("SELECT segment_id,memo,img_url,original_time,camera,longitude,latitude FROM ly_s_picture WHERE `segment_id` IN(" . implode(',', $segment_ids) . ") ORDER BY segment_id ASC LIMIT " . (($pages['page'] - 1) * $pages['pageSize']) . "," . $pages['pageSize']),
        );
        return $pics;
    }
    /**
     * 获取目的地下的poi的图片墙
     * @param $poi_ids
     * @param $uid
     * @param array $pages
     * @return array
     * @author shenxiang
     */
    public function getPicsByDest($poi_ids, $uid, $pages = array())
    {
        $return = array();
        if (!$poi_ids) {
            return $return;
        }

        $trip_ids = $this->getTripIdsByTrace($poi_ids);
        if (!$trip_ids) {
            return $return;
        }

        $segment_list  = $this->getTripSegmentList($trip_ids);
        $segment_ids   = $this->getSegmentIds($trip_ids);
        $this->comment = $this->di->get('cas')->get('comment-data-service');
        $this->praise  = $this->di->get('cas')->get('praise-data-service');
        if (!$segment_ids) {
            return $return;
        }

        $pics = $this->getlySPicture($segment_ids, $pages);
        if ($pages) {
            $pic_list = $pics['list'];
            foreach ($pic_list as $pic_k => $pic_v) {
                $praiseCount                      = $this->praise->getPraiseCount($pic_v['segment_id']);
                $commentCount                     = $this->comment->getCommentCount($pic_v['segment_id']);
                $pic_list[$pic_k]['praiseCount']  = $praiseCount;
                $pic_list[$pic_k]['commentCount'] = $commentCount;
                $pic_list[$pic_k]['is_praise']    = $praiseCount ? 'Y' : 'N';
                $pic_list[$pic_k]['is_comment']   = $commentCount ? 'Y' : 'N';
                if (isset($segment_list[$pic_v['segment_id']])) {
                    $pic_list[$pic_k]['shareCount'] = $segment_list[$pic_v['segment_id']]['count_share'];
                    $pic_list[$pic_k]['trip_title'] = $segment_list[$pic_v['segment_id']]['trip_title'];
                    $pic_list[$pic_k]['trip_id']    = $segment_list[$pic_v['segment_id']]['trip_id'];
                }
            }
            $pics["list"] = $pic_list;
        } else {
            $pics = $pics["list"];
        }
        return $pics;
    }
    /**
     * 新游记获取图片墙的方式
     * @param $dest_id 目的地ID
     * @param $page 页码
     * @param $pageSize 每页显示条数
     * @auth shenxiang
     * @return string | json
     */
    public function getTravelPicsByDest($dest_id, $page, $pageSize)
    {
        $key = RedisDataService::REDIS_DEST_TRIP_IDS . $dest_id;
        //拿到此页的游记ID集合
        $travel_ids  = $this->redis->ZRange($key, 0, -1);
        $image_ids   = array();
        $list        = array();
        $tmp_travels = array();
        $_travel_ids = array();
        $_travels    = array();
        if ($travel_ids && is_array($travel_ids)) {
            $travels = $this->di->get('cas')->get('travel_data_service');
            $total   = $travels->getTotalBy(array(
                'id' => " IN(SELECT image_id FROM tr_travel_image_rel WHERE travel_id IN(" . implode(',', $travel_ids) . "))",
            ), 'tr_image');
            $totalPage = ceil($total / $pageSize);
            $totalPage = $totalPage > 0 ? $totalPage : 1;
            $page      = $page > 0 ? $page : 1;
            $page      = $page > $totalPage ? $totalPage : $page;
            $pageSize  = $pageSize > 50 ? 50 : $pageSize;
            if ($totalPage > 0) {
                $start     = ($page - 1) * $pageSize;
                $tmp_image = $travels->querySql('SELECT travel_id,image_id FROM tr_travel_image_rel WHERE travel_id IN(' . implode(',', $travel_ids) . ') LIMIT ' . $start . ',' . $pageSize);
                foreach ($tmp_image['list'] as $k => $v) {
                    $image_ids[]                 = $v['image_id'];
                    $_travel_ids[$v['image_id']] = $v['travel_id'];
                }
                if ($_travel_ids) {
                    $tmp_travels = $travels->querySql('SELECT id,title,summary FROM tr_travel WHERE id IN(' . implode(',', $_travel_ids) . ')');
                    foreach ($tmp_travels['list'] as $k => $v) {
                        $_travels[$v['id']] = $v;
                    }
                }
                $tmp_list = $travels->querySql('SELECT id,url FROM tr_image WHERE id IN(' . implode(',', $image_ids) . ')');
                foreach ($tmp_list['list'] as $v) {
                    $list[] = array(
                        'img_url'       => $v['url'],
                        'memo'          => isset($_travels[$_travel_ids[$v['id']]]['summary']) ? $_travels[$_travel_ids[$v['id']]]['summary'] : '',
                        'trip_title'    => isset($_travels[$_travel_ids[$v['id']]]['title']) ? $_travels[$_travel_ids[$v['id']]]['title'] : '',
                        'trip_id'       => isset($_travels[$_travel_ids[$v['id']]]['id']) ? $_travels[$_travel_ids[$v['id']]]['id'] : 0,
                        'segment_id'    => '',
                        'original_time' => '',
                        'camera'        => '',
                        'longitude'     => '',
                        'latitude'      => '',
                        'praiseCount'   => '',
                        'commentCount'  => '',
                        'is_praise'     => '',
                        'is_comment'    => '',
                        'shareCount'    => '',
                    );
                }
            }
        }
        return array('pages' => array('itemCount' => (int) $total, 'pageCount' => $totalPage, 'page' => $page, 'pageSize' => $pageSize), 'list' => $list);
    }
    /**
     * 根据目的地类型及父级目的地获取下级目的地集合
     * @param $data
     * @param $dest_type
     * @param $pages
     * @param $rec_ids
     * @param $limit
     * @param string $search_name
     * @param int $stage
     * @param string $arrCondition
     * @return array
     */
    public function getDestsByPid($data, $dest_type, $pages, $rec_ids, $limit = '', $search_name = '', $stage = 0, $arrCondition = '')
    {
        if (!isset($data['dest_id'])) {
            return array();
        }

        $not_in = '';
        //判断要查询的目的地类型
        if ($dest_type) {
            if (is_array($dest_type)) {
                $dests_types = "AND`dest_type` IN('" . implode("','", $dest_type) . "')";
            } else {
                if ($dest_type == 'VIEWSPOT') {
                    $dests_types = "AND (`dest_type`='VIEWSPOT' OR (dest_type='SCENIC_ENTERTAINMENT' AND `ent_sight`='Y' ))  ";
                } elseif ($dest_type == 'SCENIC_ENTERTAINMENT') {
                    $dests_types = "AND (`dest_type`='SCENIC_ENTERTAINMENT'  OR ( dest_type='VIEWSPOT' AND `ent_sight`='Y' ))  ";
                } else {
                    $dests_types = "AND `dest_type` ='{$dest_type}'";
                }
            }
        }
        $not_in       = $rec_ids ? " AND `dest_id` NOT IN(" . $rec_ids . ")" : '';
        $search_names = $search_name ? " AND dest_name LIKE '%{$search_name}%'" : '';
        $stages       = $stage ? ' AND `stage`=' . $stage : '';
        $limit_str    = $limit ? ' LIMIT ' . $limit : ($pages ? ' LIMIT ' . (($pages['page'] - 1) * $pages['pageSize']) . ',' . $pages['pageSize'] : '');
        $where        = "`cancel_flag`='Y' AND `showed`='Y'" . $dests_types . " AND parents LIKE '{$data['parents']},%'" . $not_in . $search_names . $stages . $arrCondition;
        $sql          = "SELECT `dest_id`,`dest_name`,`pinyin`,`dest_type`,`parents`,`img_url`,`parent_id`,`en_name`,`cancel_flag`,`stage`,`range`,`intro`,`star`,`abroad`,`url`,`ent_sight`,`count_been`,`count_want`,`g_longitude`,`g_latitude`,`longitude`,`latitude`,(CASE `img_url` WHEN '' THEN 0 ELSE 1 END) AS have_image FROM " . self::TABLE_NAME . " WHERE {$where} ORDER BY have_image DESC,count_been DESC" . $limit_str;
        $redis_key    = str_replace('{sql}', md5($sql), RedisDataService::REDIS_DEST_DATA_PID);
        $redis_data   = $this->redis->get($redis_key);
        if ($redis_data === false) {
            $result = $this->getRsBySql($sql);
            $this->redis->setex($redis_key, self::EXPIRE_TIME, json_encode($result));
        } else {
            $result = json_decode($redis_data, true);
        }
        if ($pages) {
            $rs        = $this->getRsBySql("SELECT COUNT(dest_id) AS n FROM " . self::TABLE_NAME . " WHERE `cancel_flag`='Y' AND `showed`='Y'" . $dests_types . " AND parents LIKE '{$data['parents']},%'" . $search_names . $stages . $arrCondition, true);
            $num       = isset($rs['n']) ? intval($rs['n']) : 0;
            $countPage = ceil($num / $pages['pageSize']);
            return array('list' => $result, 'pages' => array('itemCount' => $num, 'pageCount' => $countPage, 'page' => $pages['page'], 'pageSize' => $pages['pageSize']));
        }
        return $result;
    }
    /**
     * 根据目的地ID获取推荐目的地
     * @param $dest_id 目的地ID
     * @param $dest_type 推荐类型
     * @return array
     * @author shenxiang
     */
    public function getRecommendDest($dest_id, $dest_type = 'MAIN_DEST', $stage = 1)
    {
        $data = array();
        if (!is_numeric($dest_id) || !in_array($dest_type, array('VIEWSPOT', 'MAIN_DEST', 'VIEW_DEST'))) {
            return $data;
        }

        $this->scenic_viewspot = $this->di->get('cas')->get('scenicviewspot-data-service');
        $viewspotarr           = $this->scenic_viewspot->getRsBySql("SELECT viewspot_id,seq,recommend_id FROM ly_scenic_viewspot WHERE `dest_id`={$dest_id}  AND `status`=99 AND `recommend_type`='{$dest_type}' AND viewspot_id != {$dest_id}");
        if ($viewspotarr) {
            $viewspotarr = UCommon::parseItem($viewspotarr, 'viewspot_id');
            foreach ($viewspotarr as $key => $row) {
                $viewspot_id[] = $row['viewspot_id'];
            }
            $viewspotids = implode(',', $viewspot_id);
            if ($viewspotids) {
                $data = $this->getRsBySql("SELECT dest_id,dest_name,pinyin,dest_type FROM " . self::TABLE_NAME . " WHERE `cancel_flag` = 'Y' AND `showed`='Y' AND `dest_id` IN({$viewspotids}) AND `stage` = {$stage}");
            }
            $data = UCommon::parseItem($data, 'dest_id');
            foreach ($data as $k => $v) {
                $data[$k]['seq']          = $viewspotarr[$k]['seq'];
                $data[$k]['recommend_id'] = $viewspotarr[$k]['recommend_id'];
            }
            $data = UCommon::array_sort($data, 'seq', 'asc');
            $data = array_slice($data, 0, 30);
        }
        return $data;
    }
    /**
     * 获取当前目的地下一级目的地
     * @param $data 目的地基本信息
     * @param $limit 获取的数量
     * @param $exclude_ids 需要排除的目的地ID
     * @return array
     * @author shenxiang
     */
    public function getNextLevelDest($data, $limit = 10, $exclude_ids = array())
    {
        if (!$data || !isset($data['dest_id'])) {
            return array();
        }

        $where = '';
        if (count($exclude_ids)) {
            $where = ' AND dest_id NOT IN(' . implode(',', $exclude_ids) . ')';
        }
        $sql    = "SELECT dest_id,dest_name,dest_type,parent_id,parents,pinyin,count_been FROM `ly_destination` WHERE cancel_flag='Y'  AND  showed='Y' AND `dest_type` NOT IN('TOWN','SPAN_TOWN') AND stage=1{$where} AND parent_id=" . $data['dest_id'] . " ORDER BY count_been DESC,dest_id DESC  LIMIT " . $limit;
        $result = $this->getRsBySql($sql);
        return $result ? $result : array();
    }
    /**
     * 获取当前目的地同级目的地
     */
    public function getSameLevelDest($data, $limit = '')
    {
        if (!$data) {
            return array();
        }

        $tmp       = $this->getRsBySql("SELECT parent_id FROM ly_destination WHERE `dest_id`={$data['dest_id']}", true);
        $parent_id = isset($tmp['parent_id']) ? $tmp['parent_id'] : 0;
        $sql       = "SELECT dest_id,dest_name,dest_type,parent_id,parents,pinyin,count_been FROM `ly_destination` WHERE cancel_flag='Y' AND showed='Y' AND parent_id= {$parent_id} AND dest_id != {$data['dest_id']} AND `dest_type`='{$data['dest_type']}' ORDER BY count_been DESC,dest_id DESC LIMIT {$limit}";
        $result    = $this->getRsBySql($sql);
        return $result ? $result : array();
    }
    /**
     * 获取周边攻略的SEO链接
     * 大洲:取下一级国家类型的目的地
     * 国家,省份:取下一级城市类型目的地加上同一省份类型目的地
     * 城市:取同一级城市类型目的地加上取下一级区县类型目的地
     * 区县:所属城市类型下的同一级目的地加上同一级区县类型目的地
     * 乡镇:所属城市类型下的同一级目的地加上所属区县类型的目的地
     * 特殊景区:所属城市类型下的同一级目的地加上所属区县类型的目的地
     * poi:所属城市类型下的同一级目的地加上所属区县下的同一级目的地
     * @param array $data 目的地基本数据
     * @return array
     * @author shenxiang
     */
    public function getAroundSeoLinks($data = array())
    {
        $return = array();
        if (!$data || !isset($data['dest_type']) || !isset($data['dest_id'])) {
            return $return;
        }

        switch ($data['dest_type']) {
            case 'CONTINENT': //大洲
            case 'SPAN_COUNTRY': //跨国家地区
                $result = $this->getRecommendDest($data['dest_id']);
                foreach ($result as $v) {
                    $dest_ids[] = $v['dest_id'];
                }
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $nextlevel = $this->getNextLevelDest($data, 30, $dest_ids);
                $result    = array_merge($result, $nextlevel);
                break;
            case 'COUNTRY': //国家
            case 'SPAN_PROVINCE': //跨州省地区
            case 'PROVINCE': //州省
                $result = $this->getRecommendDest($data['dest_id']);
                foreach ($result as $v) {
                    $dest_ids[] = $v['dest_id'];
                }
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $nextlevel = $this->getNextLevelDest($data, 30 - $get_count, $dest_ids);
                $result    = array_merge($result, $nextlevel);
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $samelevel = $this->getSameLevelDest($data, 30 - $get_count);
                $result    = array_merge($result, $samelevel);
                break;
            case 'SPAN_CITY':
            case 'CITY':
                //1.同一级城市类型的目的地
                //2.取完，再取下一级区县类型的目的地
                $result    = $this->getSameLevelDest($data, 30);
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $nextlevel = $this->getNextLevelDest($data, 30 - $get_count);
                $result    = array_merge($result, $nextlevel);
                break;
            default:
                $parents   = explode(',', $data['parents']);
                $tmp_len   = count($parents);
                $parent_id = 0;
                if (isset($parents[$tmp_len - 1])) {
                    unset($parents[$tmp_len - 1]);
                }
//去掉自己
                $parents = array_reverse($parents); //翻转
                foreach ($parents as $v) {
                    $tmp         = $this->getDestById($v);
                    $parent_type = isset($tmp) && $tmp['dest_type'] ? $tmp['dest_type'] : '';
                    if (isset($parent_type) && ($parent_type == 'CITY' || $parent_type == 'COUNTRY' || $parent_type == 'SPAN_CITY' || $parent_type == 'PROVINCE' || $parent_type == 'SPAN_PROVINCE')) {
                        $parent_id = $v;
                        break;
                    }
                }
                $data      = $this->getDestById($parent_id);
                $result    = $this->getRecommendDest($data['dest_id']);
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $samelevel = $this->getSameLevelDest($data, 30 - $get_count);
                $result    = array_merge($result, $samelevel);
                $get_count = count($result);
                if ($get_count >= 30) {
                    return $result;
                }

                $nextlevel = $this->getNextLevelDest($data, 30 - $get_count);
                $result    = array_merge($result, $nextlevel);
                break;
        }
        return $result;
    }
    /**
     * 攻略推荐(国内：随机取所属省份、城市类型的目的地 境外：随机取所属国家类型的目的地)
     * @param $data 目的地基本信息
     * @return array
     * @author shenxiang
     */
    public function getRecomGuide($data)
    {
        //不符合要求的parents
        if (!$data || !trim($data['parents']) || strpos($data['parents'], ',,') || $data['dest_type'] == 'CONTINENT' || $data['dest_type'] == 'SPAN_COUNTRY' || !isset($data['abroad'])) {
            return array();
        }

        $rs              = array();
        $country_dest_id = 0;
        $request_uri     = $_SERVER['REQUEST_URI'];
        if ($data['abroad'] == 'Y') {
//境外
            if ($data['dest_type'] == 'COUNTRY') {
                $country_dest_id = intval($data['dest_id']);
                $parents_like    = $data['parents'] . ',%';
            } else {
                //往上找到目的地类型为国家类型
                $parents = explode(',', $data['parents']);
                unset($parents[count($parents) - 1]); //去掉本身
                $tmp_parents = array_reverse($parents); //翻转
                foreach ($tmp_parents as $k => $dest_id) {
                    $tmp      = $this->getDestById($dest_id);
                    $tmp_data = isset($tmp) && $tmp['dest_type'] ? $tmp['dest_type'] : '';
                    if ($tmp_data == 'COUNTRY') {
                        $country_dest_id = $dest_id;
                        break;
                    }
                }
                $tmp_like = array();
                foreach ($parents as $v) {
                    $tmp_like[] = $v;
                    if ($v == $country_dest_id) {
                        break;
                    }
                }
                $parents_like = implode(',', $tmp_like) . ',%';
            }
        } else {
//国内统一使用中国
            $country_dest_id = 3548;
            $parents_like    = '0,3643,3548,%';
        }
        if (!intval($country_dest_id) || $parents_like == '0,%') {
            return array();
        }

        $result = $this->getRsBySql("SELECT dest_id,dest_name,dest_type,pinyin FROM " . self::TABLE_NAME . " WHERE `abroad` = '{$data['abroad']}' AND cancel_flag = 'Y' AND `showed` = 'Y' AND `stage` = 1 AND `dest_type` IN('PROVINCE','SPAN_CITY','CITY') AND `parents` LIKE '{$parents_like}' ORDER BY count_been DESC,count_want DESC");
        //获取符合条件的数量
        $total_num = count($result);
        if ($total_num > 20) {
            $start_limit = rand(0, $total_num - 1);
            if ($start_limit > $total_num - 21) {
//如果随机出来的数为最后20个结果集,则取向前推的前面20个
                $rs = array_slice($result, $start_limit - 20, 20);
            } else {
                $rs = array_slice($result, $start_limit, 20);
            }
        } else {
//不足20个的话全部取
            $rs = $result;
        }
        $site_url = UCommon::site_url();
        //添加链接和链接上应该显示的文字
        foreach ($rs as $k => $v) {
            if (strpos($request_uri, 'summary/')) {
                $rs[$k]['link']     = $site_url . 'summary/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '指南';
            } elseif (strpos($request_uri, 'scenery/')) {
                $rs[$k]['link']     = $site_url . 'scenery/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '景点';
            } elseif (strpos($request_uri, 'stay/')) {
                $rs[$k]['link']     = $site_url . 'stay/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '住宿';
            } elseif (strpos($request_uri, 'food/') || strpos($request_uri, 'restaurant/') || strpos($request_uri, 'poi/eatery')) {
                $rs[$k]['link']     = $site_url . 'food/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '美食';
            } elseif (strpos($request_uri, 'shop/') || strpos($request_uri, 'store/') || strpos($request_uri, 'poi/market')) {
                $rs[$k]['link']     = $site_url . 'shop/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '购物';
            } elseif (strpos($request_uri, 'play/')) {
                $rs[$k]['link']     = $site_url . 'play/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '娱乐';
            } elseif (strpos($request_uri, 'traffic/')) {
                $rs[$k]['link']     = $site_url . 'traffic/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '交通';
            } elseif (strpos($request_uri, 'youji/')) {
                $rs[$k]['link']     = $site_url . 'youji/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '游记';
            } elseif (strpos($request_uri, 'photo/')) {
                $rs[$k]['link']     = $site_url . 'photo/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '图片';
            } elseif (strpos($request_uri, 'map/')) {
                $rs[$k]['link']     = $site_url . 'map/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '地图';
            } elseif (strpos($request_uri, 'travel/')) {
                $rs[$k]['link']     = $site_url . 'travel/d-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '行程';
            } else {
                $rs[$k]['link']     = $site_url . 'd-' . $v['pinyin'] . $v['dest_id'] . '.html';
                $rs[$k]['linkname'] = $v['dest_name'] . '攻略';
            }
        }
        return $rs;
    }
    /**
     * 获取目的地周边指定范围的指定类型的目的地
     * @param $dest_id 目的地ID
     * @param $dest_type 目的地类型
     * @param $distance 取值范围
     * @param $limit 获取的最大条数
     * @return array
     * @author shenxiang
     */
    public function getNearDest($dest_id, $dest_type, $distance = 5, $limit = 15)
    {
        $data      = $this->getDestById($dest_id);
        $pointarea = UCommon::getPointArea($data['longitude'], $data['latitude'], $distance);
        $location  = $this->getRsBySql("SELECT `dest_id` FROM " . self::TABLE_NAME . " WHERE `dest_type` = '{$dest_type}' AND `abroad` = 'N' AND `cancel_flag` = 'Y' AND `showed` = 'Y' AND `dest_id` != {$data['dest_id']} AND `latitude` > {$pointarea['lat_min']} AND `latitude` < {$pointarea['lat_max']} AND `longitude` > {$pointarea['long_min']} AND `longitude` < {$pointarea['long_max']} LIMIT {$limit}");
        foreach ($location as $key => $val) {
            $row                        = $this->getDestById($val['dest_id']);
            $location[$key]             = $row;
            $location[$key]['pointUrl'] = UCommon::getDestTypeUrl($row);
            $location[$key]['title']    = $row['dest_name'];
            if ($data['abroad'] == 'Y') {
                $d = UCommon::getDistance($data['g_latitude'], $data['g_longitude'], $row['g_latitude'], $row['g_longitude']);
            } elseif ($data['abroad'] == 'N') {
                $d = UCommon::getDistance($data['latitude'], $data['longitude'], $row['latitude'], $row['longitude']);
            }
            $location[$key]['d'] = $d;
            if ($location[$key]['d'] < 100) {
                $location[$key]['distance'] = '< 100m';
            } elseif ($location[$key]['d'] > 100 && $location[$key]['d'] < 1000) {
                $location[$key]['distance'] = '0.1 - 1.0km';
            } else {
                $location[$key]['distance'] = '1.0 - 5.0km';
            }
        }
        $location = UCommon::array_sort($location, 'd');
        return $location;
    }

    public function getNearDestByWant($dest_id, $dest_type, $distance = 5, $limit = 15)
    {
        $data      = $this->getDestById($dest_id);
        $pointarea = UCommon::getPointArea($data['longitude'], $data['latitude'], $distance);
        $location  = $this->getRsBySql("SELECT `dest_id`,`pinyin`, `dest_name`, `img_url` FROM " . self::TABLE_NAME . " WHERE `dest_type` = '{$dest_type}' AND `abroad` = 'N' AND `cancel_flag` = 'Y' AND `showed` = 'Y' AND `dest_id` != {$data['dest_id']} AND `latitude` > {$pointarea['lat_min']} AND `latitude` < {$pointarea['lat_max']} AND `longitude` > {$pointarea['long_min']} AND `longitude` < {$pointarea['long_max']} ORDER BY `count_want` DESC LIMIT {$limit}");
        return $location;
    }

    /**
     * 获取目的地友情链接
     * @param $dest_id 目的地ID
     * @param $type tab类型
     * @return array
     * @author shenxiang
     */
    public function getSeoOutLink($dest_id, $type = '')
    {
        if (!$dest_id || !is_numeric($dest_id) || !$type) {
            return array();
        }

        $seooutkey          = 'seooutlink_lvyou_' . $type;
        $this->external_api = $this->di->get('cas')->get('external-api-data-server');
        $result             = $this->external_api->getResult('API_OUT_FRIEND_LINK', array(
            'seokey'   => $seooutkey,
            'dest_id'  => $dest_id,
            'fuzhuzhi' => 0,
        ));
        return $result;
    }
    /**
     * 获取目的地精选链接
     * @param $data 目的地基本信息
     * @return array
     * @author libiying
     */
    public function getSeoInLink($dest_id, $type = '')
    {
        if (!$dest_id || !is_numeric($dest_id) || !$type) {
            return array();
        }

        $seoinkey           = 'seoinlink_lvyou_' . $type;
        $this->external_api = $this->di->get('cas')->get('external-api-data-server');
        $result             = $this->external_api->getResult('API_IN_FRIEND_LINK', array(
            'seokey'  => $seoinkey,
            'dest_id' => $dest_id,
        ));

        return $result;
    }
    /**
     * 获取目的地的相关导航
     * @return array
     * @author libiying
     */
    public function getDestTags($data)
    {
        if (!$data) {
            return array();
        }

        $this->external_api = $this->di->get('cas')->get('external-api-data-server');
        $tags               = $this->external_api->getResult('API_SEAECH_LIST', array(
            'dest1' => $data['dest_name'],
        ));
        if ($tags && count($tags)) {
            foreach ($tags as $k => $v) {
                if ($k != 'around' && $k != 'freetour' && $k != 'group' && $k != 'local' && $k != 'ticket') {
                    unset($tags[$k]);
                }
            }
        }

        $result   = array();
        $result[] = array(
            'link'     => 'http://dujia.lvmama.com/tour/' . $data['pinyin'] . $data['dest_id'],
            'linkname' => $data['dest_name'] . '旅游',
        );
        foreach ($tags as $k => $v) {
            if ($k == 'ticket') {
                $result[] = array(
                    'link'     => 'http://ticket.lvmama.com/a-' . $data['pinyin'] . $data['dest_id'],
                    'linkname' => $data['dest_name'] . '景点门票',
                );
            } else {
                $tname = '';
                switch ($k) {
                    case 'around':$tname = '周边跟团游';
                        break;
                    case 'group':$tname = '出发地跟团游';
                        break;
                    case 'local':$tname = '目的地跟团游';
                        break;
                    case 'freetour':$tname = '自由行';
                        break;
                }
                $result[] = array(
                    'link'     => 'http://dujia.lvmama.com/tour/' . $data['pinyin'] . $data['dest_id'] . '/' . $k,
                    'linkname' => $data['dest_name'] . $tname,
                );
            }
        }
        $uris = array(
            'd-'         => '旅游攻略',
            'summary/d-' => '游玩指南',
            'scenery/d-' => '景点大全',
            'stay/d-'    => '住宿攻略',
            'food/d-'    => '美食攻略',
            'shop/d-'    => '购物攻略',
            'travel/d-'  => '行程安排',
            'traffic/d-' => '交通路线',
            'play/d-'    => '娱乐场所',
            'youji/d-'   => '游玩游记',
            'map/d-'     => '旅游地图',
            'photo/d-'   => '风景图片',
            'comment/d-' => '游玩点评',
        );
        $site_url = UCommon::site_url();
        foreach ($uris as $uri => $tname) {
            $result[] = array(
                'link'     => $site_url . $uri . $data['pinyin'] . $data['dest_id'] . '.html',
                'linkname' => $data['dest_name'] . $tname,
            );
        }

        return $result;
    }
    /**
     * 获取当前目的地热门景点SEOLINKS
     * @return array
     * @author libiying
     */
    public function getHotSeoLinks($data_id, $num = 20)
    {
        if (!$data_id) {
            return array();
        }

        $this->external_api = $this->di->get('cas')->get('external-api-data-server');
        $ticket             = $this->external_api->getResult('API_SEAECH_TICKET', array(
            'dest1' => $data_id,
            'dest2' => '',
            'num'   => $num,
        ));
        if (!isset($ticket) || !is_array($ticket)) {
            return array();
        }
        foreach ($ticket as $k => $v) {
            if ($v['destId'] == $data_id || $v['categoryId'] != 11) {
//过滤掉本身和非景点
                unset($ticket[$k]);
            }
        }
        return $ticket;
    }

    /**
     * 取指定类型的父级目的地
     * @param $parent_id
     * @param null $dest_type 目的地类型，可多选，数组形式传参
     * @return array|bool|mixed|void
     * @author libiying
     */
    public function getParentDest($parent_id, $dest_type = null)
    {
        $parent = $this->getDestById($parent_id);
        if (!$parent) {
            return false;
        }
        if ($dest_type == null
            || (is_string($dest_type) && $dest_type == $parent['dest_type'])
            || (is_array($dest_type) && in_array($parent['dest_type'], $dest_type))) {
            return $parent;
        }

        return $this->getParentDest($parent['parent_id'], $dest_type);
    }

    public function getRestaurantDetail($restaunt, $url_type = '')
    {
        if (!$restaunt || !is_array($restaunt)) {
            return array();
        }

        $rs      = array();
        $img_url = array();
        foreach ($restaunt as $v) {
            $v['subject_name'] = '';
            $v['address']      = '';
            $rs[$v['dest_id']] = $v;
        }
        $res_ids = array_keys($rs);
        $cost    = $this->getRsBySql('SELECT `dest_id`,`price` FROM ly_cost WHERE `status`=99 AND dest_id IN(' . implode(',', $res_ids) . ')');
        foreach ($cost as $v) {
            $rs[$v['dest_id']]['price'] = $v['price'];
        }
        if ($url_type == 'food_item') {
            $img_url = $this->getRsBySql('SELECT dest_id,img_url FROM ' . self::TABLE_NAME . ' WHERE cancel_flag = \'Y\' AND showed = \'Y\' AND `dest_id` IN(' . implode(',', $res_ids) . ')');
            foreach ($img_url as $v) {
                $rs[$v['dest_id']]['img_url'] = $v['img_url'];
            }
        }
        $subject = $this->di->get('cas')->get('mo-subject');
        $food    = $this->di->get('cas')->get('food-data-service');
        $themes  = $subject->query('SELECT subject_name,object_id FROM mo_subject_relation WHERE `status`=99 AND channel=\'lvyou\' AND `object_type`=\'DEST\' AND object_id  IN(' . implode(',', $res_ids) . ')', 'All');
        if ($themes) {
            foreach ($themes as $v) {
                if (!$rs[$v['object_id']]['subject_name']) {
                    $rs[$v['object_id']]['subject_name'] = $v['subject_name'];
                } else {
                    $rs[$v['object_id']]['subject_name'] .= ',' . $v['subject_name'];
                }
            }
        }
        $address = $this->getRsBySql('SELECT dest_id,address FROM ly_address WHERE `status`=99 AND first=\'Y\' AND dest_id IN(' . implode(',', $res_ids) . ')');
        foreach ($address as $v) {
            $rs[$v['dest_id']]['address'] = $v['address'];
        }
        foreach ($res_ids as $dest_id) {
            $rs[$dest_id]['best_food'] = $food->GetRestBestFood($dest_id);
        }
        return $rs;
    }

    /**
     * 添加
     *
     */
    public function insert($data)
    {
        if ($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data))) {
//             $this->findOneBy(array('id'=>$id), self::TABLE_NAME, null, true);
            //             return array('error'=>0, 'result'=>$id);
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
        $whereCondition = 'trip_id = ' . $id;
        if ($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition)) {
        }
    }
    public function getOneByDestId($dest_id)
    {
        $where_condition = array('dest_id' => "=" . $dest_id);
        return $this->getOne($where_condition, self::TABLE_NAME);
    }

    public function destHotChildren($where_condition, $limit = 5)
    {
        $where_str = implode(' AND ', $where_condition);
        $sql       = "SELECT `dest_name`,`pinyin`,`dest_id`,`dest_type` FROM " . self::TABLE_NAME . " WHERE `cancel_flag` = 'Y' AND `showed` = 'Y' AND {$where_str} " .
            " ORDER BY `count_want` DESC,`count_been` DESC LIMIT " . $limit;
//        return $sql;
        $result = $this->getRsBySql($sql);
        return ($result && is_array($result)) ? $result : array();
    }

    /**
     * 根据条件搜索目的地信息
     * @author lixiumeng
     * @datetime 2017-12-14T11:22:49+0800
     * @param    array                    $condition [description]
     * @param    array                    $limit     [description]
     * @param    array                    $fields    [description]
     * @return   [type]                              [description]
     */
    public function search($condition = array(), $limit = array(), $fields = array())
    {
        if (empty($fields)) {
            $fields = array('dest_id', 'dest_name', 'dest_type_name', 'cancel_flag', 'district_parent_names', 'parent_names');
        }

        //防止设置pageSize太大拖垮库
        $limit['page_size'] = isset($limit['page_size']) && is_numeric($limit['page_size']) ? ($limit['page_size'] > 30 ? 30 : $limit['page_size']) : 15;
        $limit['page_num']  = isset($limit['page_num']) && is_numeric($limit['page_num']) ? $limit['page_num'] : 1;
        $where              = ' WHERE 1 = 1';
        if (!empty($condition['dest_id'])) {
            $where .= ' AND dest_id = ' . $condition['dest_id'];
        }

        if (!empty($condition['dest_name'])) {
            $where .= ' AND dest_name LIKE \'%' . $condition['dest_name'] . '%\'';
        }
        //获取符合条件的总条数
        $tmp   = $this->query('SELECT COUNT(dest_id) AS c FROM ly_destination' . $where);
        $count = intval($tmp['c']);
        //总页码
        $totalPage         = ceil($count / $limit['page_size']);
        $limit['page_num'] = $limit['page_num'] > $totalPage ? $totalPage : $limit['page_num'];
        $list              = $this->query('SELECT `' . implode('`,`', $fields) . '` FROM ly_destination' . $where . ' LIMIT ' . (($limit['page_num'] - 1) * $limit['page_size']) . ',' . $limit['page_size'], 'All');

        // 补全目的信息
        // $food_id_str = implode(',', array_column($list, 'food_id'));
        // $dest_sql    = "select food_id,dest_name from ly_food_dest where parent = 0 and food_id in ({$food_id_str})";
        // $rs          = $this->query($dest_sql, 'All');
        // if (!empty($rs)) {
        //     $dest_arr = [];
        //     foreach ($rs as $k => $v) {
        //         $dest_arr[$v['food_id']][] = $v['dest_name'];
        //     }
        //     foreach ($list as $m => $n) {
        //         if (!empty($dest_arr[$n['food_id']])) {
        //             $dests = implode(',', array_unique($dest_arr[$n['food_id']]));
        //         } else {
        //             $dests = '';
        //         }
        //         $list[$m]['dests'] = $dests;
        //     }
        // }

        return array('list' => $list, 'count' => $count, 'page_num' => $limit['page_num'], 'page_size' => $limit['page_size'], 'maxPage' => $totalPage);
    }

}
