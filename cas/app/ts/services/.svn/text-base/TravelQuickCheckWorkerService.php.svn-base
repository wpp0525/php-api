<?php

use Lvmama\Cas\Component\DaemonServiceInterface,
    Lvmama\Cas\Service\BeanstalkDataService;

class TravelQuickCheckWorkerService implements DaemonServiceInterface
{

    private $traveldatasvc;
    private $configuredatasvc;
    private $redis;
    private $beanstalk;
    private $config_id_arr;
    private $flag_id;

    public function __construct($di)
    {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->tripdatasvc = $di->get('cas')->get('trip-data-service');
        $this->tripdatasvc->setReconnect(true);

        $this->configuredatasvc = $di->get('cas')->get('configure-data-service');
        $this->configuredatasvc->setReconnect(true);

        $this->redis = $di->get('cas')->getRedis();

        $this->beanstalk = $di->get('cas')->getBeanstalk();

        $this->config_id_arr = $this->getQuickCheckIdsByConfig();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null, $from = null)
    {
        $this->flag_id = $flag;
        switch ($from) {
            case 'database':
                $this->updateTravelMainStatusFromDatabase();
                break;
            case 'beanstalk':
                $this->updateTravelMainStatusFromBeanstalk();
                break;
            default:
                break;
        }

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null)
    {
        // nothing to do
    }

    /**
     *  更新数据库中符合条件的游记的状态
     */
    private function updateTravelMainStatusFromDatabase()
    {
        $where_condition = "tr.`id` > '90338'";
        if ($this->flag_id)
            $where_condition = "tr.`id` >= $this->flag_id";

        $sql = "SELECT tr.`id`,tr.`uid` FROM `tr_travel` tr LEFT JOIN `tr_travel_ext` tre ON tr.`id` = tre.`travel_id` WHERE {$where_condition} AND tre.`main_status` = '1' AND tre.`del_status` = '0'";
        $data = $this->traveldatasvc->querySql($sql);

        if (empty($data['list']))
            die('not data');

        foreach ($this->getRow($data['list']) as $row) {
            if ($this->isValid($row['id'], $row['uid'])) {
                $this->updateTravelThumb($row['id']);
                $this->updateTravelMainStatus($row['id']);
            }
        }
        unset($data);

        die('done');
    }

