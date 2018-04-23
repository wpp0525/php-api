<?php
namespace Semsearch\Account;

use \Semsearch\CommonService;
use Semsearch\SearchType;

class ShenmaAccountService extends CommonService implements AccountServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SHENMA, 'account' );
    }

    public function getAccountInfo ($getAccountInfoRequest){

        $rs = $this->execute('getAccount', $getAccountInfoRequest);

        $accountInfoRs = new GetAccountInfoResponse();
        if(isset($rs->accountInfoType) && isset($rs->accountInfoType->userId)){
            $accountInfo = array();
            $accountInfo['userId'] = $rs->accountInfoType->userId;
            $accountInfo['regionTarget'] = isset($rs->accountInfoType->regionTarget)?$rs->accountInfoType->regionTarget:'';
            $accountInfo['cost'] = isset($rs->accountInfoType->cost)?$rs->accountInfoType->cost:'0.00';
            $accountInfo['userStat'] = isset($rs->accountInfoType->userStat)?$rs->accountInfoType->userStat:'0';

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
        if($rs) {
            $rs->desc = isset($rs->desc) && $rs->desc=="执行成功"?"success":"";
            $rs->rquota = isset($rs->leftQuota)?$rs->leftQuota:'';
        }
        return $rs;
    }

}
