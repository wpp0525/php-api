<?php

use Lvmama\Common\Utils\UCommon;

/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-3-3
 * Time: 下午4:59
 */
class ProductpoolController extends ControllerBase
{
    const OPERATE_SYSTEM_DEFAULT_DENIED = 200001;

    private $pp_svc;

    /**
     * @var ProductPoolPlusDataService
     */
    private $pp_plus_srv;

    private $product_pool;

    // 产品池-商品属性
    private $product_pool_goods;

    private $pp_startdistrict_addtional;

    const SEPARATOR_RULE_ENGINE = '.';

    public function initialize()
    {
        parent::initialize();
        $this->pp_svc                     = $this->di->get('cas')->get('product_pool_data');
        $this->pp_plus_srv                = $this->di->get('cas')->get('product_pool_plus_data');
        $this->product_pool               = $this->di->get('cas')->get('product_pool_product');
        $this->product_pool_goods         = $this->di->get('cas')->get('product_pool_goods');
        $this->pp_startdistrict_addtional = $this->di->get('cas')->get('product_pool_startdistrict_addtional');
    }

    /**
     * 频道新增/修改
     * param : id, channel_name
     *      channel_name not null
     * @author liuhongfei
     */
    public function channelEditAction()
    {

        $key                  = intval($this->request->getPost('id'));
        $data                 = array();
        $data['channel_name'] = $this->request->getPost('channel_name');

        if (!$data['channel_name']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $res = $this->pp_svc->operateDataById('pp_channel', $data, $key);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }
    }

    /**
     * 频道新增/修改
     * param : id
     *      id not null
     * @author liuhongfei
     */
    public function channelDelAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_channel', 'lock_status', $key);

