<?php

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Common\Utils\Misc;

/**
 *
 */
class PlacedistWorkerService implements DaemonServiceInterface {

    /**
     * 基础库
     * @var ProductPoolDataService
     */
    private $pp_place_base;

    /**
     * @var SjTempSubjectService
     */
    private $temp_sub;

    /**
     * @var DistBaseDataService
     */
    private $dist_base;

    /**
     * redis service
     * @var
     */
    private $redis;

    /**
     * redis key
     * @var string
     */
    private $redis_cache_key = 'pp_place_district_jack';


    public function __construct($di) {
        $this->pp_place_base = $di->get('cas')->get('product_pool_data');
        //$this->distin_base->setReconnect(true);

        $this->temp_sub = $di->get('cas')->get('temp_subject');
        //$this->destin_multi_relation_base->setReconnect(true);

        $this->dist_base = $di->get('cas')->get('dist_base_service');

        $this->redis = $di->get('cas')->getRedis();

    }

    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

    public function process ($a=null,$b='c')
    {
	    while(1) {

            //获取专题频道id
            $channel_params = array(
                'table' =>'pp_channel',
                'select' => '*',
                'where' => ' `channel_name` like "%专题%" ',
                'order' => ' id ASC',
            );

            $channel_res = $this->pp_place_base->getOneByParams($channel_params);
            $channel_id = $channel_res['id'];

            //获取专题路由id
            $route_params = array(
                'table' =>'pp_route',
                'select' => '*',
                'where' => ' `intro` like "%专题%" AND del_status = 1',
                'order' => ' channel_id ASC, id ASC'
            );
            $route_res = $this->pp_place_base->getOneByParams($route_params);
            $route_id = $route_res['id'];
            if(empty($channel_id) || empty($route_id)) {
                $this->stopFlag('查询出错频道或路由不存在');
            }

	        // 分页条数
	        $limit = 1000;

	        // 获取游标
	        $last_id = $this->getLastId();

	        $sub_where = " parent_id >0 and subject_id > $last_id ";

	        // 待优化

	        $sub_info = $this->temp_sub->getDataList($sub_where, $limit);

	        if ( empty($sub_info) ) {
	            //重置游标
	            $this->setLastId(0);
	            $this->stopFlag('已结束');
	        }

	        foreach ($sub_info as $sub_value) {

	            echo '游标值:' . $this->getLastId() . PHP_EOL;
	            echo '专题ID: ' . $sub_value['subject_id'] . PHP_EOL;

                //专题名取出发地ID
                $dist_res = $this->dist_base->getOneDist(array('district_name'=>' = "'.$sub_value['name'].'"','cancel_flag'=>' = "Y"',));
                if($dist_res['district_id']){
                    $district_id = $dist_res['district_id'];
                    //有多出发地id  查询该专题的所有产品
                    $place_params = array(
                        'table' =>'pp_place',
                        'select' => '*',
                        'where' => " `channel_id` = {$channel_id} AND `route_id` = {$route_id} AND `key_id` = {$sub_value['subject_id']} AND `product_id`>0 AND `supp_goods_id`=0",
                        'order' => 'id ASC'
                    );
                    $place_res = $this->pp_place_base->getAllByParams($place_params);
                    if($place_res){
                        foreach($place_res as $place_value){
                            //获取出发地和产品关系是否存在
                            $product_id = intval(substr($place_value['product_id'],3));
                            $startdist_params = array(
                                'table' =>'pp_startdistrict_addtional',
                                'select' => 'LOWEST_SALED_PRICE',
                                'where' => " `PRODUCT_ID` = {$product_id} AND `START_DISTRICT_ID` = {$district_id}",
                                'order' => 'id ASC'
                            );
                            $startdist_res = $this->pp_place_base->getOneByParams($startdist_params);

                            if($startdist_res){
                                echo 'placeID:' . $place_value['id'] . PHP_EOL;
                                echo '更新出发地ID为:' . $district_id . PHP_EOL;
                                echo '更新价格为:' . $startdist_res['LOWEST_SALED_PRICE']/100 . PHP_EOL;

                                $place_update_res = $this->pp_place_base->update($place_value['id'],array('product_district_id'=>$district_id,'product_price'=>($startdist_res['LOWEST_SALED_PRICE'])/100));
                                if($place_update_res==1)  echo '更新成功'. PHP_EOL;
                            }
                        }
                    }
                }
	            // 更新游标
	            $this->setLastId($sub_value['subject_id']);

	            // usleep(200);
	        }
	    }

    }

    /**
     * 设置游标
     * @param $id
     * @return mixed
     */
    public function setLastId($id)
    {
        $result = $this->redis->set($this->redis_cache_key,$id,3600);
        return $result;
    }

    /**
     * 获取游标
     * @return mixed
     */
    public function getLastId()
    {
        $result = $this->redis->get($this->redis_cache_key);

        if ( empty($result) ) {
            $this->redis->set($this->redis_cache_key,0,3600);
            $result = $this->redis->get($this->redis_cache_key);
        }

        return $result;


    }

    private function stopFlag($content)
    {
        exit($content);
    }

}