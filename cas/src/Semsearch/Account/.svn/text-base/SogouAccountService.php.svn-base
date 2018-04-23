<?php
namespace Semsearch\Account;

use \Semsearch\CommonService;
use Semsearch\SearchType;

class SogouAccountService extends CommonService implements AccountServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SOGOU, 'AccountService' );
    }

    public function getAccountInfo ($getAccountInfoRequest){

        $rs = $this->execute('getAccountInfo', $getAccountInfoRequest);

        $accountInfoRs = new GetAccountInfoResponse();
        $accountInfo = array();
        if(isset($rs->accountInfoType) && isset($rs->accountInfoType->accountid)){
            $accountInfo['userId'] = $rs->accountInfoType->accountid;
            $accountInfo['cost'] = isset($rs->accountInfoType->totalCost)?$rs->accountInfoType->totalCost:'0.00';
            $accountInfo['regionTarget'] = isset($rs->accountInfoType->regions)?$rs->accountInfoType->regions:'';
            $accountInfo['userStat'] =isset($rs->accountInfoType->status)?$rs->accountInfoType->status:0;
            $accountInfoRs->addData((object)$accountInfo);
        }
        if(isset($accountInfoRs->data)){
            return $accountInfoRs;
        }
        return null;
    }

    public function updateAccountInfo ($updateAccountInfoRequest){

    }

    public function getJsonHeader(){

        $rs = parent::getJsonHeader();
        $header = array();
        if($rs) {
            $header['desc'] = isset($rs->desc) && $rs->desc=="success"?"success":"";
            $header['rquota'] = isset($rs->rquota)?$rs->rquota:'';
        }
        return (object)$header;
    }

}
