<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\UCommon;
/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class DestsumaryController extends ControllerBase {
    private $dest_sumary_svc;
    private $vst_dest_sumary_svc;
    public function initialize() {
        parent::initialize();
        $this->dest_sumary_svc=$this->di->get('cas')->get('dest_sumary_service');
        $this->vst_dest_sumary_svc=$this->di->get('cas')->get('vst_dest_sumary_service');
    }


    public function destSuggestTimeAction(){
        $dest_id=$this->dest_id;
        $result=$this->dest_sumary_svc->getSuggestTimeByDestId($dest_id);
        if($result && !empty($result['time'])){
            switch($result['unit']){
                case 'H':
                    $result['unit']='小时';
                    break;
                case 'D':
                    $result['unit']='天';
                    break;
                case 'M':
                    $result['unit']='分钟';
                    break;
            }
            $this->jsonResponse($result);
        }
    }

    public function poiSummaryDataAction(){
        $dest_id=$this->dest_id;
        $data_list=array();
        if(!$data_list){
            $text_data=$this->dest_sumary_svc->getDestTextData($dest_id);//所有TEXT格式目的地信息
            foreach($text_data as $row){
                if(trim($row['text'])!=''){
                    $data_list['text_data'][]=$row;
                }
            }
            $data_list['transport']=$this->dest_sumary_svc->getDestTransport($dest_id); //交通信息
            $data_list['address']=$this->dest_sumary_svc->getDestAddress($dest_id);  //地址
            $data_list['suggest_time']=$this->dest_sumary_svc->getSuggestTimeByDestId($dest_id); //建议游玩时间
            $data_list['contact']=$this->dest_sumary_svc->getDestContract($dest_id);
            $data_list['ticket_info']=$this->dest_sumary_svc->getDestTicketInfo($dest_id);
            $data_list['sale_time']=$this->dest_sumary_svc->getDestTime($dest_id);
        }
        $this->jsonResponse($data_list);

    }

    /**
     * COPY FROM poiSummaryDataAction
     * 更换SERVICE：dest_sumary_svc => vst_dest_sumary_svc
     */
    public function vstPoiSummaryDataAction(){
        $dest_id=$this->dest_id;
        $data_list=array();
        if(!$data_list){
            $text_data=$this->vst_dest_sumary_svc->getDestTextData($dest_id);//所有TEXT格式目的地信息
            foreach($text_data as $row){
                if(trim($row['text'])!=''){
                    $data_list['text_data'][]=$row;
                }
            }
            $data_list['transport']=$this->vst_dest_sumary_svc->getDestTransport($dest_id); //交通信息
            $data_list['address']=$this->vst_dest_sumary_svc->getDestAddress($dest_id);  //地址
            $data_list['suggest_time']=$this->vst_dest_sumary_svc->getSuggestTimeByDestId($dest_id); //建议游玩时间
            $data_list['contact']=$this->vst_dest_sumary_svc->getDestContract($dest_id);
            $data_list['ticket_info']=$this->vst_dest_sumary_svc->getDestTicketInfo($dest_id);
            $data_list['sale_time']=$this->vst_dest_sumary_svc->getDestTime($dest_id);
        }
        $this->jsonResponse($data_list);
    }


    public function destScenerySumAction(){
        $dest_id=$this->dest_id;
        $forcedb=$this->forcedb;
        $redis_key=RedisDataService::REDIS_DEST_SCENERY_SUMMARY.$dest_id;
        $result=null;
        if(!$forcedb){
            $result=$this->redis_svc->dataGet($redis_key);
        }
        if(!$result){
            $result=strip_tags($this->dest_sumary_svc->getDestScenerySummary($dest_id));
            if($result){
                $this->redis_svc->dataSet($redis_key,$result,7200);
            }
        }
        if($result){
            $this->jsonResponse($result);
        }
    }

    public function getUrlPinyinByDidAction(){
        $district_id = intval($this->request->get('district_id'));
        if(!$district_id || !is_numeric($district_id)) $this->_errorResponse(10001,'请传入正确的district_id');

        $redis_key=RedisDataService::REDIS_DEST_VST_URL_PINYIN.$district_id;

        $result=$this->redis_svc->dataGet($redis_key);

        if(!$result){
            $result = $this->dest_sumary_svc->getUrlPinyin($district_id);
            if($result){
                $this->redis_svc->dataSet($redis_key,$result,7200);
            }
        }
        if($result){
            $this->jsonResponse($result);
        }


    }

    public function getAllPoiAction(){
        $dest_id = intval($this->request->get('dest_id'));
        $result = $this->dest_sumary_svc->getAllPoi($dest_id);
        $this->jsonResponse($result);
    }


}
