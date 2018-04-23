<?php

use Lvmama\Common\Utils\ArrayUtils;
use Phalcon\CLI\Task;
use \Lvmama\Cas\Component\Kafka\Producer;
use \Lvmama\Common\Utils\UCommon;
use \Semsearch\Account\AccountService;
use \Semsearch\Account\GetAccountInfoRequest;
use \Semsearch\Adgroup\AdgroupService;
use \Semsearch\Adgroup\GetAdgroupRequest;
use \Semsearch\Campaign\CampaignService;
use \Semsearch\Campaign\GetCampaignRequest;
use \Semsearch\Keyword\GetWordRequest;
use \Semsearch\Keyword\KeywordService;
use \Semsearch\Report\GetReportRequest;
use \Semsearch\Report\ReportService;

/**
 * 百度/360/神马 等 搜索接口定时任务
 *
 * @author libiying
 *
 */
class SemsearchTask extends Task
{

    /**
     *
     * @var \Phalcon\DiInterface
     */
    private $di;

    /**
     * @var \Lvmama\Cas\Service\SemAccountBaseDataService
     */
    private $account;

    /**
     * @var \Lvmama\Cas\Service\SemKeywordBaseDataService
     */
    private $keyword;

    /**
     * @var \Lvmama\Cas\Service\SemAdgroupBaseDataService
     */
    private $adgroup;

    /**
     * @var \Lvmama\Cas\Service\SemCampaignBaseDataService
     */
    private $campaign;

    /**
     * @var \Lvmama\Cas\Service\RedisDataService;
     */
    private $redis;

    /**
     *
     * @see \Phalcon\DI\Injectable::setDI()
     */
    public function setDI(Phalcon\DiInterface $dependencyInjector)
    {
        parent::setDI($dependencyInjector);

        $this->di         = $dependencyInjector;
        $this->account    = $dependencyInjector->get('cas')->get('sem_account_service');
        $this->keyword    = $dependencyInjector->get('cas')->get('sem_keyword_service');
        $this->adgroup    = $dependencyInjector->get('cas')->get('sem_adgroup_service');
        $this->campaign   = $dependencyInjector->get('cas')->get('sem_campaign_service');
        $this->report_all = $dependencyInjector->get('cas')->get('sem_reoprt_all_service');
        $this->redis      = $dependencyInjector->get('cas')->get('redis_data_service');
    }

    /**
     * 推送关键词粒度的分时报表数据
     * @param array $params
     *  参数1：userIds 用户id，
     *  参数2：devices 推广设备，
     *  参数3：number 数量，
     *  参数4：unitOfTime 汇总类型
     * @example php ~/web/php-cas/cas/app/ts/ts.php semsearch putRealTimeData 2908883 1,2 100
     */
    public function putRealTimeDataAction($params)
    {

        $userIds    = isset($params[0]) ? $params[0] : null;
        $devices    = isset($params[1]) ? explode(',', $params[1]) : array(0, 1, 2);
        $number     = isset($params[2]) ? intval($params[2]) : 1000;
        $unitOfTime = isset($params[3]) ? intval($params[3]) : 7;
        $startDate  = isset($params[4]) ? $params[4] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));
        $endDate    = isset($params[5]) ? $params[5] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));

