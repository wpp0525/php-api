<?php

use Lvmama\Cas\Service\RedisDataService,
    Lvmama\Common\Utils\UCommon;


class TravelapiController extends ControllerBase
{

    private $travelDataService;
    private $tripDataService;
    private $redis;
    private $imagePrefix = "";

    public function initialize()
    {
        parent::initialize();

        $this->travelDataService = $this->di->get('cas')->get('travel_data_service');
        $this->tripDataService = $this->di->get('cas')->get('trip-data-service');
        $this->redis = $this->di->get('cas')->getRedis();

    }

    /**
     * 获取游记ID列表
     * @author jianghu
     */
    public function getBriefListAction()
    {
        $result = array(
            'data' => array(),
        );
        try {
            $dataSql = "SELECT tt.id AS travel_id,tt.recommend_status AS recommend_status,ttx.order_id AS order_id FROM `tr_travel` tt LEFT JOIN `tr_travel_ext` ttx ON tt.id = ttx.travel_id WHERE tt.status = 1 ORDER BY tt.publish_time DESC";
            $data = $this->travelDataService->querySql($dataSql);

            $result = array(
                'data' => $data['list'],
            );
        }catch(\Phalcon\Exception $e){
            \Lvmama\Common\Utils\Filelogger::getInstance()->addLog($e->getTraceAsString(),'error');
        }finally {
            $this->_successResponse($result);
        }
    }

    /**
     * 获取游记数据
     */
    public function getTravelDataAction()
    {
        $travelIdStr = $this->request->get('travelIdStr');

        $params = array(
            'table' => 'ly_trip_statistics',
            'select' => '`trip_id`,`hits_init`,`hits_real`,`praise_init`,`praise_real`',
            'where' => array('type' => 'total', 'trip_id' => array('IN','(' . $travelIdStr . ')')),
        );
        $travelInitDatas = $this->tripDataService->select($params);

        $travelsHitsArr = array();
        foreach ($travelInitDatas['list'] as $row) {
            $travelsHitsArr[$row['trip_id']]['hits'] = $row['hits_init'] + $row['hits_real'];
            $travelsHitsArr[$row['trip_id']]['praise'] = $row['praise_init'] + $row['praise_real'];
        }

        $params = array(
            'table' => 'travel_dest_rel',
            'select' => '`travel_id`,`dest_id`',
            'where' => array('travel_id' => array('IN','(' . $travelIdStr . ')'),'is_main' => '1'),
        );

        $travel_dest_datas = $this->travelDataService->select($params);
        $travel_dest_arr = array();
        foreach ($travel_dest_datas['list'] as $dest_data) {
            $travel_dest_arr[$dest_data['travel_id']] = $dest_data['dest_id'];
        }

        $result = array();
        $travelIdArr = explode(',',$travelIdStr);
        foreach ($travelIdArr as $travelId) {
            $redisListKey = str_replace("{travel_id}",$travelId,RedisDataService::REDIS_TRAVEL_LIST_DATA);
//            $redisListData = $this->redis->hgetall($redisListKey);
            $redisListData['id'] = $travelId;
            $redisListData['title'] = $this->redis->hget($redisListKey, 'title');
            $redisListData['thumb'] = $this->redis->hget($redisListKey, 'thumb');
            $redisListData['tags'] = $this->redis->hget($redisListKey, 'tags');
            $redisListData['summary'] = $this->redis->hget($redisListKey, 'summary');

            $redisListData['main_dest_id'] = isset($travel_dest_arr[$travelId]) ? $travel_dest_arr[$travelId] : 0;

            $redisHitsKey = str_replace("{travel_id}",$travelId,RedisDataService::REDIS_TRAVEL_VIEW_NUM);
            $redisHitsData = $this->redis->get($redisHitsKey);

            $redisListData['thumb'] = $redisListData['thumb'] ? $this->imagePrefix . $redisListData['thumb'] : $redisListData['thumb'];
            $redisListData['pageCount'] = $redisHitsData + (isset($travelsHitsArr[$travelId]) ? $travelsHitsArr[$travelId]['hits'] : 0);
            $redisListData['praiseCount'] = isset($travelsHitsArr[$travelId]) ? $travelsHitsArr[$travelId]['praise'] : 0;

            $result[] = $redisListData;
        }
        $this->_successResponse($result);
    }

    /**
     * 根据多个ID查询列表数据
     */
    public function getInfosByIdsAction()
    {
        $idJson = $this->request->get('idArr');
        $idArr = json_decode($idJson,true);
        $ids = implode("','", $idArr);

        $params = array(
            'table' => 'travel',
            'select' => '`id`',
            'where' => array('id' => array('IN', "('{$ids}')"), 'status' => 1),
        );

        $dataList = $this->travelDataService->select($params);
        foreach ($dataList['list'] as $key => $row) {
            $dataList['list'][$key]['thumb'] = $row['thumb'] ? $this->imagePrefix . $row['thumb'] : "";
        }
        $this->_successResponse($dataList);
    }
}