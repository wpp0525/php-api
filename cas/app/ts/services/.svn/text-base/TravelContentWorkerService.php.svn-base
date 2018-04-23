<?php

use Lvmama\Cas\Component\DaemonServiceInterface;

class TravelContentWorkerService implements DaemonServiceInterface{

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
        $insert_data = array(
            'travel_content' => array(
                'id' => $row['trace_id'],
                'travel_id' => $row['trip_id'],
                'title' => $row['trace_name'],
                'content' => $this->getContent($row['trace_id']),
                'order_num' => abs($row['seq']),
                'create_time' => $row['create_time'],
                'update_time' => $row['create_time'],
            ),
            'travel_content_dest_rel' => array(
                'travel_content_id' => $row['trace_id'],
                'dest_id' => $row['dest_id'],
                'dest_type' => $row['dest_type'],
                'travel_id' => $row['trip_id'],
                'is_main' => '1',
                'create_time' => $row['create_time'],
                'update_time' => $row['create_time'],
            ),
        );
        return $insert_data;
    }

    /**
     * 返回游记内容表中的内容
     * @param $trace_id
     * @return string
     */
    private function getContent($trace_id){
        $segment_id = $this->tripdatasvc->select(array(
            'table' => 'ly_segment',
            'select' => '`segment_id`,`type`',
            'where' => array('trace_id' => $trace_id,'deleted' => 'N'),
        ));
        if(empty($segment_id['list']))
            return '';
        $text_arr = $picture_arr = array();
        foreach($this->getRows($segment_id['list']) as $row){
            switch($row['type']){
                case 'PICTURE':
                    $picture = $this->tripdatasvc->select(array(
                        'table' => 'ly_s_picture',
                        'select' => '`img_url`',
                        'where' => array('segment_id' => $row['segment_id']),
                    ));
                    $picture_arr[] = '<img src=http://pic.lvmama.com/' . $picture['list']['0']['img_url'] . ' />';
                    break;
                case 'TEXT':
                    $text = $this->tripdatasvc->select(array(
                        'table' => 'ly_s_text',
                        'select' => '`memo`',
                        'where' => array('segment_id' => $row['segment_id']),
                    ));
                    $text_arr[] = $text['list']['0']['memo'];
                    break;
                default:
                    break;
            }
        }
        return implode('<br />',$text_arr) . implode('',$picture_arr);
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
        $data = $this->tripdatasvc->select(array(
            'table' => 'ly_trace',
            'select' => '`trace_id`,`trace_name`,`dest_type`,`trip_id`,`seq`,`create_time`,`dest_id`',
            'where' => $where_condition,
            'limit' => $limit,
        ));
        if(empty($data['list']))
            die('done');

        foreach ($this->getRows($data['list']) as $row) {
            $insert_data = $this->getInsertData($row);
            if(!$insert_data) continue;
            $travel_content_insert_data = array('table' => 'travel_content', 'data' => $insert_data['travel_content']);
            $res = $this->insertOrUpdate($insert_data['travel_content'],array('table' => 'travel_content','where' => array('id' => $row['trace_id'])));
            $travel_content_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_content_res = $this->traveldatasvc->insert($travel_content_insert_data);
                    break;
                case 'update':
                    $travel_content_insert_data['where'] = "id = {$row['trace_id']}";
                    $travel_content_res = $this->traveldatasvc->update($travel_content_insert_data);
                    break;
            }
            if ($travel_content_res['error'])
                continue;

            $travel_content_dest_rel_insert_data = array('table' => 'travel_content_dest_rel', 'data' => $insert_data['travel_content_dest_rel']);
            $res = $this->insertOrUpdate($insert_data['travel_content_dest_rel'],array('table' => 'travel_content_dest_rel','where' => array('travel_content_id' => $row['trace_id'])));
            $travel_content_rel_res = array('error' => '0');
            switch($res){
                case 'insert':
                    $travel_content_rel_res = $this->traveldatasvc->insert($travel_content_dest_rel_insert_data);
                    break;
                case 'update':
                    $travel_content_dest_rel_insert_data['where'] = "travel_content_id = {$row['trace_id']}";
                    $travel_content_rel_res = $this->traveldatasvc->update($travel_content_dest_rel_insert_data);
                    break;
            }
            if ($travel_content_rel_res['error'])
                continue;

            unset($insert_data);
            unset($travel_content_insert_data);
            unset($travel_content_dest_rel_insert_data);
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