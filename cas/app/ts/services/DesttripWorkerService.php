<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地关联数据 Worker服务类
 *
 * @author 洪武极
 *
 */
class DesttripWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $old_data;
    private $svc;
    public function __construct($di) {
        $this->old_data =  $di->get('cas')->get('destination-data-service');
        $this->svc = $di->get('cas')->get('dest_trips_rel_service');
        $this->svc->setReconnect(true);
        $this->old_data->setReconnect(true);
    }
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    public function process ($timestamp = null, $flag = null){
        $total=$this->old_data->getTotalBy(array('dest_type'=>"!='HOTEL'"),'ly_destination');
        $totol_page=ceil($total/100);
        $this->excuteData(1,100,$totol_page);
    }
    private function excuteData($page_num=1,$page_size=100,$total_page)
    {

        if ($page_num <= $total_page) {
            $result = $this->old_data->getList(array('dest_type'=>"!='HOTEL'"),'ly_destination',array('page_num' => $page_num, 'page_size' => $page_size),'dest_id,dest_type,parents,stage',' dest_id ASC');
            if($result){
                foreach($result as $key=>$row){
                    if($row['dest_type']=='') {
                        continue;
                    }else{
                        switch($row['dest_type']){
                            case 'COUNTRY':
                                $dest_type="CITY";
                                break;
                            case 'SPAN_COUNTRY':
                                $dest_type="CITY";
                                break;
                            case 'SPAN_PROVINCE':
                                $dest_type="CITY";
                                break;
                            case 'PROVINCE':
                                $dest_type="CITY";
                                break;
                            case 'SPAN_CITY':
                                $dest_type="CITY";
                                break;
                            case 'CITY':
                                $dest_type='COUNTY';
                                break;
                            case 'SPAN_CITY':
                                $dest_type='COUNTY';
                                break;
                            case 'COUNTY':
                                $dest_type='TOWN';
                                break;
                            case 'TOWN':
                                $dest_type='self';
                                break;
                            case 'SCENIC':
                                $dest_type='self';
                                break;
                            case 'SPAN_TOWN':
                                $dest_type='self';
                                break;
                        }
                        if($row['stage']!=1){
                            $dest_type='self';
                        }
                        if($dest_type!='self'){
                            $insert_data=array();
                            $where_condition=array('parents'=>" like '".$row['parents'].",%'",'dest_type'=>" = '".$dest_type."' ");
                            $have_dest= $this->old_data->getList($where_condition,'ly_destination',null,'dest_id');
                            if($have_dest){
                                //临时跑丢失的数据的办法
                                continue;
                                //临时跑丢失的数据的办法
                                foreach($have_dest as $item){
                                    if($item['dest_id']){
                                        $insert_data[]="(".$row['dest_id'].",".$item['dest_id'].")";
                                    }
                                }
                                $insert_data=array_merge($insert_data,array('('.$row['dest_id'].','.$row['dest_id'].')'));
                                $insert_sql="INSERT INTO `lmm_destination`.`dest_have_trips` (`direct_dest_id`,`have_dest_id`) VALUES ".implode(',',$insert_data);
                                $res=$this->svc->query($insert_sql);
                                if($res!='success'){
                                    echo 'check your code,there maybe something wrong~';
                                    exit;
                                }
                            }
                            else{
                                $insert_sql="INSERT INTO `lmm_destination`.`dest_have_trips` (`direct_dest_id`,`have_dest_id`) VALUE (".$row['dest_id'].",".$row['dest_id'].")";
                                $res=$this->svc->query($insert_sql);
                                if($res!='success'){
                                    echo 'check your code,there maybe something wrong~';
                                    exit;
                                }
                            }
                        }else{
                            //临时跑丢失的数据的办法
                            continue;
                            //临时跑丢失的数据的办法
                            $insert_sql="INSERT INTO `lmm_destination`.`dest_have_trips` (`direct_dest_id`,`have_dest_id`) VALUE (".$row['dest_id'].",".$row['dest_id'].")";
                            $res=$this->svc->query($insert_sql);
                            if($res!='success'){
                                echo 'check your code,there maybe something wrong~';
                                exit;
                            }
                        }
                    }

                }
            }
            unset($result);
            $current_page = $page_num + 1;
            sleep(5);
            $this->excuteData($current_page, $page_size, $total_page);
        } else {
            echo 'job done!';
            exit;
        }
    }
}