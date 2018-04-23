<?php
namespace Semsearch\Account;


use \Semsearch\SanLiuLingCommonService;
use Semsearch\SearchType;

class SanliulingAccountService extends SanLiuLingCommonService implements AccountServiceIface{

    public function __construct() {
        parent::__construct ( SearchType::SANLIULING, '2.0/account' );

    }

    public function getAccountInfo ($getAccountInfoRequest){
        if ($this->accessToken ){
            $accountInfoReq['format'] = 'json';
            $body = $this->execute('getInfo', $accountInfoReq);
            $accountInfo = array();
            $accountInfoRs = new GetAccountInfoResponse();
            if(!empty($body)){
                if(!isset($body->failures) && isset($body->uid)){
                    $accountInfo['userId'] = $body->uid;
                    $accountInfo['regionTarget'] = isset($body->regionTarget)?$body->regionTarget:'';
                    $accountInfo['cost'] = isset($body->cost)?$body->cost:'0.00';
                    $accountInfo['userStat'] = isset($body->status)?$body->status:'0';

                    $accountInfoRs->addData((object)$accountInfo);

                }
            }
        }
        if(isset($accountInfoRs->data)){
            return $accountInfoRs;
        }
        return null;
    }

    public function updateAccountInfo ($updateAccountInfoRequest){

    }


}
