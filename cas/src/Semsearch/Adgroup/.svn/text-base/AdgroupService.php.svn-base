<?php
namespace Semsearch\Adgroup;


use Semsearch\SearchType;


class AdgroupService {

    /**
     * @var AdgroupServiceIface
     */
    private $instance = null;

    private $searchType = null;

    public function __construct($searchType = SearchType::BAIDU) {

        $this->searchType = $searchType;

        switch ($searchType){
            case SearchType::BAIDU:
                $this->instance = new BaiduAdgroupService();
                break;
            case SearchType::SHENMA:
                $this->instance = new ShenmaAdgroupService();
                break;
            case SearchType::SANLIULING:
                $this->instance = new SanliulingAdgroupService();
                break;
            case SearchType::SOGOU:
                $this->instance = new SogouAdgroupService();
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

    public function addAdgroup ($addAdgroupRequest){
        return $this->instance->addAdgroup ($addAdgroupRequest);
    }
    public function updateAdgroup ($updateAdgroupRequest){
        return $this->instance->updateAdgroup ($updateAdgroupRequest);
    }
    public function deleteAdgroup ($deleteAdgroupRequest){
        return $this->instance->deleteAdgroup ($deleteAdgroupRequest);
    }
    public function getAdgroup ($getAdgroupRequest){
        return $this->instance->getAdgroup ($getAdgroupRequest);
    }

    public function getJsonHeader() {
        return $this->instance->getJsonHeader();
    }

    public function setAuthHeader($authHeader){
        $this->instance->setAuthHeader($authHeader);
    }

}

