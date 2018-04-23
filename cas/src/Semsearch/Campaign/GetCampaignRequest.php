<?php
namespace Semsearch\Campaign;
use Semsearch\SearchType;
use Semsearch\Xcrypt;
/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class GetCampaignRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //GetCampaignRequest Attributes
//    public $campaignFields;
//    public $campaignIds;
//    public $mobileExtend;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct($ids,$searchType = SearchType::BAIDU){
        switch ($searchType){
            case SearchType::BAIDU:
                $campaignFields = array("campaignName", "campaignType", "regionTarget", "status");
                $campaignIds = $ids;
                $this->setCampaignFields($campaignFields);
                $this->setCampaignIds($campaignIds);
                $this->setMobileExtend(1);
                break;
            case SearchType::SHENMA:
                $this->setCampaignIds($ids);
                break;
            case SearchType::SANLIULING:
                $this->setIdList($ids);
                break;
            case SearchType::SOGOU:
                $this->setCampaignIds($ids);
                break;
        }
    }

    //------------------------
    // INTERFACE
    //------------------------

    public function setIdList($ids){
        $this->idList = $ids;
    }
    public function setMobileExtend($mobileExtend){
        $this->mobileExtend = $mobileExtend;
    }

    public function setCampaignFields($acampaignFields) {
        $this->campaignFields = $acampaignFields;
    }

    public function addCampaignField($aCampaignField)
    {
        $wasAdded = false;
        $this->campaignFields[] = $aCampaignField;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeCampaignField($aCampaignField)
    {
        $wasRemoved = false;
        unset($this->campaignFields[$this->indexOfCampaignField($aCampaignField)]);
        $this->campaignFields = array_values($this->campaignFields);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setCampaignIds($acampaignIds) {
        $this->campaignIds = $acampaignIds;
    }

    public function addCampaignId($aCampaignId)
    {
        $wasAdded = false;
        $this->campaignIds[] = $aCampaignId;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeCampaignId($aCampaignId)
    {
        $wasRemoved = false;
        unset($this->campaignIds[$this->indexOfCampaignId($aCampaignId)]);
        $this->campaignIds = array_values($this->campaignIds);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getCampaignFields()
    {
        $newCampaignFields = $this->campaignFields;
        return $newCampaignFields;
    }

    public function numberOfCampaignFields()
    {
        $number = count($this->campaignFields);
        return $number;
    }

    public function indexOfCampaignField($aCampaignField)
    {
        $rawAnswer = array_search($aCampaignField,$this->campaignFields);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getCampaignIds()
    {
        $newCampaignIds = $this->campaignIds;
        return $newCampaignIds;
    }

    public function numberOfCampaignIds()
    {
        $number = count($this->campaignIds);
        return $number;
    }

    public function indexOfCampaignId($aCampaignId)
    {
        $rawAnswer = array_search($aCampaignId,$this->campaignIds);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}