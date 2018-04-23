<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class TravelDestRelationWorkerService implements DaemonServiceInterface{

    private $traveldatasvc;
    private $tripdatasvc;
    private $trip_main_dest;


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
        $this->getMainDest();
        $this->insertData();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    /**
     * 生成主目的地数据
     */
    private function getMainDest(){
        $dest_data = $this->tripdatasvc->select(array(
            'table' => 'ly_trip_dest',
            'select' => '`trip_id`,`dest_id`',
            'where' => array(),
            'order' => '`seq` DESC,`id` ASC',
            'limit' => '',
        ));
        foreach ($this->getRows($dest_data['list']) as $row) {
            if(in_array($row['trip_id'],$this->trip_main_dest))
                continue;
            $this->trip_main_dest[$row['trip_id']] = $row['dest_id'];
        }
    }

    /**
     * 区分每张表的插入数据
     * @param array $data
     * @return array
     */
    private function getInsertData(array $row){
        $row['create_time'] = time();
        $is_main = 0;
        if($row['dest_id'] == $this->trip_main_dest[$row['trip_id']])
            $is_main = 1;
        $insert_data = array(
            'travel_dest_rel' => array(
                'dest_id' => $row['dest_id'],
                'travel_id' => $row['trip_id'],
                'is_main' => $is_main,
                'create_time' => $row['create_time'],
                'update_time' => $row['create_time'],
            ),
        );
        return $insert_data;
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
        $data = $this->tripdatasvc->select(array(
            'table' => 'ly_trip_dest',
            'select' => '`trip_id`,`dest_id`',
            'where' => $where_condition,
            'limit' => $limit,
        ));
        if(empty($data['list']))
            die('done');

        foreach ($this->getRows($data['list']) as $row) {
            $insert_data = $this->getInsertData($row);
            if(!$insert_data) continue;

            $travel_dest_rel_insert_data = array('table' => 'travel_dest_rel', 'data' => $insert_data['travel_dest_rel']);
            $res = $this->insertOrUpdate($insert_data['travel_dest_rel'],array('table' => 'travel_dest_rel','where' => array('travel_id' => $row['trip_id'],'dest_id' => $row['dest_id'])));
            $travel_content_rel_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_content_rel_res = $this->traveldatasvc->insert($travel_dest_rel_insert_data);
                    break;
            }
            if ($travel_content_rel_res['error'])
                continue;

            unset($insert_data);
            unset($travel_dest_rel_insert_data);
        }

        unset($data);

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
            'where' => $params['where']
        ));
        if(empty($select_data['list'])) return 'insert';
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