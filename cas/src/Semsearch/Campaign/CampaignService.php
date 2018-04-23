<?php
namespace Semsearch\Campaign;


use Semsearch\SearchType;


class CampaignService {

    /**
     * @var CampaignServiceIface
     */
    private $instance = null;

    private $searchType = null;

    public function __construct($searchType = SearchType::BAIDU) {

        $this->searchType = $searchType;

        switch ($searchType){
            case SearchType::BAIDU:
                $this->instance = new BaiduCampaignService();
                break;
            case SearchType::SHENMA:
                $this->instance = new ShenmaCampaignService();
                break;
            case SearchType::SANLIULING:
                $this->instance = new SanliulingCampaignService();
                break;
            case SearchType::SOGOU:
                $this->instance = new SogouCampaignService();
                break;
        }

    }

    public function __call($name, $arguments){
        // TODO: Implement __call() method.
        if(method_exists($this->instance, $name)){
            return call_user_func_array(array($this->instance, $name), $arguments);
        }
        throw new \Exception("Call Wrong Function!!");
    }

    public function addCampaign ($addCampaignRequest){
        return $this->instance->addCampaign ( $addCampaignRequest );
    }
    public function updateCampaign ($updateCampaignRequest){
        return $this->instance->updateCampaign (  $updateCampaignRequest );
    }
    public function deleteCampaign ($deleteCampaignRequest){
        return $this->instance->deleteCampaign ( $deleteCampaignRequest );
    }
    public function getCampaign ($getCampaignRequest){
        return $this->instance->getCampaign ( $getCampaignRequest );
    }

    public function getJsonHeader() {
        return $this->instance->getJsonHeader();
    }

    public function setAuthHeader($authHeader){
        $this->instance->setAuthHeader($authHeader);
    }

}

