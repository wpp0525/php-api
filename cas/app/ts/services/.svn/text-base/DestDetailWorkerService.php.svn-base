<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\DestinationDataService;
use Lvmama\Cas\Service\DestStateDataService;
use Lvmama\Common\Utils\Misc;

/**
 * 游记数据统计 Worker服务类
 *
 * @author 洪武极
 *
 */
class DestDetailWorkerService implements DaemonServiceInterface {

    /**
     * @var DestdataWorkerService
     */
    private $old_data;
    private $dest_base;
    private $dest_detail;
    public function __construct($di) {
        $this->old_data =  $di->get('cas')->get('dest_old_service');
        $this->dest_base = $di->get('cas')->get('dest_base_service');
        $this->dest_detail = $di->get('cas')->get('dest_detail_service');
        $this->dest_detail->setReconnect(true);
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
        if($dest_type=='state'){
            $type='CONTINENT';
        }
        else{
            $type=strtoupper($dest_type);
        }
        $where_condition=array('dest_type'=>"="."'".$type."'");
        if($dest_type=='viewspot'){
            $where_condition="dest_type='VIEWSPOT' OR ent_sight=1";
        }
        if($dest_type=='ent'){
            $where_condition="dest_type='SCENIC_ENTERTAINMENT' OR ent_sight=1 ";
        }
        if($dest_type=='special'){
            $where_condition="dest_type IN('SPAN_COUNTRY','SPAN_PROVINCE','SPAN_CITY','SPAN_COUNTY','SPAN_TOWN')";
        }
        $base_data=$this->dest_base->getList($where_condition,'dest_base');
        if($base_data){
            foreach($base_data as $key=>$row){
                $detail_data=$this->old_data->getOneByDestId($row['dest_id']);
                $insert_data=$this->fieldMap($detail_data);
                $insert_data['base_id']=$row['base_id'];
                $insert_data['heritage']=($row['heritage']=='Y')?1:0;
                $insert_data['protected_area']=($row['protected_area']=='Y')?1:0;
                switch($dest_type){
                    case 'state':
                        $table_name='dest_state';
                        break;
                    case 'country':
                        $table_name='dest_country';
                        break;
                    case 'province':
                        $table_name='dest_province';
                        break;
                    case 'city':
                        $table_name='dest_city';
                        break;
                    case 'county':
                        $table_name='dest_county';
                        break;
                    case 'town':
                        $table_name='dest_town';
                        break;
                    case 'viewspot':
                        $table_name='dest_viewspot';
                        break;
                    case 'shop':
                        $table_name='dest_shop';
                        break;
                    case 'restaurant':
                        $table_name='dest_restaurant';
                        break;
                    case 'scenic':
                        $table_name='dest_scenic';
                        break;
                    case 'ent':
                        $table_name='dest_scenic_entertainment';
                        break;
                    case 'special':
                        $table_name='dest_special';
                        break;
                    default  :
                        $table_name='';
                        break;
                }
                $this->dest_detail->insert($insert_data,$table_name);
                unset($base_data[$row['dest_id']]);
            }
        }
        die('job done');
    }
    private function fieldMap($detail_data){
        $insert_data=array(
            'pinyin'=>$detail_data['pinyin'],
            'short_pinyin'=>$detail_data['short_pinyin'],
            'letter'=>$detail_data['letter'],
            'dest_alias'=>$detail_data['dest_alias'],
            'local_lang'=>$detail_data['local_lang'],
            'modify_time'=>$detail_data['modify_time'],
            'intro'=>$detail_data['intro'],
            'star'=>$detail_data['star'],
            'abroad'=>($detail_data['abroad']=='Y')?1:0,
            'url'=>$detail_data['url'],
            'img_url'=>$detail_data['img_url'],
            'count_want'=>$detail_data['count_want'],
            'count_been'=>$detail_data['count_been'],
            'longitude'=>$detail_data['longitude'],
            'latitude'=>$detail_data['latitude'],
            'g_longitude'=>$detail_data['g_longitude'],
            'g_latitude'=>$detail_data['g_latitude'],
            'coord_type'=>$detail_data['coord_type'],
            'en_name'=>$detail_data['en_name'],
        );
        return $insert_data;
    }
}