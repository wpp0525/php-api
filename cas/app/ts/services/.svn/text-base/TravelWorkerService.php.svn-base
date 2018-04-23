<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

class TravelWorkerService implements DaemonServiceInterface{

    private $traveldatasvc;
    
    private $tripdatasvc;
	
	/**
	 * @var RedisAdapter
	 */
	private $redis;
	
	/**
	 * @var BeanstalkAdapter
	 */
	private $beanstalk;

    private $flag_id;


    public function __construct($di) {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->tripdatasvc = $di->get('cas')->get('trip-data-service');
        $this->tripdatasvc->setReconnect(true);
        
        $this->trTravelDS = $di->get('cas')->get('tr-travel-data-service');
        $this->trTravelDS->setReconnect(true);
        
        $this->trTravelContentDS = $di->get('cas')->get('tr-travel-content-data-service');
        $this->trTravelContentDS->setReconnect(true);
        
		$this->redis = $di->get('cas')->getRedis();
		
		$this->beanstalk = $di->get('cas')->getBeanstalk();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $this->flag_id = $flag;
        $this->insertData();
    }
	
	/**
	 * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
	 */
	public function processContent2dest($timestamp = null, $flag = null) {

            if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_DEST)->ignore('default')->reserve()) {
                try {
                    if ($job_data = json_decode($job->getData(), true)) {
                        if ($job_data['id']) {
                            $contents = $this->trTravelContentDS->getContentByTravelid($job_data['id']);

                            foreach ($contents as $content) {
                                $res = $this->trTravelContentDS->getDestByContent($content['content']);

                                $keys = array('{travelid}', '{id}');
                                $values = array($job_data['id'], $content['id']);
                                $rkey = str_replace($keys, $values, RedisDataService::REDIS_TRAVEL_CONTENT_RECOMMEND_DEST);

                                foreach ($res as $value) {
                                    $this->redis->zAdd($rkey, 1, $value);
                                }
                            }
                        }
                    }
                    unset($job_data);
// 				$this->beanstalk->delete($job);
                } catch (\Exception $ex) {
                    echo $ex->getMessage() . ";" . $ex->getTraceAsString() . "\r\n";
                }
                $this->beanstalk->delete($job);
            }
            unset($job);
	}

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    /**
     * 区分每张表的插入数据
     * @param array $data
     * @return array
     */
    private function getInsertData(array $row){
        $main_status = $this->getTravelExtMainStatus($row);
        $delete_status = $this->getTravelExtDeleteStatus($row);
        $travel_status = 0;
        if($delete_status == 0 && $main_status == 4)
            $travel_status = 1;
        $insert_data = array(
            'travel' => array(
                'id' => $row['trip_id'],
                'uid' => $row['uid'],
                'username' => $row['username'],
                'title' => $row['title'],
                'seo_title' => $row['seo_title'],
                'summary' => $row['memo'],
                'thumb' => $row['thumb'],
                'start_time' => date('n',$row['visit_time']),
                'publish_time' => $row['publish_time'],
                'order_num' => $this->getOrderNumBySeq($row['seq']),
                'losc_inner' => $row['losc_in'],
                'losc_outer' => $row['losc_out'],
                'status' => $travel_status,
                'recommend_status' => $row['elite'] == 'Y' ? '2' : '1',
                'create_time' => $row['create_time'],
                'update_time' => $row['modify_time'],
            ),
            'travel_ext' => array(
                'travel_id' => $row['trip_id'],
                'order_id' => $row['order_id'],
                'product_id' => $row['product_id'],
                'source' => $this->getTravelExtSource($row),
                'platform' => $this->getTravelSource($row['source']),
                'device_no' => 0,
                'port' => 0,
                'commit_time' => in_array($main_status,array(3,4)) ? $row['modify_time'] : 0,
                'main_status' => $main_status,
                'del_status' => $delete_status,
                'fanli_status' => $row['order_status'] == 'Y' ? 1 : 0,
                'create_time' => $row['create_time'],
                'update_time' => $row['modify_time'],
            ),
        );
        return $insert_data;
    }

    /**
     * 返回游记扩展表中的来源状态
     * @param array $row
     * @return string
     */
    private function getTravelExtSource(array $row){
        if(strtoupper($row['copy']) == 'Y')
            return '2';
        if(strtoupper($row['source']) == 'ADMIN')
            return '1';
        return '0';
    }

    /**
     * 返回游记扩展表中的删除状态
     * @param $arr
     * @return int
     */
    private function getTravelExtDeleteStatus($arr){
        if(isset($arr['user_status']) && $arr['user_status'] == 2)
            return 1;
        if(isset($arr['deleted']) && $arr['deleted'] == 'Y')
            return 2;
        if(isset($arr['user_status']) && $arr['user_status'] != 2 && isset($arr['deleted']) && $arr['deleted'] == 'N')
            return 0;
        return 44;
    }

    /**
     * 返回游记扩展表中的主状态
     * @param $arr
     * @return int
     */
    private function getTravelExtMainStatus($arr){
        if((isset($arr['user_status']) && $arr['user_status'] == 1) || isset($arr['finished']) && $arr['finished'] == 'N')
            return 0;
        if(isset($arr['verify']) && $arr['verify'] == 1)
            return 1;
        if(isset($arr['verify']) && $arr['verify'] == 2)
            return 2;
        if(isset($arr['verify']) && $arr['verify'] == 99 && isset($arr['showed']) && $arr['showed'] == 'N')
            return 3;
        if(isset($arr['verify']) && $arr['verify'] == 99 && isset($arr['showed']) && $arr['showed'] == 'Y')
            return 4;
        return 44;
    }

    /**
     * 返回游记平台
     * @param $source
     * @return string
     */
    private function getTravelSource($source){
        switch(strtolower($source)){
            case 'ios':
                return '1';
            case 'android':
                return '2';
            default:
                return '0';
        }
    }

    /**
     * 返回游记的排序值
     * @param $seq
     * @return int|number
     */
    private function getOrderNumBySeq($seq){
        $res = 1;
        switch(true){
            case ($seq < 0):
                $res = abs($seq) + 1;
                break;
            case ($seq > 0):
                $res = 0;
                break;
            default:
                break;
        }
        return $res;
    }

    /**
     * 数据插入
     * @param int $start
     */
    private function insertData($start = 1)
    {
        $offset = (max(1,$start) - 1) * 100;
        $limit = $offset . ',100';
        $where_condition = array();
        if($this->flag_id){
            $where_condition = array(
                'trip_id' => array('>=',$this->flag_id),
            );
        }
        $trip_data = $this->tripdatasvc->select(array(
            'table' => 'ly_trip',
            'select' => '*',
            'where' => $where_condition,
            'order' => '',
            'group' => '',
            'limit' => $limit,
        ));
        if(empty($trip_data['list']))
            die('done');

        foreach ($this->getRows($trip_data['list']) as $row) {
            $insert_data = $this->getInsertData($row);
            if(!$insert_data) continue;
            $travel_insert_data = array('table' => 'travel', 'data' => $insert_data['travel']);
            $res = $this->insertOrUpdate($insert_data['travel'],array('table' => 'travel','where' => array('id' => $row['trip_id'])));
            $travel_res = array('error' => '0');

            switch($res){
                case 'insert':
                    $travel_res = $this->traveldatasvc->insert($travel_insert_data);
                    break;
                case 'update':
                    $travel_insert_data['where'] = "id = {$row['trip_id']}";
                    $travel_res = $this->traveldatasvc->update($travel_insert_data);
                    break;
            }
            if ($travel_res['error'])
                continue;

            $travel_ext_insert_data = array('table' => 'travel_ext', 'data' => $insert_data['travel_ext']);
            $res = $this->insertOrUpdate($insert_data['travel_ext'],array('table' => 'travel_ext','where' => array('travel_id' => $row['trip_id'])));
            $travel_ext_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_ext_res = $this->traveldatasvc->insert($travel_ext_insert_data);
                    break;
                case 'update':
                    $travel_ext_insert_data['where'] = "travel_id = {$row['trip_id']}";
                    $travel_ext_res = $this->traveldatasvc->update($travel_ext_insert_data);
                    break;
            }
            if ($travel_ext_res['error'])
                continue;

            unset($insert_data);
            unset($travel_insert_data);
            unset($travel_ext_insert_data);
        }

        unset($trip_data);

        sleep(3);
        $start++;
        $this->insertData($start);
    }

    /**
     * 判断要插入的数据是否已存在于数据库中且值是否相等
     * @param array $data
     * @param array $params
     * @return string
     */
    private function insertOrUpdate(array $data,array $params){
        $select_data = $this->traveldatasvc->select(array(
            'table' => $params['table'],
            'select' => '*',
            'where' => $params['where'],
            'limit' => 1,
        ));
        if(empty($select_data['list'])) return 'insert';
        foreach($data as $key => $value){
            if(!isset($select_data['list']['0'][$key]) || $select_data['list']['0'][$key] != $value)
                return 'update';
        }
        return 'continue';
    }

    /**
     * 生成器
     * @param array $data
     * @return Generator
     */
    private function getRows(array $data){
        foreach ($data as $item) {
            yield $item;
        }
    }
}