//        $typeName = array('userName', 'campaignName', 'adgroupName', 'keyword');
        //        $performanceData = array("impression","click","cost","ctr","cpc");

        $config   = $this->di->get('config')->kafka->baiduSearchProducer->toArray();
        $producer = new Producer($config);

        $condition = $userIds ? array(
            'userId in' => "(" . $userIds . ")",
        ) : null;
        $accounts = $this->account->getAccountList($condition);

        $sum = 0;
        foreach ($accounts as $account) {
            if ($unitOfTime == 5) {
                if ($account['platform'] == \Semsearch\SearchType::BAIDU) {
                    $fp = fopen(APPLICATION_PATH . '/logs/reportdata/report.' . $startDate . '.txt', 'a');
                } elseif ($account['platform'] == \Semsearch\SearchType::SANLIULING) {
                    $fp = fopen(APPLICATION_PATH . '/logs/reportdata/report.socom.' . $startDate . '.txt', 'a');
                } elseif ($account['platform'] == \Semsearch\SearchType::SHENMA) {
                    $fp = fopen(APPLICATION_PATH . '/logs/reportdata/report.smcn.' . $startDate . '.txt', 'a');
                } elseif ($account['platform'] == \Semsearch\SearchType::SOGOU) {
                    $fp = fopen(APPLICATION_PATH . '/logs/reportdata/report.sogou.' . $startDate . '.txt', 'a');
                }
            }

            $userId   = $account['userId'];
            $platform = $account['platform'];
            foreach ($devices as $device) {
                $req = array(
//                    'performanceData' => $performanceData,
                    //                    'levelOfDetails' => 11, //关键词粒度 6(word) 11(keyword)
                    //                    'reportType' => 14,   //关键词类型  9(word) 14(keyword)
                    'unitOfTime' => intval($unitOfTime), //7分时 5分日 8汇总
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                    'number'     => $number, //返回条数，默认1000
                    //                    'order' => true,
                    'device'     => intval($device),
                );
                $service = new ReportService($platform);
                $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
                $request = new GetReportRequest($req, $platform);
                $reports = $service->getReport($request);

                $head = $service->getJsonHeader();
                
                foreach ($reports as $key => $re) {
                    $keyword       = $this->keyword->getOneKeyword(array('keywordId = ' => "'" . $re['id'] . "'", 'platform = ' => $platform), 'keywordId, losc');
                    $losc          = $keyword ? $keyword['losc'] : '';
                    $reports[$key] = ArrayUtils::arrayInsert($re, 1, array('losc' => $losc));
                }

                if (isset($head->desc) && $head->desc == 'success') {

                    $total    = count($reports);
                    $patch    = 20;
                    $data     = array();
                    $count    = 0;
                    $dateTime = null;
                    foreach ($reports as $key => $report) {

                        if (!$dateTime) {
                            $dateTime = $report['date'];
                        }
                        //如果是当前小时，或者前一个小时的数据，则推送
                        if (strtotime($dateTime) <= (strtotime($report['date']) + 3600)) {
                            $data[] = $report;
                            $count++;
                        }
                        //1、如果推送至kafka的数据量达到 $patch 条
                        //2、当不满足条件1时，如果最新数据的时间不等于当前数据的时间-1小时（即把推送范围往后延迟一小时）
                        if ($patch == $count || strtotime($dateTime) > (strtotime($report['date']) + 3600) || ($key + 1) == $total) {

                            if ($unitOfTime == 7) {
                                //分时报表推送kafka进行实时处理
                                $producer->sendMsg(json_encode($data));
                                echo date('Y-m-d H:i:s', time()) . ":" . json_encode($data) . "\n";
                            } elseif ($unitOfTime == 5) {
                                //分日报表写入文本直接导入到库
                                $content = '';
                                foreach ($data as $d) {
                                    $content .= implode("\t", $d) . "\n";
                                }
                                fwrite($fp, $content);
                            }

                            $sum = $sum + $count;
                            //重置计数
                            $data  = array();
                            $count = 0;
                            if (strtotime($dateTime) > (strtotime($report['date']) + 3600)) {
                                break;
                            }
                            usleep(50);
                        }
                    }

                }
            }
            if ($unitOfTime == 5) {
                fclose($fp);
            }
        }

        echo date('Y-m-d H:i:s', time()) . " 共推送：" . $sum . "条数据 \n";
        return;
    }

    /**
     * 推送关键词粒度的分时报表数据
     * @param array $params
     *  参数1：userIds 用户id，
     *  参数2：devices 推广设备，
     *  参数3：number 数量，
     *  参数4：unitOfTime 汇总类型
     * @example php ~/web/php-cas/cas/app/ts/ts.php semsearch putRealTimeData 2908883 1,2 100
     */
    public function putAccountReportAction($params)
    {

        $userIds    = isset($params[0]) ? $params[0] : null;
        $devices    = isset($params[1]) ? explode(',', $params[1]) : array(0, 1, 2);
        $number     = isset($params[2]) ? intval($params[2]) : 1000;
        $unitOfTime = 5;
        $startDate  = isset($params[4]) ? $params[4] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));
        $endDate    = isset($params[5]) ? $params[5] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));

        $condition = $userIds ? array(
            'userId in' => "(" . $userIds . ")",
        ) : null;
        $accounts = $this->account->getAccountList($condition);

        foreach ($accounts as $account) {

            $userId   = $account['userId'];
            $platform = $account['platform'];
            foreach ($devices as $device) {
                $req = array(
                    'latitude'   => 'account',
                    'unitOfTime' => intval($unitOfTime), //7分时 5分日 8汇总
                    'startDate'  => $startDate,
                    'endDate'    => $endDate,
                    'number'     => $number, //返回条数，默认1000
                    'device'     => intval($device),
                );
                $service = new ReportService($platform);
                $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
                $request = new GetReportRequest($req, $platform);
                $reports = $service->getAccountReport($request);
                $head    = $service->getJsonHeader();
                var_dump($head, $reports);

                if (isset($head->desc) && $head->desc == 'success') {
                    //插入或更新数据库
                    if (!empty($reports)) {

                    } else {
                        echo "平台" . $account['platform'] . ",用户id" . $account['userId'] . ",用户名" . $account['userId'] . "api调用没有返回报表数据\n";
                    }
                } else {
                    echo "平台" . $account['platform'] . ",用户id" . $account['userId'] . ",用户名" . $account['userId'] . "api调用不成功\n";
                }
            }

        }

        return;
    }
    /**
     * @param $params
     * @example php cas/app/ts/ts.php semsearch putAccount
     */
    public function putAccountAction($params)
    {

        $userIds = isset($params[0]) ? $params[0] : null;

        $condition = $userIds ? array(
            'userId in' => "(" . $userIds . ")",
        ) : null;

        $accounts = $this->account->getAccountList($condition);

        foreach ($accounts as $account) {
            $userId                = $account['userId'];
            $platform              = $account['platform']; //1-baidu,2-360,3-shenma
            $getAccountInfoRequest = new GetAccountInfoRequest($platform);
            $service               = new AccountService($platform);
            $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
            $response = $service->getAccountInfo($getAccountInfoRequest);
            $head     = $service->getJsonHeader($platform);

            if (isset($head->desc) && $head->desc == 'success') {
                $users = $response->data;

                foreach ($users as $user) {
                    if (!empty($user->regionTarget) && is_array($user->regionTarget)) {
                        $regionTarget       = implode(',', $user->regionTarget);
                        $user->regionTarget = $regionTarget;
                    }
                    if (isset($head->rquota)) {
                        $user->rquota = $head->rquota;
                    }
                    $this->account->update($user->userId, (array) $user, $platform);
                    echo date('Y-m-d H:i:s', time()) . " update:" . $user->userId . ' data:' . json_encode($user) . "\n";
                }
            }
        }
    }

    /**
     * 计划数据落地
     *  参数1：userId
     *  参数2：campaignIds 当不传或者为null时，为全量
     * @param $params
     * @example php ~/web/php-cas/cas/app/ts/ts.php semsearch putCampaign 2908883
     */
    public function putCampaignAction($params)
    {
        $userIds = explode(',', $params[0]);
        $ids     = isset($params[1]) ? explode(',', $params[1]) : null; //计划id

        foreach ($userIds as $userId) {
            //根据id获取属于哪个平台
            $userCondition['userId = '] = $userId;
            $userInfo                   = $this->account->getOneAccount($userCondition);
            if ($userInfo['platform']) {
                $platform = $userInfo['platform'];
                $service  = new CampaignService($platform);
                $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
                $request  = new GetCampaignRequest($ids, $platform);
                $response = $service->getCampaign($request);
                $head     = $service->getJsonHeader();
                if (isset($head->desc) && strpos($head->desc, 'success') !== false) {
                    $campaigns = $response->data;
                    foreach ($campaigns as $campaign) {
                        $campaign->userId = $userId;
                        if (isset($campaign->regionTarget) && is_array($campaign->regionTarget)) {
                            $regionTarget           = implode(',', $campaign->regionTarget);
                            $campaign->regionTarget = $regionTarget;
                        }

                        $condition = array(
                            'campaignId = ' => $campaign->campaignId,
                            'userId = '     => $userId,
                        );
                        $exist = $this->campaign->getOneCampaign($condition);
                        if ($exist) {
                            $this->campaign->update($campaign->campaignId, (array) $campaign, $platform);
                            echo date('Y-m-d H:i:s', time()) . " update:" . $campaign->campaignId . ' data:' . json_encode($campaign) . "\n";
                        } else {
                            $campaign->platform = $platform;
                            $this->campaign->insert((array) $campaign);
                            echo date('Y-m-d H:i:s', time()) . " insert:" . $campaign->campaignId . ' data:' . json_encode($campaign) . "\n";
                        }
                    }
                }
            }

        }

    }

    /**
     * 单元数据落地
     *  参数1：userId
     *  参数2：idType 3：计划；5：单元
     *  参数3：ids 根据idType变化含义，当idType=3时表示计划id，若不传，取全量；当idType=5时表示单元id，必传
     * @param $params
     * @example php ~/web/php-cas/cas/app/ts/ts.php semsearch putAdgroup 2908883 5 2317006635,2317006632
     */
    public function putAdgroupAction($params)
    {
        $userIds    = explode(',', $params[0]);
        $idType     = isset($params[1]) ? intval($params[1]) : 5; //3计划 5单元
        $requestIds = isset($params[2]) ? explode(',', $params[2]) : array();

        foreach ($userIds as $userId) {
            //根据id获取属于哪个平台
            $userCondition['userId = '] = $userId;
            $userInfo                   = $this->account->getOneAccount($userCondition);
            if ($userInfo['platform']) {
                $platform = $userInfo['platform'];
                $service  = new AdgroupService($platform);
                $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));

                //取全量
                if ($idType == 3) {
                    $condition              = array();
                    $condition['userId = '] = $userId;

                    if ($requestIds) {
                        $condition['campaignId in'] = "(" . implode(",", $requestIds) . ")";
                    }
                    $total = $this->campaign->getCampaignTotal($condition);

                    $size = 10;
                    for ($page = 0; $total > $page * $size; $page++) {
                        $ids       = array();
                        $limit     = ($page * $size) . ',' . $size;
                        $campaigns = $this->campaign->getCampaignList($condition, $limit, 'campaignId');
                        foreach ($campaigns as $campaign) {
                            $ids[] = $campaign['campaignId'];
                        }
                        $this->saveAdgroup($userId, $ids, $idType, $service, $platform);
                    }
                } elseif ($idType == 5 && $requestIds) {
                    $this->saveAdgroup($userId, $requestIds, $idType, $service, $platform);
                }
            }

        }

    }

    /**
     * 拉取关键词信息
     *  参数1：userId
     *  参数2：idType 5：单元；11：关键词
     *  参数3：ids 根据idType变化含义，当idType=5时表示单元id，若不传，取全量；当idType=11时表示关键词id，必传
     * @param $params
     * @example php ~/web/php-cas/cas/app/ts/ts.php semsearch putKeyword 2908883 11 3803419586,5645402615
     */
    public function putKeywordAction($params)
    {
        $userIds    = explode(',', $params[0]);
        $idType     = isset($params[1]) ? intval($params[1]) : 11; //5单元 11关键词
        $requestIds = isset($params[2]) ? explode(',', $params[2]) : array();
        $ttl        = isset($params[3]) ? $params[3] : 60 * 60 * 24 * 3; //redis缓存时间

        foreach ($userIds as $userId) {
            //根据id获取属于哪个平台
            $userCondition['userId = '] = $userId;
            $userInfo                   = $this->account->getOneAccount($userCondition);

            if ($userInfo['platform']) {
                $platform = $userInfo['platform'];

                $service = new KeywordService($platform);
                $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));

                //取全量
                if ($idType == 5) {
                    $condition = array(
                        'userId = ' => $userId,
                    );
                    if ($requestIds) {
                        $condition['adgroupId in'] = "(" . implode(",", $requestIds) . ")";
                    }
                    $total = $this->adgroup->getAdgroupTotal($condition);

                    $size = 20;
                    for ($page = 0; $total > $page * $size; $page++) {
                        $ids      = array();
                        $limit    = ($page * $size) . ',' . $size;
                        $adgroups = $this->adgroup->getAdgroupList($condition, $limit, 'adgroupId');
                        foreach ($adgroups as $adgroup) {
                            $ids[] = $adgroup['adgroupId'];
                        }

                        $this->saveKeyword($userId, $ids, $idType, $service, $ttl, $platform);
                    }
                } elseif ($idType == 11 && $requestIds) {
                    $this->saveKeyword($userId, $requestIds, $idType, $service, $ttl, $platform);
                }
            }

        }

    }

    private function buildReport($data, $performanceData, $typeName, $device, $unitOfTime)
    {
        $arr = array();
        foreach ($data as $d) {
            $a['id'] = $d->id;
//            $redis_key = str_replace('{keywordId}', $d->id, \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_KEYWORD_LOSC);
            //            $losc = $this->redis->dataGet($redis_key);
            $keyword = $this->keyword->getOneKeyword(array('keywordId = ' => "'" . $d->id . "'", 'platform = ' => 1), 'keywordId, losc');
            $losc    = $keyword ? $keyword['losc'] : '';

            $a['losc'] = $losc ? $losc : '';
            foreach ($d->kpis as $key => $pki) {
                $a[$performanceData[$key]] = $pki;
            }
            foreach ($d->name as $key => $name) {
                $a[$typeName[$key]] = $name;
            }
            $a['date']       = $d->date;
            $a['device']     = (string) $device;
            $a['unitOfTime'] = (string) $unitOfTime;
            $arr[]           = $a;
        }
        return $arr;
    }

    private function saveAdgroup($userId, $ids, $idType, $service, $platform)
    {

        $request = new GetAdgroupRequest($ids, $idType, $platform);

        $response = $service->getAdgroup($request);
        $head     = $service->getJsonHeader();

        if (isset($head->desc) && strpos($head->desc, 'success') !== false) {
            $adgroups = $response->data;

            foreach ($adgroups as $adgroup) {
                $adgroup->userId = $userId;
                $this->adgroup->saveBatch(array((array) $adgroup), $platform);
                echo date('Y-m-d H:i:s', time()) . " save:" . $adgroup->adgroupId . ' data:' . json_encode($adgroup) . "\n";
            }

        }
    }

    private function saveKeyword($userId, $ids, $idType, $service, $ttl, $platform)
    {

        $request  = new GetWordRequest($ids, $idType, $platform);
        $response = $service->getWord($request);
        $head     = $service->getJsonHeader();

        if (isset($head->desc) && strpos($head->desc, 'success') !== false) {
            $keywords = $response->data;

            foreach ($keywords as $keyword) {
                $losc = '';
                $uri  = array();
                if (isset($keyword->pcDestinationUrl)) {
                    $uri = explode('?', $keyword->pcDestinationUrl);
                } elseif (isset($keyword->mobileDestinationUrl)) {
                    $uri = explode('?', $keyword->mobileDestinationUrl);
                }
                foreach ($uri as $ui) {
                    if (isset($ui)) {
                        $arr = \Lvmama\Common\Utils\ArrayUtils::getUri2array($ui);
                        if (isset($arr['losc'])) {
                            $losc = $arr['losc'];
                        }
                    }
                }
                if (!isset($keyword->campaignId)) {
                    //根据adgroupId查询到campaignId
                    $adgroupCondition['platform = ']  = $platform;
                    $adgroupCondition['adgroupId = '] = $keyword->adgroupId;
                    $adgroup                          = $this->adgroup->getOneAdgroup($adgroupCondition);
                    $keyword->campaignId              = isset($adgroup['campaignId']) ? $adgroup['campaignId'] : '';
                }
                $keyword->losc   = $losc;
                $keyword->userId = $userId;

                //redis存一份losc，给storm使用(实时报表)
                $keyword_key = str_replace('{keywordId}', $keyword->keywordId, \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_KEYWORD_LOSC);
                $this->redis->dataSet($keyword_key, $keyword->losc, $ttl);
                $losc_key = str_replace('{losc}', $keyword->losc, \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_LOSC_KEYWORD);
                $this->redis->dataSet($losc_key, $keyword->keywordId, $ttl);

                try {
                    $this->keyword->saveBatch(array((array) $keyword), $platform);
                    echo date('Y-m-d H:i:s', time()) . " save:" . $keyword->keywordId . ' data:' . json_encode($keyword) . "\n";
                } catch (Exception $ex) {
                    echo $ex->getMessage() . "\n";
                    echo date('Y-m-d H:i:s', time()) . " error:" . $keyword->keywordId . ' data:' . json_encode($keyword) . "\n";
                }
            }

        }
    }

    public function otherJobAction()
    {

//360
        //apiKey：9416a19fb40ab1a767e47e990d6a3e91
        //apiSecret：233d967bfaa62d0f102b075287303f65
        //        $data = array(
        //            'format' => 'json',
        //            'username' => 'lvmama.com',
        //            'password' => 'bf3addc5c42d1f78fd53254953483201b9c4475e14b5dc3837692a4cd651d645',
        //        );
        //        $service = new Semsearch\Account\AccountService(\Semsearch\SearchType::SANLIULING);
        //        $service->setAuthHeader(array(
        //            'serveToken:1278876688000',
        //            'apiKey:9416a19fb40ab1a767e47e990d6a3e91',
        //        ));
        //        $body = $service->getAccountInfo($data);
        //        $header = $service->getJsonHeader();
        //        var_dump($body, $header);die;
        //神马
        //        $request = array(
        //            'requestData' => array('account_all')
        //        );
        //        $service = new Semsearch\Account\AccountService(\Semsearch\SearchType::SHENMA);
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader('smlvmama01'));
        //        $body = $service->getAccountInfo($request);
        //        $header = $service->getJsonHeader();
        //        var_dump($body, $header);die;
        //百度
        //        $userId = '2908883';
        //        $getAccountInfoRequest = new Semsearch\Account\GetAccountInfoRequest();
        //        $service = new Semsearch\Account\AccountService();
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
        //        $fields=array("userId", "cost", "userStat");
        //        $getAccountInfoRequest->setAccountFields($fields);
        //        $response = $service->getAccountInfo($getAccountInfoRequest);
        //        $head = $service->getJsonHeader();
        //        var_dump($response, $head);die;
        //        $userId = '2908883';
        //        $ids = array(2317006635,2317006632);
        //        $fields = array("adgroupName", "status");
        //        $idType = 5;
        //        $service = new \Semsearch\Adgroup\AdgroupService();
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
        //        $request=new \Semsearch\Adgroup\GetAdgroupRequest();
        //        $request->setIds($ids);
        //        $request->setAdgroupFields($fields);
        //        $request->setIdType($idType); //3计划 5单元
        //        $response=$service->getAdgroup($request);
        //        $head = $service->getJsonHeader();
        //        var_dump($response, $head);die;
        //        $userId = '2908883';
        //        $ids = array(6068985);
        //        $fields = array("campaignName", "campaignType", "regionTarget", "status");
        //        $service = new \Semsearch\Campaign\CampaignService();
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
        //        $request=new \Semsearch\Campaign\GetCampaignRequest();
        //        $request->setCampaignIds($ids);
        //        $request->setCampaignFields($fields);
        //        $response = $service->getCampaign($request);
        //        $head = $service->getJsonHeader();
        //        var_dump($response, $head);die;
        //        $userId = '2908883';
        //        $ids = array(3803419586);
        //        $idType = 11;
        //        $fields = array("pcDestinationUrl", "mobileDestinationUrl");
        //        $service = new \Semsearch\Keyword\KeywordService();
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
        //        $request = new \Semsearch\Keyword\GetWordRequest();
        //        $request->setIds($ids);
        //        $request->setIdType($idType); //5单元 11关键词
        //        $request->setWordFields($fields);
        //        $request->setGetTemp(0);
        //        $response = $service->getWord($request);
        //        $head = $service->getJsonHeader();
        //        var_dump($response, $head);die;
        //        $userId = '2908883';
        //        $devices = 0;
        //        $number = 10;
        //        $unitOfTime = 7;
        //        $startDate = isset($params[4]) ? $params[4] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));
        //        $endDate = isset($params[5]) ? $params[5] : ($unitOfTime == 5 ? date('Y-m-d', strtotime("-1 day")) : date('Y-m-d', time()));
        //        $performanceData = array("impression","click","cost","ctr","cpc");
        //        $req = array(
        //            'performanceData' => $performanceData,
        //            'levelOfDetails' => 11, //关键词粒度 6(word) 11(keyword)
        //            'reportType' => 14,   //关键词类型  9(word) 14(keyword)
        //            'unitOfTime' => intval($unitOfTime),     //7分时 5分日 8汇总
        //            'startDate' => $startDate,
        //            'endDate' => $endDate,
        //            'number' => $number, //返回条数，默认1000
        //            'order' => true,
        //            'device' => $devices,
        //        );
        //        $service = new \Semsearch\Report\ReportService();
        //        $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
        //        $request=new \Semsearch\Report\GetRealTimeDataRequest();
        //        $type=new \Semsearch\Report\ReportRequestType($req);
        //        $request->setRealTimeRequestType($type);
        //        $response=$service->getRealTimeData($request);
        //        $head=$service->getJsonHeader();
        //        var_dump($response, $head);die;

