<?php
namespace Semsearch\Account;


use Semsearch\SearchType;

/**
 * Class AccountService
 * @package Semsearch\Account
 */
class AccountService {

    /**
     * @var AccountServiceIface
     */
    private $instance = null;

    private $searchType = null;

    /**
     * AccountService constructor.
     * @param string $searchType
     */
    public function __construct($searchType = SearchType::BAIDU) {

        $this->searchType = $searchType;

        switch ($searchType){
            case SearchType::BAIDU:
                $this->instance = new BaiduAccountService();
                break;
            case SearchType::SHENMA:
                $this->instance = new ShenmaAccountService();
                break;
            case SearchType::SANLIULING:
                $this->instance = new SanliulingAccountService();
                break;
            case SearchType::SOGOU:
                $this->instance = new SogouAccountService();
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

    /**
     * 获取账号信息
     * @param $getAccountInfoRequest
     * @return mixed
     */
    public function getAccountInfo ($getAccountInfoRequest){
        return $this->instance->getAccountInfo($getAccountInfoRequest);
    }

    /**
     * 更新账号信息
     * @param $updateAccountInfoRequest
     * @return mixed
     */
    public function updateAccountInfo ($updateAccountInfoRequest){
        return $this->instance->updateAccountInfo($updateAccountInfoRequest);
    }

    public function getJsonHeader($searchType) {
        return $this->instance->getJsonHeader($searchType);
    }

    public function setAuthHeader($authHeader){
        $this->instance->setAuthHeader($authHeader);
    }

}

