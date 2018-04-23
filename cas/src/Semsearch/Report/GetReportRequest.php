<?php
namespace Semsearch\Report;
use Semsearch\CommonType;
use Semsearch\SearchType;


class GetReportRequest{


//  public $realTimeRequestType;

    public function __construct($req, $searchType = SearchType::BAIDU){
        $latitude = isset($req['latitude'])&&$req['latitude']=='account'?'account':'';
        switch ($searchType){
            case SearchType::BAIDU:
            case SearchType::SHENMA:
                $req = array_merge($req, array(
                    'performanceData' => array("impression","click","cost","ctr","cpc"),
                    'levelOfDetails' => $latitude=="account"?2:11, //关键词粒度 6(word) 11(keyword)，2（账户）
                    'reportType' =>$latitude=="account"?2:14,   //关键词类型  9(word) 14(keyword)，2（账户）
                    'order' => true,
                ));
                $type = new ReportRequestType($req);
                $this->setRealTimeRequestType($type);
                break;
            case SearchType::SANLIULING:
                $type = 'all';
                if($req['device'] == 1){
                    $type = 'computer';
                }else if($req['device'] == 2){
                    $type = 'mobile';
                }
                $this->setFormat('json');
                $this->setStartDate($req['startDate']);
                $this->setEndDate($req['endDate']);
                $this->setLevel('account');
                $this->setType($type);
                $this->setDevice($req['device']);
                $this->setUnitOfTime($req['unitOfTime']);
                break;
            case SearchType::SOGOU:
                $platform = $req['device'];
                $unitOfTime = $req['unitOfTime'];

                $sogouReq = array(
                    'unitOfTime' => $unitOfTime,
                    'startDate' => $req['startDate'],
                    'endDate' => $req['endDate'],
                    'performanceData' => array("impression","click","cost","ctr","cpc"),
                    'reportType' => $latitude=="account"?1:5,   //关键词类型  5，账户1
                    'order' => true,
                    'platform' => $platform,
                );

                $type = new ReportRequestType($sogouReq);
                $this->setRealTimeRequestType($type);
                break;
        }
    }

    /********* 百度/神马 使用 ************/
    public function setRealTimeRequestType($aRealTimeRequestType)
    {
        $wasSet = false;
        $this->realTimeRequestType = $aRealTimeRequestType;
        $wasSet = true;
        return $wasSet;
    }

    /**
     * @return ReportRequestType
     */
    public function getRealTimeRequestType(){
        return isset($this->realTimeRequestType) ? $this->realTimeRequestType : null;
    }

    /************* 360 使用 **************/
    public function setFormat($format){
        $this->format = $format;
    }

    public function setStartDate($startDate){
        $this->startDate = $startDate;
    }
    public function getStartDate(){
        return $this->startDate;
    }

    public function setEndDate($endDate){
        $this->endDate = $endDate;
    }
    public function getEndDate(){
        return $this->endDate;
    }

    public function setLevel($level){
        $this->level = $level;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function setDevice($device){
        $this->device = $device;
    }
    public function getDevice(){
        return $this->device;
    }

    public function setUnitOfTime($unitOfTime){
        $this->unitOfTime = $unitOfTime;
    }
    public function getUnitOfTime(){
        return $this->unitOfTime;
    }

    public function setGroupBy($groupBy){
        $this->groupBy = $groupBy;
    }

    public function setPage($page){
        $this->page = $page;
    }
    /************* sogou使用 **************/
    public function setPlatform($platform){
        $this->platform = $platform;
    }
    public function getPlatform(){
        return $this->platform;
    }
}