//
        //        $redis_key = str_replace('{keywordId}', '5645402615', \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_KEYWORD_LOSC);
        //        $losc = $this->redis->dataGet($redis_key);
        //        var_dump($redis_key, $losc);die;

//        $data = array(
        //            'baidusearch:losc:order:017240:2017-02-27-16' => array(
        //                'LOSC_ID' => '017240',
        //                'AMOUNT' => 111,
        //                'ORDERNUM' => 1,
        //            ),
        //            'baidusearch:losc:order:016816:2017-02-27-16' => array(
        //                'LOSC_ID' => '016816',
        //                'AMOUNT' => 222,
        //                'ORDERNUM' => 2,
        //            ),
        //            'baidusearch:losc:order:017248:2017-02-27-16' => array(
        //                'LOSC_ID' => '017248',
        //                'AMOUNT' => 333,
        //                'ORDERNUM' => 3,
        //            ),
        //        );
        //        foreach ($data as $key => $value){
        //            $ttl = 60*60*24*2;
        //            $this->redis->dataSet($key, json_encode($value), $ttl);
        //            echo date('Y-m-d H:i:s', time()) . " redis_key:" . $key . ' data:' . json_encode($value) . "\n";
        //        }
        //        return;

        $num      = 0;
        $adgroups = $this->adgroup->getAdgroupList("");
        foreach ($adgroups as $adgroup) {

            $keywords = $this->keyword->getKeywordList(array('adgroupId = ' => $adgroup['adgroupId']));

            foreach ($keywords as $keyword) {
                $redis_key = str_replace('{keywordId}', $keyword['keywordId'], \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_KEYWORD_LOSC);
                $ttl       = 60 * 60 * 24 * 7;
                $this->redis->dataSet($redis_key, $keyword['losc'], $ttl);
                $num++;
                echo date('Y-m-d H:i:s', time()) . " redis_key:" . $redis_key . ' data:' . $keyword['losc'] . "\n";
                usleep(10);
            }
        }
        echo date('Y-m-d H:i:s', time()) . " 共推送：" . $num . "条数据 \n";

