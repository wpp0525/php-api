<?php
use \Baidusearch\Account\GetAccountInfoRequest;
use \Baidusearch\Account\AccountService;
use \Baidusearch\Report\GetRealTimeDataRequest;
use \Baidusearch\Report\ReportRequestType;
use \Baidusearch\Report\ReportService;
use \Baidusearch\Keyword\GetWordRequest;
use \Baidusearch\Keyword\KeywordService;
use \Baidusearch\Adgroup\AdgroupService;
use \Baidusearch\Adgroup\GetAdgroupRequest;
use \Lvmama\Cas\Component\Kafka\Producer;
/**
 * 管理员控制器（本接口暂不对外提供服务）
 * 
 * @author libiying
 *
 */
class BaidusearchController extends ControllerBase {

    public function getAccountInfoAction(){

        $userId = 2908883;

        $getAccountInfoRequest = new GetAccountInfoRequest();
        $service = new AccountService();
        $service->setAuthHeader(\Baidusearch\Account::getAuthHeader($userId));

        $fields=array("userId", "cost");
        $getAccountInfoRequest->setAccountFields($fields);
        $service->setIsJson(true);
        $response = $service->getAccountInfo($getAccountInfoRequest);
        $head = $service->getJsonHeader();

        $this->jsonResponse(array('header' => $head, 'body' => $response));
    }

    public function getRealTimeDataAction(){
        $userId = 2908883;

        $typeName = array('userName', 'campaignName', 'adgroupName', 'keyword');
        $performanceData = array("impression","click","cost","ctr","cpc");
        $params = array(
            'performanceData' => $performanceData,
            'levelOfDetails' => 11, //关键词粒度 6(word) 11(keyword)
            'reportType' => 14,   //关键词类型  9(word) 14(keyword)
            'unitOfTime' => 5,     //7分时 5分日 8汇总
            'startDate' => '2017-02-16',
            'endDate' => '2017-02-16',
            'number' => 5000, //返回条数，默认1000 最高10000
            'order' => true,
            'device' => 0,
        );

        $service = new ReportService();
        $service->setAuthHeader(\Baidusearch\Account::getAuthHeader($userId));
        $request = new GetRealTimeDataRequest();
        $type = new ReportRequestType($params);
        $request->setRealTimeRequestType($type);
        $response = $service->getRealTimeData($request);
        $head = $service->getJsonHeader();

        if(isset($head->desc) && $head->desc == 'success'){
            $report = $this->buildReport($response->data, $performanceData, $typeName, 0);

//            $config = $this->getDI()->get('config')->kafka->baiduSearchProducer->toArray();
//            $rk = new Producer($config);
//            $rk->sendMsg(json_encode($report));
//            echo json_encode($report);
            var_dump(count($report));
            return;
        }
        
        $this->jsonResponse(array('header' => $head, 'body' => $response));
    }

    private function buildReport($data, $performanceData, $typeName, $device){
        $arr = array();
        foreach ($data as $d){
            $a['id'] = $d->id;
            foreach ($d->kpis as $key => $pki){
                $a[$performanceData[$key]] = $pki;
            }
            foreach ($d->name as $key => $name){
                $a[$typeName[$key]] = $name;
            }
            $a['date'] = $d->date;
            $a['device'] = $device;
            $arr[] = $a;
        }
        return $arr;
    }

    public function getAdgroupAction(){



        $userId = 2908883;
        $ids = array(9171302);

        $service = new AdgroupService();
        $service->setAuthHeader(\Baidusearch\Account::getAuthHeader($userId));
        $request=new GetAdgroupRequest();
        $fields=array("adgroupName", "status"); //出了基本字段外的额外字段

        $request->setIds($ids);
        $request->setAdgroupFields($fields);
        $request->setIdType(3); //3计划 5单元
        $service->setIsJson(true);
        $response=$service->getAdgroup($request);
        echo json_encode($response)."\n";
//        $head=$service->getJsonHeader();
//        echo "status:".json_encode($head)."\n";

        return;
    }

    public function getKeywordAction(){
        $this->keyword = $this->di->get('cas')->get('sem_keyword_service');


        $userId = 2908883;
//        $ids = array(10558209002,4254146573,4254147164,5645402615,4254147194);
        //, 181034484, 181034493
        $ids = array(
            0=>
   "96417523",
            1=>
   "97500624",
            2=>
   "97505546",
            3=>
   "111176746",
            4=>
   "111176752",
            5=>
   "111176755",
            6=>
   "111176761",
            7=>
   "111176776",
            8=>
   "111176785",
            9=>
   "111457708",
        );
        $service = new KeywordService();
        $service->setAuthHeader(\Baidusearch\Account::getAuthHeader($userId));
        $request=new GetWordRequest();
        $fields=array("pcDestinationUrl", "mobileDestinationUrl"); //出了基本字段外的额外字段

        $request->setIds($ids);
        $request->setWordFields($fields);
        $request->setIdType(5); //5单元 11关键词
        $request->setGetTemp(0);
        $service->setIsJson(true);
        $response=$service->getWord($request);
        echo json_encode($response)."\n";
//        $head=$service->getJsonHeader();
//        echo "status:".json_encode($head)."\n";

        return;
    }

    
}

