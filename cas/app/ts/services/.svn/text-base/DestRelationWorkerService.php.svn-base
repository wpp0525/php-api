<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author 洪武极
 *
 */
class DestRelationWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $old_data;
    private $dest_base;
    private $dest_relation;
    public function __construct($di) {
        $this->old_data =  $di->get('cas')->get('dest_old_service');
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->dest_relation = $di->get('cas')->get('dest_relation_service');
        $this->dest_relation->setReconnect(true);
        $this->old_data->setReconnect(true);
        $this->dest_base->setReconnect(true);
    }
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    public function process ($timestamp = null, $flag = null,$dest_type=''){
        $table_name='relation_dest_'.$dest_type;
        if($dest_type=='viewspot'){
            $where_condition="dest_type='VIEWSPOT' OR ent_sight=1";
        }elseif($dest_type=='ent'){
            $where_condition="dest_type='SCENIC_ENTERTAINMENT' OR ent_sight=1 ";
            $table_name='relation_dest_entertainment';
        }elseif($dest_type=='special'){
            $where_condition="dest_type IN('SPAN_COUNTRY','SPAN_PROVINCE','SPAN_CITY','SPAN_COUNTY','SPAN_TOWN')";
        }else{
            $type=strtoupper($dest_type);
            $where_condition=array('dest_type'=>'='."'".$type."'");
        }
        $total=$this->dest_base->getTotalBy($where_condition,'dest_base');
        $total_page=ceil($total/100);
        $this->excuteData(1,100,$total_page,$where_condition,$table_name);
    }
    private function excuteData($page_num=1,$page_size=100,$total_page,$where_condition,$table_name)
    {
        if ($page_num <= $total_page) {
            $result = $this->dest_base->getList($where_condition,'dest_base',array('page_num' => $page_num, 'page_size' => $page_size));
            if ($result) {
                foreach ($result as $key => $value) {
                    $parents=$this->dest_base->getDestParents($value['dest_id']);
                    if($parents){
                        foreach($parents as $k=>$row){
                            $sql="select base_id from dest_base where dest_id=".$row;
                            $base_id=$this->dest_relation->query($sql);
                            if($base_id){
                                $insert_data=array(
                                    'pid'=>$base_id['base_id'],
                                    'cid'=>intval($value['base_id'])
                                );
                                $this->dest_relation->insert($insert_data,$table_name);
                                unset($insert_data);
                            }
                        }
                    }
                }
                unset($result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteData($current_page, $page_size, $total_page,$where_condition,$table_name);
            }
        } else {
            echo 'job done!';
            exit;
        }
    }
}