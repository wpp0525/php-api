<?php

namespace Lvmama\Cas\Service;

use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Cas\Service\SemReportEsDataService;

/**
 * 推广账户信息 服务类
 *
 * @author flash.guo
 *
 */
class SemAccountReportAllService extends DataServiceBase
{

    const TABLE_NAME = 'sem_account_report_all'; //对应数据库表
    const PV_REAL    = 2;
    const LIKE_INIT  = 3;
    /**
     * 批量保存
     * @param $data
     * @return bool|mixed
     */
    public function saveBatch($data)
    {
        foreach ($data as $k => $d) {
            $data[$k]['updateTime'] = time();
        }
        return $this->save($data, self::TABLE_NAME);
    }
    /**
     *更新日志表
     */

    public function updateReportLog($data)
    {
        $db = $this->di->get('cas')->getDbServer('dbsem');

        if (!empty($data)) {
            $logData = array(
                'tryTimes' => $data['tryTimes'],
                'success'  => $data['success'],
                'lastTry'  => $data['lastTry'],
            );
            $condition = array(
                'conditions' => 'userId = ' . $data['userId'] . ' and platform=' . $data['platform'] . " and date='" . $data['date'] . "'",
            );
            $db->update('report_log', array_keys($logData), array_values($logData), $condition);
        }
    }
    /**
     * 预备今日要处理的数据
     * @author lixiumeng
     * @datetime 2018-01-08T14:15:02+0800
     * @param    string                   $date [description]
     * @return   [type]                         [description]
     */
    public function prepareUpdateAccount($date = '')
    {
        $db = $this->di->get('cas')->getDbServer('dbsem');

        // 检查是否有上次导致中断的账户, 因为今天的数据还未插入, 所以没有处理的一定是未完成的数据
        $sql         = "select * from report_log where tryTimes = 0 and success = 0 limit 1";
        $faildRecord = $db->fetchOne($sql);
        if (!empty($faildRecord)) {
            // 将失败的账户数据标记为不可用,tryTimes 改为100,这样本次就不会处理该数据了
            $faildRecord['tryTimes'] = 100;
            $this->updateReportLog($faildRecord);
        }

        $sql = "select id from report_log where date = '{$date}'";
        $rt  = $db->fetchAll($sql);
        if (empty($rt)) {
            // 准备今天所有用户的数据
            $model       = new SemReportEsDataService($this->di);
            $users       = $model->getUserInfo();
            $userNameStr = implode('\',\'', array_unique(array_keys($users)));
            $sql         = "select userName,userId,platform,id from sem_account where userName in ('{$userNameStr}')";
            $accounts    = $db->fetchAll($sql);
            // 先从账户表里面查到用户对应的账户数据
            if (!empty($accounts)) {
                foreach ($accounts as $key => $v) {
                    # 每个账户插入一条数据
                    $data = [
                        'date'     => $date,
                        'userName' => $v['userName'],
                        'userId'   => $v['userId'],
                        'platform' => $v['platform'],
                        'device'   => 0,
                        'remark'   => '',
                        'tryTimes' => 0,
                        'success'  => 0,
                    ];
                    $db->insert('report_log', array_values($data), array_keys($data));
                }
            }
        }
    }

    /**
     * 获取要更新的账户信息
     * @author lixiumeng
     * @datetime 2018-01-08T14:19:16+0800
     * @return   [type]                   [description]
     */
    public function getUpdateAccount()
    {
        $db     = $this->di->get('cas')->getDbServer('dbsem');
        $config = $this->di->get('config')->sematcconfig;
        $limit  = !empty($config->maxtry) ? $config->maxtry : 10;
        // 获取以往要处理的数据
        $sql = "select date,userId,userName,lastTry,tryTimes,platform from report_log where success = 0 and tryTimes <= {$limit} order by platform";
        $rt  = $db->fetchAll($sql);
        if (!empty($rt)) {
            return ['error' => 0, 'data' => $rt];
        } else {
            return ['error' => 1000, 'data' => ''];
        }
    }

    /**
     * [getUnnormal description]
     * @author lixiumeng
     * @datetime 2018-01-12T18:41:17+0800
     * @return   [type]                   [description]
     */
    public function getUnnormal()
    {
        $db  = $this->di->get('cas')->getDbServer('dbsem');
        $sql = "select date,userId,userName,lastTry,tryTimes,platform from report_log where success = 0 and tryTimes >= 100";
        $rs  = $db->fetchAll($sql);
        if (!empty($rs)) {
            return $rs;
        } else {
            return [];
        }
    }

    /**
     * 统计账户每天的消费信息
     * @author lixiumeng
     * @datetime 2018-02-28T16:48:16+0800
     * @return   [type]                   [description]
     */
    public function statisticsAccountCost($date = '')
    {
        $date = empty($date) ? date("Y-m-d 00:00:00") : $date;
        $db  = $this->di->get('cas')->getDbServer('dbsem');
        $sql = "select userName,cost,platform,device,userId from sem_account_report_all where dateTime = '{$date}'";
        $rt  = $db->fetchAll($sql);
        $res = [
            "百度" => [
                "百度pc"   => 0,
                "百度wap"  => 0,
                "summary"  => 0,
            ],
            "搜狗" => [
                "搜狗pc" => 0,
                "搜狗wap" => 0,
                "summary" => 0,
            ],
            "360" => [
                "360pc" => 0,
                "360wap" => 0,
                "summary" => 0,
            ],
            "神马" => [
                "神马pc" => 0,
                "神马" => 0,
                "summary" => 0,
            ],
            "total" => [
                "pc" => 0,
                "wap" => 0,
                "summary" => 0,
            ],
        ];

        if (!empty($rt)) {
            // 获取用户列表
            $model       = new SemReportEsDataService($this->di);
            $users       = $model->getUserInfo();
            foreach ($rt as $value) {
                $userName = trim($value['userName']);
                if (!empty($users[$userName])) {
                    $users[$userName]['cost'] = $value['cost'];
                } 
            }
            foreach ($users as $k => $v) {
                $res[$v['platform']][$v['cateplatform']] += $v['cost'];
                $res[$v['platform']]['summary'] += $v['cost'];

                // echo $v['cateplatform']."\n";
                if (substr($v['cateplatform'],-2) == 'pc') {
                    $res['total']['pc'] += $v['cost'];
                } 
                // 如果是wap 或者 神马
                if($v['cateplatform'] == '神马' || substr($v['cateplatform'],-3) == 'wap') {
                    $res['total']['wap'] += $v['cost'];
                }
                $res['total']['summary'] += $v['cost'];
            }
        }
        return $res;
    }

}
