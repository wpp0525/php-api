<?php

use \Lvmama\Cas\Service\RedisDataService;
use \Lvmama\Common\Utils\UCommon;
use Lvmama\Common\Utils\Filelogger;

/**
 * 无线端接口提供
 *
 * @author win.shenxiang
 */
class DestController extends ControllerBase
{
    private $ttl = 7200;
    /**
     * @var \Lvmama\Cas\Service\DestinationDataService
     */
    private $dest;
    /**
     * @var \Lvmama\Cas\Service\RedisDataService
     */
    private $redis;
    /**
     * @var \Lvmama\Cas\Service\DestDetailDataService
     */
    private $base;
    /**
     * @var \Lvmama\Cas\Service\ImageDataService
     */
    private $image;
    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $destin;
    /**
     * @var \Lvmama\Cas\Service\DestinBaseDataService
     */
    private $dest_district_nav;

    private $recom;
    /**
     * 不与父级相关联的搜索页面
     * @var array
     */
    private $not_search_parent = array('travel_dest','food_dest','goods_dest');

    public function initialize()
    {
        parent::initialize();
        $this->dest = $this->di->get('cas')->get('destination-data-service');
        $this->redis = $this->di->get('cas')->get('redis_data_service');
        $this->base = $this->di->get('cas')->get('dest_detail_service');
        $this->image = $this->di->get('cas')->get('dest_image_service');
        $this->destin = $this->di->get('cas')->get('destin_base_service');
        $this->recom = $this->di->get('cas')->get('mo-recommend-data-service');
        $this->dest_district_nav = $this->di->get('cas')->get('dest_district_nav_service');
        $this->dest_base_service = $this->di->get('cas')->get('dist_base_service');
        $this->logger = Filelogger::getInstance();
        $this->ttl = rand(28800, 86400);
    }

    public function indexAction()
    {
        return $this->jsonResponse(array('success' => 'welcome'));
    }

    /**
     * 获取指定条件的目的地基本信息
     * @param $foreign_flag 是否境外Y是N否
     * @param $dest_type 目的地类型
     * @param $parent_id 父级目的地ID
     * @return json
     * @example curl -XGET 'http://ca.lvmama.com/dest/getDestByCondition'
     */
    public function getDestByConditionAction()
    {
        $foreign_flag = $this->request->get('foreign_flag');
        $dest_type = $this->request->get('dest_type');
        $parent_id = $this->request->get('parent_id');
        $taiwan_is_city = $this->request->get('taiwan_is_city');//将台湾省当1个城市处理
        $page = $this->request->get('page');
        $pageSize = $this->request->get('pageSize');
        $page = is_numeric($page) && $page > 0 ? $page : 1;
        $pageSize = is_numeric($pageSize) && $pageSize > 0 ? $pageSize : 800;
        $pageSize = $pageSize > 800 ? 800 : $pageSize;//限制数量保证数据库和接口稳定
        $where = ' WHERE cancel_flag = \'Y\'';
        if ($foreign_flag) $where .= ' AND foreign_flag = \'' . $foreign_flag . '\'';
        if ($dest_type) $where .= ' AND dest_type = \'' . $dest_type . '\'';
        if ($parent_id) $where .= ' AND parent_id = ' . $parent_id;
        if (!$where) $this->_errorResponse(10001, '请至少传入1个查询条件');
        if ($taiwan_is_city == 'Y' && $foreign_flag == 'N' && $dest_type == 'CITY') {
            $where .= ' AND parent_id != 401 OR dest_id = 401';
        }
        //$total = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS num FROM biz_dest '.$where,true);
        //$totalPage = $total ? ceil($total['num'] / $pageSize) : 1;
        $start = ($page - 1) * $pageSize;
        $sql = 'SELECT dest_id,parent_id,district_id,dest_type,dest_name,en_name,pinyin,short_pinyin,dest_alias,local_lang,foreign_flag,dest_mark FROM biz_dest' . $where . ' LIMIT ' . $start . ',' . $pageSize;
        $result = $this->destin->getRsBySql($sql);
        $this->_successResponse($result);
    }