    /**
     * 更新 Beanstalk 中符合条件的游记的状态
     *
     */
    private function updateTravelMainStatusFromBeanstalk()
    {
        $curr_job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRAVEL_QUICK_CHECK_LIST)->ignore('default')->reserve();
        if ($curr_job) {
            try {
                $job_data = json_decode($curr_job->getData(), true);
                if ($job_data) {
                    if ($this->isValid($job_data['id'], $job_data['uid'])) {
                        $this->updateTravelThumb($job_data['id']);
                        $this->updateTravelMainStatus($job_data['id']);
                    }
                }
                $this->beanstalk->delete($curr_job);
                unset($job_data);
            } catch (\Exception $ex) {
                echo $ex->getMessage() . ',' . $ex->getTraceAsString() . '\r\n';
            }
        }
        unset($curr_job);
    }

    /**
     * 从CMS后台获取配置的快速审核用户ID
     * @return array
     */
    private function getQuickCheckIdsByConfig()
    {
        $params = array(
            'columns' => '`value`',
            'where' => "`object_type` = 'trip' AND `key` = 'quick_check_ids'",
        );
        $result = $this->configuredatasvc->getRsBySql($params);
        return !empty($result) ? explode(',', $result['0']['value']) : array();
    }

    /**
     * 判断游记是否符合快速审核条件
     * @param int $travel_id
     * @param int $uid
     * @return bool
     */
    private function isValid($travel_id = 0, $uid = 0)
    {
        if (!$travel_id || !$uid)
            return false;

        if ($this->isBindOrder($travel_id))
            return false;

        $travel_num = $this->getValidTravelNumByUid($uid);

        $has_sensitive = $this->hasSensitive($travel_id);

        if ($has_sensitive)
            return false;
        if (in_array($uid, $this->config_id_arr))
            return true;
        if ($travel_num >= 2)
            return true;
        return false;
    }

    /**
     * 返回用户的通过并显示的游记数
     * @param $uid
     * @return int
     */
    private function getValidTravelNumByUid($uid)
    {
        $new_travel_count_sql = "SELECT COUNT(*) AS count FROM `tr_travel` tr LEFT JOIN `tr_travel_ext` tre ON `tr`.`id` = `tre`.`travel_id` WHERE `tr`.`id` > '90338' AND tre.`main_status` = '4' AND tre.`del_status` = '0' AND tr.`uid` = '{$uid}'";
        $new_travel_count_res = $this->traveldatasvc->querySql($new_travel_count_sql);
        $new_travel_count = $new_travel_count_res['list']['0']['count'];
        if ($new_travel_count >= 2)
            return $new_travel_count;

        $params = array(
            'table' => 'ly_trip',
            'select' => 'COUNT(*) AS count',
            'where' => array('deleted' => 'N', 'source' => array('!=', 'ADMIN'), 'verify' => '99', 'finished' => 'Y', 'user_status' => '99', 'uid' => $uid),
        );
        $old_travel_count_res = $this->tripdatasvc->select($params);
        $old_travel_count = $old_travel_count_res['list']['0']['count'];

        return intval($new_travel_count) + intval($old_travel_count);
    }

    /**
     * 判断章节内容是否有敏感词
     * @param $travel_id
     * @return bool
     */
    private function hasSensitive($travel_id)
    {
        $params = array(
            'table' => 'travel_content',
            'select' => 'id',
            'where' => array('travel_id' => $travel_id),
        );
        $travel_content_res = $this->traveldatasvc->select($params);
        if (empty($travel_content_res['list']))
            return true;
        foreach ($this->getRow($travel_content_res['list']) as $row) {
            $redis_data = $this->redis->hgetall("tr:travel:" . $travel_id . ":content:" . $row['id']);
            $sensitive_array = json_decode($redis_data['sensitiveWord']);
            if (!empty($sensitive_array))
                return true;
        }
        return false;
    }

    /**
     * 判断游记是否关联了订单
     * @param int $travel_id
     * @return bool
     */
    private function isBindOrder($travel_id = 0)
    {
        $params = array(
            'table' => 'travel_ext',
            'select' => 'order_id',
            'where' => array('travel_id' => $travel_id),
        );
        $travel_ext_res = $this->traveldatasvc->select($params);
        if (!empty($travel_ext_res['list']) && $travel_ext_res['list']['0']['order_id'])
            return true;
        return false;
    }

    /**
     * 更新游记状态
     * @param string $travel_id_str
     * @param int $main_status
     * @return bool
     */
    private function updateTravelMainStatus($travel_id_str = '', $main_status = '5')
    {
        $params = array(
            'table' => 'travel_ext',
            'where' => "`travel_id` IN ('{$travel_id_str}')",
            'data' => array('main_status' => $main_status),
        );
        $res = $this->traveldatasvc->update($params);
        if (isset($res['error']) && !$res['error'])
            return true;
        return false;
    }

    /**
     * 更新游记封面图
     * 取第一个章节的第一张图片作为封面图。如果第一个章节中无图，则将默认图片作为封面图
     * @param int $travel_id
     */
    private function updateTravelThumb($travel_id = 0)
    {
        //查找第一个章节
        $params = array(
            'table' => 'travel_content',
            'select' => 'content',
            'where' => array('travel_id' => $travel_id),
            'order' => 'id ASC',
            'limit' => 1,
        );
        $travel_content_res = $this->traveldatasvc->select($params);
        $travel_content = $travel_content_res['list']['0']['content'] ? $travel_content_res['list']['0']['content'] : 0;

        $patt = "/[.*]?data-link=[\\'\"]?([^\\'\"]*)[\\'\"]?.*?/i";
        preg_match($patt, $travel_content, $res);

        $default_image_url = '/img/cmt/img_120_60.jpg';
        $image_url = '';

        $image_id = $res['1'] ? explode('-', $res['1'])['1'] : '';

        if ($image_id) {
            $params = array(
                'table' => 'image',
                'select' => 'url',
                'where' => array('id' => $image_id),
            );
            $image_res = $this->traveldatasvc->select($params);
            $image_url = $image_res['list']['0']['url'];
        }

        if (!$image_url)
            $image_url = $default_image_url;

        $params = array(
            'table' => 'travel',
            'where' => "`id` = '{$travel_id}'",
            'data' => array('thumb' => $image_url),
        );
        $this->traveldatasvc->update($params);
    }

    /**
     * 生成器
     * @param array $data
     * @return Generator
     */
    private function getRow(array $data)
    {
        foreach ($data as $item) {
            yield $item;
        }
    }
}