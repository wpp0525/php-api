]<?php

use Lvmama\Cas\Service\RedisDataService,
    Lvmama\Common\Utils\UCommon;

/**
 * 处理 kafka 中游记状态变更数据
 *
 * @author jianghu
 *
 */
class CanalTravelListWorkerService implements \Lvmama\Cas\Component\Kafka\ClientInterface
{
    private $traveldatasvc;
    private $tripdatasvc;
    private $redis;

    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->tripdatasvc = $di->get('cas')->get('trip-data-service');
        $this->tripdatasvc->setReconnect(true);

        $this->redis = $di->get('cas')->getRedis();

    }

    public function handle($data)
    {
        // TODO: Implement handle() method.
        if ($data->payload) {
            echo "----------  START  ----------", PHP_EOL;
            $json_data = json_decode($data->payload, true);
            $tmp_data = $json_data['0']['cDatas'];
            $tmp_data = UCommon::parseItem($tmp_data, 'name');

            echo "json data:", json_encode($tmp_data), PHP_EOL;
            //驴游宝收益变化(lmm_lvyou.ly_bonus表数据变动)
            if (array_key_exists('bonus_id', $tmp_data)) {
                foreach ($tmp_data as $key => $row) {
                    //当收益状态变更为已打款时，更新 REDIS 中该游记的收益
                    if ($key == 'remit_status' && in_array($tmp_data['type']['value'], array('order', 'page', 'admin', 'act_trip', 'hot_trip')) && $row['updated'] && $row['value'] == '99') {
                        $travel_id = $tmp_data['trip_id']['value'];
                        $redis_key = str_replace('{travel_id}', $travel_id, RedisDataService::REDIS_TRAVEL_LIST_DATA);
                        $redis_bonus = $this->redis->hget($redis_key,'bonus');
                        $redis_bonus += $tmp_data['commission_amt']['value'];
                        $this->redis->hset($redis_key,'bonus',$redis_bonus);

                        echo "update bonus", PHP_EOL;
                    }
                }
                echo "----------  END  ----------", PHP_EOL;
            } else {
                //游记状态变化(lmm_travels.tr_travel表数据变动)
                foreach ($tmp_data as $key => $row) {
                    if ($key == 'status' && $row['updated']) {
                        $travel_id = $tmp_data['id']['value'];
                        $redis_key = str_replace('{travel_id}', $travel_id, RedisDataService::REDIS_TRAVEL_LIST_DATA);

                        //游记状态变更为审核通过并显示时，将该游记列表数据加入 REDIS
                        if ($row['value']) {
                            $travel_data = $this->getTravelData($travel_id);
                            $travel_content_data = $this->getTravelContentData($travel_id);
                            $travel_tag_data = $this->getTravelTagData($travel_id);

                            $redis_data = array();
                            if ($travel_data['list'])
                            $redis_data = $travel_data['list']['0'];

                            $redis_data['trace'] = '';

                            if ($travel_content_data['list']) {
                                $temp = array();
                                foreach ($travel_content_data['list'] as $key => $content_data)
                                    if (is_array($content_data))
                                        $temp[] = $content_data["title"];
                                    elseif($key == 'title')
                                        $temp[] = $content_data;
                                $redis_data['trace'] = implode(',',$temp);
                            }
                            $redis_data['bonus'] = $this->getTravelBonus($travel_id);
                            $redis_data['tags'] = isset($travel_tag_data[$travel_id]) ? implode(',',$travel_tag_data[$travel_id]) : '';
                            $redis_data["username"]=UCommon::maskMobile($redis_data["username"]);

                            $this->setRedisData($redis_key, $redis_data);

                            $this->updateTravelId2DestRedis($travel_id, 'add');

                            echo "update travel redis", PHP_EOL , "----------  END  ----------", PHP_EOL;
                        } else { //变更为其他状态时，删除REDIS中该游记数据
                            $this->delRedisKey($redis_key);
                            $this->updateTravelId2DestRedis($travel_id, 'del');

                            echo "delete travel redis", PHP_EOL , "----------  END  ----------", PHP_EOL;
                        }
                    }
                }
            }

            echo "----------  END  ----------", PHP_EOL,PHP_EOL;
        }
    }

    /**
     * 获取游记主表数据
     * @param array $where_condition
     * @param string $limit
     * @return mixed
     */
    private function getTravelData($travel_id)
    {
        $where = array(
            'id' => $travel_id,
        );

        return $this->traveldatasvc->select(array(
            'table' => 'travel',
            'select' => '*',
            'where' => $where,
        ));
    }

    /**
     * 获取游记内容表数据
     * @param $trip_id_str
     * @return mixed
     */
    private function getTravelContentData($travel_id)
    {
        return $this->traveldatasvc->select(array(
            'table' => 'travel_content',
            'select' => 'travel_id,title',
            'where' => array('travel_id' => $travel_id),
            'order' => 'order_num ASC',
        ));
    }

    /**
     * 获取游记收益
     * @param $travel_id
     * @return string
     */
    private function getTravelBonus($travel_id)
    {
        //获取游记收益
        $trip_bonus = $this->tripdatasvc->select(array(
            'table' => 'ly_bonus',
            'select' => 'sum(commission_amt) as amt',
            'where' => array('remit_status' => 99, 'type' => array('IN', "('order','page','admin','act_trip', 'hot_trip')"), 'trip_id' => $travel_id),
        ));
        //获取游记初始收益
        $init_bonus = $this->tripdatasvc->select(array(
            'table' => 'ly_trip_statistics',
            'select' => '`bonus_init`',
            'where' => array('type' => 'total', 'trip_id' => $travel_id),
        ));

        $trip_bonus = $trip_bonus['list']['0']['amt'] ? $trip_bonus['list']['0']['amt'] : '0.00';
        $init_bonus = $init_bonus['list']['0']['bonus_init'] ? $init_bonus['list']['0']['bonus_init'] : '0.00';
        return sprintf('%.2f',$trip_bonus + $init_bonus);
    }

    /**
     * 获取游记标签数据
     * @param $trip_id_str
     * @return array
     */
    private function getTravelTagData($travel_id)
    {
        $tag_item_data = $this->tripdatasvc->select(array(
            'table' => 'ly_tag_item',
            'select' => 'tag_id,object_id',
            'where' => array('object_type' => 'trip','object_id' => $travel_id),
        ));
        $tag_id_arr = $travel_tag_data = array();
        foreach ($tag_item_data['list'] as $item) {
            if(!in_array($item['tag_id'],$tag_id_arr))
                $tag_id_arr[] = $item['tag_id'];
        }
        if($tag_id_arr) {
            $tag_id_str = implode(',', $tag_id_arr);
            $tag_data = $this->tripdatasvc->select(array(
                'table' => 'ly_tag',
                'select' => 'tag_id,tag_name',
                'where' => array('status' => '99', 'tag_id' => array('IN', "({$tag_id_str})")),
            ));
            $tag_data = UCommon::parseItem($tag_data['list'], 'tag_id');
            foreach ($tag_item_data['list'] as $key => $item) {
                $travel_tag_data[$item['object_id']][] = $tag_data[$item['tag_id']]['tag_name'];
            }
        }
        return $travel_tag_data;
    }

    /**
     * 设置游记列表HASH
     * @param $redis_key
     * @param $data
     */
    private function setRedisData($redis_key, $data)
    {
        foreach ($data as $key => $value) {
            $this->redis->hset($redis_key, $key, $value);
        }
    }

    /**
     * 删除 REDIS 中的Key
     * @param $redis_key
     */
    private function delRedisKey($redis_key)
    {
        $this->redis->del($redis_key);
    }

    /**
     * 更新目的地ID与游记ID的关系
     * @param int $travel_id
     * @param string $action
     * @return array|bool
     */
    private function updateTravelId2DestRedis($travel_id = 0, $action = '')
    {
        if (!$travel_id)
            return array();
        $travel_dest = $this->getTravelDestByTravelId('travel_dest_rel', $travel_id);
        $travel_content_dest = $this->getTravelDestByTravelId('travel_content_dest_rel', $travel_id);

        $travel_point = $this->getTravelScore($travel_id);
        $dest_data = array_merge($travel_dest, $travel_content_dest);
        foreach ($dest_data as $dest_id) {
            $dest_redis_key = RedisDataService::REDIS_DEST_TRIP_IDS . $dest_id;
            switch ($action) {
                case 'add' :
                    $this->redis->zadd($dest_redis_key, $travel_point, $travel_id);
                    break;
                case 'del' :
                    $this->redis->zrem($dest_redis_key, $travel_id);
                    break;
                default:
                    return false;
            }
        }
    }

    /**
     * 查询数据库中游记关联的目的地
     * @param string $table 要查询的表
     * @param $travel_id
     * @return array
     */
    private function getTravelDestByTravelId($table = '', $travel_id)
    {
        $params = array(
            'table' => $table,
            'select' => 'dest_id',
            'where' => array('travel_id' => $travel_id),
        );
        $result = $this->traveldatasvc->select($params);

        $dest_data = array();
        if ($result['list']) {
            foreach ($result['list'] as $row)
                $dest_data[] = $row['dest_id'];
        }
        return $dest_data;
    }

    /**
     * 返回游记在集合中的 score 值
     * @param int $travel_id
     * @return int
     */
    private function getTravelScore($travel_id = 0)
    {
        $sql = "SELECT
                  a.`id`,
                  a.`publish_time`,
                  a.`recommend_status`,
                  b.`order_id`
                FROM
                  `tr_travel` a
                  LEFT JOIN `tr_travel_ext` b
                    ON a.`id` = b.`travel_id`
                WHERE a.`id` = '{$travel_id}'";
        $travel_status = $this->traveldatasvc->querySql($sql);
        $score = 0;
        if ($travel_status["list"]) {
            $row = $travel_status['list']['0'];
            $score = $row["publish_time"];
            if ($row["recommend_status"] == 2) {
                $score += 3000000000;
            } else {
                $score += 1000000000;
            }
            if ($row["order_id"] != 0) {
                $score += 1000000000;
            }
        }
        return $score;
    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function timeOut()
    {
        // TODO: Implement timeOut() method.
        echo 'time out!';
    }

}