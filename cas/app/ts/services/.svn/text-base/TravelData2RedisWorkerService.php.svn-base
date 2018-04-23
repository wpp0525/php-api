<?php

use Lvmama\Cas\Component\DaemonServiceInterface,
    Lvmama\Common\Utils\UCommon,
    Lvmama\Cas\Service\RedisDataService;

class TravelData2RedisWorkerService implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $tripdatasvc;
    private $flag_id;
    private $redis;


    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->tripdatasvc = $di->get('cas')->get('trip-data-service');
        $this->tripdatasvc->setReconnect(true);

        $this->redis = $di->get('cas')->getRedis();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null)
    {
        $this->flag_id = $flag;
        $this->tripData2Redis();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    /**
     * 数据插入
     * @param int $start
     */
    private function tripData2Redis($start = 1)
    {
        $offset = (max(1, $start) - 1) * 100;
        $limit = $offset . ',100';
        $where_condition = array();
        if ($this->flag_id) {
            $where_condition = array(
                'id' => array('>=', $this->flag_id),
            );
        }

        $travel_data = $this->getTravelData($where_condition, $limit);

        if (empty($travel_data['list']))
            die('done');

        $travel_list = $travel_data['list'];
        $trip_id_str = '';
        foreach ($this->getRows($travel_list) as $row) {
            $trip_id_str .= "'" . $row['id'] . "',";
        }
        $trip_id_str = rtrim($trip_id_str, ',');

        $travel_content_data = $this->getTravelContentData($trip_id_str);

        $travel_content_data = UCommon::parseItem($travel_content_data["list"], "travel_id");

        $travel_tag_data = $this->getTravelTagData($trip_id_str);

        foreach ($this->getRows($travel_list) as $item) {
            $item['trace'] = '';

            $item['bonus'] = $this->getTravelBonus($item['id']);
            $item['tags'] = isset($travel_tag_data[$item['id']]) ? implode(',', $travel_tag_data[$item['id']]) : '';
            $item["username"] = UCommon::maskMobile($item["username"]);

            if ($travel_content_data[$item['id']]) {
                $temp = array();
                foreach ($travel_content_data[$item['id']] as $key => $row) {
                    if (is_array($row))
                        $temp[] = $row["title"];
                    elseif($key == 'title')
                        $temp[] = $row;
                }
                $item['trace'] = implode(',',$temp);
            }
            $redis_key = str_replace('{travel_id}', $item['id'], RedisDataService::REDIS_TRAVEL_LIST_DATA);
            $this->setRedisData($redis_key, $item);
        }

        unset($travel_data);
        unset($travel_list);
        unset($trip_id_str);
        unset($travel_content_data);
        unset($redis_key);

        sleep(3);
        $start++;
        $this->tripData2Redis($start);
    }


    /**
     * 获取游记主表数据
     * @param array $where_condition
     * @param string $limit
     * @return mixed
     */
    private function getTravelData($where_condition = array(), $limit = '')
    {
        $init_where = array(
            'status' => '1',
        );
        $where_condition = array_merge($where_condition, $init_where);

        return $this->traveldatasvc->select(array(
            'table' => 'travel',
            'select' => '*',
            'where' => $where_condition,
            'limit' => $limit,
        ));
    }

    /**
     * 获取游记内容表数据
     * @param $trip_id_str
     * @return mixed
     */
    private function getTravelContentData($trip_id_str)
    {
        return $this->traveldatasvc->select(array(
            'table' => 'travel_content',
            'select' => 'travel_id,title',
            'where' => array('travel_id' => array('IN', '(' . $trip_id_str . ')')),
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
            'where' => array('remit_status' => 99, 'type' => array('IN', "('order','page','admin')"), 'trip_id' => $travel_id),
        ));
        //获取游记初始收益
        $init_bonus = $this->tripdatasvc->select(array(
            'table' => 'ly_trip_statistics',
            'select' => '`bonus_init`',
            'where' => array('type' => 'total', 'trip_id' => $travel_id),
        ));

        $trip_bonus = $trip_bonus['list']['0']['amt'] ? $trip_bonus['list']['0']['amt'] : '0.00';
        $init_bonus = $init_bonus['list']['0']['bonus_init'] ? $init_bonus['list']['0']['bonus_init'] : '0.00';
        return sprintf('%.2f', $trip_bonus + $init_bonus);
    }

    /**
     * 获取游记标签数据
     * @param $trip_id_str
     * @return array
     */
    private function getTravelTagData($trip_id_str)
    {
        $tag_item_data = $this->tripdatasvc->select(array(
            'table' => 'ly_tag_item',
            'select' => 'tag_id,object_id',
            'where' => array('object_type' => 'trip', 'object_id' => array('IN', '(' . $trip_id_str . ')')),
        ));
        $tag_id_arr = $travel_tag_data = array();
        foreach ($tag_item_data['list'] as $item) {
            if (!in_array($item['tag_id'], $tag_id_arr))
                $tag_id_arr[] = $item['tag_id'];
        }
        if ($tag_id_arr) {
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
     * 生成器
     * @param array $data
     * @return Generator
     */
    private function getRows(array $data)
    {
        foreach ($data as $item) {
            yield $item;
        }
    }
}