    /**
     * 查询目的地基本信息
     * @param dest_id 目的地id
     * @param num 获取娱乐点的数量
     * @param uid 用户ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getAppDestDetail
     */
    public function getAppDestDetailAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        //获取目的地基本信息
        $result = $this->dest->getDestById($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 查询目的地列表基本信息
     * @param dest_ids 目的地ids，用逗号分开
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getAppDestListDetailAction
     */
    public function getAppDestListDetailAction()
    {
        $dest_ids = isset($this->dest_ids) ? trim($this->dest_ids) : '';
        //获取目的地基本信息
        $result = $this->dest->getDestListByIds($dest_ids);

        $this->_successResponse($result);
    }

    /**
     * 查询POI目的地的详细信息
     * @param poiId 目的地ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/wapGetPoiDataById?poiId=101515
     */
    public function wapGetPoiDataByIdAction()
    {
        $dest_id = isset($this->poiId) ? intval($this->poiId) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        //获取目的地基本信息
        $data = $this->dest->getDestById($dest_id);
        if (!$data) {
            $this->_errorResponse(10003, '没有相关目的地的信息');
        }

        if ($data['stage'] != 2) {
            $this->_errorResponse(10004, '目的地类型不符');
        }

        $this->subject = $this->di->get('cas')->get('mo-subject');
        $result = array(
            'poiName' => $data['dest_name'],
            'poiType' => $data['dest_type'],
            'pinyin' => $data['pinyin'],
            'img_url' => $data['img_url'],
            'briefInfo' => $data['intro'],
            'poiSuperDest' => $this->dest->getDestById($data['parent_id']),
            'poiAddress' => $this->dest->getAddressByDestId($dest_id),
            'poiTheme' => $this->subject->getPoiThem($dest_id),
            'travelInfo' => array(
                'contact' => $this->dest->getContact($dest_id),
                'sale_time' => $this->dest->getSaleTime($dest_id),
                'suggest_time' => $this->dest->getSuggestTime($dest_id),
                'ticket' => $this->dest->getTicket($dest_id),
            ),
        );
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得交通信息
     * @param dest_id 目的地ID(多个用半角逗号隔开)
     * @param type 交通类型(AL-到达离开,LIC-本地/城际,SERV_CARD-交通卡券,SERV_OTHER-其它,POI目的地类型的交通)
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getTransportByDest?poiId=101515
     */
    public function getTransportByDestAction()
    {
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        $type = isset($this->type) ? $this->type : '';
        if (!$dest_id) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        //确保dest_id中的数据符合要求
        $tmp = array();
        foreach (explode(',', $dest_id) as $v) {
            if ($v && is_numeric($v)) {
                $tmp[] = $v;
            }
        }
        //确保交通类型数据符合要求
        if (!in_array($type, array('', 'AL', 'LIC', 'SERV_CARD', 'SERV_OTHER', 'POI'))) {
            $this->_errorResponse(10003, '请传入正确的type');
        }
        $result = $this->dest->getTransportByDest($tmp, $type);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地的取得指定推荐类型的POI目的地
     * @param dest_id 目的地ID
     * @param type 类型
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getDestByType?dest_id=1&type=VIEWSPOT
     */
    public function getDestByTypeAction()
    {
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        $type = isset($this->type) ? $this->type : '';
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 15;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        if (!$type || !in_array($type, array('VIEWSPOT', 'SCENIC_ENTERTAINMENT', 'RESTAURANT', 'SHOP', 'MAIN_DEST', 'VIEW_DEST', 'CITY', 'SCENIC'))) {
            $this->_errorResponse(10003, '请传入正确的类型');
        }
        if ($pageSize > 30) {
            $this->_errorResponse(10004, '每页最多取30条');
        }
        $result = $this->dest->getDestByType($dest_id, $type, $page, $pageSize);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得属于此目的地的ID集合
     * @param dest_id 目的地ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getSubDestIdByDestId?poiId=101515
     */
    public function getSubDestIdByDestIdAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getSubDestIdByDestId($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得地址信息
     * @param dest_id 目的地ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getAddress?poiId=101515
     */
    public function getAddressAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getAddressByDestId($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地poi的主题
     * @param dest_id 目的地POI ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getPoiThem?dest_id=101515
     */
    public function getPoiThemAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $this->subject = $this->di->get('cas')->get('mo-subject');
        $result = $this->subject->getPoiThem($dest_id);
        $this->_successResponse($result ? $result : array());
    }

    /**
     * 根据目的地POI取得联系方式
     * @param dest_id 目的地POI ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getContactById?dest_id=101515
     */
    public function getContactByIdAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getContact($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得营业时间
     * @param dest_id 目的地POI ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getTimeById?dest_id=101515
     */
    public function getTimeByIdAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getSaleTime($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地POI取得建议游玩时间
     * @param dest_id 目的地POI ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getSuggestTimeById?dest_id=101515
     */
    public function getSuggestTimeByIdAction()
    {
        $dest_ids = isset($this->dest_id) ? $this->dest_id : '';
        if (!$dest_ids) {
            $this->_errorResponse(10002, '请传入dest_id');
        }
        //只能是数字和,其他的过滤掉,防止含有,,,1231,,的情况
        $ids = array();
        foreach (explode(',', $dest_ids) as $dest_id) {
            if (is_numeric($dest_id)) {
                $ids[] = $dest_id;
            }
        }
        $result = $this->dest->getSuggestTime($ids);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地POI取得门票信息
     * @param dest_id 目的地POI ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getTicketById?dest_id=101515
     */
    public function getTicketByIdAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getTicket($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得相应的子目的地ID集合
     * @param dest_id 目的地 ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getNewPicsByDest?dest_id=1
     */
    public function getNewPicsByDestAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 15;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        if ($pageSize > 50) {
            $this->_errorResponse(10003, '每页最多取50条');
        }
        $key = 'dest:pics:dest_id:' . $dest_id . ':' . $page . ':' . $pageSize;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            //如果为大洲或者国家则取cms后台推荐的图片
            $data = $this->dest->getDestById($dest_id);
            if ($data['dest_type'] == 'CONTINENT' || $data['dest_type'] == 'SPAN_COUNTRY' || $data['dest_type'] == 'COUNTRY') {
                $result = $this->image->getListById($dest_id, 'dest', array('page' => $page, 'pageSize' => $pageSize));
            } else {
                $result = $this->dest->getTravelPicsByDest($dest_id, $page, $pageSize);
            }
            $this->redis->setArrayData($key, $result, $this->ttl);
        }
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得相应的游记图片集合
     * @param dest_id 目的地 ID
     * @param uid 目游记作者 ID
     * @param page 页码
     * @param pageSize 每页显示条数
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getPicsByDest?dest_id=1
     */
    public function getPicsByDestAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        $uid = isset($this->uid) ? addslashes($this->uid) : '';
        $page = isset($this->page) ? intval($this->page) : 1;
        $pageSize = isset($this->pageSize) ? intval($this->pageSize) : 15;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        if ($pageSize > 50) {
            $this->_errorResponse(10003, '每页最多取50条');
        }
        $key = 'dest:pics:dest_id:' . $dest_id . ':' . $uid . $page . $pageSize;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            //如果为大洲或者国家则取cms后台推荐的图片
            $data = $this->dest->getDestById($dest_id);
            if ($data['dest_type'] == 'CONTINENT' || $data['dest_type'] == 'SPAN_COUNTRY' || $data['dest_type'] == 'COUNTRY') {
                $result = $this->image->getListById($dest_id, 'dest', array('page' => $page, 'pageSize' => $pageSize));
            } else {
                $ids = $this->dest->getSubDestIdByDestId($dest_id);
                $result = $this->dest->getPicsByDest($ids, $uid, array('page' => $page, 'pageSize' => $pageSize));
            }
            $this->redis->setArrayData($key, $result, $this->ttl);
        }
        $this->_successResponse($result);
    }

    /**
     * 根据目的地ID取得相应的子目的地ID集合
     * @param dest_id 目的地 ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getPicsByDest?dest_id=1
     */
    public function getSummaryByIdAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $result = $this->dest->getSummaryById($dest_id);
        $this->_successResponse($result);
    }

    /**
     * 根据目的地取得相关游记
     * @param dest_id 目的地ID
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getTripList?dest_id=1
     */
    public function getTripListAction()
    {
        $dest_id = isset($this->dest_id) ? intval($this->dest_id) : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $poi_ids = $this->dest->getSubDestIdByDestId($dest_id);
        if (!$poi_ids) {
            $this->_errorResponse(10003, '没有找到相关的游记信息');
        }

        $trip_ids = $this->dest->getTripIdsByTrace($poi_ids);
        if (!$trip_ids) {
            $this->_errorResponse(10003, '没有找到相关的游记信息');
        }

        $trip_list = $this->dest->getTripList($trip_ids);
        if (!$trip_list) {
            $this->_errorResponse(10003, '没有找到相关的游记信息');
        }

        $this->_successResponse($trip_list);
    }

    /**
     * 根据dest_id取得base_id
     * @param dest_id
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getBaseIdByDestId?dest_id=1
     */
    public function getBaseIdByDestIdAction()
    {
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        if (!$dest_id || !is_numeric($dest_id)) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        $this->_successResponse(array('id' => $this->base->getBaseIdByDestId($dest_id)));
    }

    /**
     * 根据base_id取得dest基本信息
     * @param id
     * @return json
     * @example curl -i -X POST http://ca.lvmama.com/dest/getDestById?id=1
     */
    public function getDestByIdAction()
    {
        $id = isset($this->id) ? $this->id : 0;
        if (!$id || !is_numeric($id)) {
            $this->_errorResponse(10002, '请传入正确的id');
        }
        $this->_successResponse($this->base->getBaseInfoByBaseId($id));
    }

    /**
     * 根据dest ids取指定类型的上级目的地
     * 应用：可以查询所有祖先信息，也可以查询一个目的地属于哪个城市，省市等
     *
     * @param dest_ids string 目的地id，以英文逗号分隔
     * @param filter_type string 筛选上级目的地类型，默认为all，不进行筛选，若筛选类型为CITY且第一上级类型为COUNTRY择取COUNTRY
     * @return json
     *
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDestParentsByIds?dest_ids=3643&dest_type=CITY
     */
    public function getDestParentsByIdsAction()
    {
        $dest_ids = isset($this->dest_ids) ? $this->dest_ids : '';
        $filter_type = isset($this->filter_type) ? $this->filter_type : 'all';

        if (!$dest_ids) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        $base_srv = $this->di->get('cas')->get('dest_base_service');
        $rel_srv = $this->di->get('cas')->get('dest_relation_service');
        $base_type_ids = array();
        $data = array();
        $result = array();

        $key = 'dest:destparents:ids:' . $dest_ids . ':' . $filter_type;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            //合并同类型的dest，进行统一查询
            $dest_bases = $base_srv->getDestBaseByDestIds($dest_ids);
            foreach ($dest_bases as $base) {
                $base_type_ids[$base['dest_type']][] = $base['base_id'];
            }
            foreach ($base_type_ids as $type => $ids) {
                $data = array_merge($data, $rel_srv->getDestParentsList($ids, $type));
            }
            foreach ($dest_bases as $base) {
                $res = array();
                $res['dest_id'] = $base['dest_id'];
                foreach ($data as $d) {
                    if ($base['base_id'] == $d['child_base_id']) {
                        unset($d['child_base_id']);
                        $res['parents'][] = $d;
                    }
                }
                $result[] = $res;
            }

            //过滤
            if ($filter_type != 'all') {
                $tmp = array();
                foreach ($result as $key => $r) {
                    $tmp[$key]['dest_id'] = $r['dest_id'];
                    foreach ($r['parents'] as $rp) {
                        if ($rp['dest_type'] == $filter_type) {
                            $tmp[$key]['parents'][] = $rp;
                            break;
                        }
                    }
                    if (!isset($tmp[$key]['parents'])) {
                        $tmp[$key]['parents'][] = current($r['parents']);
                    }
                }
                $result = empty($tmp) ? $result : $tmp;
            }

            $this->redis->setArrayData($key, $result, $this->ttl);
        }

        $this->_successResponse($result);
    }

    /**
     * 根据目的地ids获取详情信息
     *
     * @param dest_ids string 以英文逗号分隔的id字符串
     * @return json
     *
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDestsByIds?dest_ids=3643
     */
    public function getDestsByIdsAction()
    {
        $dest_ids = isset($this->dest_ids) ? $this->dest_ids : '';

        if (!$dest_ids) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }

        $base_srv = $this->di->get('cas')->get('dest_base_service');
        $detail_srv = $this->di->get('cas')->get('dest_detail_service');
        $base_type_ids = array();

        $key = 'dest:getDestsByIds:ids:' . $dest_ids;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            $dest_details = array();
            $dest_bases = $base_srv->getDestBaseByDestIds($dest_ids);
            foreach ($dest_bases as $base) {
                $base_type_ids[$base['dest_type']][] = $base['base_id'];
            }
            foreach ($base_type_ids as $type => $ids) {
                $dest_details = array_merge($dest_details, $detail_srv->getDestsList($ids, $type));
            }
            //合并base和detail
            $result = $this->mergeBaseAndDetail($dest_bases, $dest_details);

            $this->redis->setArrayData($key, $result, $this->ttl);
        }

        $this->_successResponse($result);
    }

    /**
     * 获取离dest最近的其他同类型dest（单位m）
     *
     * @param dest_id int 目的地id
     * @param dest_type string 指定类型，以逗号分隔，例如 RESTAURANT,VIEWSPOT
     * @param num int 取多少条
     * @param need_self int 0\1 输出结果是否包含本身，num受其影响，默认不包含
     *
     * @example curl -i -X GET http://ca.lvmama.com/dest/getNearestDests?dest_id=3643
     */
    public function getNearestDestsAction()
    {
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        $dest_type = isset($this->dest_type) ? $this->dest_type : '';
        $num = isset($this->num) ? $this->num : 3;
        $need_self = isset($this->need_self) ? $this->need_self : 0;

        if (!$dest_id) {
            $this->_errorResponse(10002, '请传入正确的dest_id');
        }
        if (!$dest_type) {
            $this->_errorResponse(10002, '请传入正确的dest_type');
        }
        if (!$num) {
            $this->_errorResponse(10002, '请传入正确的num');
        }

        $ucommon = new UCommon();
        $base_srv = $this->di->get('cas')->get('dest_base_service');
        $rel_srv = $this->di->get('cas')->get('dest_relation_service');
        $detail_srv = $this->di->get('cas')->get('dest_detail_service');
        $base_ids = array();
        $nearest_dests = array();
        $same_level_dest_bases = array();
        $same_level_dest_details = array();

        $key = 'dest:getNearestDests:id:' . $dest_id . ':' . $dest_type . ':' . $num . ':' . $need_self;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            //获取相同父亲目的地的其他同类型目的地
            $dest_type = explode(',', $dest_type);
            $base = $base_srv->getOneByDestId($dest_id);
            $detail = $detail_srv->getDestDetailByBaseId($base['base_id'], $base['dest_type']);
            $same_level_dest_bases = $base_srv->getDestsByParentId($base['parent_id'], $dest_type);
            foreach ($same_level_dest_bases as $dest) {
                $base_ids[] = $dest['base_id'];
            }
            foreach ($dest_type as $t) {
                $same_level_dest_details[$t] = $detail_srv->getDestsList($base_ids, $t);
            }
            //计算距离
            foreach ($same_level_dest_bases as $ba) {
                foreach ($dest_type as $t) {
                    $sldd = isset($same_level_dest_details[$t]) && is_array($same_level_dest_details[$t]) ? $same_level_dest_details[$t] : array();
                    foreach ($sldd as $de) {
                        if ($ba['base_id'] == $de['base_id']) {
                            unset($de['base_id']);
                            $lat1 = $detail['latitude'];
                            $lon1 = $detail['longitude'];
                            $lat2 = $de['latitude'];
                            $lon2 = $de['longitude'];
                            if ($detail['abroad']) {
                                $lat1 = $detail['g_latitude'];
                                $lon1 = $detail['g_longitude'];
                            }
                            if ($de['abroad']) {
                                $lat2 = $de['g_latitude'];
                                $lon2 = $de['g_longitude'];
                            }
                            $distance = $ucommon->getDistance($lat1, $lon1, $lat2, $lon2);
                            $nearest_dests[$t][$distance] = array_merge($ba, $de);
                            $nearest_dests[$t][$distance]['distance'] = $distance;
                            break;
                        }
                    }
                }
            }
            //构造结果集
            foreach ($dest_type as $t) {
                ksort($nearest_dests[$t]);
                $i = 0;
                $nd = isset($nearest_dests[$t]) && is_array($nearest_dests[$t]) ? $nearest_dests[$t] : array();
                foreach ($nd as $dest) {
                    if (!$need_self && $dest['dest_id'] == $dest_id) {
                        continue;
                    }
                    if ($i >= $num) {
                        break;
                    }
                    $i++;

                    $result[$t][] = $dest;
                }
            }

            $this->redis->setArrayData($key, $result, $this->ttl);
        }

        $this->_successResponse($result);
    }

    /**
     * 根据主题查询相关目的地列表
     * @param subject_ids string 主题ids，以英文逗号分隔
     * @param dest_id int 相关目的地id
     * @param num int 取多少个
     */
    public function getDestsBySubjectIdsAction()
    {
        $subject_ids = $this->subject_ids;
        $dest_id = $this->dest_id; //'10000103';
        $num = $this->num;
        $dest_type = 'VIEWSPOT';
        $p_dest_type = 'PROVINCE';

        if (!$subject_ids) {
            $this->_errorResponse(10002, '请传入正确的subject_ids');
        }
        if (!$num) {
            $this->_errorResponse(10002, '请传入正确的num');
        }

        $base_srv = $this->di->get('cas')->get('dest_base_service');
        $detail_srv = $this->di->get('cas')->get('dest_detail_service');
        $rel_srv = $this->di->get('cas')->get('dest_relation_service');
        $product_srv = $this->di->get('cas')->get('dest_api_service');

        $key = 'dest:getDestsBySubjectIds:' . $subject_ids . ':' . $dest_id . ':' . $num;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            //查询相关目的地信息和其所在省份
            $dest = $base_srv->getOneByDestId($dest_id);
            $dest_parent = $rel_srv->getDestParentsList($dest['base_id'], $dest_type);
            $dest_province = array();
            foreach ($dest_parent as $parent) {
                if ($parent['dest_type'] == $p_dest_type) {
                    $dest_province = $parent;
                }
            }
            //查询相关目的地同省份的其他同主题目的地
            $dests = $base_srv->getDestsBySubjectIds($subject_ids, $dest_type, $dest_province['base_id']);
            //查询产品
            $bases = array();
            $base_ids = array();
            foreach ($dests as $key => $de) {
                $id = $de['dest_id'];
                $product = json_decode($product_srv->getProductByDestAndType($id, 'TICKET', 1, 0), true);
                //如果存在产品（门票）
                if (isset($product[0]) && isset($product[0]['goodsIds']) && $product[0]['goodsIds']) {
                    $bases[] = $de;
                    $base_ids[] = $de['base_id'];
                    if (count($bases) >= $num) {
                        break;
                    }
                }
            }
            if (count($bases) < $num) {
                foreach ($dests as $de) {
                    if (!in_array($de, $bases)) {
                        $bases[] = $de;
                        $base_ids[] = $de['base_id'];
                    }
                    if (count($bases) >= $num) {
                        break;
                    }
                }
            }
            $details = $detail_srv->getDestsList($base_ids, $dest_type);
            //合并base和detail
            $result = $this->mergeBaseAndDetail($bases, $details);

            $this->redis->setArrayData($key, $result, $this->ttl);
        }

        $this->_successResponse($result);
    }

