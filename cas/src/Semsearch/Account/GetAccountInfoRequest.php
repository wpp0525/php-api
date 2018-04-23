<?php
namespace Semsearch\Account;
use Semsearch\SearchType;
use Semsearch\Xcrypt;

/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class GetAccountInfoRequest
{

  //------------------------
  // MEMBER VARIABLES
  //------------------------

  //GetAccountInfoRequest Attributes

  //------------------------
  // CONSTRUCTOR
  //------------------------

  public function __construct($searchType = SearchType::BAIDU){
//      $this->searchType = $searchType;

      switch ($searchType){
          case SearchType::BAIDU:
              $requestArr = array("userId", "cost", "userStat");
              $this->setAccountFields($requestArr);
              break;
          case SearchType::SHENMA:
              $requestArr=array("account_all");
              $this->setRequestData($requestArr);
              break;
          case SearchType::SANLIULING:
              break;
          case SearchType::SOGOU:
              $requestArr=array('getAccountInfoRequest' => array());
              $this->setRequestData($requestArr);
              break;
      }
  }


  //------------------------
  // INTERFACE
  //------------------------
   public function setAccountFields($aaccountFields) {
       $this->accountFields = $aaccountFields;
   }

   public function setRequestData($requestData){
      $this->requestData = $requestData;
   }

  public function addAccountField($aAccountField)
  {
    $wasAdded = false;
    $this->accountFields[] = $aAccountField;
    $wasAdded = true;
    return $wasAdded;
  }

  public function removeAccountField($aAccountField)
  {
    $wasRemoved = false;
    unset($this->accountFields[$this->indexOfAccountField($aAccountField)]);
    $this->accountFields = array_values($this->accountFields);
    $wasRemoved = true;
    return $wasRemoved;
  }


  public function getAccountFields()
  {
    $newAccountFields = $this->accountFields;
    return $newAccountFields;
  }

  public function numberOfAccountFields()
  {
    $number = count($this->accountFields);
    return $number;
  }

  public function indexOfAccountField($aAccountField)
  {
    $rawAnswer = array_search($aAccountField,$this->accountFields);
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

?>