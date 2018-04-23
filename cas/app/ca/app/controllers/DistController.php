<?php
use Lvmama\Common\Utils\UCommon;

/**
 * 行政区控制器
 *
 * @author flash.guo
 *
 */
class DistController extends ControllerBase
{
    private $dist_base_svc;
    private $dist_type;
    protected $redis_svc;
    public function initialize()
    {
        parent::initialize();
        $this->dist_base_svc = $this->di->get('cas')->get('dist_base_service');
        $this->redis_svc     = $this->di->get('cas')->get('redis_data_service');
        $this->dist_type     = array(
            'CONTINENT'    => '洲',
            'COUNTRY'      => '国家',
            'PROVINCE'     => '省',
            'PROVINCE_DCG' => '直辖市',
            'PROVINCE_SA'  => '特别行政区',
            'PROVINCE_AN'  => '自治区',
            'CITY'         => '市',
            'COUNTY'       => '区/县',
            'TOWN'         => '乡镇/街道',
        );
    }

    /**
     * 区域级别
     */
    public function typeAction()
    {
        $this->jsonResponse($this->dist_type);
    }

    /**
     * 行政区详情
     */
    public function infoAction()
    {
        $id                                               = intval($this->request->get('id'));
        $distname                                         = trim($this->request->get('distname'));
        $disttype                                         = trim($this->request->get('disttype'));
        $conditions                                       = array();
        !empty($id) && $conditions['district_id']         = "=" . $id;
        !empty($distname) && $conditions['district_name'] = "='" . $distname . "'";
        !empty($disttype) && $conditions['district_type'] = "='" . $disttype . "'";
        !empty($conditions) && $dist_info                 = $this->dist_base_svc->getOneDist($conditions);
        if (empty($dist_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '行政区信息不存在');
            return;
        }
        $this->jsonResponse(array('results' => $dist_info));
    }

    /**
     * 上级区域
     */
    public function parentAction()
    {
        $loop       = $this->request->get('loop');
        $notype     = $this->request->get('notype');
        $ids        = trim($this->request->get('ids'), ",");
        $ids        = implode(",", array_map("intval", explode(",", $ids)));
        $conditions = array();
        if (!empty($ids)) {
            $conditions['district_id'] = " IN(" . $ids . ")";
            $dist_info                 = $this->dist_base_svc->getDistList($conditions);
        }
        if (empty($dist_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '行政区信息不存在');
            return;
        }
        $parents = "";
        $distarr = array();
        foreach ($dist_info as $dist) {
            $parents[$dist['district_id']] = $dist['district_name'] . (empty($notype) ? "(" . $this->dist_type[$dist['district_type']] . ")" : "");
            if (empty($distarr[$dist['district_id']])) {
                $distarr[$dist['district_id']]                  = array();
                $distarr[$dist['district_id']]['parent_id']     = $dist['parent_id'];
                $distarr[$dist['district_id']]['district_name'] = $dist['district_name'];
                $distarr[$dist['district_id']]['district_type'] = $dist['district_type'];
            }
            loop_start:
            if (!empty($dist['parent_id'])) {
                if (empty($distarr[$dist['parent_id']])) {
                    $parent                                       = $this->dist_base_svc->getOneDist(array('district_id' => "=" . $dist['parent_id']));
                    $distarr[$dist['parent_id']]                  = array();
                    $distarr[$dist['parent_id']]['parent_id']     = $parent['parent_id'];
                    $distarr[$dist['parent_id']]['district_name'] = $parent['district_name'];
                    $distarr[$dist['parent_id']]['district_type'] = $parent['district_type'];
                } else {
                    $parent = $distarr[$dist['parent_id']];
                }
                $parents[$dist['district_id']] .= "--" . $parent['district_name'] . (empty($notype) ? "(" . $this->dist_type[$parent['district_type']] . ")" : "");
                if (!empty($loop) && !empty($parent['parent_id']) && $parent['parent_id'] != $dist['parent_id']) {
                    $dist['parent_id'] = $parent['parent_id'];
                    goto loop_start;
                }
            }
        }
        unset($distarr);
        $this->jsonResponse(array('results' => $parents));
    }

