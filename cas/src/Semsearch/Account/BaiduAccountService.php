<?php
namespace Semsearch\Account;

use \Semsearch\CommonService;
use Semsearch\SearchType;

/*PLEASE DO NOT EDIT THIS CODE*/
/*This code was generated using the UMPLE @UMPLE_VERSION@ modeling language!*/

class UpdateAccountInfoResponse
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //UpdateAccountInfoResponse Attributes
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

class UpdateAccountInfoRequest
{

    //------------------------
    // MEMBER VARIABLES
    //------------------------

    //UpdateAccountInfoRequest Attributes
    public $accountInfo;

    //------------------------
    // CONSTRUCTOR
    //------------------------

    public function __construct()
    {}

    //------------------------
    // INTERFACE
    //------------------------

    public function setAccountInfo($aAccountInfo)
    {
        $wasSet = false;
        $this->accountInfo = $aAccountInfo;
        $wasSet = true;
        return $wasSet;
    }

    public function getAccountInfo()
    {
        return $this->accountInfo;
    }

    public function equals($compareTo)
    {
        return $this == $compareTo;
    }

    public function delete()
    {}

}


class BaiduAccountService extends CommonService implements AccountServiceIface{
    
    public function __construct() {
        parent::__construct ( SearchType::BAIDU, 'AccountService' );
    }

    public function getAccountInfo ($getAccountInfoRequest){
        $rs = $this->execute ( 'getAccountInfo', $getAccountInfoRequest );
        if(isset($rs->data)){
            foreach($rs->data as $row){
                if(isset($row->budget))  unset($row->budget);
                if(isset($row->budgetType))  unset($row->budgetType);
                if(isset($row->payment))  unset($row->payment);
                if(isset($row->balance))  unset($row->balance);
            }
        }
        return $rs;
    }
    public function updateAccountInfo ($updateAccountInfoRequest){
        return $this->execute ( 'updateAccountInfo', $updateAccountInfoRequest );
    }

}


?>