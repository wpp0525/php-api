<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class TravelImageWorkerService implements DaemonServiceInterface{

    private $traveldatasvc;
    private $tripdatasvc;
    private $flag_id;

    public function __construct($di) {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);

        $this->tripdatasvc = $di->get('cas')->get('trip-data-service');
        $this->tripdatasvc->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $this->flag_id = $flag;
        $this->insertData();
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
        $segment_data = $this->getSegmentDataBySegmentId($row['segment_id']);
        if(!$segment_data) return false;
        $insert_data = array(
            'image' => array(
                'id' => $row['segment_id'],
                'dest_id' => $segment_data['dest_id'],
                'url' => $row['img_url'],
                'width' => $row['width'],
                'create_time' => $segment_data['create_time'],
                'update_time' => $segment_data['modify_time'],
            ),
            'travel_image_rel' => array(
                'travel_id' => $segment_data['trip_id'],
                'image_id' => $row['segment_id'],
                'create_time' => $segment_data['create_time'],
                'update_time' => $segment_data['modify_time'],
            ),
        );
        return $insert_data;
    }

    /**
     * 根据片段ID返回片段数据
     * @param $segment_id
     * @return mixed
     */
    private function getSegmentDataBySegmentId($segment_id){
        $segment_data = $this->tripdatasvc->select(array(
            'table' => 'ly_segment',
            'select' => '`trip_id`,`dest_id`,`create_time`,`modify_time`',
            'where' => array('segment_id' => $segment_id),
        ));
        return empty($segment_data['list']) ? false : $segment_data['list']['0'];
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
                'segment_id' => array('>=',$this->flag_id),
            );
        }
        $data = $this->tripdatasvc->select(array(
            'table' => 'ly_s_picture',
            'select' => '`segment_id`,`img_url`,`width`',
            'where' => $where_condition,
            'limit' => $limit,
        ));
        if(empty($data['list']))
            die('done');

        foreach ($data['list'] as $row) {
            $insert_data = $this->getInsertData($row);
            if(!$insert_data) continue;
            $travel_image_insert_data = array('table' => 'image', 'data' => $insert_data['image']);
            $res = $this->insertOrUpdate($insert_data['image'],array('table' => 'image','where' => array('id' => $row['segment_id'])));
            $travel_image_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_image_res = $this->traveldatasvc->insert($travel_image_insert_data);
                    break;
                case 'update':
                    $travel_image_insert_data['where'] = "id = {$row['segment_id']}";
                    $travel_image_res = $this->traveldatasvc->update($travel_image_insert_data);
                    break;
            }
            if ($travel_image_res['error'])
                continue;

            $travel_image_rel_insert_data = array('table' => 'travel_image_rel', 'data' => $insert_data['travel_image_rel']);
            $res = $this->insertOrUpdate($insert_data['travel_image_rel'],array('table' => 'travel_image_rel','where' => array('image_id' => $row['segment_id'])));
            $travel_image_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_image_res = $this->traveldatasvc->insert($travel_image_rel_insert_data);
                    break;
                case 'update':
                    $travel_image_rel_insert_data['where'] = "image_id = {$row['segment_id']}";
                    $travel_image_res = $this->traveldatasvc->update($travel_image_rel_insert_data);
                    break;
            }
            if ($travel_image_res['error'])
                continue;

            unset($insert_data);
            unset($travel_image_insert_data);
            unset($travel_image_rel_insert_data);
        }

        unset($data);

        sleep(2);
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
            'where' => $params['where']
        ));
        if(empty($select_data['list'])) return 'insert';
        foreach($data as $key => $value){
            if(!isset($select_data['list']['0'][$key]) || $select_data['list']['0'][$key] != $value)
                return 'update';
        }
        return 'continue';
    }

}