<?php
namespace Semsearch\Adgroup;

use \Semsearch\CommonService;
use Semsearch\SearchType;

/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class GetAdgroupRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //GetAdgroupRequest Attributes

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct($ids,$idType,$searchType = SearchType::BAIDU){
        switch ($searchType){
            case SearchType::BAIDU:
                $fields = array("adgroupName", "status");//除了基本字段外的额外字段
                $this->setIds($ids);
                $this->setAdgroupFields($fields);
                $this->setIdType($idType); //3计划 5单元
                break;
            case SearchType::SHENMA:
                $this->setIdType($idType); //3计划 5单元
                $this->setIdList($ids);
                break;
            case SearchType::SANLIULING:
                $this->setIdType($idType); //3计划 5单元
                $this->setIdList($ids);
                break;
            case SearchType::SOGOU:
                $this->setIdType($idType); //3计划 5单元
                $this->setIdList($ids);
                break;
        }

    }

    //------------------------
    // INTERFACE
    //------------------------
    public function setIdList($ids){
        $this->idList = $ids;
    }
    
    public function setAdgroupFields($aadgroupFields) {
        $this->adgroupFields = $aadgroupFields;
    }

    public function addAdgroupField($aAdgroupField)
    {
        $wasAdded = false;
        $this->adgroupFields[] = $aAdgroupField;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeAdgroupField($aAdgroupField)
    {
        $wasRemoved = false;
        unset($this->adgroupFields[$this->indexOfAdgroupField($aAdgroupField)]);
        $this->adgroupFields = array_values($this->adgroupFields);
        $wasRemoved = true;
        return $wasRemoved;
    }
    public function setIds($aids) {
        $this->ids = $aids;
    }

    public function addId($aId)
    {
        $wasAdded = false;
        $this->ids[] = $aId;
        $wasAdded = true;
        return $wasAdded;
    }

    public function removeId($aId)
    {
        $wasRemoved = false;
        unset($this->ids[$this->indexOfId($aId)]);
        $this->ids = array_values($this->ids);
        $wasRemoved = true;
        return $wasRemoved;
    }

    public function setIdType($aIdType)
    {
        $wasSet = false;
        $this->idType = $aIdType;
        $wasSet = true;
        return $wasSet;
    }


    public function getAdgroupFields()
    {
        $newAdgroupFields = $this->adgroupFields;
        return $newAdgroupFields;
    }

    public function numberOfAdgroupFields()
    {
        $number = count($this->adgroupFields);
        return $number;
    }

    public function indexOfAdgroupField($aAdgroupField)
    {
        $rawAnswer = array_search($aAdgroupField,$this->adgroupFields);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }


    public function getIds()
    {
        $newIds = $this->ids;
        return $newIds;
    }

    public function numberOfIds()
    {
        $number = count($this->ids);
        return $number;
    }

    public function indexOfId($aId)
    {
        $rawAnswer = array_search($aId,$this->ids);
        $index = $rawAnswer == null && $rawAnswer !== 0 ? -1 : $rawAnswer;
        return $index;
    }

    public function getIdType()
    {
        return $this->idType;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}
