<?php


use Lvmama\Common\Utils\ArrayUtils as ArrayUtils;
use Lvmama\Common\Components\ApiClient;
use Lvmama\Cas\Service\RedisDataService;

class VstdestController extends ControllerBase {

    private $dest_base_svc;
    private $coord_base_svc;
    private $es;
    private $prod_dest_around;
    protected $baseUri = 'http://ca.lvmama.com/';
    public $client;
    public $tsrv_client;

    protected $redis_svc;

    /**
     * @var HtlProductDestService
     */
    private $hd_product_dest;

    public function initialize()
    {
        parent::initialize();
        $this->dest_base_svc = $this->di->get('cas')->get('destin_base_service');
        $this->coord_base_svc = $this->di->get('cas')->get('coord_base_service');
        $this->es = $this->di->get('cas')->get('es-data-service');
        $this->prod_dest_around = $this->di->get('cas')->get('prod_product_attr');
        $this->hd_product_dest = $this->di->get('cas')->get('hd_product_dest');
        $this->client = new ApiClient($this->baseUri);
        $this->redis_svc = $this->di->get('cas')->getRedis();
        $this->tsrv_client = $this->di->get('tsrv');
    }

    /**
     * 判断目的地名称是否重复
     * 注：同一行政区下，同一类型目的地，名称不能相同
     */
    public function checkNameExistAction()
    {
        $post = $this->request->getPost();

        !empty($post['dest_name']) && $conditions['dest_name'] = "='" . $post['dest_name'] . "'";
        !empty($post['dest_type']) && $conditions['dest_type'] = "='" . $post['dest_type'] . "'";
        !empty($post['district_id']) && $conditions['district_id'] = "=" . $post['district_id'];
        $ret = $this->dest_base_svc->getOneDest($conditions);
        if($ret == false){
            $this->_successResponse(array('Exist' => 0));
        }
        else $this->_successResponse(array('Exist' => 1));
    }

