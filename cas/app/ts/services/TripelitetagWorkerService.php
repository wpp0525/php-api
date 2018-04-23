<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class TripelitetagWorkerService implements DaemonServiceInterface{

    private $tripdatasvc;
    private $flag_id;


    public function __construct($di) {
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
        $insert_data = array(
            'tag_item' => array(
                'tag_id' => '11',
                'object_type' => 'trip',
                'object_id' => $row['trip_id'],
            )
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
        if($this->flag_id){
            $where_condition = array(
                'trip_id' => array('>=',$this->flag_id),
            );
        }
        $trip_data = $this->tripdatasvc->select(array(
            'table' => 'ly_trip',
            'select' => 'trip_id,elite',
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
            $tag_item_data = array('table' => 'ly_tag_item', 'data' => $insert_data['tag_item']);
            $res = $this->insertOrDelete($row['elite'],array(
                'table' => 'ly_tag_item',
                'where' => array(
                    'tag_id' => '11',
                    'object_type' => 'trip',
                    'object_id' => $row['trip_id']
                )));
            $travel_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_res = $this->tripdatasvc->insertData($tag_item_data);
                    break;
                case 'delete':
                    $delete_condition = array(
                        'table' => 'ly_tag_item',
                        'where' => array(
                            'tag_id' => '11',
                            'object_type' => 'trip',
                            'object_id' => $row['trip_id'],
                        ));
                    $travel_res = $this->tripdatasvc->delete($delete_condition);
                    break;
            }
            if ($travel_res['error'])
                continue;

            unset($insert_data);
            unset($tag_item_data);
        }

        unset($trip_data);

        sleep(3);
        $start++;
        $this->insertData($start);
    }

    /**
     * 判断要插入的数据是否已存在于数据库中
     * @param array $data
     * @param array $params
     * @return string
     */
    private function insertOrDelete($elite,array $params){
        $select_data = $this->tripdatasvc->select(array(
            'table' => $params['table'],
            'select' => '*',
            'where' => $params['where']
        ));
        if(empty($select_data['list']) && $elite == 'Y') return 'insert';
        if(!empty($select_data['list']) && $elite == 'N') return 'delete';
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