    /**
     * 行政区列表
     */
    public function listAction()
    {
        $order        = $this->request->get('order');
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $order        = $order ? $order : "district_id DESC";
        $condition    = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = array('page_num' => $current_page, 'page_size' => $page_size);
        $dist_info    = $this->dist_base_svc->getDistList($condition, $limit, "*", $order);
        if (empty($dist_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '行政区信息不存在');
            return;
        }
        $total_records = $this->dist_base_svc->getDistTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $dist_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 行政区新增
     */
    public function addAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['update_time'] = time();
            $result              = $this->dist_base_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '行政区信息新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 行政区更新
     */
    public function updateAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['update_time'] = time();
            $result              = $this->dist_base_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '行政区信息更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 获取行政区列表
     * params foreign_flag Y:境外 N:境内
     * params district_type 行政区域级别
     */
    public function getDistrictsAction()
    {
        $dist_list     = array();
        $foreign_flag  = trim($this->request->get('foreign_flag'));
        $district_type = trim($this->request->get('district_type'));
        $parent_id     = intval($this->request->get('parent_id'));

        $condition = array('cancel_flag' => ' = "Y"');
        if (empty($foreign_flag)) {
            $this->_errorResponse(DATA_NOT_FOUND, '参数foreign_flag不能为空!');
        }
        $condition['foreign_flag'] = ' = ' . $foreign_flag;

        if (empty($district_type)) {
            $this->_errorResponse(DATA_NOT_FOUND, '参数district_type不能为空!');
        }
        $district_type_arr = explode(",", $district_type);
        if (count($district_type_arr) == 1) {
            $condition['district_type'] = ' = ' . $district_type_arr[0];
        } else {
            $condition['district_type'] = ' IN(' . implode('","', $district_type_arr) . ')';
        }

        $dist_list = $this->redis_svc->dataGet("district-list:" . $parent_id . '_' . $foreign_flag . "_" . $district_type);

        if (empty($dist_list)) {
            if (!empty($parent_id)) {
                $condition['parent_id'] = ' = ' . $parent_id;
            }

            $col = 'district_id, parent_id, district_type, district_name';

            $dist_list = $this->dist_base_svc->getDistList($condition, null, "*", null);

            if (!empty($dist_list)) {
                $dist_list = json_encode($dist_list);
                $this->redis_svc->dataSet(
                    "district-list:" . $parent_id . '_' . $foreign_flag . "_" . $district_type,
                    $dist_list,
                    300
                );
            }
        }

        $dist_list = json_decode($dist_list);

        $this->jsonResponse(array('error' => 200, 'result' => $dist_list));
    }

    /**
     * 国内省-城市行政区菜单
     */
    public function inlandLvToCountyAction()
    {
        $info = $this->dist_base_svc->getOneDist(array('district_name' => "='中国'"));

        if (empty($info['district_id'])) {
            $this->_errorResponse(DATA_NOT_FOUND, '行政区信息不存在');
        }

        $condition                       = $province_condition                       = $city_condition                       = array();
        $condition['cancel_flag']        = " ='Y' ";
        $condition['foreign_flag']       = " ='N' ";
        $columns                         = 'district_id,parent_id,district_type,district_name';
        $province_condition              = $condition;
        $province_condition['parent_id'] = " = " . $info['district_id'];
        $dist_province                   = $this->dist_base_svc->getDistList($province_condition, 100, $columns);
        if (empty($dist_province[0]['district_id'])) {
            $this->_errorResponse(DATA_NOT_FOUND, '行政区信息不存在');
        }

        foreach ($dist_province as $key => $value) {
            $city_condition                     = $condition;
            $city_condition['parent_id']        = " = " . $value['district_id'];
            $dist_province[$key]['subordinate'] = null;
            $dist_province[$key]['subordinate'] = $this->dist_base_svc->getDistList($city_condition, 100, $columns);
        }
        $this->jsonResponse(array('results' => $dist_province));
    }

    /**
     * [getAllByDistrictIdAction 获取所有级别的行政区信息]
     * @author lixiumeng
     * @datetime 2017-10-16T10:29:09+0800
     * @return   [type]                   [description]
     */
    public function getAllByDistrictIdAction()
    {
        $ids   = $this->request->get('id');
        $ids   = array_unique(array_filter(explode(',', $ids)));
        $nodes = $nodes_res = [];

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $key = 'district:tree:' . $id;

                $nodes[UCommon::calRedisNode($key)][$id] = $key;
            }
            $nodes_res = $this->getRedisByPipline($nodes);
        }

        $r = [
            'code' => 200,
            'data' => $nodes_res,
        ];

        $this->jsonResponse($r);

    }

    /**
     * [getInfoByDistrictIdAction description]
     * @author lixiumeng
     * @datetime 2017-10-16T10:29:13+0800
     * @return   [type]                   [description]
     */
    public function getInfoByDistrictIdAction()
    {

        $ids   = $this->request->get('id');
        $ids   = array_unique(array_filter(explode(',', $ids)));
        $nodes = $nodes_res = [];
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $key = 'district:' . $id;

                $nodes[UCommon::calRedisNode($key)][$id] = $key;
            }
            $nodes_res = $this->getRedisByPipline($nodes);
        }

        $r = [
            'code' => 200,
            'data' => $nodes_res,
        ];

        $this->jsonResponse($r);
    }

    /**
     * [getSubsByDistrictIdAction description]
     * @author lixiumeng
     * @datetime 2017-10-16T10:29:16+0800
     * @return   [type]                   [description]
     */
    public function getChildrenByDistrictIdAction()
    {
        $id        = $this->request->get('id');
        $nodes_res = [];
        if (!empty($id)) {
            $this->redis = $this->di->get('cas')->getRedis();
            $key         = 'district:children:' . $id;
            $nodes_res   = $this->redis->smembers($key);
        }
        $r = [
            'code' => 200,
            'data' => $nodes_res,
        ];

        $this->jsonResponse($r);
    }

    /**
     * 获取消息
     * @author lixiumeng
     * @datetime 2017-10-16T15:00:25+0800
     * @param    [type]                   $arr [description]
     * @return   [type]                        [description]
     */
    public function getRedisByPipline($arr)
    {
        $r           = [];
        $this->redis = $this->di->get('cas')->getRedis();
        foreach ($arr as $k => $v) {
            foreach ($v as $m => $n) {
                $r[$m] = $this->redis->hGetall($n);
            }
        }
        return $r;
    }

}
