<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author 洪武极
 *
 */
class TripRelationWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $dest_base;
    private $trip_svc;
    private $dest_rel;
    public function __construct($di) {
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->trip_svc =$di->get('cas')->get('trip-data-service');
        $this->dest_rel =$di->get('cas')->get('dest_relation_service');
        $this->dest_base->setReconnect(true);
        $this->trip_svc->setReconnect(true);
        $this->dest_rel->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        //获取所有trip_dest关联表数据
        $total=$this->trip_svc->getTotalBy(array(),'ly_trip_dest');
        echo $total;exit;
        $total_page=ceil($total/100);
        $this->excuteTripDest(1,100,$total_page);

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    public function excuteTripDest($page_num=1,$page_size=100,$total_page){
        if($page_num<=$total_page){
            $result=$this->trip_svc->getTripDestList(array(),array('page_num'=>$page_num,'page_size'=>$page_size));
            if($result){
                foreach($result as $key=>$row){
                    $parent_ids=$this->dest_base->getDestParents($row['dest_id']);
                    if($parent_ids){
                        array_push($parent_ids,$row['dest_id']);
                    }else{
                        $parent_ids[]=$row['dest_id'];
                    }
                    foreach($parent_ids as $dest_id){
                            $sql="SELECT * FROM relation_dest_trip WHERE dest_id={$dest_id} AND trip_id={$row['trip_id']}";
                            $is_null=$this->dest_base->query($sql);
                            if(!$is_null){
                                $insert_data=array(
                                    'dest_id'=>$dest_id,
                                    'trip_id'=>$row['trip_id']
                                );
                                $this->dest_rel->insert($insert_data,'relation_dest_trip');
                                unset($insert_data);
                            }
                    }
                }
                unset($result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteTripDest($current_page, $page_size, $total_page);
            }
        }else{
            echo 'job done!';
            exit;
        }
    }
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function processTrace($timestamp = null, $flag = null) {
        //获取所有trip_dest关联表数据
        $total=$this->trip_svc->getTotalBy(array('dest_id'=>" != 0"),'ly_trace');
        $total_page=ceil($total/100);
        $this->excuteTraceDest(1,100,$total_page);
    }
    private function excuteTraceDest($page_num=1,$page_size=100,$total_page){
        if($page_num<=$total_page){
            $result=$this->trip_svc->getTraceList(array('page_num'=>$page_num,'page_size'=>$page_size));
            if($result){
                foreach($result as $key=>$row){
                    $parent_ids=$this->dest_base->getDestParents($row['dest_id']);
                    if($parent_ids){
                        array_push($parent_ids,$row['dest_id']);
                    }else{
                        $parent_ids[]=$row['dest_id'];
                    }
                    foreach($parent_ids as $dest_id){
                        $sql="SELECT * FROM relation_dest_trip WHERE dest_id={$dest_id} AND trip_id={$row['trip_id']}";
                        $is_null=$this->dest_base->query($sql);
                        if(!$is_null){
                            $insert_data=array(
                                'dest_id'=>$dest_id,
                                'trip_id'=>$row['trip_id']
                            );
                            $this->dest_rel->insert($insert_data,'relation_dest_trip');
                            unset($insert_data);
                        }
                    }
                }
                unset($result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteTraceDest($current_page, $page_size, $total_page);
            }
        }else{
            echo 'job done!';
            exit;
        }
    }
}