        // lock_status = 1 用户添加 可以删除
        if ($find && $find['lock_status'] == 1) {

            $del_status = intval($this->request->getPost('del_status')) == 1 ? 1 : 9;
            $data       = array('del_status' => $del_status);

            $res = $this->pp_svc->operateDataById('pp_channel', $data, $key);
            if (!$res) {
                $this->_errorResponse(OPERATION_FAILED, '操作失败');
            } else {
                $this->jsonResponse($res);
            }

        } else {
            $this->_errorResponse(OPERATE_SYSTEM_DEFAULT_DENIED, '修改系统默认设置失败');
        }
    }

    /**
     * 频道管理列表
     * param : del_status
     * @author liuhongfei
     */
    public function channelListAction()
    {

        $del_status = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;
        $page       = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;
        $pageSize   = intval($this->request->getPost('page_size')) ? intval($this->request->getPost('page_size')) : 10;

        $params = array(
            'table'  => 'pp_channel',
            'select' => '*',
            'where'  => "del_status = '{$del_status}' ",
            'order'  => ' id ASC',
            'group'  => '',
            'page'   => array('page' => $page, 'pageSize' => $pageSize),
        );

        $res = $this->pp_svc->getPageByParams($params);

        $this->jsonResponse($res);

    }

    /**
     * 查询一条记录
     * param : id
     *      id not null
     * @author liuhongfei
     */
    public function channelGetOneAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_channel', 'channel_name', $key);

        if ($find && $find['channel_name']) {
            $this->jsonResponse($find['channel_name']);
        } else {
            $this->_errorResponse(DATA_NOT_FOUND, '修改系统默认设置失败');
        }
    }

    /**
     * 获取全部有效频道
     */
    public function channelGetAllAction()
    {

        $del_status = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;

        $params = array(
            'table'  => 'pp_channel',
            'select' => '*',
            'where'  => "del_status = '{$del_status}' ",
            'order'  => ' id ASC',
        );

        $res = $this->pp_svc->getAllByParams($params);

        $this->jsonResponse($res);

    }

    /**
     * 频道管理列表
     * param : del_status
     * @author liuhongfei
     */
    public function routeListAction()
    {

        $del_status = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;
        $page       = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;
        $pageSize   = intval($this->request->getPost('page_size')) ? intval($this->request->getPost('page_size')) : 10;

        $params = array(
            'table'  => 'pp_route pr',
            'select' => 'pr.*,pc.channel_name',
            'join'   => array(' INNER JOIN pp_channel AS pc ON pr.channel_id = pc.id '),
            'where'  => "pr.del_status = '{$del_status}' ",
            'order'  => ' pr.id ASC',
            'group'  => '',
            'page'   => array('page' => $page, 'pageSize' => $pageSize),
        );

        $res = $this->pp_svc->getPageByParams($params);

        $this->jsonResponse($res);

    }

    /**
     * 频道新增/修改
     * param : id
     *      id not null
     * @author liuhongfei
     */
    public function routeDelAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_route', 'lock_status', $key);

        // lock_status = 1 用户添加 可以删除
        if ($find && $find['lock_status'] == 1) {

            $del_status = intval($this->request->getPost('del_status')) == 1 ? 1 : 9;
            $data       = array('del_status' => $del_status);

            $res = $this->pp_svc->operateDataById('pp_route', $data, $key);
            if (!$res) {
                $this->_errorResponse(OPERATION_FAILED, '操作失败');
            } else {
                $this->jsonResponse($res);
            }

        } else {
            $this->_errorResponse(OPERATE_SYSTEM_DEFAULT_DENIED, '修改系统默认设置失败');
        }
    }

    /**
     * 查询一条记录
     * param : id
     *      id not null
     * @author liuhongfei
     */
    public function routeGetRowAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_route', '*', $key);

        if ($find) {
            $this->jsonResponse($find);
        } else {
            $this->_errorResponse(DATA_NOT_FOUND, '修改系统默认设置失败');
        }
    }

    /**
     * 频道新增/修改
     * param : id, channel_name
     *      channel_name not null
     * @author liuhongfei
     */
    public function routeEditAction()
    {

        $key                      = intval($this->request->getPost('id'));
        $data                     = array();
        $data['route']            = $this->request->getPost('route');
        $data['intro']            = $this->request->getPost('intro');
        $data['route_expression'] = $this->request->getPost('route_expression');
        $data['key_info']         = $this->request->getPost('key_info');
        $data['channel_id']       = $this->request->getPost('channel_id');

        if (!$data['route'] || !$data['intro'] || !$data['channel_id'] || !$data['route_expression'] || !$data['key_info']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $res = $this->pp_svc->operateDataById('pp_route', $data, $key);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }
    }

    /**
     * 获取全部有效路由信息
     */
    public function routeGetAllAction()
    {

        $del_status = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;

        $params = array(
            'table'  => 'pp_route',
            'select' => '*',
            'where'  => "del_status = '{$del_status}' ",
            'order'  => ' channel_id ASC, id ASC',
        );

        $res = $this->pp_svc->getAllByParams($params);

//        $res_array = array();
        //        if($res){
        //            foreach($res as $row){
        //                $cid = $row['channel_id'];
        //                $res_array[$cid][] = $row;
        //            }
        //        }

        $this->jsonResponse($res);

    }

    /***
     * 根据条件查询频道
     */
    public function channelWhereListAction()
    {
        $get_where = $this->request->getPost('where');
        $get_where = !empty($get_where) ? json_decode($get_where) : null;
        $where     = !empty($get_where) ? " {$get_where} AND del_status = '1'" : "del_status = '1'";

        $params = array(
            'table'  => 'pp_channel',
            'select' => '*',
            'where'  => " {$where} ",
            'order'  => ' id ASC',
        );

        $res = $this->pp_svc->getPageByParams($params);

        $this->jsonResponse($res);

    }

    /***
     * 根据条件查询路由
     */
    public function routeWhereGetListAction()
    {
        $get_where = $this->request->getPost('where');
        $get_where = !empty($get_where) ? json_decode($get_where) : null;
        $where     = !empty($get_where) ? " {$get_where} AND del_status = '1'" : "del_status = '1'";
        $params    = array(
            'table'  => 'pp_route',
            'select' => '*',
            'where'  => "{$where}",
            'order'  => ' channel_id ASC, id ASC',
        );
        $res = $this->pp_svc->getAllByParams($params);

        $this->jsonResponse($res);
    }

    /**
     * 泛坑位 修改/新增
     */
    public function placeRuleEditAction()
    {

        $key                = intval($this->request->getPost('id'));
        $data               = array();
        $data['name']       = $this->request->getPost('name');
        $data['channel_id'] = $this->request->getPost('channel_id');
        $data['route']      = $this->request->getPost('route');
        $data['route_id']   = $this->request->getPost('route_id');
        $data['position']   = $this->request->getPost('position');
        $data['place_num']  = $this->request->getPost('place_num');
        $data['img']        = $this->request->getPost('img');

        if (!$data['name'] || !$data['channel_id'] || !$data['route'] || !$data['route_id'] || !$data['position'] || !$data['place_num'] || !$data['img']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $res = $this->pp_svc->operateDataById('pp_black_rule', $data, $key);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }

    }

    /**
     * 泛坑位规则列表
     */
    public function placeRuleListAction()
    {

        $del_status = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;
        $page       = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;

        $channel_id = $this->request->getPost('channel_id');
        $route_id   = $this->request->getPost('route_id');
        $position   = $this->request->getPost('position');

        $where = '';
        if ($channel_id > 0) {$where .= " AND pbr.channel_id = '{$channel_id}' ";}
        if ($route_id > 0) {$where .= " AND pbr.route_id = '{$route_id}' ";}
        if ($position > 0) {$where .= " AND pbr.position = '{$position}' ";}

        $params = array(
            'table'  => 'pp_black_rule pbr',
            'select' => 'pbr.*,pc.channel_name',
            'join'   => array(' INNER JOIN pp_channel AS pc ON pbr.channel_id = pc.id '),
            'where'  => "pbr.del_status = '{$del_status}' {$where}",
            'order'  => ' pbr.id ASC',
            'group'  => '',
            'page'   => array('page' => $page, 'pageSize' => 10),
        );

        $res = $this->pp_svc->getPageByParams($params);

        $this->jsonResponse($res);
    }

    public function placeRuleGetRowAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_black_rule', '*', $key);

        if ($find) {
            $this->jsonResponse($find);
        } else {
            $this->_errorResponse(DATA_NOT_FOUND, '修改系统默认设置失败');
        }
    }

    /**
     * 坑位列表
     */
    public function placeListAction()
    {

        $del_status  = intval($this->request->getPost('del_status')) == 9 ? 9 : 1;
        $page        = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;
        $channel_id  = $this->request->getPost('channel_id');
        $route_id    = $this->request->getPost('route_id');
        $position    = $this->request->getPost('position');
        $key_id      = $this->request->getPost('key_id');
        $place_order = $this->request->getPost('place_order');

        $where = '';
        if ($channel_id > 0) {$where .= " AND channel_id = '{$channel_id}' ";}
        if ($route_id > 0) {$where .= " AND route_id = '{$route_id}' ";}
        if ($position > 0) {$where .= " AND position = '{$position}' ";}
        if ($key_id > 0) {$where .= " AND key_id = '{$key_id}' ";}
        if ($place_order > 0) {$where .= " AND place_order = '{$place_order}' ";}

        $params = array(
            'table'  => 'pp_place',
            'select' => 'id, place_coordinate, channel_id, route_id, position, product_id, product_name, product_img, lock_status, del_status, position',
            'where'  => " del_status = '{$del_status}' {$where} ",
            'order'  => ' id DESC',
            'page'   => array('page' => $page, 'pageSize' => 10),
        );

        $res = $this->pp_svc->getPageByParams($params);

        $findrule = array();
        if (!empty($res['list']) && is_array($res['list'])) {
            foreach ($res['list'] as $okey => $place) {
                $key                         = $place['channel_id'] . '-' . $place['route_id'] . '-' . $place['position'];
                $findrule[$key]['where']     = " channel_id = '{$place['channel_id']}' AND route_id = '{$place['route_id']}' AND position = '{$place['position']}' ";
                $findrule[$key]['id'][$okey] = $place['id'];
            }
        }

        if ($findrule) {
            foreach ($findrule as $rule) {
                $params = array(
                    'table'  => 'pp_black_rule',
                    'select' => ' img, name, route, id AS routeId',
                    'where'  => $rule['where'],
                    'order'  => ' id DESC',
                );

                $res2 = $this->pp_svc->getOneByParams($params);

                if ($res2 && is_array($res2)) {
                    foreach ($rule['id'] as $order => $rid) {
                        $res['list'][$order] = array_merge($res['list'][$order], $res2);
                    }
                }
            }
        }

        $this->jsonResponse($res);
    }

    /**
     * 获取单个坑位
     */
    public function placeGetOneAction()
    {

        $key = intval($this->request->getPost('id'));
        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }
        $find = $this->pp_svc->getOneByCondition('pp_place', '*', $key);

        if ($find) {
            $this->jsonResponse($find);
        } else {
            $this->_errorResponse(DATA_NOT_FOUND, '没有数据');
        }

    }

    /**
     * 锁定/解锁 坑位
     */
    public function placeLockAction()
    {

        $key = intval($this->request->getPost('id'));

        if (!$key) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $lock_status = intval($this->request->getPost('lock_status')) == 1 ? 1 : 9;
        $data        = array('lock_status' => $lock_status);

        $res = $this->pp_svc->operateDataById('pp_place', $data, $key);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }

    }

    /**
     * 坑位 修改
     */
    public function placeEditAction()
    {

        $key                  = intval($this->request->getPost('id'));
        $data                 = array();
        $data['product_id']   = $this->request->getPost('product_id');
        $data['product_name'] = $this->request->getPost('product_name');
        $data['lock_status']  = $this->request->getPost('lock_status');

        if ($this->request->getPost('product_img')) {
            $data['product_img'] = $this->request->getPost('product_img');
        }

        if (!$key || !$data['product_id'] || !$data['product_name'] || !$data['lock_status']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $res = $this->pp_svc->operateDataById('pp_place', $data, $key);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }

    }

    /**
     * 接口A - 产品池 - 泛坑位规则
     * 输入字段：坐标
     * 格式：channel_id*100000+route_id.0.可以定位到模块的id.max_place_id
     * 400001.0.1.3,400001.0.33.3,400001.0.22.1,400001.0.44.3,400001.0.777.5,400001.0.109.8,400001.0.91.10
     */
    public function buildBlackRuleAction()
    {

        $coordinate = $this->request->getPost('coordinate');
        if ($coordinate == '') {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        }
        $coordinate_array = explode(',', $coordinate);

        foreach ($coordinate_array as $ck => $cv) {
//            $buildRule = $this->buildRule($cv);
            $buildRule = $this->pp_svc->buildRule($cv);

            if ((int) $buildRule > 0) {
                unset($coordinate_array[$ck]);
            }
        }

        if (count($coordinate_array) > 0) {
            $this->_errorResponse(OPERATION_FAILED, '执行中发生异常，请重试！');
        } else {
            $this->jsonResponse(200);
        }

    }

    private function buildRule($coordinate)
    {

        // 验证传入的坐标是否合法
        if ($coordinate == '') {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        }
        $coo_data = explode('.', $coordinate);
        if (count($coo_data) != 4) {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        } else {
            foreach ($coo_data as $key => $val) {
                $coo_data[$key] = (int) $val;
                if ($key == 1 && (int) $val != 0) {
                    $this->_errorResponse(PARAMS_ERROR, '参数有误');
                } elseif ($key != 1 && (int) $val == 0) {
                    $this->_errorResponse(PARAMS_ERROR, '参数有误');
                }
            }
        }

        // ====== 组成数据 BEGIN ======
        $data_array               = array();
        $data_array['channel_id'] = floor($coo_data[0] / 100000);
        $data_array['route_id']   = $coo_data[0] - $data_array['channel_id'] * 100000;
        $data_array['position']   = $coo_data[2];
        $data_array['place_num']  = $coo_data[3];

        // 判断数据是否已经存在
        $where = "del_status = '1' AND lock_status = '1' AND position = '{$data_array['position']}'";
        $where .= " AND channel_id = '{$data_array['channel_id']}' AND route_id = '{$data_array['route_id']}'";

        $params = array(
            'table'  => 'pp_black_rule',
            'select' => 'id, place_num',
            'where'  => $where,
        );

        $ishave = $this->pp_svc->getOneByParams($params);

        $sign        = 0;
        $allowupdate = 1;
        if ($ishave && is_array($ishave)) {
            $sign = intval($ishave['id']) ? intval($ishave['id']) : 0;
            if ($ishave['place_num'] == $data_array['place_num']) {
                $allowupdate = 0;
            }
        }

        if ($allowupdate) {
            $data_array['lock_status'] = 1;
            $data_array['del_status']  = 1;

            // 查询路由信息补全数据
            $find = $this->pp_svc->getOneByCondition('pp_route', 'route, intro', $data_array['route_id']);
            if ($find && is_array($find)) {
                $data_array['route'] = $find['route'];
                $data_array['name']  = $find['intro'] . ' - ' . $coo_data[2];
            }
            // ====== 组成数据 END ======

            // 写入数据库
            $res = $this->pp_svc->operateDataById('pp_black_rule', $data_array, $sign);

            return $res;
        } else {
            return $ishave['id'];
        }

    }

    /**
     * 接口B - 产品池 - 生成坑位
     * 输入字段：坐标
     * 格式：channel_id*100000+route_id.keyID.可以定位到模块的id.0
     * 400001.79.1.0,400001.79.33.0,400001.79.22.0,400001.79.44.0,400001.79.777.0,400001.79.109.0,400001.79.91.0
     * 400001.1.1.0,400001.1.33.0,400001.1.22.0,400001.1.44.0,400001.1.777.0,400001.1.109.0,400001.1.91.0
     */
    public function buildPlaceAction()
    {

//        $coordinate = "100008.18.3191.0,100008.18.3192.0,100008.18.3193.0,100008.18.3194.0";
        $coordinate = $this->request->getPost('coordinate');
        if ($coordinate == '') {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        }

//        $coordinate_array = explode(',', $coordinate);
        //        foreach($coordinate_array as $ck => $cv){
        //            $buildRule = $this->buildPlaceByCoordinate($cv);
        //            if((int)$buildRule > 0){
        //                unset($coordinate_array[$ck]);
        //            }
        //        }
        $buildRule = $this->pp_svc->buildPlaceByCoordinate($coordinate);
//        echo $buildRule;die;
        if ($buildRule) {
            $this->jsonResponse(200);
        }

//        if(count($coordinate_array) > 0){
        //            $this->_errorResponse(OPERATION_FAILED, '执行中发生异常，请重试！');
        //        }else{
        //            $this->jsonResponse(200);
        //        }

    }

    private function buildPlaceByCoordinate($coordinate)
    {

        // 验证传入的坐标是否合法
        if ($coordinate == '') {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        }
        $coo_data = explode('.', $coordinate);
        if (count($coo_data) != 4) {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        } else {
            foreach ($coo_data as $key => $val) {
                $coo_data[$key] = (int) $val;
                if ($key == 3 && (int) $val != 0) {
                    $this->_errorResponse(PARAMS_ERROR, '参数有误');
                } elseif ($key != 3 && (int) $val == 0) {
                    $this->_errorResponse(PARAMS_ERROR, '参数有误');
                }
            }
        }

        // ====== 组成数据 BEGIN ======
        $data_array               = array();
        $data_array['channel_id'] = floor($coo_data[0] / 100000);
        $data_array['route_id']   = $coo_data[0] - $data_array['channel_id'] * 100000;
        $data_array['position']   = $coo_data[2];

        // 判断 有多少个坑
        //        $where = "del_status = '1' AND lock_status = '1' AND position = '{$data_array['position']}'";
        $where = "del_status = '1' AND position = '{$data_array['position']}'";
        $where .= " AND channel_id = '{$data_array['channel_id']}' AND route_id = '{$data_array['route_id']}'";
        $params = array(
            'table'  => 'pp_black_rule',
            'select' => 'place_num',
            'where'  => $where,
        );
        $ishave = $this->pp_svc->getOneByParams($params);

        if ($ishave && $ishave['place_num']) {

            $key_id = $coo_data[1];
            // 查询已有数据
            $params2 = array(
                'table'  => 'pp_place',
                'select' => 'id, place_order, lock_status',
                'where'  => $where . " AND key_id = '{$key_id}' ",
                'order'  => ' place_order ASC',
            );
            $res = $this->pp_svc->getAllByParams($params2);
//            echo json_encode($res); die;
            $po_array = $parray = array();
            if ($res && is_array($res)) {
                $j = 1;
                foreach ($res as $val) {
                    $place_order          = $val['place_order'];
                    $po_array[$j]         = $place_order;
                    $parray[$place_order] = $val['id'];

                    if ($val['lock_status'] != 1) {
                        $plock[] = $place_order;
                    }

                    $j++;
                }
            }
//            var_dump($po_array); die;
            $coordinate_3 = "{$coo_data[0]}.{$coo_data[1]}.{$coo_data[2]}";

            $count = array();
            $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['ruleEnginePit']);

            for ($i = 1; $i <= $ishave['place_num']; $i++) {
                $po_key = array_search($i, $po_array);
                if ($po_key) {
                    $count[] = $po_array[$po_key];
                    unset($po_array[$po_key]);
                } else {
                    $post_array = array(
                        'place_coordinate' => $coordinate_3 . '.' . $i,
                        'channel_id'       => $data_array['channel_id'],
                        'route_id'         => $data_array['route_id'],
                        'position'         => $data_array['position'],
                        'key_id'           => $key_id,
                        'place_order'      => $i,
                        'lock_status'      => 1,
                        'del_status'       => 1,
                    );
                    // 写入数据库
                    $res2    = $this->pp_svc->operateDataById('pp_place', $post_array);
                    $count[] = $res2;
                    unset($res2);
                    unset($post_array);
                }

                // 需要扔进mq的数据
                if (!in_array($i, $plock)) {
                    $kfk_array = $coordinate_3 . '.' . $i;
                    $kafka->sendMsg($kfk_array);
                    unset($kfk_array);
                }

            }

            // 逻辑删除多出数据...
            if (count($po_array)) {
                foreach ($po_array as $po_val) {
                    $post_array = array(
                        'del_status' => 9,
                    );
                    $res3 = $this->pp_svc->operateDataById('pp_place', $post_array, $parray[$po_val]);
                    unset($res3);
                    unset($post_array);
                }
            }

            if ($ishave['place_num'] == count($count)) {
                return $ishave['place_num'];
            } else {
                $this->_errorResponse(OPERATION_FAILED, '程序发生错误，请重试！');
            }
        } else {
            $this->_errorResponse(OPERATION_FAILED, '规则不存在，无法生成！');
        }

    }

    /**
     * 坑位坐标对应产品
     */
    public function ruleEngineAddAction()
    {
        $product_id  = $this->request->getPost('product_id');
        $district_id = $this->request->getPost('district_id');

        $where_data               = array();
        $where_data['channel_id'] = $this->request->getPost('channel_id');
        $where_data['route_id']   = $this->request->getPost('route_id');
        $where_data['key_id']     = $this->request->getPost('key_id');
        $where_data['position']   = $this->request->getPost('position');
//        去除product_id不为空的 hard code
        $where_data['product_id'] = 0;

        if (!$where_data['channel_id'] || !$where_data['route_id'] || !$where_data['key_id'] || !$where_data['position']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        // 获取对应坑位数量
        $res = $this->pp_svc->getByCondition('id', $where_data);
        if (empty($res)) {
            $this->_errorResponse(PARAMS_ERROR, '没有剩余坑位可供选择，请先添加坑位信息');
        }

        $product_id_arr       = explode(self::SEPARATOR_RULE_ENGINE, $product_id);
        $product_id_arr_count = count($product_id_arr);
        if ($product_id_arr_count == 0) {
            $this->_errorResponse(PARAMS_ERROR, "请至少选择一种产品");
        }

        $res_count = count($res);
        if ($res_count < $product_id_arr_count) {
            $this->_errorResponse(PARAMS_ERROR, "选择产品大于剩余坑位数量($res_count)，请减少所选产品");
        }
        $pids = array();
        foreach ($product_id_arr as $item) {
            $pids[] = intval(substr($item, 3, 10));
        }
        if (!empty($district_id)) {
            $district_product_list = $this->pp_startdistrict_addtional->getListByPidDid($district_id, implode(',', $pids));
        }

        $res_update = $this->pp_svc->simpleQuery($res, $product_id_arr, $district_product_list);
        //添加促销信息
        $this->upProductPromotion(array_slice($res, 0, $product_id_arr_count), $product_id_arr);
        // kafka 推送
        $kafka = new \Lvmama\Cas\Component\Kafka\Producer(
            $this->di->get('config')->kafka->toArray()['msgProducer']
        );

        foreach ($product_id_arr as $product_id_arr_value) {
            $kafka->sendMsg($product_id_arr_value);
        }

        $result          = array();
        $result['error'] = '000000';
        $result['msg']   = 'ok';
        $this->jsonResponse($result);

    }

    /**
     * 坑位坐标对应产品自动填充
     */
    public function ruleEngineAddPlusAction()
    {
        $product_id = $this->request->getPost('product_id');

        $where_data                = array();
        $where_data['channel_id']  = $this->request->getPost('channel_id');
        $where_data['route_id']    = $this->request->getPost('route_id');
        $where_data['key_id']      = $this->request->getPost('key_id');
        $where_data['position']    = $this->request->getPost('position');
        $where_data['place_order'] = 0;

        $place_coordinate               = UCommon::buildRule($where_data);
        $where_data['place_coordinate'] = $place_coordinate;

        if (!$where_data['channel_id'] || !$where_data['route_id'] || !$where_data['key_id'] || !$where_data['position']) {
            $this->_errorResponse(PARAMS_ERROR, '无参数');
        }

        $product_id_arr       = explode(self::SEPARATOR_RULE_ENGINE, $product_id);
        $product_id_arr_count = count($product_id_arr);
        if ($product_id_arr_count == 0) {
            $this->_errorResponse(PARAMS_ERROR, "请至少选择一种产品");
        }

        // kafka 推送
        $kafka = new \Lvmama\Cas\Component\Kafka\Producer(
            $this->di->get('config')->kafka->toArray()['msgProducer']
        );

        foreach ($product_id_arr as $product_id_arr_value) {
            $where_data['product_id'] = $product_id_arr_value;
            $this->pp_plus_srv->createPlusData($where_data);

            $kafka->sendMsg($product_id_arr_value);
        }

        $result          = array();
        $result['error'] = '000000';
        $result['msg']   = 'ok';
        $this->jsonResponse($result);

    }

    /**
     * 查询出来图片
     */
    public function getImageAction()
    {
        $type = $this->request->getPost('type');
        $id   = intval($this->request->getPost('id'));

        $type_array = array('rule', 'product');
        if (!in_array($type, $type_array) || !$id) {
            $this->_errorResponse(PARAMS_ERROR, '参数有误');
        }

        if ($type == 'rule') {
            $res = $this->pp_svc->getOneByCondition('pp_black_rule', 'img', $id);
        } elseif ($type == 'product') {
            $res = $this->pp_svc->getOneByCondition('pp_place', 'product_img AS img', $id);
        }

        if ($res && $res['img']) {
            $this->jsonResponse($res['img']);
        } else {
            $this->jsonResponse('');
        }

    }

    public function findRouteIdAction()
    {

        $channel_id = $this->request->getPost('channel_id');
        $route      = $this->request->getPost('route');

        $params = array(
            'table'  => 'pp_route',
            'select' => '*',
            'where'  => " `channel_id` =  '{$channel_id}' AND `route` = '{$route}' ",
        );
        $res = $this->pp_svc->getOneByParams($params);

        $this->jsonResponse($res);
    }

    public function updatePlacesAction()
    {

        $channel_id = $this->request->getPost('channel_id');
        $route_id   = $this->request->getPost('route_id');
        $key_id     = $this->request->getPost('key_id');

        $params = array(
            'table'  => 'pp_place',
            'select' => 'place_coordinate',
            'where'  => " `channel_id` =  '{$channel_id}' AND `route_id` = '{$route_id}' AND `key_id` = '{$key_id}' AND `lock_status` = '1' AND `del_status` = '1' ",
        );

        $res = $this->pp_svc->getAllByParams($params);

        $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['ruleEnginePit']);

        if ($res && is_array($res)) {
            foreach ($res as $key => $val) {
                $kafka->sendMsg($val['place_coordinate']);
                unset($res[$key]);
            }

            if (count($res) == 0) {
                $this->jsonResponse('200');
            } else {
                $this->_errorResponse(OPERATION_FAILED, '程序发生错误，请重试！');
            }
        } else {
            $this->_errorResponse(OPERATION_FAILED, '原来的数据不存在，请先生成！');
        }

    }

    public function routeGetExpressionAction()
    {
        $channel_id = $this->request->getPost('channel_id');
        $params     = array(
            'table'  => 'pp_route',
            'select' => 'route_expression, key_info, id',
            'where'  => " `channel_id` =  '{$channel_id}' AND `del_status` = '1' AND  `route_expression` !='' AND `key_info` != '' ",
        );

        $res = $this->pp_svc->getAllByParams($params);
        $this->jsonResponse($res);
    }

    public function getPagePlaceNumAction()
    {
        $channel_id = $this->request->getPost('channel_id');
        $route_id   = $this->request->getPost('route_id');
        $key_id     = $this->request->getPost('key_id');
        $where      = " `channel_id` = {$channel_id} AND `route_id` = {$route_id} AND `key_id` = {$key_id} AND `lock_status` = '1' AND `del_status` = '1' ";
        $res        = $this->pp_svc->getSingleCountByParams('pp_place', $where);
        $this->jsonResponse($res);
    }

    public function refreshPagePlaceAction()
    {
        // 获取页面上一共有多少个坑位
        $channel_id = $this->request->getPost('channel_id');
        $route_id   = $this->request->getPost('route_id');
        $key_id     = $this->request->getPost('key_id');
        $where      = " `channel_id` = {$channel_id} AND `route_id` = {$route_id} AND `key_id` = {$key_id} AND `lock_status` = '1' AND `del_status` = '1' ";
        $count      = $this->pp_svc->getSingleCountByParams('pp_place', $where);

        // 坑位总数/50 计算页码
        $totle_page = ceil($count / 60);

        $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['ruleEnginePit']);
        // for循环取 循环扔...
        for ($i = 1; $i <= $totle_page; $i++) {
            $params = array(
                'table'  => 'pp_place',
                'select' => 'place_coordinate',
                'where'  => $where,
                'order'  => ' id ASC',
                'group'  => '',
                'page'   => array('page' => $i, 'pageSize' => 60),
            );

            $res = $this->pp_svc->getPageByParams($params);

            if (is_array($res) && $res['list']) {
                foreach ($res['list'] as $list) {
                    $kafka->sendMsg($list['place_coordinate']);
                }
            }
            unset($res);
        }
        $this->jsonResponse(array('code' => '200'));

    }

    /**
     * 根据条件获取数据
     * @param $coordinate
     * @author shenxiang
     * @return json
     */
    public function getProductByCoordinateAction()
    {
        $coordinate = $this->request->get('coordinate');
        if (!$coordinate) {
            $this->_errorResponse(10001, '请传入参数coordinate');
        }

        $tmp = explode('.', $coordinate);
        if (count($tmp) != 4) {
            $this->_errorResponse(10002, '请传入正确的coordinate');
        }

        foreach ($tmp as $v) {
            if (!is_numeric($v)) {
                $this->_errorResponse(10003, '请传入正确的coordinate');
            }
        }
        $spm   = UCommon::spreadRule($coordinate);
        $where = array(
            'channel_id' => $spm['channel_id'],
            'route_id'   => $spm['route_id'],
            'key_id'     => $spm['key_id'],
            'position'   => $spm['position'],
            'del_status' => 1,
        );
        $field = 'id,place_order,product_id,supp_goods_id,product_name,product_img,product_tips,product_price,product_url,product_commentCount,product_commentGood,product_promotionTitle,product_district_id,lock_status';
        $data  = $this->pp_svc->getByCondition($field, $where);
        $this->_successResponse($data);
    }

    /**
     * 更新产品数据到指定位置
     */
    public function updateProductAction()
    {
        $currDataString   = urldecode($this->request->getPost('currData'));
        $targetDataString = urldecode($this->request->getPost('targetData'));
        if (!$currDataString) {
            $this->_errorResponse(10001, '请传入参数currData');
        }

        if (!$targetDataString) {
            $this->_errorResponse(10002, '请传入参数targetData');
        }

        $currData   = unserialize($currDataString);
        $targetData = unserialize($targetDataString);
        try {
            $this->pp_svc->beginTransaction();
            $id = $currData['id'];
            unset($currData['id']);
            $this->pp_svc->update($id, $currData);
            $id = $targetData['id'];
            unset($targetData['id']);
            $this->pp_svc->update($id, $targetData);
            $this->pp_svc->commit();
            $this->_successResponse('操作成功');
        } catch (\Exception $e) {
            $this->pp_svc->rollBack();
            $this->_errorResponse(10003, '操作失败,事务被回滚');
        }
    }
    /**
     * 保存线路不同出发地的产品
     * @param $data
     */
    public function saveDistrictProductAction()
    {
        $content = $this->request->getPost('content');
        if (!$content) {
            $this->_errorResponse(10001, '参数不能为空');
        }

        $data                   = explode(',', $content);
        $this->district_product = $this->di->get('cas')->get('product_pool_district_product');
        foreach ($data as $rows) {
            $tmp = explode('|', $rows);
            $row = array(
                'keyword_id'    => $tmp[0],
                'module_id'     => $tmp[1],
                'district_id'   => $tmp[2],
                'district_name' => $tmp[3],
                'dest_id'       => $tmp[4],
                'max_count'     => $tmp[5],
            );
            $return = $this->district_product->save($row);
        }
        if ($return) {
            $this->_successResponse('保存成功');
        } else {
            $this->_errorResponse(10001, '保存失败');
        }
    }

    /**
     * 产品列表
     */
    public function listInfoAction()
    {

        $product_ids = $this->request->get('product_ids');
        $district_id = $this->request->get('district_id');
//        $product_ids = $this->request->getPost('product_ids');

        $res = $this->product_pool->getAllWithAddtionalByProductId($product_ids);
        if ($res && !empty($district_id)) {
            $pp_startdistrict_addtional = $this->di->get('cas')->get('product_pool_startdistrict_addtional');

            $where         = " `product_id` IN (" . $product_ids . ") AND `start_district_id`=" . $district_id;
            $district_data = $pp_startdistrict_addtional->getDataList($where, 100, 'PRODUCT_ID');

            if (!empty($district_data)) {
                $district_pid = array();
                foreach ($district_data as $value) {
                    $district_pid[] = $value['PRODUCT_ID'];
                }

                foreach ($res as $key => $value) {
                    if (in_array($value['PRODUCT_ID'], $district_pid)) {
                        $res[$key]['district_id'] = $district_id;
                    }

                }
            }
        }

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }
    }

    /**
     * 商品列表
     */
    public function listGoodsInfoAction()
    {

        $supp_goods_ids = $this->request->get('supp_goods_ids');

        $res = $this->product_pool_goods->getAllByGoodsId($supp_goods_ids);

        if (!$res) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败');
        } else {
            $this->jsonResponse($res);
        }

    }

    /**
     * 调用方注册module
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-01T11:03:57+0800
     * @version 1.0.0
     * @return  [type]                   [description]
     */
    public function regModuleAction()
    {
        $module_name = $this->request->get('module_name');
        $type        = $this->request->get('type');

        if (empty($module_name) || empty($type)) {
            $this->_errorResponse(OPERATION_FAILED, "缺少关键参数");
        }

        if ($type != 1 && $type != 2) {
            $this->_errorResponse(OPERATION_FAILED, "非法的注册类型");
        }

        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $rs             = $this->pprd_svs->regModule($module_name, $type);

        if (!empty($rs)) {
            $r = [
                'error' => 0,
                'msg'   => '注册成功',
            ];
            $this->jsonResponse($r);
        } else {
            $this->_errorResponse(OPERATION_FAILED, "注册失败");
        }
    }

    /**
     * [moduleListAction 已注册列表]
     * @author lixiumeng@lvmama.com
     * @addtime 2017-08-01T14:17:06+0800
     * @version 1.0.0
     * @return  [type]                   [description]
     */
    public function moduleListAction()
    {
        $type = $this->request->get('type');
        if ($type != 1 && $type != 2) {
            $this->_errorResponse(OPERATION_FAILED, "非法的注册类型");
        }
        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $list           = $this->pprd_svs->moduleList($type);
        if (empty($list)) {
            $r = [
                'error' => 1002,
                'msg'   => '',
            ];
        } else {
            $r = [
                'error' => 0,
                'msg'   => '',
                'data'  => $list,
            ];
        }
        return $this->jsonResponse($r);
    }

    /**
     * 根据产品id从redis产品池获取产品数据
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-14T16:46:59+0800
     * @version 1.0.0
     * @param   $product_ids 产品id序列
     *          $req_code    请求码(预留)
     *          $from_code   请求来源模块
     * @return  [type]                   [description]
     */
    public function getProductInfoByProductIdAction()
    {
        $product_ids = $this->request->get('product_ids');
        $from_code   = $this->request->get('from_code');

        if (empty($product_ids) || empty($from_code)) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败,缺少必要参数');
        }
        //简单过滤无效的请求
        // $limit = 10000000;
        // $ids   = explode(',', $product_ids);

        // foreach ($ids as $id) {
        //     if (intval($id) > $limit) {
        //         $this->jsonResponse([
        //             'error' => 1002,
        //             'msg'   => '请求的产品数据不合法, 请输入小于' . $limit . '的产品id',
        //             'data'  => '',
        //         ]);
        //     }
        // }

        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');

        // 调用方模块限制,redis存储
        $type              = 1;
        $allow_pj_module   = $this->pprd_svs->moduleList($type);
        $allow_pj_module[] = 'kxlx';
        if (!in_array($from_code, $allow_pj_module)) {
            $this->_errorResponse(OPERATION_FAILED, '操作权限不足');
        }

        $pro_info = $this->pprd_svs->getProductInfoByProductId($product_ids, $from_code);
        if ($pro_info) {
            $return = [
                'code' => 0,
                'msg'  => '',
                'data' => $pro_info,
            ];
        } else {
            $return = [
                'code' => 1000,
                'msg'  => '没有找到请求数据',
                'data' => [],
            ];
        }
        return $this->jsonResponse($return);
    }

    /**
     * 根据商品id从redis中获取数据
     * @author lixiumeng@lvmama.com
     * @addtime 2017-07-19T11:15:27+0800
     * @version 1.0.0
     * @param   $goods_ids 产品id序列
     *          $req_code    请求码(预留)
     *          $from_code   请求来源模块
     * @return  [type]                   [description]
     */
    public function getGoodsInfoByGoodsIdAction()
    {
        $goods_ids = $this->request->get('goods_ids');
        $from_code = $this->request->get('from_code');

        if (empty($goods_ids) || empty($from_code)) {
            $this->_errorResponse(OPERATION_FAILED, '操作失败,缺少必要参数');
        }
        //简单过滤无效的请求
        // $limit = 10000000;
        // $ids   = explode(',', $goods_ids);

        // foreach ($ids as $id) {
        //     if (intval($id) > $limit) {
        //         $this->jsonResponse([
        //             'error' => 1002,
        //             'msg'   => '请求的商品数据不合法,请输入小于' . $limit . '的商品数据',
        //             'data'  => '',
        //         ]);
        //     }
        // }
        $this->pprd_svs    = $this->di->get('cas')->get('product_pool_redis_data');
        $type              = 2; // 调用类型
        $allow_pj_module   = $this->pprd_svs->moduleList($type);
        $allow_pj_module[] = 'kxlx';
        if (!in_array($from_code, $allow_pj_module)) {
            $this->_errorResponse(OPERATION_FAILED, '操作权限不足');
        }
        $pro_info = $this->pprd_svs->getGoodsInfoByGoodsId($goods_ids, $from_code);
        if ($pro_info) {
            $return = [
                'code' => 0,
                'msg'  => '',
                'data' => $pro_info,
            ];
        } else {
            $return = [
                'code' => 1000,
                'msg'  => '没有找到请求数据',
                'data' => [],
            ];
        }
        return $this->jsonResponse($return);
    }

    /**
     * 添加产品时 调用促销标签接口 添加促销标签
     * @param $place array 坑位id
     * @param $product_id_arr array 产品商品id
     */
    private function upProductPromotion($place, $product_id_arr)
    {
        $tsrv_svc = $this->di->get('tsrv');

        foreach ($place as $key => $value) {
            $where                  = $up_data                  = $data                  = array();
            $title1                 = $title2                 = null;
            $where['distributorId'] = null;
            $str_pos                = strpos($product_id_arr[$key], '|');
            if ($str_pos === false) {
                $product_id          = intval(substr($product_id_arr[$key], 3));
                $where['objectId']   = $product_id;
                $where['objectType'] = 'PRODUCT';
            } else {
                $goods_id            = explode('|', $product_id_arr[$key])[1];
                $where['objectId']   = $goods_id;
                $where['objectType'] = 'GOODS';
            }
            $data  = $tsrv_svc->exec('product/getPromotions', array('params' => json_encode($where)));
            $title = $data['returnContent']['items'][0]['title'];
            if ($data['success'] == true && !empty($title)) {
                $title_pos = strpos($title, '(') ? strpos($title, '(') : strpos($title, '（');
                if ($title_pos === false) {
                    $title1 = $title;
                } else {
                    $title1 = substr($title, 0, $title_pos);

                    preg_match_all("/(?:\(|\（)(.*)(?:\)|\）)/i", $title, $result);
                    $title2 = $result[1][0];
                }
            }
            if ($title1) {
                $up_data['product_tips'] = $title1;
            }

            if ($title2) {
                $up_data['product_promotionTitle'] = $title2;
            }

            if (!empty($up_data)) {
                $this->pp_svc->update($value['id'], $up_data);
            }

        }
    }

    /***
     *  根据spm返回pp_place表 product_ids
     */
    public function getProductListBySpmAction()
    {
        $spm = $this->request->get('spm');
        if (empty($spm)) {
            $this->_errorResponse(PARAMS_ERROR, 'spm参数格式错误');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);

        if (empty($spm_str['channel_id']) || empty($spm_str['route_id']) || empty($spm_str['key_id'])) {
            $this->_errorResponse(PARAMS_ERROR, 'spm参数精度错误');
            return;
        }

        $where = array();

        if (!empty($spm_str['channel_id'])) {
            $where[] = ' `channel_id`= ' . $spm_str['channel_id'];
        }

        if (!empty($spm_str['route_id'])) {
            $where[] = ' `route_id`= ' . $spm_str['route_id'];
        }

        if (!empty($spm_str['key_id'])) {
            $where[] = ' `key_id`= ' . $spm_str['key_id'];
        }

        if (!empty($spm_str['position'])) {
            $where[] = ' `position`= ' . $spm_str['position'];
        }

        if (!empty($spm_str['place_order'])) {
            $where[] = ' `place_order`= ' . $spm_str['place_order'];
        }

        $where_str = implode(' AND ', $where);

        $sql = 'SELECT `product_id` FROM `lmm_pp`.`pp_place`  WHERE `product_id`!=0 AND ' . $where_str;

        $product_pool_vst_dest = $this->di->get('cas')->get('product_pool_vst_dest');
        $product_ids           = $product_pool_vst_dest->getRsBySql($sql);
        if (empty($product_ids)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据为空');
        }

        $product_ids_uniq = array();
        foreach ($product_ids as $k => $v) {
            $product_ids_uniq[$v['product_id']] = intval(substr($v['product_id'], 3));
        }
        $product_ids_str = implode(',', $product_ids_uniq);
        $this->_successResponse($product_ids_str);
    }

    /**
     * 刷新产品池信息
     * @addtime 2017-08-29T14:09:29+0800
     * @return  [type]                   [description]
     */
    public function rebuildAllInfoAction()
    {
        $id   = $this->request->get('id');
        $type = $this->request->get('type');
        if (empty($id)) {
            $id = 1;
        }
        if (empty($type)) {
            $type = 1;
        }
        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $rs             = $this->pprd_svs->flashRedisFlag($id, $type);
        $r              = [
            'code' => 200,
            'msg'  => '刷新产品池信息',
            'data' => $rs,
        ];
        return $this->jsonResponse($r);
    }

    /**
     * 重新构建产品池数据
     * @addtime 2017-08-29T13:52:00+0800
     * @return  [type]                   [description]
     */
    public function rebuildProductAction()
    {
        $ids   = $this->request->get('ids');
        $range = $this->request->get('range');
        $type  = 1;

        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $rs             = $this->pprd_svs->rebuildInfo($ids, $range, $type);
        $r              = [
            'code' => 200,
            'msg'  => '刷新产品信息' . $ids,
            'data' => $rs,
        ];
        return $this->jsonResponse($r);
    }

    /**
     * 重新构建商品池数据
     * @addtime 2017-08-29T13:52:11+0800
     * @return  [type]                   [description]
     */
    public function rebuildGoodsAction()
    {
        $ids   = $this->request->get('ids');
        $range = $this->request->get('range');
        $type  = 2;

        $this->pprd_svs = $this->di->get('cas')->get('product_pool_redis_data');
        $rs             = $this->pprd_svs->rebuildInfo($ids, $range, $type);

        $r = [
            'code' => 200,
            'msg'  => '刷新商品信息' . $ids,
            'data' => $rs,
        ];
        return $this->jsonResponse($r);
    }

    /**
     * 添加消息到指定kafka中
     * @author lixiumeng
     * @datetime 2017-10-11T14:21:09+0800
     */
    public function addMsgToKafkaAction()
    {
        $msg   = $this->request->get('msg');
        $topic = $this->request->get('topic');
        $count = $this->request->get('count');

        if ($msg && $topic && $count) {
            $config = $this->di->get('config')->kafka->toArray()['productpoolv2'];

            $config['topics'] = $topic;

            $this->kafka = new Lvmama\Cas\Component\Kafka\Producer($config);

            for ($i = 0; $i < $count; $i++) {
                $this->kafka->sendMsg($msg);
            }
            $r = [
                'code' => 200,
                'msg'  => 'success',
                'data' => [$topic, $msg, $count],
            ];
        } else {
            $r = [
                'code' => 1000,
                'msg'  => 'error',
                'data' => '',
            ];
        }
        return $this->jsonResponse($r);
    }

    /**
     * 从kafka中获取数据
     * @author lixiumeng
     * @datetime 2017-10-11T15:00:04+0800
     * @return   [type]                   [description]
     */
    public function getMsgFromKafkaAction()
    {
        $topic = $this->request->get('topic');
        $count = $this->request->get('count');
        $group = $this->request->get('group');

        $config            = $this->di->get('config')->kafka->toArray()['productpoolv2'];
        $config['topics']  = [$topic];
        $config['groupId'] = $group;

        $this->topicConf = new \RdKafka\TopicConf();
        // 'smallest': start from the beginning
        $this->topicConf->set('auto.offset.reset', 'largest');

        $this->conf = new \RdKafka\Conf();
        $this->conf->setRebalanceCb(function (\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(null);
                    break;

                default:
                    throw new \Exception($err);
            }
        });
        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $this->conf->set('group.id', $config['groupId']);
        // Initial list of Kafka brokers
        $this->conf->set('metadata.broker.list', $config['brokerList']);
        // Set the configuration to use for subscribed/assigned topics
        $this->conf->setDefaultTopicConf($this->topicConf);

        $this->kafkaConsumer = new \RdKafka\KafkaConsumer($this->conf);

        $this->kafkaConsumer->subscribe($config['topics']);

        $data = [];

        $msg = $this->kafkaConsumer->consume(120 * 1000);

        $data = [
            'key'     => $msg->key,
            'payload' => $msg->payload,
        ];
        echo json_encode($data, true);

        file_put_contents('/tmp/kafka_msg.log', json_encode($data, true) . "\n");

        $r = [
            'code' => 200,
            'msg'  => 'success',
            'data' => json_encode($data, true),
        ];

        return $this->jsonResponse($r);
    }

    /**
     * 获取pp_place表中信息
     */
    public function getPlaceProductsAction()
    {
        $channel_id = $this->request->getPost('channel_id');
        $route_id = $this->request->getPost('route_id');
        $key_id = $this->request->getPost('key_id');

        $params = array(
            'table' => 'pp_place',
            'select' => '*',
            'where' => " `channel_id` =  '{$channel_id}' AND `route_id` = '{$route_id}' AND `key_id` = '{$key_id}' AND `product_id` != '0' ",
        );

        $res = $this->pp_svc->getAllByParams($params);
        $this->jsonResponse($res);
    }
}