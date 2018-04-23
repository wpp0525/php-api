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

class ShenmaReportService extends CommonService implements ReportServiceIface
{

    public function __construct()
    {
        parent::__construct(SearchType::SHENMA, 'report');
    }

    /**
     * @param GetReportRequest $getReportRequest
     * @return array
     */
    public function getReport($getReportRequest)
    {
        // TODO: Implement getReport() method.
        $type     = $getReportRequest->getRealTimeRequestType();
        $response = $this->execute('getReport', $type);

        echo json_encode($response) . "\n";

        $content    = '';
        $sleepTimes = 1;
        $maxTimes   = 3;
        $sleepSec   = 5;
        while ($sleepTimes <= $maxTimes) {
            sleep($sleepSec * $sleepTimes);

            $this->setServiceName('task');
            $task         = new \stdClass();
            $task->taskId = $response->taskId;
            $tst          = $this->execute('getTaskState', $task);

            echo json_encode($tst) . "\n";

            if ($tst->status == 'FINISHED' && $tst->success == true) {
                $file         = new \stdClass();
                $file->fileId = $tst->fileId;
                $this->setServiceName('file');
                $content = $this->downloadFile('download', $file, false);
                break;
            }

            $sleepTimes++;
        }

        $reports    = array();
        $unitOfTime = $type->getUnitOfTime();
        $rows       = explode("\r\n", $content);
        unset($rows[0]);
        //$title = '"时间",账户ID,账户,推广计划ID,推广计划,推广单元ID,推广单元,关键词ID,关键词,展现量,点击量,消费,点击率,平均点击价格';
        foreach ($rows as $key => $row) {
            if (trim($row) == '') {
                continue;
            }
            $cols = explode(',', $row);

            if ($unitOfTime == 7) {
                $cols[0] = substr_replace($cols[0], '', strpos($cols[0], '时')) . ':00';
                if (date('Y-m-d H:00', time() - 7200) != $cols[0] && date('Y-m-d H:00', time() - 3600) != $cols[0]) {
                    continue;
                }
            }

            $report = array(
                'id'           => $cols[7],
                'impression'   => $cols[9],
                'click'        => $cols[10],
                'cost'         => $cols[11],
                'ctr'          => floatval(str_replace('%', '', $cols[12])) * 0.01,
                'cpc'          => $cols[13],
                'userName'     => $cols[2],
                'campaignName' => $cols[4],
                'adgroupName'  => $cols[6],
                'keyword'      => $cols[8],
                'date'         => $cols[0],
                'device'       => $type->getDevice(),
                'unitOfTime'   => $unitOfTime,
            );
            $reports[] = $report;
        }

        return $reports;
    }

    //获取神马账户报表
    public function getAccountReport($getReportRequest, $userId)
    {

        $arr = array(
            'head'    => '',
            'reports' => array(),
        );
        $type     = $getReportRequest->getRealTimeRequestType();
        $response = $this->execute('getReport', $type);
        $head     = $this->getJsonHeader();

        if (isset($head->desc) && $head->desc == 'success') {
            if (isset($response->taskId)) {
                $content    = '';
                $sleepTimes = 1;
                $maxTimes   = 1;
                $sleepSec   = 3;
                while ($sleepTimes <= $maxTimes) {
                    sleep($sleepSec);

                    $this->setServiceName('task');
                    $task         = new \stdClass();
                    $task->taskId = $response->taskId;
                    $tst          = $this->execute('getTaskState', $task);
                    $head         = $this->getJsonHeader();

                    if (isset($head->desc) && $head->desc == 'success') {
                        if ($tst->status == 'FINISHED' && $tst->success == true) {
                            $file         = new \stdClass();
                            $file->fileId = $tst->fileId;
                            $this->setServiceName('file');
                            $content = $this->downloadFile('download', $file, false);
                            $head    = $this->getJsonHeader();
                            if (!(isset($head->desc) && $head->desc == 'success')) {
                                echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用download失败\n";
                            }
                            break;
                        } else {
                            echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用getTaskState 未返回数据\n";
                        }

                    } else {
                        echo "第" . $sleepTimes . "次," . "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用getTaskState 失败\n";
                    }
                    $sleepTimes++;
                }

                if (!empty($content)) {
                    $rows = explode("\r\n", $content);
                    unset($rows[0]);

                    //$title = "﻿"时间",账户ID,账户,展现量,点击量,消费,点击率,平均点击价格;
                    foreach ($rows as $key => $row) {
                        if (trim($row) == '') {
                            continue;
                        }
                        $cols = explode(',', $row);

                        $report = array(
                            'userId'     => $cols[1], //??????????????
                            'impression' => $cols[3],
                            'click'      => $cols[4],
                            'cost'       => $cols[5],
//                            'ctr' => floatval(str_replace('%', '', $cols[6])) * 0.01,
                            //                            'cpc' => $cols[7],
                            'userName'   => $cols[2],
                            'dateTime'   => $cols[0],
                            'device'     => $type->getDevice(),
                            'unitOfTime' => 5,
                            'platform'   => SearchType::SHENMA,
                        );
                        $arr['reports'][] = $report;
                    }
                } else {
                    echo "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用download未返回数据";
                }

            } else {
                echo "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用getReport未返回数据";
            }

        } else {
            echo "平台:" . SearchType::SHENMA . ",用户id:" . $userId . ",用户名:" . $this->authHeader->username . ",设备:" . $type->getDevice() . ",日期:" . $type->startDate . "调用getReport失败";
        }

        $arr['head'] = $head;
        return $arr;
    }
    public function getJsonHeader()
    {
        $head = parent::getJsonHeader();
        //设置状态码
        $head->resultCode = !empty($head->failures[0]->code) ? $head->failures[0]->code : '';
        $head->rsMsg      = !empty($head->failures[0]->code) ? $this->msgOfCode($head->failures[0]->code) : '';
        return $head;
    }
    //获取状态码对应的message
    private function msgOfCode($code)
    {
        $arr = SearchType::getSHENMAMsgDic();
        return !empty($arr[$code]) ? $arr[$code] : '';
    }
}
