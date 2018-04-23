<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/12
 * Time: 14:56
 */

namespace Semsearch\Report;

use Semsearch\SearchType;
use \Semsearch\CommonService;

class SogouReportService extends CommonService implements ReportServiceIface
{

    public function __construct()
    {
        parent::__construct(SearchType::SOGOU, 'ReportService');
    }

    /**
     * @param GetReportRequest $getReportRequest
     * @return array
     */
    public function getReport($getReportRequest)
    {

        $type = $getReportRequest->getRealTimeRequestType();
        if ($type->unitOfTime == 5) {
//分日
            $type->unitOfTime = 1;

            $req = array(
                'getReportIdRequest' => array(
                    'reportRequestType' => (array) $type,
                ),
            );
            $response = $this->execute('getReportId', $req);

            echo json_encode($response) . "\n";

            $content    = '';
            $sleepTimes = 1;
            $maxTimes   = 3;
            $sleepSec   = 5;
            while ($sleepTimes <= $maxTimes) {
                sleep($sleepSec * $sleepTimes);

                $statusReq = array(
                    '﻿getReportStateRequest' => array(
                        'reportId' => $response->reportId,
                    ),
                );
                $tst = $this->execute('getReportState', $statusReq);
                echo json_encode($tst) . "\n";

                if (isset($tst->isGenerated) && $tst->isGenerated == 1) {

                    $downLoadReq = array(
                        'getReportPathRequest' => array(
                            'reportId' => $response->reportId,
                        ),
                    );
                    $fileUrl = $this->execute('getReportPath', $downLoadReq);
                    $file    = file_get_contents("compress.zlib://" . $fileUrl->reportFilePath);
                    $content = iconv("gb2312", "utf-8//IGNORE", $file);
                    break;
                }

                $sleepTimes++;
            }
            $reports    = array();
            $unitOfTime = $this->getStandardUnitOfTime($type->getUnitOfTime());

            $rows = explode("\n", $content);

            unset($rows[0]);
            //编0号,日1期,账2户,推广计3划ID,推广4计划,推广组5ID,推广6组,关键7词id,关键8词,消9耗,点击均价,点击11数,展示数,点13击率
            foreach ($rows as $key => $row) {
                if (trim($row) == '') {
                    continue;
                }
                $cols = explode(',', $row);
                if ($cols[0] == '总计') {
                    continue;
                }

                $report = array(
                    'id'           => $cols[7],
                    'impression'   => $cols[12],
                    'click'        => $cols[11],
                    'cost'         => $cols[9],
                    'ctr'          => floatval(str_replace('%', '', $cols[13])) * 0.01,
                    'cpc'          => $cols[11] != 0 ? $cols[9] / $cols[11] : 0,
                    'userName'     => $cols[2],
                    'campaignName' => $cols[4],
                    'adgroupName'  => $cols[6],
                    'keyword'      => $cols[8],
                    'date'         => $cols[1],
                    'device'       => $type->getPlatform(),
                    'unitOfTime'   => $unitOfTime,
                );
                $reports[] = $report;
            }
            return $reports;
        } elseif ($type->unitOfTime == 7) {
//分时
            $this->serviceName = 'RealTimeReportService';
            $realTimeReq       = array(
                'GetAccountReportRequest' => array(
                    'realTimeReportRequest' => array(
                        'hour' => date('H:00:00', time() - 3600),
                    ),
                ),
            );
            $response = $this->execute('getAccountReport', $realTimeReq);
            $reports  = isset($response->realTimeReportResponse) ? (array) $response->realTimeReportResponse : null;
            var_dump($reports);die;
            return $reports;
        }

    }
    private function getStandardUnitOfTime($unit)
    {
        if ($unit == 1) { //分日
            $unitOfTime = 5; //
        } elseif ($unit == 7) {
//分时
            //@TODO
            $unitOfTime = 7;
        }
        return $unitOfTime;
    }
    public function getAccountReport($getReportRequest, $userId)
    {
        $arr = array(
            'head'    => '',
            'reports' => array(),
        );

        $type = $getReportRequest->getRealTimeRequestType();

//        var_dump($this->authHeader);die;
        if ($type->unitOfTime == 5) {
//分日
            $type->unitOfTime = 1;

            $req = array(
                'getReportIdRequest' => array(
                    'reportRequestType' => (array) $type,
                ),
            );
            $response = $this->execute('getReportId', $req);
            $head     = $this->getJsonHeader();
            if (isset($head->desc) && $head->desc == 'success') {
                if (isset($response->reportId)) {
                    $content    = '';
                    $sleepTimes = 1;
                    $maxTimes   = 1;
                    $sleepSec   = 3;
                    while ($sleepTimes <= $maxTimes) {
                        sleep($sleepSec);

                        $statusReq = array(
                            '﻿getReportStateRequest' => array(
                                'reportId' => $response->reportId,
                            ),
                        );
                        $tst  = $this->execute('getReportState', $statusReq);
                        $head = $this->getJsonHeader();

                        if (isset($head->desc) && $head->desc == 'success') {
                            if (isset($tst->isGenerated) && $tst->isGenerated == 1) {

                                $downLoadReq = array(
                                    'getReportPathRequest' => array(
                                        'reportId' => $response->reportId,
                                    ),
                                );
                                $fileUrl = $this->execute('getReportPath', $downLoadReq);
                                $head    = $head    = $this->getJsonHeader();
                                if (isset($head->desc) && $head->desc == 'success') {
                                    if (isset($fileUrl->reportFilePath)) {
                                        $file    = file_get_contents("compress.zlib://" . $fileUrl->reportFilePath);
                                        $content = iconv("gb2312", "utf-8//IGNORE", $file);
                                        break;
                                    } else {
                                        echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportPath未返回数据\n";
                                    }

                                } else {
                                    echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportPath失败\n";
                                }
                                echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportState未返回数据\n";
                            }

                        } else {
                            echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportState失败\n";
                        }
                        $sleepTimes++;
                    }
                    $unitOfTime = $this->getStandardUnitOfTime($type->getUnitOfTime());
                    if (!empty($content)) {
                        $rows = explode("\n", $content);

                        unset($rows[0]);
                        //编0号,日1期,账2户,消耗3,点击均价4,点击数5,展示数6,点击率7
                        foreach ($rows as $key => $row) {
                            if (trim($row) == '') {
                                continue;
                            }
                            $cols = explode(',', $row);
                            if ($cols[0] == '总计') {
                                continue;
                            }

                            $report = array(
                                'userId'     => $userId,
                                'impression' => $cols[6],
                                'click'      => $cols[5],
                                'cost'       => $cols[3],
//                                'ctr' => floatval(str_replace('%', '', $cols[7])) * 0.01,
                                //                                'cpc' => $cols[4],
                                'userName'   => $cols[2],
                                'dateTime'   => $cols[1],
                                'device'     => $type->getPlatform(),
                                'unitOfTime' => 5,
                                'platform'   => SearchType::SOGOU,
                            );
                            $arr['reports'][] = $report;
                        }

                    }

                } else {
                    echo "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportId没拿到api返回的数据\n";
                }

            } else {
                echo "平台:" . SearchType::SOGOU . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->platform . ",日期:" . $type->startDate . "请求getReportId失败\n";
            }
            $arr['head'] = $head;
            return $arr;
        }
    }

    public function getJsonHeader()
    {
        $head = parent::getJsonHeader();
        //设置状态码
        $head->resultCode = !empty($head->failures->code) ? $head->failures->code : '';
        $head->rsMsg      = !empty($head->failures->code) ? $this->msgOfCode($head->failures->code) : '';
        return $head;
    }
    //获取状态码对应的message
    private function msgOfCode($code)
    {
        $arr = SearchType::getSOGOUMsgDic();
        return !empty($arr[$code]) ? $arr[$code] : '';
    }
}
