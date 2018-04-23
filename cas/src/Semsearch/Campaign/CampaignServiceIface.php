<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/10
 * Time: 14:40
 */

namespace Semsearch\Campaign;


interface CampaignServiceIface{

    public function addCampaign ($addCampaignRequest);

    public function updateCampaign ($updateCampaignRequest);

    public function deleteCampaign ($deleteCampaignRequest);

    public function getCampaign ($getCampaignRequest);
}