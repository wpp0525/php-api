<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Report;

use Semsearch\SearchType;
use \Semsearch\SanLiuLingCommonService;

class SanliulingReportService extends SanLiuLingCommonService implements ReportServiceIface
{

    public function __construct()
    {
        parent::__construct(SearchType::SANLIULING, '2.0/report');
    }

    /**
     * @param GetReportRequest $getReportRequest
     * @return array
     */
    public function getReport($getReportRequest)
    {

        $reports    = array();
        $unitOfTime = $getReportRequest->getUnitOfTime();
        $action     = '';
        if ($unitOfTime == 5) {
//日报表
            $action = 'keyword';
        } elseif ($unitOfTime == 7) {
//时报表
            $action = 'hourList';
            $getReportRequest->setGroupBy('keyword');
            $startDate = $getReportRequest->getStartDate();
            $endDate   = $getReportRequest->getEndDate();
            $hour      = date('H:00:00', time() - 3600);
            $getReportRequest->setStartDate($startDate . ' ' . $hour);
            $getReportRequest->setEndDate($endDate . ' ' . $hour);
        }

        $total = $this->execute($action . 'Count', (array) $getReportRequest);

        for ($page = 1; $page <= $total->totalPage; $page++) {
            $getReportRequest->setPage($page);
            $response = $this->execute($action, (array) $getReportRequest);
            if ($unitOfTime == 5) {
                $keywords = $response->keywordList;
            } else {
                $keywords = $response->hourList;
            }
//            var_dump($getReportRequest, count($keywords));die;
            foreach ($keywords as $keyword) {
                $report = array(
                    'id'           => $keyword->keywordId,
                    'impression'   => $keyword->views,
                    'click'        => $keyword->clicks,
                    'cost'         => $keyword->totalCost,
                    'ctr'          => $keyword->views != 0 ? (float) $keyword->clicks / $keyword->views : 0,
                    'cpc'          => $keyword->clicks != 0 ? (float) $keyword->totalCost / $keyword->clicks : 0,
                    'userName'     => $this->auth->username,
                    'campaignName' => $keyword->campaignName,
                    'adgroupName'  => $keyword->groupName,
                    'keyword'      => $keyword->keyword,
                    'date'         => $unitOfTime == 5 ? $keyword->date : $keyword->date . ' ' . $keyword->hour . ':00',
                    'device'       => $getReportRequest->getDevice(),
                    'unitOfTime'   => $unitOfTime,
                );
                $reports[] = $report;
            }
        }

        return $reports;
    }

    public function setAuthHeader($authHeader)
    {
        parent::setAuthHeader($authHeader);
    }
    //获取360账户报表数据
    public function getAccountReport($getReportRequest, $userId)
    {
        $arr = array(
            'head'    => '',
            'reports' => array(),
        );

        $unitOfTime = $getReportRequest->getUnitOfTime();
        $action     = '';
        if ($unitOfTime == 5) {
            $action = 'accountDaily'; //日报表
        }

        $response    = $this->execute($action, (array) $getReportRequest);
        $head        = $this->getJsonHeader();
        $arr['head'] = $head;
        if (isset($head->desc) && $head->desc == 'success') {
            if (isset($response->dailyList) && !empty($response->dailyList)) {
                foreach ($response->dailyList as $data) {
                    $report = array(
                        'userId'     => $userId,
                        'impression' => $data->views,
                        'click'      => $data->clicks,
                        'cost'       => $data->totalCost,
//                        'ctr' => $data->views != 0 ? (float)$data->clicks / $data->views : 0,
//                        'cpc' => $data->clicks != 0 ? (float)$data->totalCost / $data->clicks : 0,
                        'userName'   => $this->auth->username,
                        'dateTime'   => $data->date,
                        'device'     => $getReportRequest->getDevice(),
                        'unitOfTime' => 5,
                        'platform'   => SearchType::SANLIULING,
                    );
                    $arr['reports'][] = $report;
                }

            } else {
                //打印日志
                echo "平台:" . SearchType::SANLIULING . ",用户id:" . $userId . ",用户名:" . $this->auth->username . ",设备:" . $getReportRequest->getDevice() . ",日期:" . $getReportRequest->startDate . "报表api调用未返回数据\n";
            }
        } else {
            //打印日志
            echo "平台:" . SearchType::SANLIULING . ",用户id:" . $userId . ",用户名:" . $this->auth->username . ",设备:" . $getReportRequest->getDevice() . ",日期:" . $getReportRequest->startDate . "报表api调用不成功\n";
        }

        return $arr;
    }

    public function getJsonHeader()
    {
        $head = parent::getJsonHeader();
        $head->resultCode = !empty($head->resultCode)?$head->resultCode:'';
        $head->rsMsg = !empty($head->resultCode)?$this->msgOfCode($head->resultCode):'';
        return $head;
    }
    //获取状态码对应的message
    private function msgOfCode($code){
        $arr = SearchType::getSANLIULINGMsgDic();
        return !empty($arr[$code])?$arr[$code]:'';
    }

}
