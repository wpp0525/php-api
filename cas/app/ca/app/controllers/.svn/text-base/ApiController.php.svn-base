<?php

use Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Components\ApiClient;

/**
 * Created by PhpStorm.
 * User: 1
 * Date: 2017/3/13
 * Time: 11:38
 */
class ApiController  extends ControllerBase
{
    /**
     * @var \Lvmama\Cas\Service\DestinationDataService
     */
    private $dest;
    /**
     * @var \Lvmama\Cas\Service\MoRecommendDataService
     */
    private $moRecommend;
    /**
     * @var \Lvmama\Cas\Service\ImageDataService
     */
    private $destImage;

    public function initialize() {
        parent::initialize();
        $this->dest = $this->di->get('cas')->get('destination-data-service');
        $this->moRecommend = $this->di->get('cas')->get('mo-recommend-data-service');
        $this->destImage = $this->di->get('cas')->get('dest_image_service');
    }
    /**
     * 根据POI_ID获取景点信息
     */
    public function getPoiinfoByDestidAction(){
        $dest_ids = $this->request->get('dest_id');
        if(!$dest_ids) $this->_errorResponse(10001,'请传入参数dest_id');
        $tmp = array();
        foreach(explode(',',$dest_ids) as $v){
            if($v && is_numeric($v)){
                $tmp[] = $v;
            }
        }
        if(count($tmp) > 20) $this->_errorResponse(10002,'请求个数超出限制');
        $data = $this->getPoiInfoBdestid($tmp);
		$this->_successResponse($data);
    }
    private function getPoiInfoBdestid($dest_ids){
        $res = array();
        foreach($dest_ids as $dest_id){
            $data = $this->dest->getDestById($dest_id);
            //去掉原来的图片尺寸
            if($data['img_url']){
                $data['img_url'] = UCommon::picUrlReplace($data['img_url']);
            }
            if(empty($data) || ($data['stage'] == 1 && $data['dest_type'] != 'SCENIC')) $this->_errorResponse(10003,'错误的目的地ID');
            $dest_focus = $this->moRecommend->getDestFocusByDestid($data['dest_id']);
            if(!$dest_focus){
                $dest_focus = $this->destImage->getDestEliteImgByDestid($data['dest_id'],5);
            }
            $data['focus'] = $dest_focus;
            //去掉原来的图片尺寸
            if($data['focus']){
                foreach($data['focus'] as $key=>$row){
                    $data['focus'][$key]['image'] = UCommon::picUrlReplace($row['image']);
                }
            }
            $data['addr'] = $this->dest->getAddressByDestId($dest_id);
            //增加 开放时间 和 目的地简介
            $data['sale_time'] = $this->dest->getBusinessHours($dest_id);
			
            // cate_id = 3 poi ； cate_id = 16 景区
            $cate_id = 3;
            if($data['dest_type'] == 'SCENIC') $cate_id = 16;
            $data['dest_summary'] = $this->dest->getSummaryText($dest_id,$cate_id);
            if($data['dest_summary']){
                $data['dest_summary'] = UCommon::vstCensor($data['dest_summary']);
            }else{
                $data['dest_summary'] = "";
            }
            $res[$dest_id] = $data;
        }
        return $res;
    }
}