    /**
     * 获取推荐目的地
     * @param identity string 推荐类型，例如lvyou_recom_season（按月份推荐），lvyou_in_current（当季热门) 详情请查mo_recommend_block表identity字段
     * @param recom_name string 推荐名称，以英文逗号分隔
     *
     * @example curl -i -X GET http://ca.lvmama.com/dest/getRecommendDests?identity=lvyou_recom_season&recom_name=1月,2月,3月
     */
    public function getRecommendDestsAction()
    {
        $identity = $this->identity;
        $recom_name = $this->recom_name;
        $per_num = $this->per_num;

        if (!$identity) {
            $this->_errorResponse(10002, '请传入正确的identity');
        }

        $recom_srv = $this->di->get('cas')->get('mo-recommend-data-service');
        $base_srv = $this->di->get('cas')->get('dest_base_service');
        $detail_srv = $this->di->get('cas')->get('dest_detail_service');
        $recom_dest_ids = array();
        $recom_name_ids = array();
        $bases = array();
        $details = array();
        $result = array();

        $key = 'dest:getRecommendDests:' . $identity . ':' . $recom_name . ':' . $per_num;
        $result = $this->redis->getArrayData($key);
        if (!$result) {
            $names = explode(',', $recom_name);
            if ($per_num) {
                //如果限定了数量，则按 recom_name 依次查询指定数量
                foreach ($names as $n) {
                    $recom_dest_ids = array_merge($recom_dest_ids, $recom_srv->getRecommendDestIds($identity, array($n), $per_num));
                }
            } else {
                $recom_dest_ids = $recom_srv->getRecommendDestIds($identity, explode(',', $recom_name));
            }
            $ids = array();
            foreach ($recom_dest_ids as $re) {
                $ids[] = $re['object_id'];
                $recom_name_ids[$re['object_id']] = $re['name'];
            }
            $bases = $base_srv->getDestBaseByDestIds($ids);
            $types = array();
            foreach ($bases as $key => $ba) {
                $types[$ba['dest_type']][] = $ba['base_id'];
                $bases[$key]['recom_name'] = $recom_name_ids[$ba['dest_id']];
            }
            foreach ($types as $type => $b_ids) {
                $details = array_merge($details, $detail_srv->getDestsList($b_ids, $type));
            }
            //合并
            $result = $this->mergeBaseAndDetail($bases, $details);

            $this->redis->setArrayData($key, $result, $this->ttl);
        }

        $this->_successResponse($result);
    }

