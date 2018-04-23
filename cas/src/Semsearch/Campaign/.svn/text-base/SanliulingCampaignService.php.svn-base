<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Campaign;

use \Semsearch\SanLiuLingCommonService;
use Semsearch\SearchType;

class SanliulingCampaignService extends SanLiuLingCommonService{

    public function __construct() {
        parent::__construct ( SearchType::SANLIULING, '2.0/campaign' );
    }
    public function getCampaign ($getCampaignRequest){
        if ($this->accessToken ){

            if(!empty($getCampaignRequest->idList)){
                $ids = array_unique($getCampaignRequest->idList);
            }else{
                $getCampaginIdReq['format'] = 'json';
                $tmpServiceName = $this->serviceName;
                $this->serviceName = '2.0/account';
                $campaignRs = $this->execute('getCampaignIdList', $getCampaginIdReq);
                $this->serviceName = $tmpServiceName;
                $campainIds = isset($campaignRs->campaignIdList)?$campaignRs->campaignIdList:array();
                $ids= array_unique($campainIds);
            }
            //100个分组，请求
            $idsLen = count($ids);
            $sliceLen = 100;
            $compainRs = array();
            for($i=0;$i<$idsLen;$i=$i+$sliceLen){
                $getCampaginInfoReq['format'] = 'json';
                $getCampaginInfoReq['idList'] = json_encode(array_slice($ids,$i,$sliceLen));

                $rs = $this->execute('getInfoByIdList', $getCampaginInfoReq);
                $compainRs = array_merge($compainRs,$rs->campaignList);
            }

            if(!empty($compainRs)){
                $campaignInfoRs = new GetCampaignResponse();
                foreach($compainRs as $row){
                    $campaignInfo = array();
                    if(isset($row->id)){
                        $campaignInfo['campaignId'] = $row->id;
                        $campaignInfo['campaignName'] = isset($row->name)?$row->name:'';
                        $campaignInfo['regionTarget'] =isset($row->region)?json_decode($row->region):'';
                        $campaignInfo['status'] = isset($row->status) && $row->status=='enable'?1:0;

                        $campaignInfoRs->addData((object)$campaignInfo);
                    }

                }

            }
        }

        if(isset($campaignInfoRs->data)){
            return $campaignInfoRs;
        }
        return null;
    }

    public function addCampaign ($addCampaignRequest){
        return $this->execute ( 'addCampaign', $addCampaignRequest );
    }
    public function updateCampaign ($updateCampaignRequest){
        return $this->execute ( 'updateCampaign', $updateCampaignRequest );
    }
    public function deleteCampaign ($deleteCampaignRequest){
        return $this->execute ( 'deleteCampaign', $deleteCampaignRequest );
    }

}