    /**
     * 新增目的地+坐标信息
     * 传入的目的地相关参数中 coordinate为json_encode后的对象
     * 参数coord解码后进行对象->数组的转换，再作为坐标相关的参数
     */
    public function setDestCoorDataAction()
    {
        $post = $this->request->getPost();
        $coord = json_decode($post['coordinate']);
        $coord = ArrayUtils::object2array($coord);
        unset($post['coordinate']);
        unset($post['api']);

        if(!empty($post)) {
            $post['update_time'] = time();
            $result = $this->dest_base_svc->insert($post);
        }
        if(!empty($coord) && $result){
            $coord['update_time'] = time();
            $coord['object_id'] = $result;
            $ret = $this->coord_base_svc->insert($coord);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'目的地信息新增失败');
            exit;
        }
        $this->jsonResponse(array('result' => $result, 'coord' => $ret));
    }

    public function getDestRelationAction(){
        $dest_id = $this->dest_id;
        $dest = $this->dest_base_svc->getOneById($dest_id);
        $type = 'PROVINCE';
        if($dest['foreign_flag'] == 'Y') $type = 'COUNTRY';
        if(($dest['dest_type'] == $type) || ($dest['parent_id'] == 3548)){
            $this->jsonResponse(array('result' => $dest['dest_name']));
        }else{
            $ret = $this->getParentDest($dest['parent_id'])."-".$dest['dest_name'];
            $this->jsonResponse(array('result' => $ret));
        }
    }

    public function getParentDest($dest_id){
        $dest = $this->dest_base_svc->getOneById($dest_id);
        $type = 'PROVINCE';
        if($dest['foreign_flag'] == 'Y') $type = 'COUNTRY';
        if(($dest['dest_type'] == $type) || ($dest['parent_id'] == 3548)){
            return $dest['dest_name'];
        }else{
            return self::getParentDest($dest['parent_id'])."-".$dest['dest_name'];
        }
    }

    /**
     * 酒店产品周边目的地增量推送接口
     * 对接人——王永方 肖宇林
     */
    public function setHotelAroundDestAction(){
        $dest_id = $this->dest_id;
        $prod_id = $this->prod_id;
        $dest_info = $this->dest_base_svc->getOneById($dest_id);

        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');
        $prod_exist = $this->prod_dest_around->getProdCount($prod_id);
        if($prod_exist['count'] > 0) {
            $this->prod_dest_around->deleteProd($prod_id);
        }

        // 初始化酒店周边目的地
//        $this->formatHdProductDest($dest_id,$prod_id);

        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '5km';
        $dests = $this->es->getHotelAroundDest($location, $distance);
        $sum = $dests['hits']['total'];
        if($sum == 0) $this->_errorResponse(PARAMS_ERROR,'该目的地周边没有数据');
        $pages = ceil($sum/10);
        for($i=0;$i<$pages;$i++){
            $data = $this->es->getHotelAroundDest($location, $distance, $i*10);
            $attr = "";
            foreach($data['hits']['hits'] as $k => $v){
                if($v['_source']['dest_type'] == 'VIEWSPOT') $v['_source']['dest_type'] = 2007;
                if($v['_source']['dest_type'] == 'SCENIC') $v['_source']['dest_type'] = 2002;
                if($v['_source']['state'] == 'sign') $attr .= "('DISTRICT_SIGN','";
                else $attr .= "('DEST','";
                $attr .= $v['_source']['id']."','".$v['_source']['dest_name']."',".$v['_source']['dest_type'].",'".$prod_id."','Y','".(round($v['sort'][0],3)*1000)."',".time()."),";
            }
            $attr = substr($attr, 0, -1);
            $this->prod_dest_around->insertRelation($attr);
        }
        $this->jsonResponse(array('result' => true));
    }

    /**
     * 一次性
     * 酒店产品周边目的地 全量 推送接口
     * 对接人——王永方 肖宇林
     * @params prod 产品:目的地 数组 jsonencode
     */
    public function setHotelAroundListAction(){
        $prod = $this->prod;
        $prod = json_decode($prod);
        $out = array();
        foreach($prod as $key => $val){
            $val = ArrayUtils::object2array($val);
            if(!$val['prod_id'] || !$val['dest_id']) continue;
            $ret = $this->setHotelAround($val['prod_id'], $val['dest_id']);
            if($ret == 0) $out[] = array('prod_id' => $val['prod_id'],'dest_id' => $val['dest_id']);
        }
        $this->jsonResponse(array('result' => $out));
    }

    /**
     * 计算产品周边目的地并入库
     */
    private function setHotelAround($prod_id, $dest_id)
    {
        $dest_info = $this->dest_base_svc->getOneById($dest_id);
        if(!$dest_info) return 0;
        $prod_exist = $this->prod_dest_around->getProdCount($prod_id);
        if($prod_exist['count'] > 0)
            $this->prod_dest_around->deleteProd($prod_id);
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '5km';
        $dests = $this->es->getHotelAroundDest($location, $distance);
        $sum = $dests['hits']['total'];
        if($sum == 0) return 1;
        $pages = ceil($sum/10);
        for($i=0;$i<$pages;$i++){
            $data = $this->es->getHotelAroundDest($location, $distance, $i*10);
            $attr = "";
            foreach($data['hits']['hits'] as $k => $v){
                if($v['_source']['dest_type'] == 'VIEWSPOT') $v['_source']['dest_type'] = 2007;
                if($v['_source']['dest_type'] == 'SCENIC') $v['_source']['dest_type'] = 2002;
                if($v['_source']['state'] == 'sign') $attr .= "('DISTRICT_SIGN','";
                else $attr .= "('DEST','";
                $attr .= $v['_source']['id']."','".$v['_source']['dest_name']."',".$v['_source']['dest_type'].",'".$prod_id."','Y','".(round($v['sort'][0],3)*1000)."',".time()."),";
            }
            $attr = substr($attr, 0, -1);
            $this->prod_dest_around->insertRelation($attr);
        }
        return 1;
    }

    /**
     * 酒店周边数据-距离
     */
    public function getHotelAroundDistanceAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_HOTEL_DISTANCE;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();

        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);
        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '10km';
        $dests = $this->es->getDestAround($location, $distance);

        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                $tmp_array = $dests['hits']['hits'];
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_id'] = $tmp_array_value['_source']['dest_id'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_name'] = $tmp_array_value['_source']['dest_name'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);



            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 1));
                }
            }

            $i = 0;
            $return = array();
            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }

        }




        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($result);
    }



    /**
     * 酒店周边数据-评价
     */
    public function getHotelAroundEvaluationAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_HOTEL_EVALUATION;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();

        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);
        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '20km';
        $dests = $this->es->getDestAroundDesc($location, $distance);

        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                $tmp_array = $dests['hits']['hits'];
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_id'] = $tmp_array_value['_source']['dest_id'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_name'] = $tmp_array_value['_source']['dest_name'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);



            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 1));
                }
            }

            $i = 0;
            $return = array();
            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }

        }




        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($result);
    }


    /**
     * 酒店周边数据-人气
     */
    public function getHotelAroundPopularityAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_HOTEL_POPULARITY;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();

        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);
        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '20km';

        $dests = $this->es->getDestAroundHotSale($location, $distance,'HOTEL',0,3);
        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                $tmp_array = $dests['hits']['hits'];
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['destId']]['dest_id'] = $tmp_array_value['_source']['destId'];
                    $dests_format[$tmp_array_value['_source']['destId']]['dest_name'] = $tmp_array_value['_source']['destName'];
                    $dests_format[$tmp_array_value['_source']['destId']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);



            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 1));
                }
            }

            $i = 0;

            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }

        }



        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }


        $this->jsonResponse($result);



    }


    /**
     * 景点周边数据-距离
     */
    public function getViewspotAroundDistanceAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_VIEWSPOT_DISTANCE;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);
            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();


        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);

        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        if ( $dest_info['foreign'] == 'N' ) {
            $distance = '50km';
        } elseif ( $dest_info['foreign'] == 'Y' ) {
            $distance = '100km';
        } else {
            $distance = '50km';
        }

        // 获取周边景点 取30条
        $dests = $this->es->getDestAround($location, $distance,'VIEWSPOT',0,30);

        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                $tmp_array = $dests['hits']['hits'];
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_id'] = $tmp_array_value['_source']['dest_id'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_name'] = $tmp_array_value['_source']['dest_name'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);



            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 11 ));
                }
            }

            // 数组下标
            $i = 0;
            $return = array();
            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }
        }



        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($result);
    }


    /**
     * 景点周边数据-类型
     */
    public function getViewspotAroundTypeAction()
    {


        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_VIEWSPOT_EVALUATION;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);
            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();


        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);

        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        if ( $dest_info['foreign'] == 'N' ) {
            $distance = '50km';
        } elseif ( $dest_info['foreign'] == 'Y' ) {
            $distance = '100km';
        } else {
            $distance = '50km';
        }

        // 获取周边景点 取30条
        $dests = $this->es->getDestAround($location, $distance,'VIEWSPOT',0,30);

        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                // 避免跟距离最近重复
                $tmp_array = array_reverse($dests['hits']['hits']);
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_id'] = $tmp_array_value['_source']['dest_id'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['dest_name'] = $tmp_array_value['_source']['dest_name'];
                    $dests_format[$tmp_array_value['_source']['dest_id']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);

            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 11 ));
                }
            }

            // 数组下标
            $i = 0;
            $return = array();
            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }
        }



        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($result);

    }

    /**
     * 景点周边数据-人气
     */
    public function getViewspotAroundPopularityAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_AROUND_VIESPOT_POPULARITY;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();

        // 检测目的地有消息
        $dest_info = $this->dest_base_svc->getOneById($dest_id);
        if(!$dest_info) $this->_errorResponse(PARAMS_ERROR,'目的地ID不存在');

        // 获取坐标
        $where = "object_id = {$dest_id} AND object_type = 'BIZ_DEST' AND coord_type = 'BAIDU'";
        $dest = $this->coord_base_svc->getOneCoord($where);
        if(!$dest) $this->_errorResponse(PARAMS_ERROR,'目的地ID坐标数据不详');

        // 获取周边数据
        $location = array('lon' => $dest['longitude'], 'lat' => $dest['latitude']);
        $distance = '20km';

        // -- 待定
        $dests = $this->es->getDestAroundHotSale($location, $distance,'VIEWSPOT',0,3);
        if ( $dests['hits']['total'] == 0 ) {
            $code = '00001';
        } else {

            // 格式化ES返回结果
            $dests_format = array();
            if ( isset($dests['hits']) && isset($dests['hits']['hits']) ) {
                $tmp_array = $dests['hits']['hits'];
                foreach ( $tmp_array as $tmp_array_value ) {
                    $dests_format[$tmp_array_value['_source']['destId']]['dest_id'] = $tmp_array_value['_source']['destId'];
                    $dests_format[$tmp_array_value['_source']['destId']]['dest_name'] = $tmp_array_value['_source']['destName'];
                    $dests_format[$tmp_array_value['_source']['destId']]['distance'] = (round($tmp_array_value['sort'][0],3)*1000);
                }
            }


            // 获取对的product_id
            $dest_ids = array_keys($dests_format);
            $dest_ids_str = implode(',',$dest_ids);

            $multi_product_data = $this->hd_product_dest->getMutilProductByDest($dest_ids_str);



            // 产品服务
            $res = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            // 格式化产品数据
            $multi_product_data_format_dest_id = array();
//        $multi_product_data_format_product_id = array();
            foreach ( $multi_product_data as $multi_product_data_value ) {
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['dest_id'] = $multi_product_data_value['dest_id'];
                $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_id'] = $multi_product_data_value['product_id'];

//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['dest_id'] = $multi_product_data_value['dest_id'];
//            $multi_product_data_format_product_id[$multi_product_data_value['product_id']]['product_id'] = $multi_product_data_value['product_id'];

                if ( is_numeric( $multi_product_data_value['product_id'] ) ) {
                    $multi_product_data_format_dest_id[$multi_product_data_value['dest_id']]['product_info'] = $product_service->inputProductPool(array('product_id' => $multi_product_data_value['product_id'],'type_id' => 1));
                }
            }

            $i = 0;

            foreach ( $dests_format as $dests_format_value ) {
                if ( isset( $multi_product_data_format_dest_id[$dests_format_value['dest_id']] ) ) {
                    $return[$i]['dest_id'] = $dests_format_value['dest_id'];
                    $return[$i]['dest_name'] = $dests_format_value['dest_name'];
                    $return[$i]['distance'] = $dests_format_value['distance'];
                    $return[$i]['product_info'] = $multi_product_data_format_dest_id[$dests_format_value['dest_id']]['product_info'];
                    ++$i;
                }
            }

        }



        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }


        $this->jsonResponse($result);
    }



    /**
     * POI右侧推荐
     */
    public function getRecommendPopularityAction()
    {
        $dest_id = $this->dest_id;
        $city_id = $this->city_id;
        $limit_num = $this->request->get('limit_num');
        if ( empty($limit_num) ) {
            $limit_num = 4;
        }
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_RECOMMEND_POPULARITY;
        $cache_key = $this->getCacheKey($cache_prefix, $city_id . $dest_id . $limit_num );
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();
        $product_ids = array();

        // 待优化 -- 待定
        $dests = $this->es->getPoiRecommendHotProductByCategoryId($city_id,11 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductByCategoryId($city_id,15 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductBySubCategoryId($city_id,181 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductBySubCategoryId($city_id,182 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);


        if ( empty($product_ids) ) {
            $code = '00001';
        } else {

            // 是否增加缓存  -- 待定  -- 命中较低
            $product_ids_str = implode(',',$product_ids);

            $product_pool_product_service = $this->di->get('cas')->get('product_pool_product');
            $product_info_simple = $product_pool_product_service->getByProductId($product_ids_str);

            // 调取产品详细数据
            $return = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');


            $i = 0;
            foreach ( $product_info_simple as $product_info_simple_value ) {
                if ( $product_info_simple_value['SUB_CATEGORY_ID'] ) {
                    if ( count($return[$product_info_simple_value['SUB_CATEGORY_ID']]) > $limit_num - 1 ) {
                        continue;
                    }
                    $return[$product_info_simple_value['SUB_CATEGORY_ID']][$i] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['SUB_CATEGORY_ID']));
                } else {
                    if ( count($return[$product_info_simple_value['CATEGORY_ID']]) > $limit_num - 1 ) {
                        continue;
                    }
                    $return[$product_info_simple_value['CATEGORY_ID']][$i] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['CATEGORY_ID']));
                }

                ++$i;
            }


        }

        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }


        $this->jsonResponse($result);
    }

    /**
     * 首屏产品展示非门票
     */
    public function getRecommendProductsAction()
    {
        $dest_id = $this->dest_id;
        $city_id = $this->city_id;
        $limit_num = $this->request->get('limit_num');
        if ( empty($limit_num) ) {
            $limit_num = 4;
        }
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_FIRST_SCREEN_PRODUCTS;
        $cache_key = $this->getCacheKey($cache_prefix, $city_id . $dest_id );
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();
        $product_ids = array();

        // 待优化 -- 待定
        $dests = $this->es->getPoiRecommendHotProductByCategoryIdAndDestId($city_id, $dest_id, 11 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductByCategoryIdAndDestId($city_id, $dest_id, 15 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductBySubCategoryIdAndDestId($city_id, $dest_id, 181 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        $dests = $this->es->getPoiRecommendHotProductBySubCategoryIdAndDestId($city_id, $dest_id, 182 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);


        if ( empty($product_ids) ) {

            $code = '00001';

        } else {

            $product_ids_str = implode(',',$product_ids);

            // 待定
//            $product_ids_str = '927135,927244,927292,947876,947920,948009,927893,927895,927904';

            $request_data = array(
                'filters' =>
                    array(
                        'PRODUCT_ID' => $product_ids_str
                    ),
                'currentPage' => 1,
                'pageSize' => 100
            );

            $request_json = json_encode($request_data);

            $datas = $this->tsrv_client->exec(
                'search/getSimpleRoute',
                array('params' => $request_json)
            );


            if ( empty($datas) || empty($datas['items']) ) {

                $code = '00002';

            } else {

                $i = 0;
                foreach ( $datas['items'] as $product_info_simple_value ) {

                    if ( $product_info_simple_value['subCategoryId'] ) {

                        $return[$product_info_simple_value['subCategoryId']][$i] = $product_info_simple_value;

                    } else {

                        $return[$product_info_simple_value['categoryId']][$i] = $product_info_simple_value;

                    }

                    ++$i;

                }

            }


        }



        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }


        $this->jsonResponse($result);
    }


    /**
     * 首屏产品展示门票
     */
    public function getRecommendTicketGoodsAction()
    {
        $dest_id = $this->dest_id;
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_RECOMMEND_TICKET_GOODS;
        $cache_key = $this->getCacheKey($cache_prefix,$dest_id);
        $cache_data = $this->redis_svc->get($cache_key);

        if ( empty($no_cache) && !empty($cache_data) ) {

            $cache_data_arr = json_decode($cache_data,true);

            $this->jsonResponse($cache_data_arr);

        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();


        $request_data = array(
               'productId' => $dest_id
        );

        $request_json = json_encode($request_data);

        $datas = $this->tsrv_client->exec(
            'scenic/getSuppGoodsByProductId',
            array('params' => $request_json)
        );

        if ( empty($datas) || !isset($datas['returnContent']) ) {
            $code = '00001';
        } else {
            $return = $datas['returnContent'];
        }


        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }


        $this->jsonResponse($result);
    }







    /**
     * 初始化酒店周边目的地 -- 待定 优化:事务处理
     * @param $dest_id
     * @param $prod_id
     */
    private function formatHdProductDest($dest_id, $prod_id)
    {
        $prod_exist_hotel = $this->hd_product_dest->getProdCount($prod_id);
        if($prod_exist_hotel['count'] > 0) {
            $this->hd_product_dest->deleteProd($prod_id);
        }
        $hd_insert_data = array();
        $hd_insert_data['dest_id'] = $dest_id;
        $hd_insert_data['product_id'] = $prod_id;
        $this->hd_product_dest->insert($hd_insert_data);
    }


    private function getCacheKey($prefix,$id)
    {
        $result = $prefix . $id;
        return $result;

    }

    /**
     * poi数据转换product_ids
     * @param $dests
     * @param $product_ids
     */
    private function poiDataFormat($dests,&$product_ids)
    {
        if ( $dests['hits']['total'] != 0 ) {
            if (isset($dests['hits']) && isset($dests['hits']['hits'])) {
                $tmp_array = $dests['hits']['hits'];
                foreach ($tmp_array as $tmp_array_value) {

                    $product_ids[] = $tmp_array_value['_source']['product_id'];

                }
            }
        }
    }


    /**
     * 推荐的跟团游和酒店
     */
    public function getRecommendHLAction(){

        $dest_id = $this->request->get('dest_id');
        $limit_num = $this->request->get('limit_num');
        if ( empty($limit_num) ) {
            $limit_num = 4;
        }
        $no_cache = $this->request->get('no_cache');
        $cache_prefix = RedisDataService::REDIS_RECOMMEND_POPULARITY_OTHER;
        $cache_key = $this->getCacheKey($cache_prefix, $dest_id . $limit_num );
        $cache_data = $this->redis_svc->get($cache_key);
//        $cache_data = '';
        if ( empty($no_cache) && !empty($cache_data) ) {
            $cache_data_arr = json_decode($cache_data,true);
            $this->jsonResponse($cache_data_arr);
        }

        $code = '00000';
        $msg = 'ok';
        $result = array();
        $return = array();
        $product_ids = array();

        // 待优化 -- 待定
        $dests = $this->es->getPoiRecommendHotProductByCategoryId($dest_id,16 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        // 就是取个酒店的数据 呵呵 可能是1 可能是17
        $dests = $this->es->getPoiRecommendHotProductByCategoryId($dest_id, 1 ,0 ,$limit_num);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);

        if ( empty($product_ids) ) {
            $code = '00001';
        } else {

            // 是否增加缓存  -- 待定  -- 命中较低
            $product_ids_str = implode(',',$product_ids);

            $product_pool_product_service = $this->di->get('cas')->get('product_pool_product');
            $product_info_simple = $product_pool_product_service->getByProductId($product_ids_str);
            // 调取产品详细数据
            $return = array();
            $product_service = $this->di->get('cas')->get('product-info-data-service');

            $i = 0;
            foreach ( $product_info_simple as $product_info_simple_value ) {
                if ( $product_info_simple_value['SUB_CATEGORY_ID'] ) {
                    if ( count($return[$product_info_simple_value['SUB_CATEGORY_ID']]) > $limit_num ) {
                        continue;
                    }
                    $return[$product_info_simple_value['SUB_CATEGORY_ID']][$i] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['SUB_CATEGORY_ID']));
                } else {
                    if ( count($return[$product_info_simple_value['CATEGORY_ID']]) > $limit_num ) {
                        continue;
                    }
                    $return[$product_info_simple_value['CATEGORY_ID']][$i] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['CATEGORY_ID']));
                }

                ++$i;
            }


        }

        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['total'] = count($return);
        $result['data'] = $return;

        $cache_data = json_encode($result);
        if ( $result['code'] != '00000') {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($result);
    }


    // 销量推 目的地下 poi
    public function getRecomPoiAction(){
        $dest_id = $this->request->get('dest_id');
//        $exp_pids = unserialize($this->exp_pids) ? unserialize($this->exp_pids) : array();
        $exp_p_ids = $this->request->get('exp_pids') ? $this->request->get('exp_pids') : '';
        $limit_num = $this->request->get('limit_num') ? $this->request->get('limit_num') : 8;
        $rel_dest_type = $this->request->get('rel_type') ? $this->request->get('rel_type')."_id" : 'city_id';

        $cache_prefix = RedisDataService::REDIS_RECOMMEND_POPULARITY_RPOI;
        $cache_key = $this->getCacheKey($cache_prefix, $dest_id . $limit_num );
        $cache_data = $this->redis_svc->get($cache_key);
//        $cache_data = '';
        if ( !empty($cache_data) ) {
            $cache_data_arr = json_decode($cache_data,true);
            $this->jsonResponse($cache_data_arr);
        }

        $limit = $limit_num * 12;

        if($exp_p_ids){
            $exp_pids = explode(',', $exp_p_ids);
        }else{
            $exp_pids = array();
        }

        $dests = $this->es->getHotPoiByDestId($dest_id, 0 ,$limit, $rel_dest_type, $exp_pids);
        $res = $tmp_array = $dests['hits']['hits'];
        unset($dests);

        $return = array();
        $return_ids = $return_pids = array();

        if($res){
            foreach($res as  $resdata){
                if(count($return_pids) >= $limit_num){
                    break;
                }

                if(!$resdata['_source']['poi_id'] || !$resdata['_source']['product_id']){
                    continue;
                }

                if(!in_array($resdata['_source']['poi_id'], $return_ids)){
                    $return['a'][] = array(
                        'poi_id' => $resdata['_source']['poi_id'],
                        'product_id' => $resdata['_source']['product_id']
                    );
                    $return_ids[] = $resdata['_source']['poi_id'];
                    $return_pids[] = $resdata['_source']['product_id'];
                }

            }

            // 是否增加缓存  -- 待定  -- 命中较低
            $product_ids_str = implode(',',$return_pids);

            $product_pool_product_service = $this->di->get('cas')->get('product_pool_product');
            $product_info_simple = $product_pool_product_service->getByProductId($product_ids_str);

            $product_service = $this->di->get('cas')->get('product-info-data-service');

            foreach ( $product_info_simple as $product_info_simple_value ) {
                if ( $product_info_simple_value['SUB_CATEGORY_ID'] ) {
                    $return['p'][$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['SUB_CATEGORY_ID']));
                } elseif( $product_info_simple_value['CATEGORY_ID'] ) {
                    $return['p'][$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['CATEGORY_ID']));
                }
            }

            $base_srv = $this->di->get('cas')->get('dest_base_service');

            $poi_ids = implode(',',$return_ids);
            $return['s'] = $base_srv->getDestBaseByDestIds($poi_ids);
        }

        $dest_detail_svc = $this->di->get('cas')->get('dest_detail_service');
        $dest_image_svc = $this->di->get('cas')->get('dest_image_service');

        foreach($return['a'] as $v){
            foreach($return['s'] as $sv){

                $redis_key=RedisDataService::REDIS_DEST_DETAIL_BASEID.$sv['base_id'];
                $red = $this->redis_svc->dataHgetall($redis_key);
                if(!$red){
                    $red=$dest_detail_svc->getDestDetailByBaseId($sv['base_id'],$sv['dest_type']);
                    if($red){
                        $ttl=$this->redisConfig['ttl']['lvyou_dest_detail']?$this->redisConfig['ttl']['lvyou_dest_detail']:null;
                        $this->redis_svc->dataHmset($redis_key, $red, $ttl);
                    }
                }

                $sight = array_merge($sv, $red);
                if(!$sight['img_url']){
                    $sight['img_url'] = $dest_image_svc->getCoverByDestId($sight['dest_id']);
                }

                if($v['poi_id'] == $sv['dest_id']){
                    $info[] = array(
                        's' => $sight,
                        'p' =>$return['p'][$v['product_id']]
                    );
                }
            }
        }

        $cache_data = json_encode($info);
        if ( empty($info) ) {
            $this->redis_svc->set($cache_key,$cache_data,10);
        } else {
            $this->redis_svc->set($cache_key,$cache_data,86400);
        }

        $this->jsonResponse($info);
    }

    public function getNewDestHotTripProductAction(){

        $poi_id = $this->poi_id;
        $exp_pids = unserialize($this->exp_pids) ? unserialize($this->exp_pids) : array();
        $size = intval($this->size) ? intval($this->size) : 5;

//        echo json_encode($exp_pids); die;
        $dests = $this->es->getHotProductSimpleByPoiId($poi_id, array('181','182'), $exp_pids, 1, 0 ,$size);
//        echo json_encode($dests); die;
//        $cols = array('product_id', 'poi_id', 'sub_category_id', 'category_id');
//        $this->formatSearchMiniResult($dests , $product_ids ,$cols, $miniResult);
        $this->poiDataFormat($dests,$product_ids);
        unset($dests);
//        echo json_encode($product_ids); die;
        if($product_ids){
            $product_ids_str = implode(',', $product_ids);
            $product_pool_product_service = $this->di->get('cas')->get('product_pool_product');
            $product_info_simple = $product_pool_product_service->getByProductId($product_ids_str);

            $product_service = $this->di->get('cas')->get('product-info-data-service');

            foreach ( $product_info_simple as $product_info_simple_value ) {
                if ( $product_info_simple_value['SUB_CATEGORY_ID'] ) {
                    $return[$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['SUB_CATEGORY_ID']));
                } else {
                    $return[$product_info_simple_value['PRODUCT_ID']] = $product_service->inputProductPool(array('product_id' => $product_info_simple_value['PRODUCT_ID'],'type_id' => $product_info_simple_value['CATEGORY_ID']));
                }
            }

        }
        $this->jsonResponse($return);

    }


}