//        $num = 0;
        //        $loscs = $this->keyword->query("select count(*),keywordId, losc from sem_keyword GROUP BY losc", 'All');
        //        foreach ($loscs as $losc){
        //            $losc_key = str_replace('{losc}', $losc['losc'], \Lvmama\Cas\Service\RedisDataService::REDIS_BAIDUSEARCH_LOSC_KEYWORD);
        //            $ttl = 60*60*24*7;
        //            $this->redis->dataSet($losc_key, $losc['keywordId'], $ttl);
        //            $num ++;
        //            echo date('Y-m-d H:i:s', time()) . " redis_key:" . $losc_key . ' data:' . $losc['keywordId'] . "\n";
        //        }
        //        echo date('Y-m-d H:i:s', time()) . " 共推送：" . $num . "条数据 \n";
    }

    /*************************************************************
     *
     *
     *
     *
     *
     *
     *
     *
     * 以下方法归属于任务: 根据sem账户获取消费信息
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     **************************************************************/

    /**
     * 从媒体获取账户的消费信息
     * @author lixiumeng
     * @datetime 2018-01-08T14:15:27+0800
     * @return   [type]                   [description]
     */
    public function getAccountCostFromMediaAction($params)
    {
        echo "------------------------".date("Y-m-d H:i:s")."拉取账户信息脚本执行------------------\n";
        $send         = 1;
        $this->config = empty($this->di->get('config')->sematcconfig) ? [] : $this->di->get('config')->sematcconfig;
        $this->csrv   = $this->di->get('cas')->get('sem_reoprt_all_service'); // 当前使用的service
        if (!empty($params[0]) && $params[0] == 1) {
            $date = date("Y-m-d 00:00:00", strtotime($params[1]));
        } else {
            $date = date("Y-m-d 00:00:00", (time() - 3600 * 24));
        }
        //修复某个账户某天的数据
        if(!empty($params[2])){
            $condition =  array(
                'userId in' => "(" . $params[2] . ")",
            ) ;
            $accounts = $this->account->getAccountList($condition);
            $accountReportFailed = array();
            foreach ($accounts as $v) {
                $this->_saveAccountReport($v['userId'], $v['userName'], $date, $v['platform'], 1, $accountReportFailed);
                echo "用户{$v['userId']}:{$v['userName']} 在 {$date} 的数据已处理\n";
            }
        }else{//正常拉取数据
            // 插入今日要处理的数据,如果存在就不插入
            $this->csrv->prepareUpdateAccount($date);
            // 获取今日所有要处理的数据 all = 遗留的数据 + 今日要处理的数据
            $rs       = $this->csrv->getUpdateAccount();
            $accounts = !empty($rs['data']) ? $rs['data'] : [];

            $accountReportFailed = array();
            foreach ($accounts as $v) {
                $this->_saveAccountReport($v['userId'], $v['userName'], $v['date'], $v['platform'], $v['tryTimes'], $accountReportFailed);
                echo "用户{$v['userId']}:{$v['userName']} 在 {$v['date']} 的数据已处理\n";
            }

            if (!empty($this->config->mailnotice) && $this->config->mailnotice == 1 && $send == 1) {
                $this->_sendNotifyEmail($accountReportFailed);
            }

            // 检查是否有上次导致脚本中断的记录
            $this->_checkFailed();
        }
        echo "--------------------------------------well,done----------------------------------\n";
    }

    /**
     * 处理失败的记录 tryTimes = 100 的记录
     * @author lixiumeng
     * @datetime 2018-01-12T18:39:44+0800
     * @return   [type]                   [description]
     */
    private function _checkFailed()
    {
        // 获取失败的账户
        $account = $this->csrv->getUnnormal();
        //处理之前异常的账户
        $accountReportFailed = [];
        foreach ($account as $v) {
            $this->_saveAccountReport($v['userId'], $v['userName'], $v['date'], $v['platform'], $v['tryTimes'], $accountReportFailed);
            echo "用户{$v['userId']}:{$v['userName']} 在 {$v['date']} 的数据已处理\n";
        }

    }

    /**
     * 拉取账户报表，并更新账户报表和日志表
     * @param $userId
     * @param $userName
     * @param $date
     * @param $platform
     * @param $tryTimes
     * @param $accountReportFailed
     */
    private function _saveAccountReport($userId, $userName, $date, $platform, $tryTimes, &$accountReportFailed)
    {
        $config      = $this->di->get('config')->sematcconfig;
        $tryMaxTimes = !empty($config->maxtry) ? $config->maxtry : 10;
        $tryMinTimes = !empty($config->mintry) ? $config->mintry : 4;
        $devices     = array(0); //0全部，1pc，2moblie
        $unitOfTime  = 5;

        $startDate = !empty($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d', strtotime("-1 day"));
        $endDate   = !empty($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d', strtotime("-1 day"));

        foreach ($devices as $device) {
            $req = array(
                'latitude'   => 'account',
                'unitOfTime' => intval($unitOfTime), //7分时 5分日 8汇总
                'startDate'  => $startDate,
                'endDate'    => $endDate,
                'device'     => intval($device),
            );
            $service = new ReportService($platform);
            $service->setAuthHeader(\Semsearch\Account::getAuthHeader($userId));
            $request = new GetReportRequest($req, $platform);

            $response = $service->getAccountReport($request, $userId);

            $head    = $response['head'];
            $reports = $response['reports'];
            $data    = array(
                'tryTimes' => $tryTimes + 1,
                'lastTry'  => date("Y-m-d H:i:s"),
                'userId'   => $userId,
                'platform' => $platform,
                'date'     => $date,
            );
            if (isset($head->desc) && $head->desc == 'success') {
                if (!empty($reports)) {
                    //插入or更新报表
                    $this->report_all->saveBatch($reports);
                    $data['success'] = 1;//拉取成功，有数据返回
                    //更新log表
                    $this->report_all->updateReportLog($data);
                    echo "{$userId} 账户 {$date} 拉取成功\n";
                } else {
                    //更新log表,发邮件
                    $data['success'] = 0;//表示拉取成功，无数据返回
                    $this->report_all->updateReportLog($data);

                    $rs = $this->_getFailedUserInfo($userId, $userName, $date, $head, 1);
                    //添加到未拉取成功的账户数组
                    if ($tryTimes > $tryMinTimes && $tryTimes <= $tryMaxTimes) {
                        $accountReportFailed[] = $rs;
                    }
                    
                    echo "{$userId} 账户 {$date} 未取到数据,返回码: {$rs['code']},信息: {$rs['infomation']}\n";
                }
            } else {
                //更新log表,发邮件
                $data['success'] = 0;
                $this->report_all->updateReportLog($data);
                $rs = $this->_getFailedUserInfo($userId, $userName, $date, $head, 2);
                if ($tryTimes > $tryMinTimes && $tryTimes <= $tryMaxTimes) {
                    //添加到未拉取成功的账户数组
                    $accountReportFailed[] = $rs;
                }
                echo "{$userId} 账户 {$date} 接口调用不成功,返回码: {$rs['code']},信息: {$rs['infomation']}\n";
            }
        }
    }
    /**
     * 获取拉取失败的账户信息
     * @param $userId
     * @param $userName
     * @param $date
     * @param $head
     * @param $flag,1表示未拉到数据，2表示接口未调成功
     * @return mixed
     */
    private function _getFailedUserInfo($userId, $userName, $date, $head, $flag)
    {
        $accountInfo['userId']   = $userId;
        $accountInfo['userName'] = $userName;
        $accountInfo['dateTime'] = $date;
        // $accountInfo['createTime'] = date("Y-m-d H:i:s");
        $userInfo = $this->_getUserInfo($userName);
        // $accountInfo['platform']   = $userInfo['platform'];
        $accountInfo['category'] = $userInfo['category'];
        $accountInfo['media']    = $userInfo['media'];
        if ($flag == 1) {
            $accountInfo['code']       = '--';
            $accountInfo['infomation'] = '调用成功，未拉取到数据';
        } else {
            $accountInfo['code']       = $head->resultCode;
            $accountInfo['infomation'] = $head->rsMsg;
        }
        return $accountInfo;
    }
    //获取账户品类和媒体
    private function _getUserInfo($userName)
    {
        $this->srv = $this->di->get('cas')->get('sem_report_service'); // 当前使用的service
        $userInfo  = $this->srv->getUserInfo();
        $platform  = $userInfo[$userName]['platform'];
        $category  = $userInfo[$userName]['category'];
        $media     = $userInfo[$userName]['media'];

        return array(
            'platform' => $platform,
            'category' => $category,
            'media'    => $media,
        );
    }

    /**
     * 发送异常提醒邮件
     * @author lixiumeng
     * @datetime 2018-01-10T16:27:56+0800
     * @param    array                    $record [description]
     * @return   [type]                           [description]
     */
    private function _sendNotifyEmail($record = [])
    {
        if (empty($record)) {
            return true;
        }
        $this->config = !empty($this->di->get('config')->sematcconfig) ? $this->di->get('config')->sematcconfig : array();
        $date         = date("Y-m-d");
        $tmp_arr      = $this->_array2table($record);
        $subject      = "SEM账户消费情况获取报告({$date})";
        $fmt_table    = isset($this->config->tablestyle) ? $this->config->tablestyle : 1;
        $sendUsers    = !empty($this->config->sendto) ? $this->config->sendto : 'lixiumeng@lvmama.com,zhuge@lvmama.com,zhouwenyi@lvmama.com';
        $token        = !empty($this->config->mailtoken) ? $this->config->mailtoken : '0b306786-3891-4f21-96b3-d6e57edff623';
        if ($fmt_table) {
            // 使用表格方式发送内容
            $body = "<table>";
            foreach ($tmp_arr as $value) {
                $body .= "<tr>";
                foreach ($value as $k => $v) {
                    $body .= "<td>{$v}</td>";
                }
                $body .= "</tr>";
            }
            $body .= "</table>";
        } else {
            // 使用普通文本
            $body = "";
            foreach ($tmp_arr as $value) {
                $body .= implode("   ", $value) . "\r\n";
            }
        }
        $text = "SEM账户消费情况报告({$date}):
         具体如下:

         {$body}
         ";

        $content = array(
            'token'   => $token,
            'content' => $text,
            'subject' => $subject,
            'tos'     => $sendUsers,
        );
        print_r(UCommon::curl('http://super.lvmama.com/channel_back/sendEmail/httpSend', 'POST', $content));
        echo "\n";
    }

    /**
     * 二维数组变为表格格式
     * @author lixiumeng
     * @datetime 2018-01-10T16:54:51+0800
     * @param    array                    $array [description]
     * @return   [type]                          [description]
     */
    private function _array2table($array = [])
    {
        $lines  = [];
        $header = [];
        foreach ($array as $key => $value) {
            foreach ($value as $k => $v) {
                if ($key == 0) {
                    $header[] = $k;
                }
                $lines[$key][] = $v;
            }
        }
        array_unshift($lines, $header);
        return $lines;
    }
}
