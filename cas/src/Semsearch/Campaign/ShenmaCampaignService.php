<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Campaign;

use \Semsearch\CommonService;
use Semsearch\SearchType;

class ShenmaCampaignService extends CommonService{

    public function __construct() {
        parent::__construct ( SearchType::SHENMA, 'campaign' );
    }

    public function getCampaign ($getCampaignRequest){

        if(!empty($getCampaignRequest->campaignIds)){
            $rs = $this->execute ( 'getCampaignByCampaignId', $getCampaignRequest );
        }else{
            $rs = $this->execute ( 'getAllCampaign', $getCampaignRequest );
        }

        $campaignInfoRs = new GetCampaignResponse();

        if(isset($rs->campaignTypes)){
            foreach($rs->campaignTypes as $row){
                $campaignInfo = array();
                if(isset($row->campaignId)){
                    $campaignInfo['campaignId'] = $row->campaignId;
                    $campaignInfo['campaignName'] = isset($row->campaignName)?$row->campaignName:'';
                    $campaignInfo['regionTarget'] = isset($row->regionTarget)&&!empty($row->regionTarget)?$row->regionTarget:'';
                    $campaignInfo['status'] = isset($row->status)?$row->status:0;

                    $campaignInfoRs->addData((object)$campaignInfo);
                }

            }
        }
        if(isset($campaignInfoRs->data)){
            return $campaignInfoRs;
        }
        return null;
    }
    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="执行成功"?"success":"";
        }
        return $rs;
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