<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\RedisDataService;

class TravelDestWorkerService implements DaemonServiceInterface{

    private $traveldatasvc;
    private $traveldestsvc;

    public function __construct($di) {
        $this->traveldatasvc = $di->get('cas')->get('travel_data_service');
        $this->traveldatasvc->setReconnect(true);
        $this->traveldestsvc = $di->get('cas')->get('dest_trips_rel_service');
        $this->traveldestsvc->setReconnect(true);
        $this->redis_svc=$di->get('cas')->get('redis_data_service');
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function process($timestamp = null, $flag = null) {
        $this->findData();
    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    /**
     * 游记查询
     * @param int $start
     */
    private function findData($start = 1)
    {
        $offset = (max(1,$start) - 1) * 100;
        $limit = $offset . ',100';
        $data = $this->traveldestsvc->getList('',"dest_have_trips",$limit);
        if(empty($data))
            die('done');
        foreach ($data as $row) {
            $tmp=RedisDataService::REDIS_DEST_TRIP_IDS;
            $redis_key1 = $tmp.$row['have_dest_id'];
            $redis_key2 = $tmp.$row['direct_dest_id'];
            $totle = $this->redis_svc->getZCard($redis_key1);
            if($totle>0){
                if($row['have_dest_id']==$row['direct_dest_id']){
                    continue;
                }else{
                    $res = $this->redis_svc->getZRange($redis_key1,0,-1,true);
                    foreach($res as $val => $rv){
                        $this->redis_svc->dataZAdd($redis_key2, $rv, $val);
                    }
                }
            }
            $sql="SELECT a.`id`
                FROM tr_travel a
                LEFT JOIN tr_travel_dest_rel b
                ON a.`id`=b.`travel_id`
                WHERE a.`status`=1 AND b.`dest_id`={$row['have_dest_id']}
                UNION
                SELECT a.`id`
                FROM tr_travel a
                LEFT JOIN tr_travel_content_dest_rel c
                ON a.`id`=c.`travel_id`
                WHERE a.`status`=1 AND c.`dest_id`={$row['have_dest_id']}";
            $travel_res = $this->traveldatasvc->querySql($sql);
            if(empty($travel_res["list"]) || $travel_res['error']){
                continue;
            }else{
                $trip_ids=array();
                foreach($travel_res["list"] as $row){
                    $trip_ids[]=$row['id'];
                }
                $trip_ids= implode(',',$trip_ids);
                $sql="SELECT
                  a.`id`,
                  a.`publish_time`,
                  a.`recommend_status`,
                  b.`order_id`
                FROM
                  `tr_travel` a
                  LEFT JOIN `tr_travel_ext` b
                    ON a.`id` = b.`travel_id`
                WHERE a.`id` IN ({$trip_ids})";
                $travel_status = $this->traveldatasvc->querySql($sql);
                if(!empty($travel_status["list"])){
                    foreach($travel_status["list"] as $row){
                        $point=$row["publish_time"];
                        if($row["recommend_status"]==2){
                            $point+=3000000000;
                        }else{
                            $point+=1000000000;
                        }
                        if($row["order_id"]!=0){
                            $point+=1000000000;
                        }
                        $this->redis_svc->dataZAdd($redis_key1, $point, $row["id"]);
                    }
                }
            }
        }
        unset($data);
        sleep(2);
        $start++;
        $this->findData($start);
    }
}