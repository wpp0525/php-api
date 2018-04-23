<?php
namespace Semsearch\Campaign;

use \Semsearch\CommonService;
use Semsearch\SearchType;

/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class OfflineTimeType
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //OfflineTimeType Attributes
    public $time;
    public $flag;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------

    public function setTime($aTime)
    {
        $wasSet = false;
        $this->time = $aTime;
        $wasSet = true;
        return $wasSet;
    }

    public function setFlag($aFlag)
    {
        $wasSet = false;
        $this->flag = $aFlag;
        $wasSet = true;
        return $wasSet;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getFlag()
    {
        return $this->flag;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class UpdateCampaignRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //UpdateCampaignRequest Attributes
    public $campaignTypes;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setCampaignTypes($acampaignTypes) {
        $this->campaignTypes = $acampaignTypes;
    }

    public function addCampaignType($aCampaignType)
    {
        $wasAdded = false;
        $this->campaignTypes[] = $aCampaignType;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeCampaignType($aCampaignType)
    {
        $wasRemoved = false;
        unset($this->campaignTypes[$this->indexOfCampaignType($aCampaignType)]);
        $this->campaignTypes = array_values($this->campaignTypes);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getCampaignTypes()
    {
        $newCampaignTypes = $this->campaignTypes;
        return $newCampaignTypes;
    }

    public function numberOfCampaignTypes()
    {
        $number = count($this->campaignTypes);
        return $number;
    }

    public function indexOfCampaignType($aCampaignType)
    {
        $rawAnswer = array_search($aCampaignType,$this->campaignTypes);
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


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class AddCampaignResponse
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //AddCampaignResponse Attributes
    public $data;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setData($adata) {
        $this->data = $adata;
    }

    public function addData($aData)
    {
        $wasAdded = false;
        $this->data[] = $aData;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeData($aData)
    {
        $wasRemoved = false;
        unset($this->data[$this->indexOfData($aData)]);
        $this->data = array_values($this->data);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getData()
    {
        $newData = $this->data;
        return $newData;
    }

    public function numberOfData()
    {
        $number = count($this->data);
        return $number;
    }

    public function indexOfData($aData)
    {
        $rawAnswer = array_search($aData,$this->data);
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


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class OfflineReason
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //OfflineReason Attributes
    public $mainReason;
    public $detailReason;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------

    public function setMainReason($aMainReason)
    {
        $wasSet = false;
        $this->mainReason = $aMainReason;
        $wasSet = true;
        return $wasSet;
    }

    public function setDetailReason($aDetailReason)
    {
        $wasSet = false;
        $this->detailReason = $aDetailReason;
        $wasSet = true;
        return $wasSet;
    }

    public function getMainReason()
    {
        return $this->mainReason;
    }

    public function getDetailReason()
    {
        return $this->detailReason;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class DeleteCampaignRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //DeleteCampaignRequest Attributes
    public $campaignIds;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
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





/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class CampaignType
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //CampaignType Attributes
    public $campaignId;
    public $campaignName;
    public $budget;
    public $regionTarget;
    public $negativeWords;
    public $exactNegativeWords;
    public $schedule;
    public $budgetOfflineTime;
    public $showProb;
    public $pause;
    public $status;
    public $isDynamicCreative;
    public $internalType;
    public $campaignType;
    public $device;
    public $priceRatio;
    public $excludeIp;
    public $dynCreativeExclusion;
    public $isDynamicTagSublink;
    public $isDynamicTitle;
    public $isDynamicHotRedirect;
    public $operator;
    public $rmktStatus;
    public $rmktPriceRatio;
    public $offlineReasons;
    public $adType;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------

    public function setCampaignId($aCampaignId)
    {
        $wasSet = false;
        $this->campaignId = $aCampaignId;
        $wasSet = true;
        return $wasSet;
    }

    public function setCampaignName($aCampaignName)
    {
        $wasSet = false;
        $this->campaignName = $aCampaignName;
        $wasSet = true;
        return $wasSet;
    }

    public function setBudget($aBudget)
    {
        $wasSet = false;
        $this->budget = $aBudget;
        $wasSet = true;
        return $wasSet;
    }
    public function setRegionTarget($aregionTarget) {
        $this->regionTarget = $aregionTarget;
    }

    public function addRegionTarget($aRegionTarget)
    {
        $wasAdded = false;
        $this->regionTarget[] = $aRegionTarget;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeRegionTarget($aRegionTarget)
    {
        $wasRemoved = false;
        unset($this->regionTarget[$this->indexOfRegionTarget($aRegionTarget)]);
        $this->regionTarget = array_values($this->regionTarget);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setNegativeWords($anegativeWords) {
        $this->negativeWords = $anegativeWords;
    }

    public function addNegativeWord($aNegativeWord)
    {
        $wasAdded = false;
        $this->negativeWords[] = $aNegativeWord;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeNegativeWord($aNegativeWord)
    {
        $wasRemoved = false;
        unset($this->negativeWords[$this->indexOfNegativeWord($aNegativeWord)]);
        $this->negativeWords = array_values($this->negativeWords);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setExactNegativeWords($aexactNegativeWords) {
        $this->exactNegativeWords = $aexactNegativeWords;
    }

    public function addExactNegativeWord($aExactNegativeWord)
    {
        $wasAdded = false;
        $this->exactNegativeWords[] = $aExactNegativeWord;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeExactNegativeWord($aExactNegativeWord)
    {
        $wasRemoved = false;
        unset($this->exactNegativeWords[$this->indexOfExactNegativeWord($aExactNegativeWord)]);
        $this->exactNegativeWords = array_values($this->exactNegativeWords);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setSchedule($aschedule) {
        $this->schedule = $aschedule;
    }

    public function addSchedule($aSchedule)
    {
        $wasAdded = false;
        $this->schedule[] = $aSchedule;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeSchedule($aSchedule)
    {
        $wasRemoved = false;
        unset($this->schedule[$this->indexOfSchedule($aSchedule)]);
        $this->schedule = array_values($this->schedule);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setBudgetOfflineTime($abudgetOfflineTime) {
        $this->budgetOfflineTime = $abudgetOfflineTime;
    }

    public function addBudgetOfflineTime($aBudgetOfflineTime)
    {
        $wasAdded = false;
        $this->budgetOfflineTime[] = $aBudgetOfflineTime;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeBudgetOfflineTime($aBudgetOfflineTime)
    {
        $wasRemoved = false;
        unset($this->budgetOfflineTime[$this->indexOfBudgetOfflineTime($aBudgetOfflineTime)]);
        $this->budgetOfflineTime = array_values($this->budgetOfflineTime);
        $wasRemoved = true;
        return $wasRemoved;
    }

    public function setShowProb($aShowProb)
    {
        $wasSet = false;
        $this->showProb = $aShowProb;
        $wasSet = true;
        return $wasSet;
    }

    public function setPause($aPause)
    {
        $wasSet = false;
        $this->pause = $aPause;
        $wasSet = true;
        return $wasSet;
    }

    public function setStatus($aStatus)
    {
        $wasSet = false;
        $this->status = $aStatus;
        $wasSet = true;
        return $wasSet;
    }

    public function setIsDynamicCreative($aIsDynamicCreative)
    {
        $wasSet = false;
        $this->isDynamicCreative = $aIsDynamicCreative;
        $wasSet = true;
        return $wasSet;
    }

    public function setInternalType($aInternalType)
    {
        $wasSet = false;
        $this->internalType = $aInternalType;
        $wasSet = true;
        return $wasSet;
    }

    public function setCampaignType($aCampaignType)
    {
        $wasSet = false;
        $this->campaignType = $aCampaignType;
        $wasSet = true;
        return $wasSet;
    }

    public function setDevice($aDevice)
    {
        $wasSet = false;
        $this->device = $aDevice;
        $wasSet = true;
        return $wasSet;
    }

    public function setPriceRatio($aPriceRatio)
    {
        $wasSet = false;
        $this->priceRatio = $aPriceRatio;
        $wasSet = true;
        return $wasSet;
    }
    public function setExcludeIp($aexcludeIp) {
        $this->excludeIp = $aexcludeIp;
    }

    public function addExcludeIp($aExcludeIp)
    {
        $wasAdded = false;
        $this->excludeIp[] = $aExcludeIp;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeExcludeIp($aExcludeIp)
    {
        $wasRemoved = false;
        unset($this->excludeIp[$this->indexOfExcludeIp($aExcludeIp)]);
        $this->excludeIp = array_values($this->excludeIp);
        $wasRemoved = true;
        return $wasRemoved;
    }

    public function setDynCreativeExclusion($aDynCreativeExclusion)
    {
        $wasSet = false;
        $this->dynCreativeExclusion = $aDynCreativeExclusion;
        $wasSet = true;
        return $wasSet;
    }

    public function setIsDynamicTagSublink($aIsDynamicTagSublink)
    {
        $wasSet = false;
        $this->isDynamicTagSublink = $aIsDynamicTagSublink;
        $wasSet = true;
        return $wasSet;
    }

    public function setIsDynamicTitle($aIsDynamicTitle)
    {
        $wasSet = false;
        $this->isDynamicTitle = $aIsDynamicTitle;
        $wasSet = true;
        return $wasSet;
    }

    public function setIsDynamicHotRedirect($aIsDynamicHotRedirect)
    {
        $wasSet = false;
        $this->isDynamicHotRedirect = $aIsDynamicHotRedirect;
        $wasSet = true;
        return $wasSet;
    }

    public function setOperator($aOperator)
    {
        $wasSet = false;
        $this->operator = $aOperator;
        $wasSet = true;
        return $wasSet;
    }

    public function setRmktStatus($aRmktStatus)
    {
        $wasSet = false;
        $this->rmktStatus = $aRmktStatus;
        $wasSet = true;
        return $wasSet;
    }

    public function setRmktPriceRatio($aRmktPriceRatio)
    {
        $wasSet = false;
        $this->rmktPriceRatio = $aRmktPriceRatio;
        $wasSet = true;
        return $wasSet;
    }
    public function setOfflineReasons($aofflineReasons) {
        $this->offlineReasons = $aofflineReasons;
    }

    public function addOfflineReason($aOfflineReason)
    {
        $wasAdded = false;
        $this->offlineReasons[] = $aOfflineReason;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeOfflineReason($aOfflineReason)
    {
        $wasRemoved = false;
        unset($this->offlineReasons[$this->indexOfOfflineReason($aOfflineReason)]);
        $this->offlineReasons = array_values($this->offlineReasons);
        $wasRemoved = true;
        return $wasRemoved;
    }

    public function setAdType($aAdType)
    {
        $wasSet = false;
        $this->adType = $aAdType;
        $wasSet = true;
        return $wasSet;
    }

    public function getCampaignId()
    {
        return $this->campaignId;
    }

    public function getCampaignName()
    {
        return $this->campaignName;
    }

    public function getBudget()
    {
        return $this->budget;
    }


    public function getRegionTarget()
    {
        $newRegionTarget = $this->regionTarget;
        return $newRegionTarget;
    }

    public function numberOfRegionTarget()
    {
        $number = count($this->regionTarget);
        return $number;
    }

    public function indexOfRegionTarget($aRegionTarget)
    {
        $rawAnswer = array_search($aRegionTarget,$this->regionTarget);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getNegativeWords()
    {
        $newNegativeWords = $this->negativeWords;
        return $newNegativeWords;
    }

    public function numberOfNegativeWords()
    {
        $number = count($this->negativeWords);
        return $number;
    }

    public function indexOfNegativeWord($aNegativeWord)
    {
        $rawAnswer = array_search($aNegativeWord,$this->negativeWords);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getExactNegativeWords()
    {
        $newExactNegativeWords = $this->exactNegativeWords;
        return $newExactNegativeWords;
    }

    public function numberOfExactNegativeWords()
    {
        $number = count($this->exactNegativeWords);
        return $number;
    }

    public function indexOfExactNegativeWord($aExactNegativeWord)
    {
        $rawAnswer = array_search($aExactNegativeWord,$this->exactNegativeWords);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getSchedule()
    {
        $newSchedule = $this->schedule;
        return $newSchedule;
    }

    public function numberOfSchedule()
    {
        $number = count($this->schedule);
        return $number;
    }

    public function indexOfSchedule($aSchedule)
    {
        $rawAnswer = array_search($aSchedule,$this->schedule);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getBudgetOfflineTime()
    {
        $newBudgetOfflineTime = $this->budgetOfflineTime;
        return $newBudgetOfflineTime;
    }

    public function numberOfBudgetOfflineTime()
    {
        $number = count($this->budgetOfflineTime);
        return $number;
    }

    public function indexOfBudgetOfflineTime($aBudgetOfflineTime)
    {
        $rawAnswer = array_search($aBudgetOfflineTime,$this->budgetOfflineTime);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function getShowProb()
    {
        return $this->showProb;
    }

    public function getPause()
    {
        return $this->pause;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getIsDynamicCreative()
    {
        return $this->isDynamicCreative;
    }

    public function getInternalType()
    {
        return $this->internalType;
    }

    public function getCampaignType()
    {
        return $this->campaignType;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function getPriceRatio()
    {
        return $this->priceRatio;
    }


    public function getExcludeIp()
    {
        $newExcludeIp = $this->excludeIp;
        return $newExcludeIp;
    }

    public function numberOfExcludeIp()
    {
        $number = count($this->excludeIp);
        return $number;
    }

    public function indexOfExcludeIp($aExcludeIp)
    {
        $rawAnswer = array_search($aExcludeIp,$this->excludeIp);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function getDynCreativeExclusion()
    {
        return $this->dynCreativeExclusion;
    }

    public function getIsDynamicTagSublink()
    {
        return $this->isDynamicTagSublink;
    }

    public function getIsDynamicTitle()
    {
        return $this->isDynamicTitle;
    }

    public function getIsDynamicHotRedirect()
    {
        return $this->isDynamicHotRedirect;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getRmktStatus()
    {
        return $this->rmktStatus;
    }

    public function getRmktPriceRatio()
    {
        return $this->rmktPriceRatio;
    }


    public function getOfflineReasons()
    {
        $newOfflineReasons = $this->offlineReasons;
        return $newOfflineReasons;
    }

    public function numberOfOfflineReasons()
    {
        $number = count($this->offlineReasons);
        return $number;
    }

    public function indexOfOfflineReason($aOfflineReason)
    {
        $rawAnswer = array_search($aOfflineReason,$this->offlineReasons);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function getAdType()
    {
        return $this->adType;
    }

    public function isPause()
    {
        return $this->pause;
    }

    public function isIsDynamicCreative()
    {
        return $this->isDynamicCreative;
    }

    public function isIsDynamicTagSublink()
    {
        return $this->isDynamicTagSublink;
    }

    public function isIsDynamicTitle()
    {
        return $this->isDynamicTitle;
    }

    public function isIsDynamicHotRedirect()
    {
        return $this->isDynamicHotRedirect;
    }

    public function isRmktStatus()
    {
        return $this->rmktStatus;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class DeleteCampaignResponse
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //DeleteCampaignResponse Attributes
    public $data;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setData($adata) {
        $this->data = $adata;
    }

    public function addData($aData)
    {
        $wasAdded = false;
        $this->data[] = $aData;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeData($aData)
    {
        $wasRemoved = false;
        unset($this->data[$this->indexOfData($aData)]);
        $this->data = array_values($this->data);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getData()
    {
        $newData = $this->data;
        return $newData;
    }

    public function numberOfData()
    {
        $number = count($this->data);
        return $number;
    }

    public function indexOfData($aData)
    {
        $rawAnswer = array_search($aData,$this->data);
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


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class UpdateCampaignResponse
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //UpdateCampaignResponse Attributes
    public $data;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setData($adata) {
        $this->data = $adata;
    }

    public function addData($aData)
    {
        $wasAdded = false;
        $this->data[] = $aData;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeData($aData)
    {
        $wasRemoved = false;
        unset($this->data[$this->indexOfData($aData)]);
        $this->data = array_values($this->data);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getData()
    {
        $newData = $this->data;
        return $newData;
    }

    public function numberOfData()
    {
        $number = count($this->data);
        return $number;
    }

    public function indexOfData($aData)
    {
        $rawAnswer = array_search($aData,$this->data);
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


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class ScheduleType
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //ScheduleType Attributes
    public $startHour;
    public $endHour;
    public $weekDay;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------

    public function setStartHour($aStartHour)
    {
        $wasSet = false;
        $this->startHour = $aStartHour;
        $wasSet = true;
        return $wasSet;
    }

    public function setEndHour($aEndHour)
    {
        $wasSet = false;
        $this->endHour = $aEndHour;
        $wasSet = true;
        return $wasSet;
    }

    public function setWeekDay($aWeekDay)
    {
        $wasSet = false;
        $this->weekDay = $aWeekDay;
        $wasSet = true;
        return $wasSet;
    }

    public function getStartHour()
    {
        return $this->startHour;
    }

    public function getEndHour()
    {
        return $this->endHour;
    }

    public function getWeekDay()
    {
        return $this->weekDay;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class AddCampaignRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //AddCampaignRequest Attributes
    public $campaignTypes;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------
    public function setCampaignTypes($acampaignTypes) {
        $this->campaignTypes = $acampaignTypes;
    }

    public function addCampaignType($aCampaignType)
    {
        $wasAdded = false;
        $this->campaignTypes[] = $aCampaignType;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeCampaignType($aCampaignType)
    {
        $wasRemoved = false;
        unset($this->campaignTypes[$this->indexOfCampaignType($aCampaignType)]);
        $this->campaignTypes = array_values($this->campaignTypes);
        $wasRemoved = true;
        return $wasRemoved;
    }


    public function getCampaignTypes()
    {
        $newCampaignTypes = $this->campaignTypes;
        return $newCampaignTypes;
    }

    public function numberOfCampaignTypes()
    {
        $number = count($this->campaignTypes);
        return $number;
    }

    public function indexOfCampaignType($aCampaignType)
    {
        $rawAnswer = array_search($aCampaignType,$this->campaignTypes);
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


/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class BaiduCampaignService extends CommonService
{
    public function __construct() {
        parent::__construct ( SearchType::BAIDU, 'CampaignService' );
    }

    // ABSTRACT METHODS

    public function getCampaign ($getCampaignRequest){
        $rs = $this->execute ( 'getCampaign', $getCampaignRequest );
        if(isset($rs->data)){
            foreach($rs->data as $row){
                if(isset($row->bidPrefer))  unset($row->bidPrefer);
            }
        }
        return $rs;
    }
    public function updateCampaign ($updateCampaignRequest){
        return $this->execute ( 'updateCampaign', $updateCampaignRequest );
    }
    public function deleteCampaign ($deleteCampaignRequest){
        return $this->execute ( 'deleteCampaign', $deleteCampaignRequest );
    }
    public function addCampaign ($addCampaignRequest){
        return $this->execute ( 'addCampaign', $addCampaignRequest );
    }

}
