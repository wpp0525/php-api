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
class DestdatarepairWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $old_data;
    private $dest_base;
    private $dest_rel;
    private $dest_detail;
    public function __construct($di) {
        $this->old_data =  $di->get('cas')->get('destination-data-service');
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->dest_detail =$di->get('cas')->get('dest_detail_service');
        $this->dest_rel=$di->get('cas')->get('dest_relation_service');
        $this->dest_detail->setReconnect(true);
        $this->old_data->setReconnect(true);
        $this->dest_base->setReconnect(true);
        $this->dest_rel->setReconnect(true);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $total=$this->old_data->getTotalBy(array('dest_name'=>"!=''",'dest_type'=>"!='HOTEL'"),'ly_destination');
        $total_page=ceil($total/50);
        $this->excuteData(1,50,$total_page);
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }
    private function excuteData($page_num=1,$page_size=50,$total_page)
    {
        if($page_num<=$total_page){
            $sql="SELECT * FROM ly_destination WHERE dest_name!='' AND dest_type!='HOTEL' ORDER BY dest_id DESC  LIMIT ".($page_num-1)*$page_size.",".$page_size;
            $dest_old_result=$this->old_data->query($sql,'All');
            if($dest_old_result){
                foreach($dest_old_result as $key=>$dest_data){
                    if($dest_data && $dest_data['dest_type']){
                        $new_info=$this->checkOldData($dest_data);
                        $base_id=$this->dest_base->getBaseIdByDestId($dest_data['dest_id']);
                        $table_name='dest_'.$this->initDestType($dest_data['dest_type']);
                        $relation_table='relation_dest_'.$this->initDestType($dest_data['dest_type']);
                        if($dest_data['dest_type']=='SCENIC_ENTERTAINMENT'){
                            $relation_table='relation_dest_entertainment';
                        }
                        if($base_id){
                            $this->dest_base->update($dest_data['dest_id'],$new_info['base']);
                            $this->dest_detail->update($base_id,$new_info['ext'],$table_name);
                        }else{
                            $base_id=$this->dest_base->insert($new_info['base']);
                            if($base_id){
                                $new_info['ext'][ 'base_id']=$base_id;
                                $this->dest_detail->insert($new_info['ext'],$table_name);
                            }
                        }
                        if($dest_data['parent_id'] && $dest_data['dest_type']!='CONTINENT'){
                            $parents=$this->dest_base->getDestParents($dest_data['dest_id']);
                            if($parents){
                                $this->dest_rel->delete($relation_table,"cid={$base_id}");
                                foreach($parents as $k=>$row){
                                    $sql="select base_id from dest_base where dest_id=".$row;
                                    $parent_base_id=$this->dest_rel->query($sql);
                                    if($base_id){
                                        $insert_data=array(
                                            'pid'=>$parent_base_id['base_id'],
                                            'cid'=>intval($base_id)
                                        );
                                        $this->dest_rel->insert($insert_data,$relation_table);
                                        unset($insert_data);
                                    }
                                }
                            }
                        }
                    }
                    unset($new_info);
                }
                unset($dest_old_result);
                $current_page = $page_num + 1;
                sleep(5);
                $this->excuteData($current_page, $page_size, $total_page);
            }
        }else{
            echo 'job done';
            exit;
        }
    }
    private function checkOldData($old_data){
        $info = array();
        $info['base'] = array(
            'dest_id'=>intval($old_data['dest_id']),
            'dest_name'=>trim($old_data['dest_name']),
            'dest_type'=>trim($old_data['dest_type']),
            'parent_id'=>intval($old_data['parent_id']),
            'district_id'=>intval($old_data['district_id']),
            'district_parent_id'=>trim($old_data['district_parent_id']),
            'cancel_flag'=>trim($old_data['cancel_flag'])=='Y'?'1':'0',
            'stage'=>($old_data['stage']),
            'range'=>($old_data['range']),
            'showed'=>trim($old_data['showed'])=='Y'?'1':'0',
            'ent_sight'=>trim($old_data['ent_sight'])=='Y'?'1':'0',
        );

        $info['ext'] = array(
            'pinyin'=>trim($old_data['pinyin']),
            'short_pinyin'=>trim($old_data['short_pinyin']),
            'letter'=>trim($old_data['letter']),
            'en_name'=>trim($old_data['en_name']),
            'dest_alias'=>trim($old_data['dest_alias']),
            'local_lang'=>trim($old_data['local_lang']),
            'abroad'=>trim($old_data['abroad'])=='Y'?'1':'0',
            'latitude'=>trim($old_data['latitude']),
            'longitude'=>trim($old_data['longitude']),
            'g_latitude'=>trim($old_data['g_latitude']),
            'g_longitude'=>trim($old_data['g_longitude']),
            'coord_type'=>trim($old_data['coord_type']),
            'modify_time'=>trim($old_data['modify_time']),
            'intro'=>trim($old_data['intro']),
            'star'=>trim($old_data['star']),
            'url'=>trim($old_data['url']),
            'img_url'=>trim($old_data['img_url']),
            'heritage'=>trim($old_data['heritage'])=='Y'?'1':'0',
            'protected_area'=>trim($old_data['protected_area'])=='Y'?'1':'0',

        );

        return $info;
    }
    /**
     * 目的地类型初始化
     * @param $dest_type
     * @return string
     */
    private function initDestType($dest_type){
        if(!$dest_type) return '';
        switch($dest_type) {
            case 'CONTINENT':
                $dest_type = 'state';
                break;
            case 'SPAN_COUNTRY':
            case 'SPAN_PROVINCE':
            case 'SPAN_CITY':
            case 'SPAN_COUNTY':
            case 'SPAN_TOWN':
                $dest_type='special';
                break;
            default :
                $dest_type=strtolower($dest_type);
                break;
        }
        return $dest_type;
    }
}