    /**
     * 合并base和detail
     */
    private function mergeBaseAndDetail($bases, $details)
    {
        $result = array();
        foreach ($bases as $base) {
            foreach ($details as $detail) {
                if ($base['base_id'] == $detail['base_id']) {
                    unset($detail['base_id']);
                    $result[] = array_merge($base, $detail);
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 获取门票频道的省与地级市导航信息
     * @return string |json
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDestNav
     */
    public function getDestNavAction()
    {
        $type = $this->request->get('type');
        $table = 'dest_nav';
        if (!empty($type)) {
            $table .= "_" . $type;
        }

        $data = $this->destin->getList('', $table);
        $this->_successResponse($data);
    }

    /**
     * 根据行政区ID获取目的地基本信息
     * @param $district_id
     * @return string | json
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDistrictInfoById
     */
    public function getDistrictInfoByIdAction()
    {
        $district_id = $this->request->get('district_id');
        if (!$district_id || !is_numeric($district_id)) {
            $this->_errorResponse(10001, '请传入正确的district_id');
        }

        $data = $this->destin->getDistrictById($district_id);
        $this->_successResponse($data);
    }

    /**
     * 根据目的地ID获取行政区ID
     * @param $dest_id
     * @return string | json
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDistrictIdByDestId
     */
    public function getDistrictIdByDestIdAction()
    {
        $data = array();

        $dest_id = $this->request->get('dest_id');
        if (empty($dest_id)) {
            $this->_errorResponse(10001, '请传入正确的dest_id');
        }

        $redis_key = 'DistrictIdByDestId:' . $dest_id;
        $data = $this->redis->dataGet($redis_key);

        if (empty($data)) {
            $dest_info = $this->destin->getOneById($dest_id);

            if (!empty($dest_info)) {
                $data['district_id'] = $dest_info['district_id'];
                $data = json_encode($data);
                $this->redis->dataSet($redis_key, $data, 300);
            }
        }

        $data = json_decode($data, true);

        $this->_successResponse($data);
    }

    /**
     * 获省与地级市导航信息
     * @return string |json
     * @example curl -i -X GET http://ca.lvmama.com/dest/getDestDistrictNav
     */
    public function getDestDistrictNavAction()
    {
        $data = array();
        $type = $this->request->get('type');

        $redis_key = 'destDistrictNavList:' . $type;
        $data = $this->redis->dataGet($redis_key);

        if (empty($data)) {
            if ($type == 'subject' || $type == 'channel') {
                $type = 'homepage';
            }
            $data_list = $this->dest_district_nav->geDestDistrictNavList($type);
            if (!empty($data_list)) {
                $data = json_encode($data_list);
                $this->redis->dataSet($redis_key, $data, 300);
            }
        }
        $data = json_decode($data, true);

        $this->_successResponse($data);
    }

    //将表dest_nav中的数据同步到dest_district_nav表
    public function setDestDistrictNavByDestNavAction()
    {
        $data = $dest_ids = array();
        $data_nav = $this->destin->getList('', 'dest_nav');

        if (!empty($data_nav)) {
            foreach ($data_nav as $item) {
                $data[$item['dest_id']]['dest_id'] = $item['dest_id'];
                $data[$item['dest_id']]['dest_name'] = $item['dest_name'];
                $data[$item['dest_id']]['dest_parent_id'] = $item['parent_id'];
                $data[$item['dest_id']]['dest_pinyin'] = $item['pinyin'];
                $dest_ids[] = $item['dest_id'];
            }
        }
        $sql = "select bd.dest_id,bdt.district_id,bdt.district_name,bdt.parent_id as district_parent_id,bdt.pinyin as district_pinyin from biz_dest bd join biz_district bdt on bd.district_id = bdt.district_id where bd.dest_id in (" . implode(',', $dest_ids) . ");";

        $data_dest_district = $this->dest_district_nav->getRsBySql($sql);

        if (!empty($data_dest_district)) {
            foreach ($data_dest_district as $info) {
                $data[$info['dest_id']]['district_id'] = $info['district_id'];
                $data[$info['dest_id']]['district_name'] = $info['district_name'];
                $data[$info['dest_id']]['district_parent_id'] = $info['district_parent_id'];
                $data[$info['dest_id']]['district_pinyin'] = $info['district_pinyin'];
                $data[$info['dest_id']]['is_ticket'] = 1;
                $this->dest_district_nav->insert($data[$info['dest_id']]);
            }
        }

        $this->_successResponse($data);
    }

    //将表dest_nav_abroad中的数据同步到dest_district_nav表
    public function setDestDistrictNavByDestNavAbroadAction()
    {
        $data = $dest_ids = array();
        $data_abroad = $this->destin->getList('', 'dest_nav_abroad');

        if (!empty($data_abroad)) {
            foreach ($data_abroad as $item) {
                $data[$item['dest_id']]['dest_id'] = $item['dest_id'];
                $data[$item['dest_id']]['dest_name'] = $item['dest_name'];
                $data[$item['dest_id']]['dest_parent_id'] = $item['parent_id'];
                $data[$item['dest_id']]['dest_pinyin'] = $item['pinyin'];
                $dest_ids[] = $item['dest_id'];
            }
        }
        $sql = "select bd.dest_id,bd.dest_name,bd.parent_id as dest_parent_id,bd.pinyin as dest_pinyin,bdt.district_id,bdt.district_name,bdt.parent_id as district_parent_id,bdt.pinyin as district_pinyin from biz_dest bd join biz_district bdt on bd.district_id = bdt.district_id where bd.dest_id in (" . implode(',', $dest_ids) . ");";

        $data_dest_district = $this->dest_district_nav->getRsBySql($sql);

        if (!empty($data_dest_district)) {
            foreach ($data_dest_district as $info) {
                $data[$info['dest_id']]['district_id'] = $info['district_id'];
                $data[$info['dest_id']]['district_name'] = $info['district_name'];
                $data[$info['dest_id']]['district_parent_id'] = $info['district_parent_id'];
                $data[$info['dest_id']]['district_pinyin'] = $info['district_pinyin'];
                $data[$info['dest_id']]['is_abroad'] = 1;

                $res = $this->dest_district_nav->getDestDistrictByDestId($info['dest_id']);

                if (empty($res)) {
                    $this->dest_district_nav->insert($data[$info['dest_id']]);
                } else {
                    $this->dest_district_nav->updateByDestId($info['dest_id'], array('is_abroad' => 1));
                }
            }
        }

        $this->_successResponse($data);
    }

    public function destHotChildrenAction()
    {
        $parent_id = $this->request->get('parent_id');
        $abroad = $this->request->get('abroad');
        $stage = $this->request->get('stage');
        $exception_id = $this->request->get('exception_id');
        $limit = intval($this->request->get('limit')) ? intval($this->request->get('limit')) : '5';
        $where_condition = array();
        if ($parent_id) {
            $where_condition[] = "`parent_id` = " . $parent_id;
        }

        if ($abroad) {
            $where_condition[] = "`abroad` = '" . $abroad . "'";
        }

        if ($stage) {
            $where_condition[] = "`stage` = " . $stage;
        }

        if ($exception_id) {
            if (strpos($exception_id, ',')) {
                $where_condition[] = "`dest_id` NOT IN (" . $exception_id . ")";
            } else {
                $where_condition[] = "`dest_id` != " . $exception_id;
            }
        }

//        echo json_encode($where_condition); die;
        $data = $this->dest->destHotChildren($where_condition, $limit);
        $json_data = json_encode($data);

        $dest = explode(',', $exception_id);
        foreach ($dest as $id) {
            $redis_key = str_replace(array('{dest_id}', '{parent_id}', '{limit}'), array(trim($id), $parent_id, $limit), RedisDataService::REDIS_NEW_DEST_HOT_BROTHER);
            $redis_key = str_replace(',', '_', $redis_key);
            $ttl = $this->redisConfig['ttl']['lvyou_new_dest_hot_brother'] ? $this->redisConfig['ttl']['lvyou_new_dest_hot_brother'] : 14400;
            $this->redis->dataSet($redis_key, $json_data, $ttl);
        }

        echo $json_data;
    }

    public function getDestSeoLinkAction()
    {
        $dest_id = $this->request->getPost('dest_id');
    }

    /**
     * 获取当季热推
     */
    public function getDestRecomSeasonAction()
    {
        $dest_id = $this->request->getPost('dest_id');
        $redis_key = RedisDataService::REDIS_NEW_HOT_DEST_RECOM_SEASON . $dest_id;
        $recom = $this->redis->dataGet($redis_key);

        if (!$recom) {
            $recom = $this->recom->getDestRecomSeason($dest_id);
            if ($recom && is_array($recom)) {
                $this->redis->dataSet($redis_key, json_encode($recom), 86400);
                echo json_encode($recom);
                die;
            } else {
                echo json_encode(array());
                die;
            }
        }
        echo $recom;
    }

    /**
     * 根据POI集合获取其图片
     * @param $dest_ids poi集合,半角逗号分隔
     * @param $num 获取图片的张数
     * @return json
     * @example curl -XGET 'http://ca.lvmama.com/dest/getPicsByPois'
     */
    public function getPicsByPoisAction()
    {
        $dest_ids = $this->request->get('dest_ids');
        $num = $this->request->get('num');
        $img_size = $this->request->get('img_size');
        $num = is_numeric($num) ? $num : 4;
        $pois = array();
        foreach (explode(',', $dest_ids) as $id) {
            if ($id && is_numeric($id)) {
                $pois[] = $id;
            }
        }
        if (!$pois || count($pois) > 25) {
            $this->_errorResponse(10001, '请传入正确的参数dest_ids且dest_id总数不能超过25个');
        }
        $pois_str = implode(',', $pois);
        $redis_key = str_replace(array('{num}', '{pois}'), array($num, $pois_str), RedisDataService::REDIS_POIS_IMAGE_LIST);
        $redis_data = $this->redis->dataGet($redis_key);
        if ($redis_data) {
            $data = json_decode($redis_data, true);
        } else {
            $data = $this->image->getImgByIds($pois_str, $num);
            $this->redis->dataSet($redis_key, json_encode($data, JSON_UNESCAPED_UNICODE), $this->ttl);
        }
        if ($img_size) {
            foreach ($data as $poi => $item) {
                foreach ($item as $k => $v) {
                    $item[$k]['img_url' . $img_size] = '/' . UCommon::makePicSize2($v['img_url'], $img_size);
                }
                $data[$poi] = $item;
            }
        }
        $this->_successResponse($data);
    }

    /**
     * 获取dest_id下的子列表
     * @params dest_id
     * @return array
     */
    public function getDestChildListAction()
    {
        $data = array();

        $dest_id = $this->request->get('dest_id');
        if (empty($dest_id)) {
            $this->_errorResponse(10001, '请传入正确的dest_id');
        }

        $redis_key = 'getDestChildListByParentId:' . $dest_id;
        $data = $this->redis->dataGet($redis_key);

        if (empty($data)) {
            $dest_info = $this->destin->getDestChildListByParentId($dest_id);

            if (!empty($dest_info)) {
                $data = json_encode($dest_info);
                $this->redis->dataSet($redis_key, $data, 300);
            }
        }

        $data = json_decode($data, true);

        $this->_successResponse($data);
    }

    /**
     * 根据行政区名字取行政区数据
     * 精确匹配行政区名称，返回区/县级别以上的数据
     * @param district_name String 行政区名称
     * @author jianghu
     */
    public function getDistrictDataByDistrictNameAction()
    {
        $district_name = $this->request->get('district_name');
        if (!$district_name) {
            $this->_errorResponse(10001, "请传入正确的 districtName");
        }

        $where = "(`district_name` = '{$district_name}' OR `district_name2` = '{$district_name}') AND `cancel_flag` = 'Y' AND `district_type` != 'TOWN'";
        $data = $this->dest_base_service->getDistList($where);

        $this->_successResponse($data);
    }

    /**
     * 获取dest_type信息
     * @param $code
     * @return array
     */
    public function getDestTypeAction()
    {
        $code = $this->request->get('code');
        $group_id = $this->request->get('group_id');
        $condition = array();
        if ($code) {
            if (!preg_match('^\w+$', $code)) {
                $this->_errorResponse(10001, '请传入正确的 code');
            }
            $condition['code'] = $code;
        }
        if ($group_id && is_numeric($group_id)) {
            $condition['group_id'] = $group_id;
        }
        $dest_types = $this->destin->getDestType($condition);
        $this->_successResponse($dest_types);
    }

    /**
     * 搜索目的地列表数据
     * 支持的搜索为 dest_id,dest_name,dest_type,parent_name,stage,cancel_flag
     * @return json
     * @example curl -XGET 'http://ca.lvmama.com/dest/search'
     * @author shenxiang
     */
    public function searchAction()
    {
        $searchCondition = $this->request->get();
        $limit = array();
        if (isset($searchCondition['page_num'])) {
            $limit['page_num'] = $searchCondition['page_num'];
            unset($searchCondition['page_num']);
        }
        if (isset($searchCondition['page_size'])) {
            $limit['page_size'] = $searchCondition['page_size'];
            unset($searchCondition['page_size']);
        }
        $this->_successResponse($this->destin->search($searchCondition, $limit));
    }

    public function getInfoByConditionAction()
    {
        $gets = $this->request->get('param');
        if (empty($gets)) $this->_errorResponse(10001, '请传入param查询条件');
        $params = json_decode($gets, true);
        if (!is_array($params)) $this->_errorResponse(10002, 'param查询条件格式有误');
        if (empty($params['where'])) $this->_errorResponse(10003, '请传入查询条件');
        if (empty($params['table'])) $this->_errorResponse(10004, '请传入表名');
        if (empty($params['limit'])) $params['limit'] = null;
        if (empty($params['columns'])) $params['columns'] = 'dest_id,parent_id,district_id,district_name,dest_type,dest_type_name,dest_name,en_name,pinyin,short_pinyin,dest_alias,local_lang,cancel_flag,parents,parent_name,parent_names,district_parent_id,district_parent_ids,district_parent_name,district_parent_names,stage,`range`,intro,star,abroad,url,heritage,protected_area,ent_sight,showed,img_url,coord_type,longitude,latitude,g_longitude,g_latitude';
        if (empty($params['order'])) $params['order'] = null;
        $data = $this->destin->getList($params['where'], $params['table'], $params['limit'], $params['columns'], $params['order']);
        $this->_successResponse($data);
    }

    /**
     * 保存目的地编辑页的内容
     * @param $data (一维数组，保证数组键名和表字段名一致)
     * @author shenxiang
     * @example curl -i -X POST http://ca.lvmama.com/dest/saveDestination
     */
    public function saveDestinationAction()
    {
        $data = $this->request->getPost();
        if (empty($data['dest_id']) || !is_numeric($data['dest_id'])) $this->_errorResponse(10001, '请传入需要修复的dest_id且保证类型正确');
        if (isset($data['_url'])) unset($data['_url']);
        if (isset($data['submit'])) unset($data['submit']);
        $dest_id = $data['dest_id'];
        unset($data['dest_id']);
        if (!count($data)) $this->_errorResponse(10002, '请传入需要修改的字段及值');
        $fields = array();
        $param = array(':dest_id' => $dest_id);
        foreach ($data as $field => $value) {
            $param[':' . $field] = $value;
            $fields[] = '`' . $field . '` = :' . $field;
        }
        $sql = 'UPDATE ly_destination SET ' . implode(',', $fields) . ' WHERE dest_id = :dest_id';
        $this->logger->addLog($sql, 'INFO');
        $this->logger->addLog(json_encode($param, JSON_UNESCAPED_UNICODE), 'INFO');
        $this->destin->execute($sql, $param) ? $this->_successResponse('保存成功') : $this->_errorResponse(10003, '保存成失败');

    }

    /**
     * 新增目的地编辑页的内容
     * @param $data (一维数组，保证数组键名和表字段名一致)
     * @author shenxiang
     * @example curl -i -X POST http://ca.lvmama.com/dest/saveDestination
     */
    public function insertDestinationAction()
    {
        $data = $this->request->getPost();
        if (empty($data['dest_id']) || !is_numeric($data['dest_id'])) $this->_errorResponse(10001, '请传入需要修复的dest_id且保证类型正确');
        if (isset($data['_url'])) unset($data['_url']);
        if (isset($data['submit'])) unset($data['submit']);
        if (!count($data)) $this->_errorResponse(10002, '请传入需要修改的字段及值');
        $fields = array();
        $param = array();
        foreach ($data as $field => $value) {
            $param[':' . $field] = $value;
            $fields[] = '`' . $field . '` = :' . $field;
        }
        $sql = 'INSERT INTO ly_destination SET ' . implode(',', $fields);
        $this->logger->addLog($sql, 'INFO');
        $this->logger->addLog(json_encode($data, JSON_UNESCAPED_UNICODE), 'INFO');
        $this->destin->execute($sql, $param) ? $this->_successResponse('保存成功') : $this->_errorResponse(10003, '保存成失败');

    }

    /**
     * 根据dest_id批量查询是否简化模板
     * @param $dest_ids 目的地ID集合,半角逗号隔开
     * @return json
     * @author shenxiang
     * @example curl -XGET 'http://ca.lvmama.com/dest/getTempCode'
     */
    public function getTempCodeAction()
    {
        $dest_ids = $this->request->get('dest_ids');
        $return = array();
        $ids = array();
        foreach (explode(',', $dest_ids) as $id) {
            if (is_numeric($id)) {
                $ids[] = $id;
            }
        }
        if (empty($ids)) $this->_successResponse($return);
        $rs = $this->destin->getList(array('dest_id' => ' IN(' . implode(',', $ids) . ')'), 'dest_base', array(), 'temp_code,dest_id');
        foreach ($rs as $row) {
            $return[$row['dest_id']] = $row['temp_code'];
        }
        $this->_successResponse($return);
    }

    /**
     * 设置cancel_flag状态
     * @param dest_id 目的地ID
     * @return json
     */
    public function setCancelFlagAction()
    {
        $dest_id = $this->request->get('dest_id');
        if (!$dest_id || !is_numeric($dest_id)) $this->_successResponse(0);
        $row = $this->destin->getOne(array('dest_id' => ' = ' . $dest_id), 'ly_destination', 'cancel_flag');
        if (empty($row)) $this->_successResponse(0);
        $new_cancel_flag = $row['cancel_flag'] == 'Y' ? 'N' : 'Y';
        $sql = 'UPDATE ly_destination SET `cancel_flag` = :cancel_flag WHERE dest_id = :dest_id';
        $flag = $this->destin->execute($sql, array(':cancel_flag' => $new_cancel_flag, ':dest_id' => $dest_id));
        $this->_successResponse($flag ? 1 : 0);
    }

    /**
     * 设置showed状态
     * @param dest_id 目的地ID
     * @return json
     */
    public function setShowedAction()
    {
        $dest_id = $this->request->get('dest_id');
        if (!$dest_id || !is_numeric($dest_id)) $this->_successResponse(0);
        $row = $this->destin->getOne(array('dest_id' => ' = ' . $dest_id), 'ly_destination', 'showed');
        if (empty($row)) $this->_successResponse(0);
        $new_showed = $row['showed'] == 'Y' ? 'N' : 'Y';
        $sql = 'UPDATE ly_destination SET `showed` = :showed WHERE dest_id = :dest_id';
        $flag = $this->destin->execute($sql, array(':showed' => $new_showed, ':dest_id' => $dest_id));
        $this->_successResponse($flag ? 1 : 0);
    }

    /**
     * 设置showed状态
     * @param dest_id 目的地ID
     * @return json
     */
    public function setTempCodeAction()
    {
        $dest_id = $this->request->get('dest_id');
        if (!$dest_id || !is_numeric($dest_id)) $this->_successResponse(0);
        $row = $this->destin->getOne(array('dest_id' => ' = ' . $dest_id), 'dest_base', 'temp_code');
        if (empty($row)){//添加,并设置为简化模板
            $fields = array('dest_id','dest_name','dest_type','parent_id','district_id','district_parent_id','cancel_flag','showed','stage','range','ent_sight');
            $values = array();
            //查询此目的地基本信息
            $row = $this->destin->getRsBySql('SELECT `'.implode('`,`',$fields).'` FROM ly_destination WHERE dest_id = '.$dest_id,true);
            $row['temp_code'] = 1;
            $fields[] = 'temp_code';
            foreach($fields as $key){
                $values[':'.$key] = $row[$key];
            }
            $sql = 'INSERT INTO dest_base(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
            $flag = $this->destin->execute($sql, $values);
        }else{
            $new_status = $row['temp_code'] == 1 ? 0 : 1;
            $sql = 'UPDATE dest_base SET `temp_code` = :temp_code WHERE dest_id = :dest_id';
            $flag = $this->destin->execute($sql, array(':temp_code' => $new_status, ':dest_id' => $dest_id));
        }
        $this->_successResponse($flag ? 1 : 0);
    }

    /**
     * 保存ly_data的排序值
     */
    public function saveSeqsAction()
    {
        $seqs = $this->request->get('seqs');
        if (empty($seqs)) $this->_errorResponse(10001, '排序值参数无效');
        $data = json_decode($seqs, true);
        $params = array();
        foreach ($data as $data_id => $seq) {
            if (!is_numeric($seq)) continue;
            $params[] = array(':seq' => $seq, ':data_id' => $data_id);
        }
        $sql = 'UPDATE ly_data SET seq = :seq WHERE data_id = :data_id';
        $this->_successResponse($this->destin->execute($sql, $params,true));
    }

    public function getNavAction()
    {
        $params = $this->request->get();
        $dest_id = empty($params['dest_id']) ? 0 : $params['dest_id'];
        $cate_id = empty($params['cate_id']) ? 1 : $params['cate_id'];
        $data_id = empty($params['data_id']) ? 0 : $params['data_id'];
        if (empty($dest_id) || !is_numeric($dest_id)) $this->_errorResponse(10001, '请传入正确的dest_id');
        $dest = $this->destin->getOne(array('dest_id' => '=' . $dest_id), 'ly_destination', 'dest_id,dest_name,stage,dest_type');
        switch ($dest['stage']) {
            case 1:
                $object_type = 'DEST';
                break;
            case 2:
                $object_type = 'POI';
                break;
            case 3:
                $object_type = 'HOTEL';
                break;
            case 4:
                $object_type = 'TRAFFIC';
                break;
        }
        $nav = $this->destin->getRsBySql("SELECT * FROM ly_category WHERE `deved`='Y' AND `parent_id`='0' AND `channel`='lvyou' AND FIND_IN_SET('{$object_type}',object_type) ORDER BY `seq` ASC,`cate_id` ASC");
        foreach ($nav as $key => $row) {
            $sub = $this->destin->getRsBySql("SELECT * FROM ly_category WHERE `deved`='Y' AND `parent_id`='{$row['cate_id']}' ORDER BY `seq` ASC,`cate_id` ASC");
            if (empty($sub)) continue;
            $nav[$key]['sub'] = $sub;
        }
        foreach($sub as $sub_key => $sub_row){
            $ssub = $this->destin->getRsBySql('SELECT * FROM ly_data WHERE dest_id = '.$dest_id.' AND cate_id = '.$cate_id.' AND parent_id = '.$data_id);
            if(empty($ssub)) continue;
            $nav[$key]['sub'][$sub_key]['sub'] = $ssub;
        }
        $this->_successResponse($nav);
    }
    /**
     * 保存ly_data数据
     */
    public function saveLyDataAction(){
        $info = $this->request->getPost();
        if(isset($info['_url'])) unset($info['_url']);
        if(isset($info['api'])) unset($info['api']);
        if(!isset($info['en_text'])) $info['en_text'] = '';
        $fields = array();
        $vals = array();
        if(empty($info)) $this->_errorResponse(10001, 'POST数据为空');
        if(empty($info['data_id'])){//自建添加
            if(isset($info['data_id'])) unset($info['data_id']);
            foreach($info as $key => $val){
                $fields[] = $key;
                $vals[':'.$key] = $val;
            }
            $sql = 'INSERT INTO ly_data(`'.implode('`,`',$fields).'`) VALUES (:'.implode(',:',$fields).')';
            $return = $this->destin->execute($sql,$vals);
            $data_id = $return ? $this->destin->lastInsertId() : 0;
        }else{//修改编辑
            if(!is_numeric($info['data_id'])) $this->_errorResponse(10002, 'data_id不正确');
            $data_id = $info['data_id'];
            unset($info['data_id']);
            $vals[':data_id'] = $data_id;
            foreach($info as $key => $val){
                $fields[] = '`'.$key.'` = :'.$key;
                $vals[':'.$key] = $val;
            }
            $sql = 'UPDATE ly_data SET '.implode(',',$fields).' WHERE data_id = :data_id';
            $return = $this->destin->execute($sql,$vals);
            $data_id = $return ? $data_id : 0;
        }
        $this->_successResponse($data_id);
    }
    /**
     * 删除feature_id
     */
    public function featureDelAction(){
        $feature_id = $this->request->get('feature_id');
        if(empty($feature_id) || !is_numeric($feature_id)) $this->_errorResponse(10001,'请传入正确的feature_id');
        $sql = 'DELETE FROM ly_feature WHERE feature_id = :feature_id';
        $this->_successResponse($this->destin->execute($sql,array(':feature_id' => $feature_id)));
    }
    /**
     * 保存ly_data的排序值
     */
    public function featureSeqsAction(){
        $seqs = $this->request->get('seqs');
        if (empty($seqs)) $this->_errorResponse(10001, '排序值参数无效');
        $data = json_decode($seqs, true);
        $params = array();
        foreach ($data as $feature_id => $seq) {
            if (!is_numeric($seq)) continue;
            $params[] = array(':seq' => $seq, ':feature_id' => $feature_id);
        }
        $sql = 'UPDATE ly_feature SET seq = :seq WHERE feature_id = :feature_id';
        $this->_successResponse($this->destin->execute($sql, $params,true));
    }
    /**
     * 保存特色亮点
     */
    public function featureSaveAction(){
        $data = $this->request->getPost();
        if(empty($data['dest_id']) || !is_numeric($data['dest_id'])){
            $this->_errorResponse(10001, '请传入目的地ID且为正整数');
        }
        if(empty($data['feature_name'])){
            $this->_errorResponse(10002, '标题不能为空');
        }
        $fields = array();
        $param = array();
        if(empty($data['feature_id']) || !is_numeric($data['feature_id'])){//视为新增
            foreach($data as $field => $value){
                $fields[] = $field;
                $param[':'.$field] = $value;
            }
            $sql = 'INSERT INTO ly_feature(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
            $flag = $this->destin->execute($sql,$param);
        }else{
            $feature_id = $data['feature_id'];
            unset($data['feature_id']);
            foreach($data as $field => $value){
                $fields[] = $field.'=:'.$field;
                $param[':'.$field] = $value;
            }
            $param[':feature_id'] = $feature_id;
            $sql = 'UPDATE ly_feature SET '.implode(',',$fields).' WHERE feature_id = :feature_id';
            $flag = $this->destin->execute($sql,$param);
        }
        $this->_successResponse($flag);
    }
    /**
     * 保存建议游玩时间
     */
    public function saveSuggestTimeAction(){
        $data = $this->request->getPost();
        $units = array('H','M','D');
        if(empty($data['dest_id']) || !is_numeric($data['dest_id'])) $this->_errorResponse(10001, '请传入目的地ID且为正整数');
        if(empty($data['time']) || !is_numeric($data['time'])) $this->_errorResponse(10002, '请传正确的建议游玩时间');
        if(empty($data['unit']) || !in_array($data['unit'],$units)) $this->_errorResponse(10003, '请传入正确的时间单位');
        $fields = array();
        $param = array();
        if(empty($data['st_id'])){//创建
            foreach($data as $field => $value){
                $fields[] = $field;
                $param[':'.$field] = $value;
            }
            $sql = 'INSERT INTO ly_suggest_time(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
            $flag = $this->destin->execute($sql,$param);
        }else{//更新
            $st_id = $data['st_id'];
            unset($data['st_id']);
            if(!is_numeric($st_id)) $this->_errorResponse(10004, '请传正确的st_id');
            foreach($data as $field => $value){
                $fields[] = '`'.$field.'` = :'.$field;
                $param[':'.$field] = $value;
            }
            $param[':st_id'] = $st_id;
            $sql = 'UPDATE ly_suggest_time SET '.implode(',',$fields).' WHERE st_id = :st_id';
            $flag = $this->destin->execute($sql,$param);
            //ly_data里面把status也更新一下
            $sql = 'UPDATE ly_data SET `status` = :status WHERE data_id = :data_id';
            $flag = $this->destin->execute($sql,array(
                ':status' => $data['status'],
                ':data_id' => $data['data_id']
            ));
        }
        $this->_successResponse($flag);
    }
    /**
     * 保存ly_ticket
     */
    public function saveTicketAction(){
        $data = $this->request->getPost();
        $ticket_types = array('unknown','free','payed');
        if(empty($data['dest_id']) || !is_numeric($data['dest_id'])) $this->_errorResponse(10001, '请传入目的地ID且为正整数');
        if(empty($data['ticket_type']) || !in_array($data['ticket_type'],$ticket_types)) $this->_errorResponse(10003, '请传入正确的门票类型');
        $fields = array();
        $param = array();
        if(empty($data['ticket_id'])){//创建
            foreach($data as $field => $value){
                $fields[] = $field;
                $param[':'.$field] = $value;
            }
            $sql = 'INSERT INTO ly_ticket(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
            $flag = $this->destin->execute($sql,$param);
        }else{//更新
            $ticket_id = $data['ticket_id'];
            unset($data['ticket_id']);
            if(!is_numeric($ticket_id)) $this->_errorResponse(10004, '请传正确的ticket_id');
            foreach($data as $field => $value){
                $fields[] = '`'.$field.'` = :'.$field;
                $param[':'.$field] = $value;
            }
            $param[':ticket_id'] = $ticket_id;
            $sql = 'UPDATE ly_ticket SET '.implode(',',$fields).' WHERE ticket_id = :ticket_id';
            $flag = $this->destin->execute($sql,$param);
        }
        $this->_successResponse($flag);
    }

    /**
     * 获取推荐信息列表
     */
    public function getRecommendListAction()
    {
        $data = $this->request->get();
        $page = !empty($data['page']) && is_numeric($data['page']) ? $data['page'] : 1;
        $pageSize = !empty($data['pageSize']) && is_numeric($data['pageSize']) && $data['pageSize'] < 50 ? $data['pageSize'] : 10;
        $start = ($page - 1) * $pageSize;
        $dest_id = $data['dest_id'];
        $data['page_type'] = empty($data['page_type']) ? 'scenic' : $data['page_type'];
        switch($data['page_type']){
            case 'main_dest':
            case 'scenic':
            case 'restaurant':
            case 'shop':
            case 'playspot':
                $dest_type = $data['dest_type'];
                if (empty($dest_id) || !is_numeric($dest_id)) $this->_errorResponse(10001, '请传入目的地ID且为正整数');
                if (empty($dest_type)) $this->_errorResponse(10003, '类型不能为空');
                $sql = "SELECT s.*,d.dest_name,d.parent_name,d.parent_names FROM ly_scenic_viewspot AS s INNER JOIN ly_destination AS d ON s.viewspot_id=d.dest_id AND s.recommend_type='{$dest_type}' AND s.dest_id={$dest_id} ".(empty($data['object_id']) ? '' : ' AND `object_id` = '.$data['object_id'])." AND s.`status`=99 ORDER BY seq ASC LIMIT {$start},{$pageSize}";
                break;
            case 'food':
                $sql = 'SELECT r.*,f.food_name FROM ly_food_recommend AS r INNER JOIN ly_food AS f ON r.food_id = f.food_id AND r.dest_id = '.$dest_id.' ORDER BY r.seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'goods':
                $sql = 'SELECT r.*,g.goods_name FROM ly_goods_recommend AS r INNER JOIN ly_goods AS g ON r.goods_id = g.goods_id AND r.dest_id = '.$dest_id.' ORDER BY r.seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'play':
                $sql = 'SELECT play_type_id,type_name,`status`,dest_id,seq FROM ly_play_type WHERE dest_id = '.$dest_id.' ORDER BY seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'stay':
                $sql = 'SELECT * FROM ly_stay_type WHERE dest_id = '.$dest_id.' ORDER BY seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'stay_dest':
                $sql = 'SELECT s.*,d.dest_name,d.dest_type_name,d.parent_name FROM ly_stay_dest AS s INNER JOIN ly_destination AS d ON s.rel_dest_id = d.dest_id AND s.dest_id = '.$dest_id.' ORDER BY s.seq ASC,s.rel_id ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'substay':
                $sql = 'SELECT stay_id,stay_name,dest_id,stay_type_id,`status`,seq FROM ly_stay WHERE dest_id = '.$dest_id.' AND stay_type_id = '.$data['stay_type_id'].' ORDER BY seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'travel':
                $sql = 'SELECT travel_id,title,dest_id,status,seq,travel_days,cost,img_url FROM ly_travel WHERE dest_id = '.$dest_id.' ORDER BY seq ASC,travel_id ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'travel_day':
                if (empty($data['travel_id']) || !is_numeric($data['travel_id'])) $this->_errorResponse(10004, '请传入正确的travel_id');
                $sql = 'SELECT travel_day_id,travel_id,status,seq FROM ly_travel_day WHERE travel_id='.$data['travel_id'].' ORDER BY seq ASC LIMIT '.$start.','.$pageSize;
                break;
            case 'travel_day_dest':
                if (empty($data['travel_day_id']) || !is_numeric($data['travel_day_id'])) $this->_errorResponse(10005, '请传入正确的travel_day_id');
                $sql = 'SELECT t.travel_day_id,t.dest_id,d.dest_name FROM ly_travel_day_dest AS t INNER JOIN ly_destination AS d ON t.dest_id = d.dest_id AND t.travel_day_id = '.$data['travel_day_id'];
                break;
            case 'travel_dest':
                $sql = '';
                break;
        }
        $result = $this->destin->getRsBySql($sql);

        $this->_successResponse($result);
    }
    /**
     * 保存ly_must的值
     */
    public function mustSaveAction(){
        $post = $this->request->getPost();
        //自建或者编辑
        $post['action'] = isset($post['action']) ? $post['action'] : '';
        $fields = array();
        $values = array();
        $param = array();
        $return = array();
        switch($post['action']){
            case 'seq'://保存排序
                if(empty($post['seq']) || !is_array($post['seq'])){
                    $this->_errorResponse(10001, '请传入排序值');
                }
                foreach($post['seq'] as $must_id => $seq){
                    $param[] = array(':seq' => $seq,':must_id' => $must_id);
                }
                $sql = 'UPDATE ly_must SET `seq` = :seq WHERE `must_id` = :must_id';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'add'://自建添加
                foreach($post['info'] as $field => $value){
                    $fields[] = $field;
                    $values[':'.$field] = $value;
                }
                $sql = 'INSERT INTO ly_must(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$values);
                break;
            case 'edit'://编辑
                $must_id = $post['info']['must_id'];
                unset($post['info']['must_id']);
                $values[':must_id'] = $must_id;
                foreach($post['info'] as $field => $value){
                    $fields[] = '`'.$field.'` = :'.$field;
                    $values[':'.$field] = $value;
                }
                $sql = 'UPDATE ly_must SET '.implode(',',$fields).' WHERE `must_id` = :must_id';
                $return = $this->destin->execute($sql,$values);
                break;
            case 'delete':
                $param[':must_id'] = $post['must_id'];
                $sql = 'DELETE FROM ly_must WHERE must_id = :must_id';
                $return = $this->destin->execute($sql,$param);
                break;
            default:
                //暂不处理
        }
        $this->_successResponse($return);
    }
    /**
     * 搜索推荐
     */
    public function recommendSearchAction(){
        $post = $this->request->getPost();
        $page = empty($post['page']) || !is_numeric($post['page']) || $post['page'] < 1 ? 1 : intval($post['page']);
        $pageSize = empty($post['pageSize']) || !is_numeric($post['pageSize']) || $post['pageSize'] < 1 ? 10 : intval($post['pageSize']);
        $pageSize = $pageSize > 30 ? 30 : $pageSize;
        if(!in_array($post['page_type'],$this->not_search_parent)){
            $sql = 'SELECT parents FROM ly_destination WHERE dest_id = '.$post['dest_id'];
            $row = $this->destin->getRsBySql($sql,true);
            if(empty($row['parents'])) $this->_errorResponse(10001, 'not found parents by dest_id='.$post['dest_id'].'');
            $parents = $row['parents'];
        }
        switch($post['page_type']){
            case 'scenic':
                $where = "cancel_flag='Y' AND showed='Y'  AND (dest_type IN('VIEWSPOT','SCENIC' ) OR ent_sight='Y') AND (parents LIKE '{$parents},%')";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'food':
                $where = " AND (fd.dest_id = {$post['dest_id']} OR fd.`dest_parents` LIKE '{$parents},%')";
                if($post['food_status'] && is_numeric($post['food_status'])) $where .= ' AND f.status = '.$post['food_status'];
                if($post['food_id'] && is_numeric($post['food_id'])) $where .= ' AND f.food_id = '.$post['food_id'];
                if($post['food_name']) $where .= " AND f.food_name LIKE '%{$post['food_name']}%'";
                $row = $this->destin->getRsBySql('SELECT fd.id FROM ly_food_dest AS fd INNER JOIN ly_food AS f ON fd.food_id = f.food_id '.$where);
                $total = count($row);
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT fd.id,f.food_id,f.food_name,fd.dest_id,fd.dest_name,fd.food_seq,fd.food_status,fd.dest_type FROM ly_food_dest AS fd INNER JOIN ly_food AS f ON fd.food_id = f.food_id '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'restaurant':
                $where = "cancel_flag='Y' AND showed='Y' AND dest_type = 'RESTAURANT' AND (parents LIKE '{$parents},%' OR parent_id = {$post['dest_id']})";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'goods':
                $where = "(dest_id = {$post['dest_id']} OR `dest_parents` LIKE '{$parents},%')";
                if($post['goods_status'] && is_numeric($post['goods_status'])) $where .= ' AND goods_status = '.$post['goods_status'];
                if($post['goods_id'] && is_numeric($post['goods_id'])) $where .= ' AND goods_id = '.$post['goods_id'];
                if($post['goods_name']) $where .= " AND goods_name LIKE '%{$post['goods_name']}%'";
                $row = $this->destin->getRsBySql('SELECT id FROM ly_goods_dest WHERE '.$where.' GROUP BY goods_id');
                $total = count($row);
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT id,goods_id,goods_name,dest_id,dest_name,goods_seq,goods_status,dest_type FROM ly_goods_dest WHERE '.$where.' GROUP BY goods_id LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'shop':
                $where = "cancel_flag='Y' AND showed='Y' AND dest_type = 'SHOP' AND (parents LIKE '{$parents},%' OR parent_id = {$post['dest_id']})";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'play':
                $where = "cancel_flag='Y' AND showed='Y'  AND (dest_type = 'SCENIC_ENTERTAINMENT' OR (dest_type = 'VIEWSPOT' AND ent_sight='Y')) AND (parents LIKE '{$parents},%' OR parents = '{$parents}')";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'playspot':
                $where = "cancel_flag='Y' AND showed='Y'  AND (dest_type = 'SCENIC_ENTERTAINMENT' OR (dest_type = 'VIEWSPOT' AND ent_sight='Y')) AND (parents LIKE '{$parents},%')";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'hotel':
                $where = "cancel_flag='Y' AND showed='Y' AND dest_type = 'HOTEL' AND (parents LIKE '{$parents},%' OR parent_id = {$post['dest_id']})";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'stay_dest':
                $where = "cancel_flag='Y' AND showed='Y' AND stage = 1 AND (parents LIKE '{$parents},%' OR parent_id = {$post['dest_id']})";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['cancel_flag']) $where .= ' AND cancel_flag = \''.$post['cancel_flag'].'\'';
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
            case 'travel_dest':
            case 'food_dest':
            case 'goods_dest':
                $where = "cancel_flag='Y' AND showed='Y'";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                if($post['dest_type']) $where .= ' AND dest_type = \''.$post['dest_type'].'\'';
                if(is_numeric($post['parent_id'])) $where .= ' AND parent_id = '.$post['parent_id'];
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page-1) * $pageSize).','.$pageSize;
                break;
            case 'main_dest':
                $where = "cancel_flag='Y' AND showed='Y' AND (parents LIKE '{$parents},%' OR parent_id = {$post['dest_id']})";
                if($post['scenic_id'] && is_numeric($post['scenic_id'])) $where .= ' AND dest_id = '.$post['scenic_id'];
                if($post['scenic_name']) $where .= ' AND dest_name LIKE \'%'.$post['scenic_name'].'%\'';
                if ($post['stage'] && is_numeric($post['stage'])) {
                    $where .= ' AND stage = ' . $post['stage'];
                } else {
                    $where .= ' AND stage = 1';
                }
                $row = $this->destin->getRsBySql('SELECT COUNT(dest_id) AS c FROM ly_destination WHERE '.$where,true);
                $total = $row['c'];
                $totalPage = ceil($total / $pageSize);
                $page = $page > $totalPage ? $totalPage : $page;
                $sql = 'SELECT dest_id,dest_name,cancel_flag,showed,stage,dest_type,dest_type_name,parent_names,district_parent_names FROM ly_destination WHERE '.$where.' LIMIT '.(($page - 1) * $pageSize).','.$pageSize;
                break;
        }
        $return = array(
            'page' => $page,
            'current_page' => $page,
            'total_pages' => $totalPage,
            'list' => $this->destin->getRsBySql($sql)
        );
        $this->_successResponse($return);
    }
    public function recommendSaveAction(){
        $post = $this->request->getPost();
        $return = array();
        if(empty($post['dest_id']) || !is_numeric($post['dest_id'])) $this->_errorResponse(10001,'请传入正确的dest_id');
        $post['dest_id'] = intval($post['dest_id']);
        //根据推荐类型设置数量限制
        switch($post['recommend_type']){
            case 'MAIN_DEST':
                $limits = 10;
                break;
            default :
                $limits = 20;
                break;
        }
        switch($post['page_type']){
            case 'scenic':
            case 'shop':
            case 'playspot':
            case 'main_dest':
                if(empty($post['ids'])) $this->_errorResponse(10002,'请传入需要推荐的ID');
                if(empty($post['recommend_type'])) $this->_errorResponse(10003,'请传入需要推荐类型');
                if(empty($post['object_id'])) $post['object_id'] = 0;
                $ids = array();
                foreach(explode(',',$post['ids']) as $id){
                    if(is_numeric($id)){
                        $ids[] = $id;
                    }
                }
                if(empty($ids)) $this->_errorResponse(10004,'请传入正确推荐ID');
                //先查询有哪些是已经添加的
                $sql = 'SELECT viewspot_id FROM ly_scenic_viewspot WHERE dest_id = '.$post['dest_id'].' AND recommend_type = \''.$post['recommend_type'].'\' AND object_id = '.$post['object_id'].' AND `status`=99';
                $tmp = $this->destin->getRsBySql($sql);
                $exists_ids = array();
                if(!empty($tmp)){
                    foreach($tmp as $row){
                        $exists_ids[] = $row['viewspot_id'];
                    }
                }
                $ids = array_diff($ids,$exists_ids);
                if(count($tmp) + count($ids) > $limits){
                    $this->_errorResponse(10005,'超过最大推荐量,还可以推荐'.($limits-count($tmp)).'条!');
                }
                if(empty($ids)) $this->_successResponse(array('error' => 0, 'result' => true));
                $sql = 'SELECT dest_id,dest_type FROM ly_destination WHERE dest_id IN('.implode(',',$ids).')';
                $tmp = $this->destin->getRsBySql($sql);
                $dest_id_types = UCommon::parseItem($tmp,'dest_id');
                $fields = array('status','dest_id','seq','recommend_type','object_id','viewspot_id','dest_type');
                $param = array();
                foreach($ids as $id){
                    $param[] = array(
                        ':status' => 99,
                        ':dest_id' => $post['dest_id'],
                        ':seq' => 0,
                        ':recommend_type' => $post['recommend_type'],
                        ':object_id' => $post['object_id'],
                        ':viewspot_id' => $id,
                        ':dest_type' => $dest_id_types[$id]['dest_type']
                    );
                }
                $sql = 'INSERT INTO ly_scenic_viewspot(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'food':
                if(empty($post['ids'])) $this->_errorResponse(10002,'请选择要推荐的美食');
                $ids = array();
                foreach(explode(',',$post['ids']) as $id){
                    if(is_numeric($id)){
                        $ids[] = $id;
                    }
                }
                if(empty($ids)) $this->_errorResponse(10004,'请正确选择要推荐的美食');
                //先查询有哪些是已经添加的
                $sql = 'SELECT food_id FROM ly_food_recommend WHERE dest_id = '.$post['dest_id'].' AND `status`=99';
                $tmp = $this->destin->getRsBySql($sql);
                $exists_ids = array();
                if(!empty($tmp)){
                    foreach($tmp as $row){
                        $exists_ids[] = $row['food_id'];
                    }
                }
                $ids = array_diff($ids,$exists_ids);
                if(count($tmp) + count($ids) > $limits){
                    $this->_errorResponse(10005,'超过最大推荐量,还可以推荐'.($limits-count($tmp)).'条!');
                }
                if(empty($ids)) $this->_successResponse(array('error' => 0, 'result' => true));

                $fields = array('dest_id','seq','food_id','status');
                $param = array();
                foreach($ids as $id){
                    $param[] = array(
                        ':dest_id' => $post['dest_id'],
                        ':seq' => 0,
                        ':food_id' => $id,
                        ':status' => 99
                    );
                }
                $sql = 'INSERT INTO ly_food_recommend(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'restaurant':
                if(empty($post['ids'])) $this->_errorResponse(10002,'请传入需要推荐的餐厅ID');
                if(empty($post['recommend_type'])) $this->_errorResponse(10003,'请传入需要推荐类型');
                if(empty($post['object_id'])) $post['object_id'] = 0;
                $ids = array();
                foreach(explode(',',$post['ids']) as $id){
                    if(is_numeric($id)){
                        $ids[] = $id;
                    }
                }
                if(empty($ids)) $this->_errorResponse(10004,'请传入正确推荐餐厅ID');
                //先查询有哪些是已经添加的
                $sql = 'SELECT viewspot_id FROM ly_scenic_viewspot WHERE dest_id = '.$post['dest_id'].' AND recommend_type = \''.$post['recommend_type'].'\' AND `status`=99';
                $tmp = $this->destin->getRsBySql($sql);
                $exists_ids = array();
                if(!empty($tmp)){
                    foreach($tmp as $row){
                        $exists_ids[] = $row['viewspot_id'];
                    }
                }
                $ids = array_diff($ids,$exists_ids);
                if(count($tmp) + count($ids) > $limits){
                    $this->_errorResponse(10005,'超过最大推荐量,还可以推荐'.($limits-count($tmp)).'条!');
                }
                if(empty($ids)) $this->_successResponse(array('error' => 0, 'result' => true));
                $sql = 'SELECT dest_id,dest_type FROM ly_destination WHERE dest_id IN('.implode(',',$ids).')';
                $tmp = $this->destin->getRsBySql($sql);
                $dest_id_types = UCommon::parseItem($tmp,'dest_id');
                $fields = array('status','dest_id','seq','recommend_type','object_id','viewspot_id','dest_type');
                $param = array();
                foreach($ids as $id){
                    $param[] = array(
                        ':status' => 99,
                        ':dest_id' => $post['dest_id'],
                        ':seq' => 0,
                        ':recommend_type' => $post['recommend_type'],
                        ':object_id' => $post['object_id'],
                        ':viewspot_id' => $id,
                        ':dest_type' => $dest_id_types[$id]['dest_type']
                    );
                }
                $sql = 'INSERT INTO ly_scenic_viewspot(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'goods':
                if(empty($post['ids'])) $this->_errorResponse(10002,'请选择要推荐的商品');
                $ids = array();
                foreach(explode(',',$post['ids']) as $id){
                    if(is_numeric($id)){
                        $ids[] = $id;
                    }
                }
                if(empty($ids)) $this->_errorResponse(10004,'请正确选择要推荐的商品');
                //先查询有哪些是已经添加的
                $sql = 'SELECT goods_id FROM ly_goods_recommend WHERE dest_id = '.$post['dest_id'].' AND `status`=99';
                $tmp = $this->destin->getRsBySql($sql);
                $exists_ids = array();
                if(!empty($tmp)){
                    foreach($tmp as $row){
                        $exists_ids[] = $row['goods_id'];
                    }
                }
                $ids = array_diff($ids,$exists_ids);
                if(count($tmp) + count($ids) > $limits){
                    $this->_errorResponse(10005,'超过最大推荐量,还可以推荐'.($limits-count($tmp)).'条!');
                }
                if(empty($ids)) $this->_successResponse(array('error' => 0, 'result' => true));

                $fields = array('dest_id','seq','goods_id','status');
                $param = array();
                foreach($ids as $id){
                    $param[] = array(
                        ':dest_id' => $post['dest_id'],
                        ':seq' => 0,
                        ':goods_id' => $id,
                        ':status' => 99
                    );
                }
                $sql = 'INSERT INTO ly_goods_recommend(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'stay_dest':
                if(empty($post['ids'])) $this->_errorResponse(10002,'请选择要推荐的目的地');
                $ids = array();
                foreach(explode(',',$post['ids']) as $id){
                    if(is_numeric($id)){
                        $ids[] = $id;
                    }
                }
                if(empty($ids)) $this->_errorResponse(10004,'请正确选择要推荐的目的地');
                //先查询有哪些是已经添加的
                $sql = 'SELECT rel_dest_id FROM ly_stay_dest WHERE dest_id = '.$post['dest_id'].' AND `status`=99';
                $tmp = $this->destin->getRsBySql($sql);
                $exists_ids = array();
                if(!empty($tmp)){
                    foreach($tmp as $row){
                        $exists_ids[] = $row['rel_dest_id'];
                    }
                }
                $ids = array_diff($ids,$exists_ids);
                if(count($tmp) + count($ids) > $limits){
                    $this->_errorResponse(10005,'超过最大推荐量,还可以推荐'.($limits-count($tmp)).'条!');
                }
                if(empty($ids)) $this->_successResponse(array('error' => 0, 'result' => true));

                $fields = array('dest_id','seq','rel_dest_id','status');
                $param = array();
                foreach($ids as $id){
                    $param[] = array(
                        ':dest_id' => $post['dest_id'],
                        ':seq' => 0,
                        ':rel_dest_id' => $id,
                        ':status' => 99
                    );
                }
                $sql = 'INSERT INTO ly_stay_dest(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $return = $this->destin->execute($sql,$param,true);
                break;
            case 'stay':
                break;
            case 'substay':
                break;
            case 'hotel':
                break;
        }
        $this->_successResponse($return);
    }
    /**
     * 保存推荐住宿子维度的信息
     */
    public function saveStayAction(){
        $post = $this->request->getPost();
        if(empty($post['dest_id']) || !is_numeric($post['dest_id'])) $this->_errorResponse(10001,'请传入正确的dest_id');
        if(empty($post['stay_type_id']) || !is_numeric($post['stay_type_id'])) $this->_errorResponse(10002,'请传入正确的stay_type_id');
        if(empty($post['memo'])) $post['status'] = 1;
        $ids = array();
        if(!empty($post['ids'])){
            foreach($post['ids'] as $row){
                if(is_numeric($row)){
                    $ids[] = $row;
                }
            }
        }
        $fields = array('stay_name','dest_id','stay_type_id','memo','status');
        $param = array();
        $value = array();
        foreach($fields as $field){
            $param[':'.$field] = empty($post[$field]) ? '' : $post[$field];
            $value[] = '`'.$field.'` = :'.$field;
        }
        try{
            $this->destin->beginTransaction();
            if(empty($post['stay_id'])){
                $sql = 'INSERT INTO ly_stay(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $this->destin->execute($sql,$param);
                $post['stay_id'] = $this->destin->lastInsertId();
            }else{
                $param[':stay_id'] = $post['stay_id'];
                $sql = 'UPDATE ly_stay SET '.implode(',',$value).' WHERE stay_id = :stay_id';
                $this->destin->execute($sql,$param);
            }
            if($ids){
                $sql = 'DELETE FROM ly_stay_hotel WHERE dest_id = :dest_id AND stay_id = :stay_id';
                $this->destin->execute($sql,array(':dest_id' => $post['dest_id'],':stay_id' => $post['stay_id']));
                $param = array();
                foreach($ids as $hotel_id){
                    $param[] = array(
                        ':stay_id' => $post['stay_id'],
                        ':hotel_id' => $hotel_id,
                        ':dest_id' => $post['dest_id']
                    );
                }
                $sql = 'INSERT INTO ly_stay_hotel(stay_id,hotel_id,dest_id) VALUES(:stay_id,:hotel_id,:dest_id)';
                $this->destin->execute($sql,$param,true);
            }
            $this->destin->commit();
            $this->_successResponse(true);
        }catch (\Exception $e){
            $this->destin->rollBack();
            $this->_errorResponse($e->getCode(),$e->getMessage());
        }
    }
    public function saveTravelAction(){
        $post = $this->request->getPost();
        if(empty($post['dest_id']) || !is_numeric($post['dest_id'])) $this->_errorResponse(10001,'请传入正确的dest_id');
        if(empty($post['travel_id']) || !is_numeric($post['travel_id'])) $post['travel_id'] = 0;
        if(empty($post['memo'])) $post['status'] = 1;
        $fields = array('title','dest_id','travel_days','memo','status','cost','img_url');
        $param = array();
        $value = array();
        foreach($fields as $field){
            $param[':'.$field] = empty($post[$field]) ? '' : $post[$field];
            $value[] = '`'.$field.'` = :'.$field;
        }
        try{
            $this->destin->beginTransaction();
            if(empty($post['travel_id'])){
                $sql = 'INSERT INTO ly_travel(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $this->destin->execute($sql,$param);
                $post['travel_id'] = $this->destin->lastInsertId();
            }else{
                $param[':travel_id'] = $post['travel_id'];
                $sql = 'UPDATE ly_travel SET '.implode(',',$value).' WHERE travel_id = :travel_id';
                $this->destin->execute($sql,$param);
            }
            if($post['subject_id']){
                $sql = 'DELETE FROM mo_subject_relation WHERE channel = :channel AND object_type = :object_type AND object_id = :object_id';
                $this->destin->execute($sql,array(
                    ':channel' => 'lvyou',
                    ':object_type' => 'TRAVEL',
                    ':object_id' => $post['travel_id']
                ));
                $param = array();
                foreach($post['subject_id'] as $subject_id => $isMain){
                    $param[] = array(
                        ':subject_id' => $subject_id,
                        ':subject_name' => $post['subject_name'][$subject_id],
                        ':subject_pinyin' => $post['subject_pinyin'][$subject_id],
                        ':channel' => 'lvyou',
                        ':object_type' => 'TRAVEL',
                        ':object_id' => $post['travel_id'],
                        ':main' => $isMain
                    );
                }
                $sql = 'INSERT INTO mo_subject_relation(subject_id,subject_name,subject_pinyin,channel,object_type,object_id,main) VALUES(:subject_id,:subject_name,:subject_pinyin,:channel,:object_type,:object_id,:main)';
                $this->destin->execute($sql,$param,true);
            }
            $this->destin->commit();
            $this->_successResponse($post['travel_id']);
        }catch (\Exception $e){
            $this->destin->rollBack();
            $this->_errorResponse($e->getCode(),$e->getMessage());
        }
    }

    /**
     * 保存行程每天详情信息
     */
    public function saveTravelDayAction(){
        $post = $this->request->getPost();
        if(empty($post['info'])) $this->_errorResponse(10001,'请传入正确的每日详情信息!');
        $info = $post['info'];
        if(empty($info['travel_id']) || !is_numeric($info['travel_id'])) $this->_errorResponse(10002,'请传入travel_id且为正整数!');
        if(empty($info['seq']) || !is_numeric($info['seq'])) $this->_errorResponse(10003,'请传入正确的天数');
        $ids  = empty($post['ids']) ? array() : $post['ids'];
        try{
            $this->destin->beginTransaction();
            $fields = array();
            $values = array();
            if(empty($info['travel_day_id']) || !is_numeric($info['travel_day_id'])){//新增
                foreach($info as $key => $val){
                    $fields[] = $key;
                    $values[':'.$key] = $val;
                }
                $sql = 'INSERT INTO ly_travel_day(`'.implode('`,`',$fields).'`) VALUES(:'.implode(',:',$fields).')';
                $this->destin->execute($sql,$values);
                $id = $this->destin->lastInsertId();
            }else{//修改
                $id = $info['travel_day_id'];
                unset($info['travel_day_id']);
                foreach($info as $key => $val){
                    $fields[] = '`'.$key.'` = :'.$key;
                    $values[':'.$key] = $val;
                }
                $values[':travel_day_id'] = $id;
                $sql = 'UPDATE ly_travel_day SET '.implode(',',$fields).' WHERE travel_day_id = :travel_day_id';
                $this->destin->execute($sql,$values);
                $sql = 'DELETE FROM ly_travel_day_dest WHERE travel_day_id = :travel_day_id';
                $this->destin->execute($sql,array(':travel_day_id' => $id));
            }
            $values = array();
            foreach($ids as $dest_id){
                $values[] = array(':travel_day_id' => $id,':dest_id' => $dest_id);
            }
            $sql = 'INSERT INTO ly_travel_day_dest(`travel_day_id`,`dest_id`) VALUES(:travel_day_id,:dest_id)';
            $this->destin->execute($sql,$values,true);
            $this->destin->commit();
            $this->_successResponse(true);
        }catch (\Exception $e){
            $this->_errorResponse($e->getCode(),$e->getMessage());
            $this->destin->rollBack();
        }
    }
}