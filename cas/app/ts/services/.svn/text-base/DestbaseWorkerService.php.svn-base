<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\DestinationDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author 洪武极
 *
 */
class DestbaseWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $old_data;
    private $dest_base;
    public function __construct($di) {
        $this->old_data =  $di->get('cas')->get('dest_old_service');
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->old_data->setReconnect(true);
        $this->dest_base->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $total=$this->old_data->getTotalBy(array('dest_name'=>"!=''",'dest_type'=>"!='HOTEL'"),'ly_destination');
        $total_page=ceil($total/300);
        $this->excuteData(1,300,$total_page);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    private function excuteData($page_num=1,$page_size=300,$total_page)
    {
        if ($page_num <= $total_page) {
            $result = $this->old_data->getList(array('dest_name'=>"!=''",'dest_type'=>"!='HOTEL'"),'ly_destination',array('page_num' => $page_num, 'page_size' => $page_size));
            if ($result) {
                foreach ($result as $key => $value) {
                    $insert_data = array(
                        'dest_id' => $value['dest_id'],
                        'dest_name' => $value['dest_name'],
                        'parent_id' => $value['parent_id'],
                        'district_id' => $value['district_id'],
                        'dest_type' => $value['dest_type'],
                        'district_parent_id' => $value['district_parent_id'],
                        'cancel_flag' => ($value['cancel_flag'] == 'Y') ? 1 : 0,
                        'stage' => $value['stage'],
                        'showed' => ($value['showed'] == 'Y') ? 1 : 0,
                        'range'=>$value['range'],
                        'ent_sight'=>($value['ent_sight'] == 'Y') ? 1 : 0,

                    );
                    $this->dest_base->insert($insert_data);
                    unset($insert_data);
                }
                unset($result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteData($current_page, $page_size, $total_page);
            }
        } else {
            echo 'job done!';
            exit;
        }
    }
}