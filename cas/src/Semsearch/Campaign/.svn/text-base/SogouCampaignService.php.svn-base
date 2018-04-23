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

class SogouCampaignService extends CommonService{

    public function __construct() {
        parent::__construct ( SearchType::SOGOU, 'CpcPlanService' );
    }

    public function getCampaign ($getCampaignRequest){

        if(!empty($getCampaignRequest->campaignIds)){
            $request = array(
                        'getCpcPlanByCpcPlanIdRequest' => array(
                            'cpcPlanIds' => $getCampaignRequest->campaignIds
                        )
                    );
            $rs = $this->execute ( 'getCpcPlanByCpcPlanId', $request );
        }else{

            $request = array('getAllCpcPlanIdRequest' => array());
            $rs = $this->execute ( 'getAllCpcPlan', $request );
        }
        $campaignInfoRs = new GetCampaignResponse();
        if(isset($rs->cpcPlanTypes)){
            $campaigns = is_array($rs->cpcPlanTypes)?$rs->cpcPlanTypes:array($rs->cpcPlanTypes);

            foreach($campaigns as $row){
                $campaignInfo = array();
                if(isset($row->cpcPlanId)){
                    $campaignInfo['campaignId'] = $row->cpcPlanId;
                    $campaignInfo['campaignName'] = isset($row->cpcPlanName)?$row->cpcPlanName:'';
                    $campaignInfo['regionTarget'] = isset($row->regions)?(is_array($row->regions)?$row->regions:array($row->regions)):'';
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
            $rs->desc = isset($rs->desc) && $rs->desc=="success"?"success":"";
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