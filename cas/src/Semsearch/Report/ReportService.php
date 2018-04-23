<?php
namespace Semsearch\Report;


use Semsearch\SearchType;


class ReportService {

    /**
     * @var ReportServiceIface
     */
    private $instance = null;

    private $searchType = null;

    public function __construct($searchType = SearchType::BAIDU) {

        $this->searchType = $searchType;

        switch ($searchType){
            case SearchType::BAIDU:
                $this->instance = new BaiduReportService();
                break;
            case SearchType::SHENMA:
                $this->instance = new ShenmaReportService();
                break;
            case SearchType::SANLIULING:
                $this->instance = new SanliulingReportService();
                break;
            case SearchType::SOGOU:
                $this->instance = new SogouReportService();
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

    public function getReport($getReportRequest){
        return $this->instance->getReport($getReportRequest);
    }

    public function getJsonHeader() {
        return $this->instance->getJsonHeader();
    }

    public function setAuthHeader($authHeader){
        $this->instance->setAuthHeader($authHeader);
    }

}

