<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/10
 * Time: 14:40
 */

namespace Semsearch\Report;


interface ReportServiceIface{

    public function getReport($getReportRequest);
    public function getAccountReport($getReportRequest,$userId);
}