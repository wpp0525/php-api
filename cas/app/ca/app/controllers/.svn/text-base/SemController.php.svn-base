<?php

/**
 * sem数据统计控制器
 *
 * @author flash.guo
 *
 */
class SemController extends ControllerBase
{
    private $sem_account_svc;
    private $sem_campaign_svc;
    private $sem_adgroup_svc;
    private $sem_keyword_svc;
    private $sem_creative_svc;
    private $sem_monitor_service;
    private $sem_report_svc;
    /**
     * @var Lvmama\Cas\Service\SemOrderDataService
     */
    private $sem_order_svc;
    public function initialize()
    {
        parent::initialize();
        $this->sem_account_svc  = $this->di->get('cas')->get('sem_account_service');
        $this->sem_campaign_svc = $this->di->get('cas')->get('sem_campaign_service');
        $this->sem_adgroup_svc  = $this->di->get('cas')->get('sem_adgroup_service');
        $this->sem_keyword_svc  = $this->di->get('cas')->get('sem_keyword_service');
        $this->sem_creative_svc = $this->di->get('cas')->get('sem_creative_service');
        $this->sem_monitor_svc  = $this->di->get('cas')->get('sem_monitor_service');
        $this->sem_report_svc   = $this->di->get('cas')->get('sem_report_service');
        $this->temp_subject     = $this->di->get('cas')->get('temp_subject');
        $this->sem_budget       = $this->di->get('cas')->get('sem_budget_service');
    }

    /**
     * 推广账户详情
     */
    public function infoAccountAction()
    {
        $id                                  = intval($this->request->get('id'));
        $conditions                          = array();
        !empty($id) && $conditions['userId'] = "=" . $id;
        !empty($conditions) && $account_info = $this->sem_account_svc->getOneAccount($conditions);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广账户不存在');
            return;
        }
        $this->jsonResponse(array('results' => $account_info));
    }

    /**
     * 推广账户列表
     */
    public function listAccountAction()
    {
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns      = trim($this->request->get('columns'));
        $order        = trim($this->request->get('order'));
        $condition    = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order        = $order ? $order : null;
        $account_info = $this->sem_account_svc->getAccountList($condition, $limit, $columns, $order);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广账户不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $account_info));
            return;
        }
        $total_records = $this->sem_account_svc->getAccountTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $account_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广账户新增
     */
    public function addAccountAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_account_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广账户新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广账户更新
     */
    public function updateAccountAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_account_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广账户更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广账户删除
     */
    public function deleteAccountAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_account_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广账户删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广计划详情
     */
    public function infoCampaignAction()
    {
        $id                                      = intval($this->request->get('id'));
        $conditions                              = array();
        !empty($id) && $conditions['campaignId'] = "=" . $id;
        !empty($conditions) && $account_info     = $this->sem_campaign_svc->getOneCampaign($conditions);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广计划不存在');
            return;
        }
        $this->jsonResponse(array('results' => $account_info));
    }

    /**
     * 推广计划列表
     */
    public function listCampaignAction()
    {
        $condition     = $this->request->get('condition');
        $page_size     = intval($this->request->get('page_size'));
        $current_page  = intval($this->request->get('current_page'));
        $columns       = trim($this->request->get('columns'));
        $order         = trim($this->request->get('order'));
        $condition     = json_decode($condition, true);
        $current_page  = $current_page ? $current_page : 1;
        $page_size     = $page_size ? $page_size : 10;
        $limit         = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order         = $order ? $order : null;
        $campaign_info = $this->sem_campaign_svc->getCampaignList($condition, $limit, $columns, $order);
        if (empty($campaign_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广计划不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $campaign_info));
            return;
        }
        $total_records = $this->sem_campaign_svc->getCampaignTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $campaign_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广计划新增
     */
    public function addCampaignAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_campaign_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广计划新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广计划更新
     */
    public function updateCampaignAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_campaign_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广计划更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广计划删除
     */
    public function deleteCampaignAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_campaign_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广计划删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广单元详情
     */
    public function infoAdgroupAction()
    {
        $id                                     = intval($this->request->get('id'));
        $conditions                             = array();
        !empty($id) && $conditions['adgroupId'] = "=" . $id;
        !empty($conditions) && $account_info    = $this->sem_adgroup_svc->getOneAdgroup($conditions);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广单元不存在');
            return;
        }
        $this->jsonResponse(array('results' => $account_info));
    }

    /**
     * 推广单元列表
     */
    public function listAdgroupAction()
    {
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns      = trim($this->request->get('columns'));
        $order        = trim($this->request->get('order'));
        $condition    = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order        = $order ? $order : null;
        $adgroup_info = $this->sem_adgroup_svc->getAdgroupList($condition, $limit, $columns, $order);
        if (empty($adgroup_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广单元不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $adgroup_info));
            return;
        }
        $total_records = $this->sem_adgroup_svc->getAdgroupTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $adgroup_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广单元新增
     */
    public function addAdgroupAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_adgroup_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广单元新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广单元更新
     */
    public function updateAdgroupAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_adgroup_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广单元更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广单元删除
     */
    public function deleteAdgroupAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_adgroup_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广单元删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广关键词详情
     */
    public function infoKeywordAction()
    {
        $id                                     = intval($this->request->get('id'));
        $conditions                             = array();
        !empty($id) && $conditions['keywordId'] = "=" . $id;
        !empty($conditions) && $account_info    = $this->sem_keyword_svc->getOneKeyword($conditions);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广关键词不存在');
            return;
        }
        $this->jsonResponse(array('results' => $account_info));
    }

    /**
     * 推广关键词列表
     */
    public function listKeywordAction()
    {
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns      = trim($this->request->get('columns'));
        $order        = trim($this->request->get('order'));
        $condition    = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order        = $order ? $order : null;
        $keyword_info = $this->sem_keyword_svc->getKeywordList($condition, $limit, $columns, $order);
        if (empty($keyword_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广关键词不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $keyword_info));
            return;
        }
        $total_records = $this->sem_keyword_svc->getKeywordTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $keyword_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广关键词新增
     */
    public function addKeywordAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_keyword_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广关键词新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广关键词更新
     */
    public function updateKeywordAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_keyword_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广关键词更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广关键词删除
     */
    public function deleteKeywordAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_keyword_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广关键词删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广创意详情
     */
    public function infoCreativeAction()
    {
        $id                                      = intval($this->request->get('id'));
        $conditions                              = array();
        !empty($id) && $conditions['creativeId'] = "=" . $id;
        !empty($conditions) && $account_info     = $this->sem_creative_svc->getOneCreative($conditions);
        if (empty($account_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广创意不存在');
            return;
        }
        $this->jsonResponse(array('results' => $account_info));
    }

    /**
     * 推广创意列表
     */
    public function listCreativeAction()
    {
        $condition     = $this->request->get('condition');
        $page_size     = intval($this->request->get('page_size'));
        $current_page  = intval($this->request->get('current_page'));
        $columns       = trim($this->request->get('columns'));
        $order         = trim($this->request->get('order'));
        $condition     = json_decode($condition, true);
        $current_page  = $current_page ? $current_page : 1;
        $page_size     = $page_size ? $page_size : 10;
        $limit         = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order         = $order ? $order : null;
        $creative_info = $this->sem_creative_svc->getCreativeList($condition, $limit, $columns, $order);
        if (empty($creative_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广创意不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $creative_info));
            return;
        }
        $total_records = $this->sem_creative_svc->getCreativeTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $creative_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广创意新增
     */
    public function addCreativeAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_creative_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广创意新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广创意更新
     */
    public function updateCreativeAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_creative_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广创意更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广创意删除
     */
    public function deleteCreativeAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_creative_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广创意删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    public function listOrderLoscReportAction()
    {
        $losc_id    = $this->request->get('losc_id');
        $order_id   = $this->request->get('order_id');
        $start_time = $this->request->get('start_time');
        $end_time   = $this->request->get('end_time');
        $type       = $this->request->get('type');
        $page_num   = $this->request->get('current_page') ? $this->request->get('current_page') : 1;
        $page_size  = $this->request->get('page_size') ? $this->request->get('page_size') : 20;

        $this->sem_order_svc = $this->di->get('cas')->get('sem_order_service');

        $condition = array();
        if ($losc_id) {
            $condition['LOSC_ID IN'] = "(" . $losc_id . ")";
        }
        if ($order_id) {
            $condition['ORDER_ID IN'] = "(" . $order_id . ")";
        }
        if ($start_time) {
            $condition['PAYMENT_TIME >='] = "'" . $start_time . "'";
        }
        if ($end_time) {
            $condition['PAYMENT_TIME <='] = "'" . $end_time . "'";
        }
        if ($type) {
            $condition['TYPE ='] = "'" . $type . "'";
        }
        $total_records = $this->sem_order_svc->getOrderLoscReportTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $limit         = array('page_num' => $page_num, 'page_size' => $page_size);
        $result        = $this->sem_order_svc->getOrderLoscReport($condition, $limit);

        $this->jsonResponse(array('result' => $result, 'total_records' => intval($total_records), 'page_index' => $page_num, 'total_pages' => $total_pages));
    }

    /**
     * 推广监控详情
     */
    public function infoMonitorAction()
    {
        $id                                  = intval($this->request->get('id'));
        $conditions                          = array();
        !empty($id) && $conditions['id']     = "=" . $id;
        !empty($conditions) && $monitor_info = $this->sem_monitor_svc->getOneMonitor($conditions);
        if (empty($monitor_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广监控不存在');
            return;
        }
        $this->jsonResponse(array('results' => $monitor_info));
    }

    /**
     * 推广监控列表
     */
    public function listMonitorAction()
    {
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns      = trim($this->request->get('columns'));
        $order        = trim($this->request->get('order'));
        $condition    = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order        = $order ? $order : null;
        $monitor_info = $this->sem_monitor_svc->getMonitorList($condition, $limit, $columns, $order);
        if (empty($monitor_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广监控不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $monitor_info));
            return;
        }
        $total_records = $this->sem_monitor_svc->getMonitorTotal($condition);
        $total_pages   = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array('results' => $monitor_info, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 推广监控新增
     */
    public function addMonitorAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['createTime'] = $post['updateTime'] = time();
            $result             = $this->sem_monitor_svc->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广监控新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广监控更新
     */
    public function updateMonitorAction()
    {
        $post = $this->request->getPost();
        $id   = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['updateTime'] = time();
            $result             = $this->sem_monitor_svc->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广监控更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广监控删除
     */
    public function deleteMonitorAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->sem_monitor_svc->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '推广监控删除失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 推广报告修复，清除重复数据
     */
    public function repairReportAction()
    {
        $platform     = $this->request->get('platform');
        $condition    = $this->request->get('condition');
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $group        = trim($this->request->get('group'));
        $sort         = trim($this->request->get('sort'));
        $interval     = trim($this->request->get('interval')); //统计时间分段(day/week/month)
        $delete       = intval($this->request->get('delete')); //是否删除重复数据
        $platform     = $platform ? $platform : 1; //所属平台 默认为1百度
        $condition    = json_decode($condition, true);
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 10000);
        $sort         = json_decode($sort, true);

        if (empty($group) || empty($sort) || !isset($terms['device']) || empty($terms['unitOfTime'])) {
            $this->_errorResponse(DATA_NOT_FOUND, '请设置分组、排序、设备及时间单位等必要条件');
            return;
        }
        if (empty($condition['startDate']) || empty($condition['endDate']) || strtotime($condition['endDate']) - strtotime($condition['startDate']) > 86400) {
            $this->_errorResponse(DATA_NOT_FOUND, '请设置起始日期，并且相隔一天');
            return;
        }

        $condition             = $condition ? $condition : array();
        $terms                 = $terms ? $terms : array();
        $condition['platform'] = "=" . $platform;
        if (isset($condition['startDate'])) {
            $range['date']['gte'] = strtotime($condition['startDate']) * 1000;
            unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
            $range['date']['lt'] = strtotime($condition['endDate']) * 1000;
            unset($condition['endDate']);
        }

        $report_list = $this->sem_report_svc->getReportList($terms, $range, $limit, $group, $sort, $interval);
        if (empty($report_list)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
            return;
        }
        $count = 0;
        header("Content-type: text/html; charset=utf-8");
        foreach ($report_list['list'] as $report) {
            if ($report['doc_count'] == 1) {
                break;
            }

            echo json_encode($report) . "<br>\n";
            $terms['keywordId'] = $report['keywordId'];
            $keyword_list       = $this->sem_report_svc->getReportList($terms, $range, $limit, null, null, null);
            foreach ($keyword_list['list'] as $key => $keyword) {
                if (!$key) {
                    continue;
                }

                if (!$delete) {
                    echo $keyword['_id'] . "<br>\n";
                } else {
                    $res = $this->sem_report_svc->delReport($keyword['_id']);
                    if (isset($res['found']) && $res['found'] == true) {
                        echo $keyword['_id'] . "删除成功<br>\n";
                    } else {
                        echo $keyword['_id'] . "删除失败<br>\n";
                    }
                }
            }
            $count++;
        }
        echo "共处理重复记录" . $count . "条\n\r";
    }

    /**
     * 推广报告列表
     */
    public function listReportAction()
    {
        $platform     = $this->request->get('platform');
        $condition    = $this->request->get('condition');
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns      = trim($this->request->get('columns'));
        $order        = trim($this->request->get('order'));
        $group        = trim($this->request->get('group'));
        $sort         = trim($this->request->get('sort'));
        $interval     = trim($this->request->get('interval')); //统计时间分段(day/week/month)
        $have_report  = intval($this->request->get('have_report'));
        $nocache      = intval($this->request->get('nocache')); //用于强制刷新缓存
        $platform     = $platform ? $platform : 1; //所属平台 默认为1百度
        $condition    = json_decode($condition, true);
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 10000);
        $order        = $order ? $order : null;
        $sort         = json_decode($sort, true);
        $order_column = trim(str_replace(array("asc", "desc"), "", strval($order)));

        $range      = $export_msg      = array();
        $condition  = $condition ? $condition : array();
        $terms      = $terms ? $terms : array();
        $export_msg = array_merge($condition, $terms);
        if (isset($condition['startDate'])) {
            $range['date']['gte'] = strtotime($condition['startDate']) * 1000;
            unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
            $range['date']['lt'] = strtotime($condition['endDate']) * 1000;
            unset($condition['endDate']);
        }
        isset($terms['date']) && $terms['date'] = strtotime($terms['date']) * 1000;

        //异步导出
        $xls_name = trim($this->request->get('xls_name'));
        if (!empty($xls_name)) {
            $report_list = $this->sem_report_svc->getHiveReport($terms, $range, array('page_num' => 1, 'page_size' => 1), $group, $sort, $interval, $platform, $nocache);
            if (empty($report_list['list'])) {
                $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
                return;
            }
            $export_cache               = array();
            $export_cache['xlsName']    = $xls_name;
            $export_cache['createTime'] = date("Y-m-d H:i:s");
            $export_cache['status']     = 0;
            $this->di->get('cas')->getRedis()->setex(
                "sem-export:" . $xls_name,
                86400 * 7,
                json_encode($export_cache)
            );
            $export_msg['platform'] = $platform;
            $export_msg['group']    = $group;
            $export_msg['interval'] = $interval;
            $export_msg['xlsName']  = $xls_name;
            $kafka                  = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['stormExport']);
            $kafka->sendMsg(json_encode($export_msg));
            $this->jsonResponse(array('results' => $export_msg));
            return;
        }

        $order_columns = array("impression", "click", "cost", "orderNum", "amount", "ctr", "cpc", "rate", "roi", "dateTime");
        if (!$have_report && ($group == 'adgroupName' || ($group != 'date_histogram' && $interval) || in_array($order_column, $order_columns))) {
            $this->_errorResponse(DATA_NOT_FOUND, '你当前设置的查询条件需要将have_report值设置为1');
            return;
        }

        if ($group) {
            if (($group == 'userName' || $group == 'campaignName' || $group == 'adgroupName' || $group == 'losc' || $group == 'keywordId') && isset($_REQUEST['current_page'])) {
                if ($have_report) {
//走presto
                    $table              = isset($export_msg['startDate']) && $export_msg['startDate'] == date("Y-m-d") ? "sem_realtime_report" : "sem_report";
                    $table              = $platform == 4 ? "hive.sogou." . $table : ($platform == 3 ? "hive.smcn." . $table : ($platform == 2 ? "hive.socom." . $table : "hive.default." . $table)); //所属平台（1：百度 2：360 3：神马 4：搜狗）
                    $limit['page_size'] = $limit['page_size'] > 100 ? 100 : $limit['page_size'];
                    $where              = "1 = 1";
                    $where .= isset($export_msg['startDate']) ? " and dateTime >= timestamp '" . $export_msg['startDate'] . "'" : "";
                    $where .= isset($export_msg['endDate']) ? " and dateTime < timestamp '" . $export_msg['endDate'] . "'" : "";
                    foreach ($terms as $key => $term) {
                        if (is_array($term)) {
                            $where .= " and " . $key . " in(" . (in_array($key, array("device", "unitOfTime", "keywordId")) ? implode(",", $term) : "'" . implode("','", $term) . "'") . ")";
                        } else {
                            $where .= " and " . $key . " = " . (in_array($key, array("device", "unitOfTime", "keywordId")) ? $term : "'" . $term . "'");
                        }
                    }
                    $orderby = $order ? "order by " . $order . "," . $group . " asc" : "order by " . $group . " asc";
                    $groups  = array("userName", "campaignName", "adgroupName", "losc", "keyword", "keywordId");
                    array_search($group, $groups) !== false && array_splice($groups, array_search($group, $groups) + 1);
                    $groups                     = implode(",", $groups);
                    $group == "losc" && $groups = $group;
                    switch ($group) {
                        case 'keywordId': //关键词不需要对losc去重
                            if ($interval) {
                                $sum = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount,";
                                $sum .= "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as ctr,";
                                $sum .= "(case when sum(click) = 0 then 0 else sum(cost)*10000/sum(click) end) as cpc,";
                                $sum .= "(case when sum(click) = 0 then 0 else sum(orderNum)*10000/sum(click) end) as rate,";
                                $sum .= "(case when sum(cost) = 0 then 0 else sum(amount)*10000/sum(cost) end) as roi";
                                $sum      = "keywordId," . $sum;
                                $field    = "r.impression,r.click,r.cost,r.orderNum,r.amount,r.ctr,r.cpc,r.rate,r.roi,r2." . str_replace(",", ",r2.", $groups);
                                $subfield = "impression,click,cost,orderNum,amount,ctr,cpc,rate,roi,keywordId";
                                switch ($interval) {
                                    case "day": //关键词分日报告
                                        $sum      = $interval . "(dateTime) as " . $interval . ",dateTime," . $sum;
                                        $field    = $field . "," . $interval . ",r.dateTime";
                                        $subfield = $subfield . "," . $interval . ",dateTime";
                                        $groupby  = "dateTime," . $group;
                                        break;
                                    case "week":
                                    case "month":
                                        $sum      = $interval . "(dateTime) as " . $interval . ",min(dateTime) as dateTime," . $sum;
                                        $field    = $field . ",r." . $interval . ",r.dateTime";
                                        $subfield = $subfield . "," . $interval . ",dateTime";
                                        $groupby  = $interval . "(dateTime)," . $group;
                                        break;
                                    default:;
                                }
                                $sql = "select " . $subfield . " FROM (select " . $subfield . ",row_number() OVER (" . $orderby . ") AS rownum FROM ";
                                $sql .= "(select " . $sum . " FROM " . $table . " where " . $where . " group by " . $groupby . ") AS sub) AS result ";
                                $sql .= " where rownum > " . ($limit['page_num'] - 1) * $limit['page_size'] . " and rownum <= " . $limit['page_num'] * $limit['page_size'];
                                $join = "(select a.userName,a.campaignName,a.adgroupName,a.losc,a.keyword,a.keywordId from " . $table . " a right join ";
                                $join .= "(select keywordId,max(dateTime) as dateTime from " . $table . " where " . $where . " group by keywordId) b ";
                                $join .= "on b.keywordId = a.keywordId and a.dateTime = b.dateTime where " . str_replace(" and ", " and a.", $where) . ")"; //注意关键词从属关系会变动，取最新一天的数据
                                $sql = "select " . $field . " FROM (" . $sql . ") r left join " . $join . " r2 on r2.keywordId = r.keywordId";
                            } else {
                                switch ($order_column) {
                                    case "ctr":$sum = "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as " . $order_column;
                                        break;
                                    case "cpc":$sum = "(case when sum(click) = 0 then 0 else sum(cost)*10000/sum(click) end) as " . $order_column;
                                        break;
                                    case "rate":$sum = "(case when sum(click) = 0 then 0 else sum(orderNum)*10000/sum(click) end) as " . $order_column;
                                        break;
                                    case "roi":$sum = "(case when sum(cost) = 0 then 0 else sum(amount)*10000/sum(cost) end) as " . $order_column;
                                        break;
                                    default:$sum = "sum(" . $order_column . ") as " . $order_column;
                                }
                                $sum   = $order_column ? $group . "," . $sum : $group;
                                $field = $order_column ? $group . "," . $order_column : $group;
                                $sql   = "select " . $field . " FROM (select " . $field . ",row_number() OVER (" . $orderby . ") AS rownum FROM ";
                                $sql .= "(select " . $sum . " FROM " . $table . " where " . $where . " group by " . $group . ") AS sub) AS result";
                                $sql .= " where rownum > " . ($limit['page_num'] - 1) * $limit['page_size'] . " and rownum <= " . $limit['page_num'] * $limit['page_size'];
                            }
                            break;
                        case "userName":
                        case "campaignName":
                        case "adgroupName":
                        case "losc":
                            $sum = "sum(impression) as impression,sum(click) as click,sum(cost) as cost,";
                            $sum .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum,";
                            $sum .= "sum(case when losc is null or amount is null then 0 else amount end) as amount,";
                            $sum .= "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as ctr,";
                            $sum .= "(case when sum(click) = 0 then 0 else sum(cost)*10000/sum(click) end) as cpc,";
                            $sum .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)*10000/sum(click) end) as rate,";
                            $sum .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)*10000/sum(cost) end) as roi";
                            $sum                = $groups . "," . $sum;
                            $interval && $sum   = $interval == 'day' ? ("min(day) as day,min(dateTime) as dateTime," . $sum) : ($interval . ",min(dateTime) as dateTime," . $sum);
                            $field              = "impression,click,cost,orderNum,amount,ctr,cpc,rate,roi," . $groups;
                            $interval && $field = $field . "," . $interval . ",dateTime";
                            $groupby            = $interval && $interval != 'day' ? $interval . "(dateTime)," . $groups : $groups;
                            $group != "losc" && $groupby .= ",losc";
                            $submin = $group == "losc" ? $groups : $groups . ",losc";
                            $submin .= ",sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount";
                            $submin   = $interval ? $interval . "(dateTime) as " . $interval . ",min(dateTime) as dateTime," . $submin : $submin;
                            $sub      = "(select " . $submin . " FROM " . $table . " where " . $where . " group by dateTime," . $groupby . ")";
                            $newgroup = $interval ? ($interval != 'day' ? $interval . "," . $groups : "dateTime,day," . $groups) : $groups;
                            $subsum   = $group == "losc" ? $groups : $groups . ",losc";
                            $subsum .= ",sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount";
                            $subsum                    = $interval ? $interval . ",min(dateTime) as dateTime," . $subsum : $subsum;
                            $interval != 'day' && $sub = "(select " . $subsum . " FROM " . $sub . " group by " . $newgroup . ($group != "losc" ? ",losc" : "") . ")"; //分日报告少一层sql嵌套
                            $sql                       = "select " . $field . " FROM (select " . $field . ",row_number() OVER (" . $orderby . ") AS rownum FROM ";
                            $sql .= "(select " . $sum . " FROM " . $sub . " group by " . $newgroup . ") AS sub) AS result";
                            $sql .= " where rownum > " . ($limit['page_num'] - 1) * $limit['page_size'] . " and rownum <= " . $limit['page_num'] * $limit['page_size'];
                            break;
                        default:;
                    }
                    $data_list = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
                    $expire    = isset($export_msg['startDate']) && $export_msg['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
                    if ($data_list === false || !empty($nocache)) {
                        $this->di->get('presto')->Query($sql);
                        $this->di->get('presto')->WaitQueryExec();
                        $data_list = $this->di->get('presto')->GetData();
                        $this->di->get('cas')->getRedis()->setex(
                            "sem-presto:" . md5($sql),
                            $expire,
                            json_encode($data_list)
                        );
                    } else {
                        $data_list = json_decode($data_list, true);
                    }
                    if (empty($data_list)) {
                        $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
                        return;
                    } else {
                        $report_list = $loscs = array();
                        foreach ($data_list as $key => $data) {
                            if ($interval) {
//获取各个时间分区的起始时间
                                $count                        = count($data);
                                $report_list[$key][$interval] = $data[$count - 2];
                                $dateTime                     = strtotime($data[$count - 1]);
                                switch ($interval) {
                                    case "week":
                                        $weekNum   = date("w", $dateTime) ? date("w", $dateTime) : 7; //星期天为0需转换
                                        $startDate = mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime) + 1 - $weekNum, date("Y", $dateTime));
                                        $endDate   = mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime) + 7 - $weekNum, date("Y", $dateTime));
                                        break;
                                    case "month":
                                        $startDate = mktime(0, 0, 0, date("m", $dateTime), 1, date("Y", $dateTime));
                                        $endDate   = mktime(0, 0, 0, date("m", $dateTime) + 1, 1, date("Y", $dateTime)) - 86400;
                                        break;
                                    case "day":
                                        $startDate = $endDate = $dateTime;
                                        break;
                                    default:;
                                }
                                $report_list[$key]['startDate'] = isset($export_msg['startDate']) && $startDate < strtotime($export_msg['startDate']) ? $export_msg['startDate'] : date('Y-m-d', $startDate);
                                $report_list[$key]['endDate']   = isset($export_msg['endDate']) && $endDate > strtotime($export_msg['endDate']) - 86400 ? date('Y-m-d', strtotime($export_msg['endDate']) - 86400) : date('Y-m-d', $endDate);
                                unset($data[$count - 1], $data[$count - 2]);
                            } elseif ($group == 'keywordId') {
//时间不分段的关键词报告走es查询
                                $data_list[$key][$group] = $data[0];
                                $terms[$group][]         = $data[0];
                                continue;
                            }
                            isset($data[9]) && $group != "losc" && $report_list[$key]['userName'] = $data[9];
                            isset($data[9]) && $group == "losc" && $report_list[$key]['losc']     = $data[9];
                            isset($data[10]) && $report_list[$key]['campaignName']                = $data[10];
                            isset($data[11]) && $report_list[$key]['adgroupName']                 = $data[11];
                            isset($data[12]) && $report_list[$key]['losc']                        = $data[12];
                            isset($data[13]) && $report_list[$key]['keyword']                     = $data[13];
                            isset($data[14]) && $report_list[$key]['keywordId']                   = $data[14];
                            $report_list[$key]['impression']                                      = intval($data[0]);
                            $report_list[$key]['click']                                           = intval($data[1]);
                            $report_list[$key]['cost']                                            = round($data[2], 2);
                            $report_list[$key]['orderNum']                                        = intval($data[3]);
                            $report_list[$key]['amount']                                          = round($data[4], 2);
                            $report_list[$key]['ctr']                                             = round($data[5] / 10000, 4);
                            $report_list[$key]['cpc']                                             = round($data[6] / 10000, 4);
                            $report_list[$key]['rate']                                            = round($data[7] / 10000, 4);
                            $report_list[$key]['roi']                                             = round($data[8] / 10000, 4);
                            $group == "losc" && $loscs[$report_list[$key]['losc']]                = $report_list[$key]['losc'];
                        }
                    }
                    if ($interval) {
                        $groupby                     = $group == 'keywordId' ? str_replace("userName,campaignName,adgroupName,losc,keyword,", "", $groupby) : $groupby;
                        $group != "losc" && $groupby = str_replace(",losc", "", $groupby);
                        $groupby                     = $group != 'keywordId' && $interval == 'day' ? "dateTime," . $groupby : $groupby;
                        $sql                         = "select rownum from (select row_number() OVER (order by " . $group . ") AS rownum from " . $table . " where " . $where . " group by " . $groupby . ") order by rownum desc limit 1";
                    } else {
                        $sql = "select count(distinct(" . $group . ")) from " . $table . " where " . $where;
                    }
                    $total_records = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
                    $expire        = isset($export_msg['startDate']) && $export_msg['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
                    if ($total_records === false || !empty($nocache)) {
                        $this->di->get('presto')->Query($sql);
                        $this->di->get('presto')->WaitQueryExec();
                        $total_records = $this->di->get('presto')->GetData();
                        $this->di->get('cas')->getRedis()->setex(
                            "sem-presto:" . md5($sql),
                            $expire,
                            json_encode($total_records)
                        );
                    } else {
                        $total_records = json_decode($total_records, true);
                    }
                    $total_records = $total_records[0][0];
                    $total_pages   = intval(($total_records - 1) / $limit['page_size'] + 1);
                    if ($group == 'losc') {
//获取losc对应渠道名称
                        $losc_channels = $this->di->get('cas')->get('ora_mark_channel_service')->getListWithFather(array(
                            'c.VALID'        => "='Y'",
                            'c.CHANNEL_CODE' => " IN('" . implode("','", $loscs) . "')",
                        ));
                        foreach ($losc_channels as $losc_channel) {
                            $losc_channels[$losc_channel['CHANNEL_CODE']] = $losc_channel;
                        }
                        foreach ($report_list as $key => $report) {
                            !empty($losc_channels[$report['losc']]) && $report_list[$key] += $losc_channels[$report['losc']];
                        }
                    }
                    if ($group != 'keywordId' || ($interval && $group == 'keywordId')) {
                        $data_list = null;
                        $this->jsonResponse(array('results' => $report_list, 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
                        return;
                    }
                } else {
//走mysql
                    $condition['platform'] = "=" . $platform;
                    $limit['page_size']    = $limit['page_size'] > 100 ? 100 : $limit['page_size'];
                    switch ($group) {
                        case 'userName':
                            if (!empty($terms['userName'])) {
                                break;
                            }

                            switch ($platform) {
                                case 1: //百度
                                    $condition['userStat'] = empty($condition['userStat']) ? " in(0,1,2,3,11)" : $condition['userStat'];
                                    break;
                                case 2: //360
                                    $condition['userStat'] = empty($condition['userStat']) ? " in(1,2)" : $condition['userStat'];
                                    break;
                                case 3: //神马
                                    $condition['userStat'] = empty($condition['userStat']) ? " in(20,21,22,100)" : $condition['userStat'];
                                    break;
                                case 4: //搜狗
                                default:;
                            }
                            $data_list = $this->sem_account_svc->getAccountList($condition, $limit, $columns, $order);
                            if (empty($data_list)) {
                                $terms['userName'][] = '';
                            } else {
                                foreach ($data_list as $data) {
                                    $terms['userName'][] = $data['userName'];
                                }
                            }
                            $total_records = $this->sem_account_svc->getAccountTotal($condition);
                            break;
                        case 'campaignName':
                            if (!empty($terms['campaignName'])) {
                                break;
                            }

                            switch ($platform) {
                                case 1: //百度
                                    $condition['status'] = empty($condition['status']) ? " in(21,24,25)" : $condition['status'];
                                    break;
                                case 2: //360
                                    $condition['status'] = empty($condition['status']) ? " = 1" : $condition['status'];
                                    break;
                                case 3: //神马
                                    $condition['status'] = empty($condition['status']) ? " = 4" : $condition['status'];
                                    break;
                                case 4: //搜狗
                                default:;
                            }
                            $data_list = $this->sem_campaign_svc->getCampaignList($condition, $limit, $columns, $order);
                            if (empty($data_list)) {
                                $terms['campaignName'][] = '';
                            } else {
                                foreach ($data_list as $data) {
                                    $terms['campaignName'][] = $data['campaignName'];
                                }
                            }
                            $total_records = $this->sem_campaign_svc->getCampaignTotal($condition);
                            break;
                        case 'keywordId':
                            if (!empty($terms['keywordId'])) {
                                break;
                            }

                            switch ($platform) {
                                case 1: //百度
                                    $condition['status'] = empty($condition['status']) ? " in(40,41,47,48,49,50)" : $condition['status'];
                                    break;
                                case 2: //360
                                    break;
                                case 3: //神马
                                    $condition['status'] = empty($condition['status']) ? " = 7" : $condition['status'];
                                    break;
                                case 4: //搜狗
                                default:;
                            }
                            $data_list = $this->sem_keyword_svc->getKeywordList($condition, $limit, $columns, $order);
                            if (empty($data_list)) {
                                $terms['keywordId'][] = 0;
                            } else {
                                foreach ($data_list as $data) {
                                    $terms['keywordId'][] = $data['keywordId'];
                                }
                            }
                            $total_records = $this->sem_keyword_svc->getKeywordTotal($condition);
                            break;
                        default:;
                    }
                    $total_pages = intval(($total_records - 1) / $limit['page_size'] + 1);
                }
            }
            $report_list = $this->sem_report_svc->getHiveReport($terms, $range, $limit, $group, $sort, $interval, $platform, $nocache);
            if (empty($report_list)) {
                $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
                return;
            }
            //与上面从mysql获取的数据进行合并
            if (!empty($data_list)) {
                if (!is_array($report_list)) {
                    $report_list = array();
                }

                foreach ($data_list as $key => $data) {
                    if (!isset($report_list['list'][$data[$group]])) {
                        $report_list['list'][$data[$group]] = array(
                            "doc_count"                                => 0,
                            "amount"                                   => 0,
                            "cost"                                     => 0,
                            "impression"                               => 0,
                            "orderNum"                                 => 0,
                            "click"                                    => 0,
                            "ctr"                                      => 0,
                            "cpc"                                      => 0,
                            "rate"                                     => 0,
                            "roi"                                      => 0,
                            $group == "keywordId" ? "keyword" : $group => $data[$group == "keywordId" ? "keyword" : $group],
                        );
                    }
                    $report_list['list'][$key] = $report_list['list'][$data[$group]] + $data;
                    unset($report_list['list'][$data[$group]]);
                }
                $data_list = null;
            }
            //去取hive重新统计后的订单数据
            if (($group == 'device' || $group == 'date' || $group == 'date_histogram') && (empty($terms['campaignName']) && empty($terms['adgroupName']) && empty($terms['keywordId']))) {
                $report_list = array();
                $table       = $platform == 4 ? "sem_account_report_sogou" : ($platform == 3 ? "sem_account_report_smcn" : ($platform == 2 ? "sem_account_report_socom" : "sem_account_report"));
                $field       = ($group == 'date_histogram' || $group == 'date') ? "dateTime,sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount" :
                "device,sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount";
                $dateGroup = $interval == "week" ? "week(dateTime,1)" : ($interval == "month" ? "month(dateTime)" : "dateTime");
                $group_by  = ($group == 'date_histogram' || $group == 'date') ? " group by " . $dateGroup : " group by device";
                $where     = "1 = 1";
                $where .= isset($export_msg['startDate']) ? " and dateTime >= '" . $export_msg['startDate'] . "'" : "";
                $where .= isset($export_msg['endDate']) ? " and dateTime < '" . $export_msg['endDate'] . "'" : "";
                foreach ($terms as $key => $term) {
                    if (is_array($term)) {
                        $where .= " and " . $key . " in(" . (in_array($key, array("device", "unitOfTime", "keywordId")) ? implode(",", $term) : "'" . implode("','", $term) . "'") . ")";
                    } else {
                        $where .= " and " . $key . " = " . (in_array($key, array("device", "unitOfTime", "keywordId")) ? $term : "'" . $term . "'");
                    }
                }
                $sql             = "select " . $field . " FROM " . $table . " where " . $where . $group_by;
                $account_reports = $this->sem_account_svc->query($sql, 'All');
                foreach ($account_reports as $account_report) {
                    $dateTime = strtotime($account_report['dateTime']);
                    switch ($interval) {
                        case "week":
                            $weekNum  = date("w", $dateTime) ? date("w", $dateTime) : 7; //星期天为0需转换
                            $dateTime = mktime(0, 0, 0, date("m", $dateTime), date("d", $dateTime) + 1 - $weekNum, date("Y", $dateTime));
                            break;
                        case "month":
                            $dateTime = mktime(0, 0, 0, date("m", $dateTime), 1, date("Y", $dateTime));
                            break;
                        default:;
                    }
                    $key                                     = ($group == 'date_histogram' || $group == 'date') ? date('Y-m-d H:i:s', $dateTime) : $account_report[$group];
                    $report_list['list'][$key][$group]       = $key;
                    $report_list['list'][$key]['impression'] = intval($account_report['impression']);
                    $report_list['list'][$key]['click']      = intval($account_report['click']);
                    $report_list['list'][$key]['cost']       = round($account_report['cost'], 2);
                    $report_list['list'][$key]['orderNum']   = intval($account_report['orderNum']);
                    $report_list['list'][$key]['amount']     = round($account_report['amount'], 2);
                    $report_list['list'][$key]['ctr']        = $report_list['list'][$key]['impression'] ? round($report_list['list'][$key]['click'] / $report_list['list'][$key]['impression'], 4) : 0;
                    $report_list['list'][$key]['cpc']        = $report_list['list'][$key]['click'] ? round($report_list['list'][$key]['cost'] / $report_list['list'][$key]['click'], 4) : 0;
                    $report_list['list'][$key]['rate']       = $report_list['list'][$key]['click'] ? round($report_list['list'][$key]['orderNum'] / $report_list['list'][$key]['click'], 4) : 0;
                    $report_list['list'][$key]['roi']        = $report_list['list'][$key]['cost'] ? round($report_list['list'][$key]['amount'] / $report_list['list'][$key]['cost'], 4) : 0;
                }
                if (isset($_REQUEST['current_page'])) {
                    $field         = ($group == 'date_histogram' || $group == 'date') ? "count(distinct " . $dateGroup . ")" : "count(distinct device)";
                    $sql           = "select " . $field . " as total FROM " . $table . " where " . $where;
                    $total_records = $this->sem_account_svc->query($sql, 'All');
                    $total_records = $total_records[0]['total'];
                    $total_pages   = intval(($total_records - 1) / $limit['page_size'] + 1);
                }
            }
            $total_records = empty($total_records) ? intval($report_list['pages']['itemCount']) : $total_records;
            $total_pages   = empty($total_pages) ? intval($report_list['pages']['pageCount']) : $total_pages;
            $this->jsonResponse(array('results' => $report_list['list'], 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
        } else {
            //按过去7天统计数据排序
            if (isset($sort['week_cost']) || isset($sort['week_click']) || isset($sort['week_roi']) || isset($sort['week_rate'])) {
                $table              = $platform == 4 ? "hive.sogou.sem_report" : ($platform == 3 ? "hive.smcn.sem_report" : ($platform == 2 ? "hive.socom.sem_report" : "hive.default.sem_report")); //所属平台（1：百度 2：360 3：神马 4：搜狗）
                $limit['page_size'] = $limit['page_size'] > 100 ? 100 : $limit['page_size'];
                $where              = "dateTime >= timestamp '" . date("Y-m-d", strtotime("7 days ago")) . "'";
                $where .= " and dateTime < timestamp '" . date("Y-m-d", time()) . "'";
                $where .= " and unitOfTime = 5 and device = 0";
                isset($terms['userName']) && $where .= " and userName = '" . $terms['userName'] . "'";
                isset($terms['keywordId']) && $where .= " and keywordId = " . $terms['keywordId'];
                isset($terms['keyword']) && $where .= " and keyword = '" . $terms['keyword'] . "'";
                $group        = 'userName,campaignName,adgroupName,keyword,keywordId';
                $order_column = explode("_", array_keys($sort)[0])[1];
                switch ($order_column) {
                    case "ctr":$sum = "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as " . $order_column;
                        break;
                    case "cpc":$sum = "(case when sum(click) = 0 then 0 else sum(cost)*10000/sum(click) end) as " . $order_column;
                        break;
                    case "rate":$sum = "(case when sum(click) = 0 then 0 else sum(orderNum)*10000/sum(click) end) as " . $order_column;
                        break;
                    case "roi":$sum = "(case when sum(cost) = 0 then 0 else sum(amount)*10000/sum(cost) end) as " . $order_column;
                        break;
                    default:$sum = "sum(" . $order_column . ") as " . $order_column;
                }
                $sum     = $group . "," . $sum;
                $field   = $group . "," . $order_column;
                $orderby = "order by " . $order_column . " " . $sort["week_" . $order_column] . ",keywordId asc";
                $sql     = "select " . $field . " FROM (select " . $field . ",row_number() OVER (" . $orderby . ") AS rownum FROM ";
                $sql .= "(select " . $sum . " FROM " . $table . " where " . $where . " group by " . $group . ") AS sub) AS result";
                $sql .= " where rownum > " . ($limit['page_num'] - 1) * $limit['page_size'] . " and rownum <= " . $limit['page_num'] * $limit['page_size'];
                $data_list = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
                if ($data_list === false || !empty($nocache)) {
                    $this->di->get('presto')->Query($sql);
                    $this->di->get('presto')->WaitQueryExec();
                    $data_list = $this->di->get('presto')->GetData();
                    $this->di->get('cas')->getRedis()->setex(
                        "sem-presto:" . md5($sql),
                        3600,
                        json_encode($data_list)
                    );
                } else {
                    $data_list = json_decode($data_list, true);
                }
                if (empty($data_list)) {
                    $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
                    return;
                } else {
                    foreach ($data_list as $key => $data) {
                        $terms["keywordId"][] = $data[4];
                    }
                }

                $sql           = "select count(distinct(keywordId)) from " . $table . " where " . $where;
                $total_records = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
                $expire        = isset($export_msg['startDate']) && $export_msg['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
                if ($total_records === false || !empty($nocache)) {
                    $this->di->get('presto')->Query($sql);
                    $this->di->get('presto')->WaitQueryExec();
                    $total_records = $this->di->get('presto')->GetData();
                    $this->di->get('cas')->getRedis()->setex(
                        "sem-presto:" . md5($sql),
                        $expire,
                        json_encode($total_records)
                    );
                } else {
                    $total_records = json_decode($total_records, true);
                }
                $total_records     = $total_records[0][0];
                $total_pages       = intval(($total_records - 1) / $limit['page_size'] + 1);
                $group             = "";
                $sort              = "";
                $limit['page_num'] = 1;
            }
            $report_list = $this->sem_report_svc->getReportList($terms, $range, $limit, $group, $sort, $interval, $platform);
            if (!empty($data_list)) {
                foreach ($report_list['list'] as $key => $report) {
                    $report_list['list'][$report['keywordId']] = $report;
                }
                foreach ($data_list as $key => $data) {
                    $keywordId = $data[4];
                    if (empty($report_list['list'][$keywordId])) {
                        $data_list[$key]["userName"]              = $data[0];
                        $data_list[$key]["campaignName"]          = $data[1];
                        $data_list[$key]["adgroupName"]           = $data[2];
                        $data_list[$key]["keyword"]               = $data[3];
                        $data_list[$key]["keywordId"]             = $keywordId;
                        $data_list[$key]["week_" . $order_column] = $data[5];
                        $data_list[$key]["impression"]            = $data_list[$key]["click"]            = $data_list[$key]["cost"]            = 0;
                        $data_list[$key]["orderNum"]              = $data_list[$key]["amount"]              = $data_list[$key]["ctr"]              = 0;
                        $data_list[$key]["cpc"]                   = $data_list[$key]["rate"]                   = $data_list[$key]["roi"]                   = 0;
                        unset($data_list[$key][0], $data_list[$key][1], $data_list[$key][2], $data_list[$key][3], $data_list[$key][4], $data_list[$key][5]);
                    } else {
                        $data_list[$key]                          = $report_list['list'][$keywordId];
                        $data_list[$key]["week_" . $order_column] = $data[5];
                    }
                }
                $this->jsonResponse(array('results' => $data_list, 'total_records' => $total_records, 'page_index' => $current_page, 'total_pages' => $total_pages));
            }
            if (empty($report_list['list'])) {
                $this->_errorResponse(DATA_NOT_FOUND, '推广报告不存在');
                return;
            }
            $this->jsonResponse(array('results' => $report_list['list'], 'total_records' => $report_list['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $report_list['pages']['pageCount']));
        }
    }

    /**
     * ROI/转化率TOP10报告列表
     */
    public function listTopAction()
    {
        $platform  = $this->request->get('platform');
        $condition = $this->request->get('condition');
        $terms     = $this->request->get('terms');
        $limit     = intval($this->request->get('limit'));
        $order     = trim($this->request->get('order'));
        $group     = trim($this->request->get('group'));
        $ordertype = trim($this->request->get('ordertype'));
        $nocache   = intval($this->request->get('nocache')); //用于强制刷新缓存
        $condition = json_decode($condition, true);
        $terms     = json_decode($terms, true);
        $platform  = $platform ? $platform : 1; //所属平台 默认为1百度
        $limit     = $limit ? $limit : 10;
        $order     = $order ? $order : "rate";
        $group     = $group ? $group : "campaignName";
        $ordertype = $ordertype ? $ordertype : "desc";

        $table    = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? "sem_realtime_report" : "sem_report";
        $table    = $platform == 4 ? "hive.sogou." . $table : ($platform == 3 ? "hive.smcn." . $table : ($platform == 2 ? "hive.socom." . $table : "hive.default." . $table)); //所属平台（1：百度 2：360 3：神马 4：搜狗）
        $field    = $group == "keywordId" ? "arbitrary(keyword) as keyword" : $group;
        $subfield = $group == "keywordId" ? "keywordId,arbitrary(keyword) as keyword" : $group;
        $where    = "1 = 1";
        $where .= isset($condition['startDate']) ? " and dateTime >= timestamp '" . $condition['startDate'] . "'" : "";
        $where .= isset($condition['endDate']) ? " and dateTime < timestamp '" . $condition['endDate'] . "'" : "";
        foreach ($terms as $key => $term) {
            if (is_array($term)) {
                $where .= " and " . $key . " in(" . (in_array($key, array("device", "unitOfTime")) ? implode(",", $term) : "'" . implode("','", $term) . "'") . ")";
            } else {
                $where .= " and " . $key . " = " . (in_array($key, array("device", "unitOfTime")) ? $term : "'" . $term . "'");
            }
        }

        if ($group == "keywordId") {
//关键词订单不需要按losc分组计算
            $groupby = "userName,keyword," . $group;
            $sql     = "select sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(amount) as amount,";
            $sql .= "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as ctr,";
            $sql .= "(case when sum(click) = 0 then 0 else sum(orderNum)*10000/sum(click) end) as rate,";
            $sql .= "(case when sum(cost) = 0 then 0 else sum(amount)*10000/sum(cost) end) as roi," . $groupby . " from " . $table;
            $sql .= " where " . $where . " group by " . $groupby . " order by " . $order . " " . $ordertype . "," . $group . " asc limit " . $limit;
        } else {
            $groupby = $group == "campaignName" ? "userName," . $group : "userName,campaignName," . $group;
            $sql     = "select sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(case when losc is null or amount is null then 0 else amount end) as amount,";
            $sql .= "(case when sum(impression) = 0 then 0 else sum(click)*10000/sum(impression) end) as ctr,";
            $sql .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)*10000/sum(click) end) as rate,";
            $sql .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)*10000/sum(cost) end) as roi," . $groupby . " from ";
            $sql .= "(select " . $groupby . ",losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,sum(orderNum) as orderNum,sum(amount) as amount from ";
            $sql .= "(select " . $groupby . ",losc,sum(impression) as impression,sum(click) as click,sum(cost) as cost,min(orderNum) as orderNum,min(amount) as amount from ";
            $sql .= $table . " where " . $where . " group by dateTime," . $groupby . ",losc) group by " . $groupby . ",losc)";
            $sql .= "group by " . $groupby . " order by " . $order . " " . $ordertype . "," . $group . " asc limit " . $limit;
        }
        $top_list = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
        $expire   = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
        if ($top_list === false || !empty($nocache)) {
            $this->di->get('presto')->Query($sql);
            $this->di->get('presto')->WaitQueryExec();
            $top_list = $this->di->get('presto')->GetData();
            $this->di->get('cas')->getRedis()->setex(
                "sem-presto:" . md5($sql),
                $expire,
                json_encode($top_list)
            );
        } else {
            $top_list = json_decode($top_list, true);
        }
        if (empty($top_list)) {
            $this->_errorResponse(DATA_NOT_FOUND, 'ROI/转化率TOP10报告不存在');
            return;
        } else {
            $results = array();
            foreach ($top_list as $key => $top) {
                $results[$key][$group]                                    = $group == "campaignName" ? $top[8] : $top[9];
                $results[$key]['impression']                              = $top[0];
                $results[$key]['click']                                   = $top[1];
                $results[$key]['cost']                                    = round($top[2], 2);
                $results[$key]['amount']                                  = round($top[3], 2);
                $results[$key]['ctr']                                     = round($top[4] / 10000, 4);
                $results[$key]['rate']                                    = round($top[5] / 10000, 4);
                $results[$key]['roi']                                     = round($top[6] / 10000, 4);
                $results[$key]['userName']                                = $top[7];
                $group == "adgroupName" && $results[$key]['campaignName'] = $top[8];
                $group == "keywordId" && $results[$key]['keyword']        = $top[8];
            }
            $top_list = null;
        }
        $this->jsonResponse(array('results' => $results));
    }

    /**
     * 质量监控报告列表
     */
    public function listMonitorReportAction()
    {
        $id        = intval($this->request->get('id'));
        $condition = $this->request->get('condition');
        $num       = intval($this->request->get('num')); //筛选条件序号
        $nocache   = intval($this->request->get('nocache')); //用于强制刷新缓存
        $condition = json_decode($condition, true);

        !empty($id) && $monitor_info = $this->sem_monitor_svc->getOneMonitor(array("id" => " = " . $id));
        if (empty($monitor_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '推广监控不存在');
            return;
        }
        $platform = intval($monitor_info['platform']);
        $userName = trim($monitor_info['userName']);
        $click    = intval($monitor_info['click']);
        $roi      = floatval($monitor_info['roi']);
        $url      = trim($monitor_info['url']);
        $object   = intval($monitor_info['object']);

        $table = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? "sem_realtime_report" : "sem_report";
        $table = $platform == 4 ? "hive.sogou." . $table : ($platform == 3 ? "hive.smcn." . $table : ($platform == 2 ? "hive.socom." . $table : "hive.default." . $table)); //所属平台（1：百度 2：360 3：神马 4：搜狗）
        $where = " where cast(k.platform as bigint) = " . $platform;
        if (strstr($url, "http://m.lvmama.com")) {
            $where .= " and mobileDestinationUrl like '" . $url . "%' and device = 0";
            $losc_where = " where r2.device = 0";
        } else {
            $where .= " and pcDestinationUrl like '" . $url . "%' and device = 0";
            $losc_where = " where r2.device = 0";
        }
        $where .= isset($condition['startDate']) ? " and dateTime >= timestamp '" . $condition['startDate'] . "'" : "";
        $where .= isset($condition['endDate']) ? " and dateTime < timestamp '" . $condition['endDate'] . "'" : "";
        $where .= !empty($userName) ? " and userName = '" . $userName . "'" : "";
        $losc_where .= isset($condition['startDate']) ? " and r2.dateTime >= timestamp '" . $condition['startDate'] . "'" : "";
        $losc_where .= isset($condition['endDate']) ? " and r2.dateTime < timestamp '" . $condition['endDate'] . "'" : "";
        $losc_where .= !empty($userName) ? " and r2.userName = '" . $userName . "'" : "";

        if ($object == 1) {
//监控对象（1推广单元 2关键词3losc）
            $select = "select min(count) as count, min(cost) as cost, sum(amount) as amount, min(click) as click, sum(orderNum) as orderNum from (";
            $select .= "select min(count) as count, min(cost) as cost, min(click) as click, min(device) as device, ";
            $select .= "min(case when losc is null or amount is null then 0 else amount end) as amount, min(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum from ";
            $select .= "(select sub.count, sub.cost, sub.click, r2.dateTime, r2.losc, r2.device, r2.amount, r2.orderNum from ";
            $select .= "(select campaignName, adgroupName, count(adgroupName) over (partition by device) as count,sum(cost) over (partition by device) as cost,sum(click) over (partition by device) as click from ";
            $child = "select campaignName, adgroupName, sum(cost) as cost, sum(case when losc is null or amount is null then 0 else amount end) as amount, ";
            $child .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum, sum(click) as click, ";
            $child .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi, min(device) as device from ";
            $child .= "(select campaignName, adgroupName, losc, sum(cost) as cost, sum(amount) as amount, sum(orderNum) as orderNum, sum(click) as click, min(device) as device from ";
            $child .= "(select r.campaignName, r.adgroupName, r.losc, sum(cost) as cost, min(amount) as amount, min(orderNum) as orderNum, sum(click) as click, min(device) as device from " . $table . " r ";
            $child .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group     = " group by r.dateTime,r.campaignName,r.adgroupName,r.losc) group by campaignName,adgroupName,losc) group by campaignName,adgroupName";
            $losc_join = " left join " . $table . " r2 on r2.campaignName = sub.campaignName and r2.adgroupName = sub.adgroupName " . $losc_where . ") group by dateTime,losc)";
        } elseif ($object == 2) {
            $select = "select min(count) as count, min(cost) as cost, sum(amount) as amount, min(click) as click, sum(orderNum) as orderNum from (";
            $select .= "select min(count) as count, min(cost) as cost, min(click) as click, min(device) as device, ";
            $select .= "min(case when losc is null or amount is null then 0 else amount end) as amount, min(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum from ";
            $select .= "(select sub.count, sub.cost, sub.click, r2.dateTime, r2.losc, r2.device, r2.amount, r2.orderNum from ";
            $select .= "(select keywordId, count(keywordId) over (PARTITION BY device) as count,sum(cost) over (PARTITION BY device) as cost,sum(click) over (PARTITION BY device) as click from ";
            $child = "select r.keywordId, sum(cost) as cost, sum(case when r.losc is null or amount is null then 0 else amount end) as amount, sum(orderNum) as orderNum, sum(click) as click, ";
            $child .= "(case when sum(cost) = 0 then 0 else sum(amount)/sum(cost) end) as roi, min(device) as device from " . $table . " r ";
            $child .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group     = " group by r.keywordId";
            $losc_join = " left join " . $table . " r2 on r2.keywordId = sub.keywordId " . $losc_where . ") group by dateTime,losc)";
        } else {
            $select = "select count(losc) as count, sum(cost) as cost, sum(amount) as amount, sum(click) as click, sum(orderNum) as orderNum from (";
            $select .= "select losc, cost, amount, orderNum, click, device from ";
            $child = "select losc, sum(cost) as cost, sum(case when losc is null or amount is null then 0 else amount end) as amount, ";
            $child .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum, sum(click) as click, ";
            $child .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi, min(device) as device from ";
            $child .= "(select r.losc, sum(cost) as cost, min(amount) as amount, min(orderNum) as orderNum, sum(click) as click, min(device) as device from " . $table . " r ";
            $child .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group     = " group by r.dateTime,r.losc) group by losc";
            $losc_join = "";
        }
        $filters = array(
            0 => "",
            1 => " where click >= " . $click . " and roi > 0 and roi < " . $roi,
            2 => " where click >= " . $click . " and roi >= " . $roi,
            3 => " where click > 0 and click < " . $click . " and roi > 0 and roi < " . $roi,
            4 => " where click > 0 and click < " . $click . " and roi >= " . $roi,
            5 => " where cost > 0 and amount = 0",
            6 => " where click = 0",
        );
        $expire = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
        foreach ($filters as $key => $filter) {
            if ($key && $num >= 0 && $num != $key) {
                continue;
            }

            $sql            = $select . "(" . $child . $where . $group . ")" . $filter . ") as sub" . $losc_join . " group by device";
            $monitor_report = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
            if ($monitor_report === false || !empty($nocache)) {
                $this->di->get('presto')->Query($sql);
                $this->di->get('presto')->WaitQueryExec();
                $monitor_report = $this->di->get('presto')->GetData();
                $this->di->get('cas')->getRedis()->setex(
                    "sem-presto:" . md5($sql),
                    $expire,
                    json_encode($monitor_report)
                );
            } else {
                $monitor_report = json_decode($monitor_report, true);
            }
            if (empty($monitor_report)) {
                $monitor_reports[$key] = array(
                    'count'            => 0,
                    'count_percent'    => 0,
                    'cost'             => 0,
                    'cost_percent'     => 0,
                    'amount'           => 0,
                    'amount_percent'   => 0,
                    'click'            => 0,
                    'click_percent'    => 0,
                    'orderNum'         => 0,
                    'orderNum_percent' => 0,
                    'rate'             => 0,
                    'roi'              => 0,
                );
            } else {
                $monitor_reports[$key] = array(
                    'count'            => intval($monitor_report[0][0]),
                    'count_percent'    => $key ? ($monitor_reports[0]['count'] ? round(intval($monitor_report[0][0]) * 100 / $monitor_reports[0]['count'], 2) . '%' : '0%') : '100%',
                    'cost'             => round($monitor_report[0][1], 2),
                    'cost_percent'     => $key ? ($monitor_reports[0]['cost'] ? round(intval($monitor_report[0][1]) * 100 / $monitor_reports[0]['cost'], 2) . '%' : '0%') : '100%',
                    'amount'           => round($monitor_report[0][2], 2),
                    'amount_percent'   => $key ? ($monitor_reports[0]['amount'] ? round(intval($monitor_report[0][2]) * 100 / $monitor_reports[0]['amount'], 2) . '%' : '0%') : '100%',
                    'click'            => intval($monitor_report[0][3]),
                    'click_percent'    => $key ? ($monitor_reports[0]['click'] ? round(intval($monitor_report[0][3]) * 100 / $monitor_reports[0]['click'], 2) . '%' : '0%') : '100%',
                    'orderNum'         => intval($monitor_report[0][4]),
                    'orderNum_percent' => $key ? ($monitor_reports[0]['orderNum'] ? round(intval($monitor_report[0][4]) * 100 / $monitor_reports[0]['orderNum'], 2) . '%' : '0%') : '100%',
                    'rate'             => round($monitor_report[0][4] / $monitor_report[0][3], 4),
                    'roi'              => round($monitor_report[0][2] / $monitor_report[0][1], 4),
                );
            }
        }
        $this->jsonResponse(array('results' => $monitor_reports, 'monitor' => $monitor_info));
    }

    /**
     * 质量分析报告列表
     */
    public function listAnalysisReportAction()
    {
        $mid          = intval($this->request->get('mid')); //监控ID
        $condition    = $this->request->get('condition');
        $filter       = trim($this->request->get('filter')); //筛选条件
        $order        = trim($this->request->get('order'));
        $platform     = intval($this->request->get('platform'));
        $userName     = trim($this->request->get('userName'));
        $url          = trim($this->request->get('monitor_url'));
        $object       = intval($this->request->get('object'));
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $nocache      = intval($this->request->get('nocache')); //用于强制刷新缓存
        $condition    = json_decode($condition, true);
        $platform     = $platform ? $platform : 1;
        $object       = $object ? $object : 1;
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 20;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);

        if (!empty($mid)) {
            $monitor_info = $this->sem_monitor_svc->getOneMonitor(array("id" => " = " . $mid));
            if (empty($monitor_info)) {
                $this->_errorResponse(DATA_NOT_FOUND, '推广监控不存在');
                return;
            } else {
                $platform = intval($monitor_info['platform']);
                $userName = trim($monitor_info['userName']);
                $click    = intval($monitor_info['click']);
                $roi      = floatval($monitor_info['roi']);
                $url      = trim($monitor_info['url']);
                $object   = intval($monitor_info['object']);
                $filters  = array(
                    0 => "",
                    1 => "click >= " . $click . " and roi > 0 and roi < " . $roi,
                    2 => "click >= " . $click . " and roi >= " . $roi,
                    3 => "click > 0 and click < " . $click . " and roi > 0 and roi < " . $roi,
                    4 => "click > 0 and click < " . $click . " and roi >= " . $roi,
                    5 => "cost > 0 and amount = 0",
                    6 => "click = 0",
                );
                $num    = intval($this->request->get('num')); //筛选条件序号
                $filter = $filters[$num ? $num : 1];
            }
        }
        if (empty($filter)) {
            $this->_errorResponse(DATA_NOT_FOUND, '筛选条件至少选1个');
            return;
        }

        $table = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? "sem_realtime_report" : "sem_report";
        $table = $platform == 4 ? "hive.sogou." . $table : ($platform == 3 ? "hive.smcn." . $table : ($platform == 2 ? "hive.socom." . $table : "hive.default." . $table)); //所属平台（1：百度 2：360 3：神马 4：搜狗）
        if (strstr($url, "http://m.lvmama.com")) {
            $where = "cast(k.platform as bigint) = " . $platform . " and mobileDestinationUrl like '" . $url . "%' and device = 0";
        } elseif (!empty($url)) {
            $where = "cast(k.platform as bigint) = " . $platform . " and pcDestinationUrl like '" . $url . "%' and device = 0";
        } else {
            $where = "device = 0";
        }
        $where .= isset($condition['startDate']) ? " and dateTime >= timestamp '" . $condition['startDate'] . "'" : "";
        $where .= isset($condition['endDate']) ? " and dateTime < timestamp '" . $condition['endDate'] . "'" : "";
        $where .= !empty($userName) ? " and userName = '" . $userName . "'" : "";

        $expire = isset($condition['startDate']) && $condition['startDate'] == date("Y-m-d") ? 3600 : 86400; //当天报告仅缓存1小时
        if ($object == 1) {
//监控对象（1推广单元 2关键词3losc）
            $select = "select count(adgroupName) as count from (select adgroupName, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when losc is null or amount is null then 0 else amount end) as amount, min(device) as device, ";
            $select .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)/sum(click) end) as rate, ";
            $select .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi from ";
            $select .= "(select userName, campaignName, adgroupName, losc, sum(cost) as cost, sum(orderNum) as orderNum, sum(amount) as amount, sum(click) as click, min(device) as device from ";
            $select .= "(select r.userName, r.campaignName, r.adgroupName, r.losc, sum(cost) as cost, min(orderNum) as orderNum, min(amount) as amount, sum(click) as click, min(device) as device from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.dateTime,r.userName,r.campaignName,r.adgroupName,r.losc) group by userName,campaignName,adgroupName,losc) group by userName,campaignName,adgroupName";
        } elseif ($object == 2) {
            $select = "select count(keywordId) as count from (select r.keywordId, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when r.losc is null or amount is null then 0 else amount end) as amount, (case when sum(click) = 0 then 0 else sum(orderNum)/sum(click) end) as rate, ";
            $select .= "(case when sum(cost) = 0 then 0 else sum(amount)/sum(cost) end) as roi, min(device) as device from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.userName,r.keyword,r.keywordId";
        } else {
            $select = "select count(losc) as count from (select losc, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when losc is null or amount is null then 0 else amount end) as amount, min(device) as device, ";
            $select .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)/sum(click) end) as rate, ";
            $select .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi from ";
            $select .= "(select r.losc, sum(cost) as cost, min(orderNum) as orderNum, min(amount) as amount, sum(click) as click, min(device) as device from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.dateTime,r.losc) group by losc";
        }
        $sql            = $select . " where " . $where . $group . ") as sub " . (!empty($filter) ? "where " . $filter : "") . " group by device";
        $analysis_total = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
        if ($analysis_total === false || !empty($nocache)) {
            $this->di->get('presto')->Query($sql);
            $this->di->get('presto')->WaitQueryExec();
            $analysis_total = $this->di->get('presto')->GetData();
            $this->di->get('cas')->getRedis()->setex(
                "sem-presto:" . md5($sql),
                $expire,
                json_encode($analysis_total)
            );
        } else {
            $analysis_total = json_decode($analysis_total, true);
        }
        $total_records = !empty($analysis_total) ? intval($analysis_total[0][0]) : 0;
        $total_pages   = intval(($total_records - 1) / $limit['page_size'] + 1);

        $column = trim(str_replace(array("asc", "desc"), "", $order));
        $desc   = trim(str_replace($column, "", $order));
        switch ($column) {
            case "ctr":$column = "(case when impression = 0 then 0 else click*10000/impression end)";
                break;
            case "cpc":$column = "(case when click = 0 then 0 else cost*10000/click end)";
                break;
            case "rate":$column = "(case when click = 0 then 0 else orderNum*10000/click end)";
                break;
            case "roi":$column = "(case when cost = 0 then 0 else amount*10000/cost end)";
                break;
            default:;
        }
        if ($object == 1) {
//监控对象（1推广单元 2关键词3losc）
            $orderby = empty($order) ? "order by adgroupName" : "order by " . $column . " " . $desc;
            $select  = "select adgroupName, impression, click, cost, orderNum, amount, userName, campaignName from ";
            $select .= "(select userName, campaignName, adgroupName, impression, click, cost, orderNum, amount, row_number() OVER (" . $orderby . ") AS rownum from ";
            $select .= "(select userName, campaignName, adgroupName, sum(impression) as impression, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum, sum(case when losc is null or amount is null then 0 else amount end) as amount, ";
            $select .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)/sum(click) end) as rate, ";
            $select .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi from ";
            $select .= "(select userName, campaignName, adgroupName, losc, sum(impression) as impression, sum(click) as click, sum(cost) as cost, sum(orderNum) as orderNum, sum(amount) as amount from ";
            $select .= "(select r.userName, r.campaignName, r.adgroupName, r.losc, sum(impression) as impression, sum(click) as click, sum(cost) as cost, min(orderNum) as orderNum, min(amount) as amount from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.dateTime,r.userName,r.campaignName,r.adgroupName,r.losc) group by userName,campaignName,adgroupName,losc) group by userName,campaignName,adgroupName";
        } elseif ($object == 2) {
            $orderby = empty($order) ? "order by keywordId" : "order by " . $column . " " . $desc;
            $select  = "select keywordId, impression, click, cost, orderNum, amount, userName, campaignName, adgroupName, keyword from ";
            $select .= "(select userName, campaignName, adgroupName, keyword, keywordId, impression, click, cost, orderNum, amount, row_number() OVER (" . $orderby . ") AS rownum from ";
            $select .= "(select r.userName, r.campaignName, r.adgroupName, r.keyword, r.keywordId, sum(impression) as impression, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when r.losc is null or orderNum is null then 0 else orderNum end) as orderNum, sum(case when r.losc is null or amount is null then 0 else amount end) as amount, ";
            $select .= "(case when sum(click) = 0 then 0 else sum(orderNum)/sum(click) end) as rate, (case when sum(cost) = 0 then 0 else sum(amount)/sum(cost) end) as roi from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.userName,r.campaignName,r.adgroupName,r.keyword,r.keywordId";
        } else {
            $orderby = empty($order) ? "order by losc" : "order by " . $column . " " . $desc;
            $select  = "select losc, impression, click, cost, orderNum, amount from ";
            $select .= "(select losc, impression, click, cost, orderNum, amount, row_number() OVER (" . $orderby . ") AS rownum from ";
            $select .= "(select losc, sum(impression) as impression, sum(click) as click, sum(cost) as cost, ";
            $select .= "sum(case when losc is null or orderNum is null then 0 else orderNum end) as orderNum, sum(case when losc is null or amount is null then 0 else amount end) as amount, ";
            $select .= "(case when sum(click) = 0 then 0 else sum(case when losc is null or orderNum is null then 0 else orderNum end)/sum(click) end) as rate, ";
            $select .= "(case when sum(cost) = 0 then 0 else sum(case when losc is null or amount is null then 0 else amount end)/sum(cost) end) as roi from ";
            $select .= "(select r.losc, sum(impression) as impression, sum(click) as click, sum(cost) as cost, min(orderNum) as orderNum, min(amount) as amount from " . $table . " r ";
            !empty($url) && $select .= "left join mysql.lmm_sem.sem_keyword k on cast(k.keywordId as bigint) = r.keywordId ";
            $group = " group by r.dateTime,r.losc) group by losc";
        }
        $sql = $select . " where " . $where . $group . ")" . (!empty($filter) ? " where " . $filter : "");
        $sql .= ") as sub where rownum > " . ($limit['page_num'] - 1) * $limit['page_size'] . " and rownum <= " . $limit['page_num'] * $limit['page_size'];
        $analysis_reports = $this->di->get('cas')->getRedis()->get("sem-presto:" . md5($sql));
        if ($analysis_reports === false || !empty($nocache)) {
            $this->di->get('presto')->Query($sql);
            $this->di->get('presto')->WaitQueryExec();
            $analysis_reports = $this->di->get('presto')->GetData();
            $this->di->get('cas')->getRedis()->setex(
                "sem-presto:" . md5($sql),
                $expire,
                json_encode($analysis_reports)
            );
        } else {
            $analysis_reports = json_decode($analysis_reports, true);
        }
        $groups = $loscs = array();
        foreach ($analysis_reports as $key => $analysis_report) {
            $results = array(
                $object == 1 ? 'adgroupName' : ($object == 2 ? 'adgroupName' : 'losc') => $analysis_report[0],
                'impression'                                                           => intval($analysis_report[1]),
                'click'                                                                => intval($analysis_report[2]),
                'cost'                                                                 => $analysis_report[3],
                'orderNum'                                                             => intval($analysis_report[4]),
                'amount'                                                               => $analysis_report[5],
                'userName'                                                             => $analysis_report[6],
                'campaignName'                                                         => $analysis_report[7],
            );
            $object == 2 && $results['adgroupName']  = $analysis_report[8];
            $object == 2 && $results['keyword']      = $analysis_report[9];
            $object == 3 && $loscs[$results['losc']] = $results['losc'];
            $results['ctr']                          = $results['impression'] ? round($results['click'] / $results['impression'], 4) : 0;
            $results['cpc']                          = $results['click'] ? round($results['cost'] / $results['click'], 2) : 0;
            $results['rate']                         = $results['click'] ? round($results['orderNum'] / $results['click'], 4) : 0;
            $results['roi']                          = $results['cost'] ? round($results['amount'] / $results['cost'], 4) : 0;
            $results['cost']                         = round($results['cost'], 2);
            $results['amount']                       = round($results['amount'], 2);
            $analysis_reports[$key]                  = $results;
            unset($results);
        }
        if ($object == 3) {
//获取losc对应渠道名称
            $losc_channels = $this->di->get('cas')->get('ora_mark_channel_service')->getListWithFather(array(
                'c.VALID'        => "='Y'",
                'c.CHANNEL_CODE' => " IN('" . implode("','", $loscs) . "')",
            ));
            foreach ($losc_channels as $losc_channel) {
                $losc_channels[$losc_channel['CHANNEL_CODE']] = $losc_channel;
            }
            foreach ($analysis_reports as $key => $report) {
                !empty($losc_channels[$report['losc']]) && $analysis_reports[$key] += $losc_channels[$report['losc']];
            }
        }
        $this->jsonResponse(array('results' => $analysis_reports, 'total_records' => $total_records, 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 热销产品列表
     */
    public function listProductAction()
    {
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $sort         = trim($this->request->get('sort'));
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 1000);
//        $order = $order ? $order : null;
        $sort = json_decode($sort, true);

        $product_list = $this->sem_report_svc->getProductList($terms, $limit, $sort);
        if (empty($product_list['list'])) {
            $this->_errorResponse(DATA_NOT_FOUND, '热销产品不存在');
            return;
        }
        $this->jsonResponse(array('results' => $product_list['list'], 'total_records' => $product_list['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $product_list['pages']['pageCount']));
    }
    /**
     * subject订单列表
     */
    public function listSubjectOrderAction()
    {
        $condition    = $this->request->get('condition');
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
//        $group = trim($this->request->get('group'));
        $sort         = trim($this->request->get('sort'));
        $condition    = json_decode($condition, true);
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 1000);
        $sort         = json_decode($sort, true);

        $range     = array();
        $condition = $condition ? $condition : array();
        $terms     = $terms ? $terms : array();
        if (isset($condition['startDate'])) {
            $range['date']['gte'] = strtotime($condition['startDate']) * 1000;
            unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
            $range['date']['lt'] = strtotime($condition['endDate']) * 1000;
            unset($condition['endDate']);
        }
        isset($terms['date']) && $terms['date'] = strtotime($terms['date']) * 1000;

        $subject_order = $this->sem_report_svc->getSubjectOrder($terms, $range, $limit, $sort);

        if (empty($subject_order['list'])) {
            $this->_errorResponse(DATA_NOT_FOUND, '专题订单不存在');
            return;
        }
        $this->jsonResponse(array('results' => $subject_order['list'], 'total_records' => $subject_order['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $subject_order['pages']['pageCount']));
    }
    /**
     * losc订单列表
     */
    public function listLoscOrderAction()
    {
        $condition    = $this->request->get('condition');
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $group        = trim($this->request->get('group'));
        $sort         = trim($this->request->get('sort'));
        $interval     = trim($this->request->get('interval')); //统计时间分段(day/week/month)
        $condition    = json_decode($condition, true);
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 1000);
//        $order = $order ? $order : null;
        $sort = json_decode($sort, true);

        $range     = array();
        $condition = $condition ? $condition : array();
        $terms     = $terms ? $terms : array();
        if (isset($condition['startDate'])) {
            $range['date']['gte'] = strtotime($condition['startDate']) * 1000;
            unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
            $range['date']['lt'] = strtotime($condition['endDate']) * 1000;
            unset($condition['endDate']);
        }
        isset($terms['date']) && $terms['date'] = strtotime($terms['date']) * 1000;
        if (!empty($terms['subject_ids'])) {
            $subjects     = array();
            $subject_info = $this->temp_subject->getDataList(array('subject_id' => ' in(' . implode(",", $terms['subject_ids']) . ')'), $limit, "subject_id,losc_code");
            foreach ($subject_info as $subject) {
                $subjects[$subject['subject_id']] = $subject['losc_code'];
            }
            $terms['losc'] = array_values(array_unique($subjects));
            unset($terms['subject_ids']);
        }

        $losc_order = $this->sem_report_svc->getLoscOrder($terms, $range, $limit, $group, $sort, $interval);
        if (empty($losc_order['list'])) {
            $this->_errorResponse(DATA_NOT_FOUND, 'losc订单不存在');
            return;
        }
        if (!empty($subjects)) {
            foreach ($losc_order['list'] as $key => $result) {
                if ($group == 'date_histogram') {
                    foreach ($result as $k => $res) {
                        if (in_array($k, $subjects)) {
                            $subject_ids = array_keys($subjects, $k);
                            foreach ($subject_ids as $subject_id) {
                                $losc_order['list'][$key][$subject_id] = $res;
                            }
                            unset($losc_order['list'][$key][$k]);
                        }
                    }
                } else {
                    if (in_array($key, $subjects)) {
                        $subject_ids = array_keys($subjects, $key);
                        foreach ($subject_ids as $subject_id) {
                            $losc_order['list'][$subject_id] = $result;
                        }
                        unset($losc_order['list'][$key]);
                    }
                }
            }
        }
        $this->jsonResponse(array('results' => $losc_order['list'], 'total_records' => $losc_order['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $losc_order['pages']['pageCount']));
    }

    /**
     * 上传预算数据
     */
    public function upBudgetAction()
    {
        $excelData   = json_decode($this->request->getPost('excel_data'), true);
        $len         = count($excelData);
        $sliceLength = 30; //分组上传,片段长度30
        for ($i = 0; $i < $len; $i = $i + $sliceLength) {
            $excelDataSlice = array_slice($excelData, $i, $sliceLength);
            $rs[]           = $this->sem_budget->saveBudget($excelDataSlice);
        }
        $this->jsonResponse(array('results' => $rs));
    }
    /**
     * 获取费用承担部门
     */
    public function getChargeDepartAction()
    {
        $source = trim($this->request->get('source')); //数据来源
        $source = empty($source) ? "plan" : $source;
        if (!in_array($source, array("plan", "report", "all"))) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据来源不正确，请使用plan、report或all');
            return;
        }

        $rs = $this->sem_budget->getChargedepart();
        if ($source == "report" || $source == "all") {
            $res            = $this->sem_report_svc->getPromCoupon(array(), array(), array('page_num' => -1, 'page_size' => 1000), "chargeDepart", null, null);
            $charge_departs = array_keys($res['list']);
            $rs             = $source == "all" ? array_unique(array_merge($rs, $charge_departs)) : $charge_departs;
        }
        $this->jsonResponse(array('results' => $rs));
    }
    /**
     * 获取预算数据列表
     */
    public function getBudgetAction()
    {
        $condition    = $this->request->get('condition');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));

        $whereStr = "";
        if ($condition) {
            $conditionArr = json_decode($condition, true);
            if (isset($conditionArr['startDate'])) {
                $where['date'] = " `date` >='" . $conditionArr['startDate'] . "'and `date`<='" . $conditionArr['endDate'] . "'";
            }
            //按值
            if (isset($conditionArr['chargeDepart'])) {
                $str                   = implode("','", $conditionArr['chargeDepart']);
                $where['chargeDepart'] = " chargeDepart in ('" . $str . "')";
            }
            //按code
            if (isset($conditionArr['orderChannel'])) {
                $str                   = implode("','", $conditionArr['orderChannel']);
                $where['orderChannel'] = " orderChannelCode in ('" . $str . "')";
            }
            $where && $whereStr = implode(' and ', $where);
        }
        $orderStr     = $this->request->get('order');
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 15;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 1000);
        //列表
        $budgetList = $this->sem_budget->getBudgetList($whereStr, $limit, null, $orderStr);
        //总记录条数
        $budgetCount = $this->sem_budget->getBudgetTotal($whereStr);
        //金额合计
        $selectStr             = "select sum(planSaleAmount) as planSaleAmount,sum(planCouponAmount) as planCouponAmount  from sem_budget";
        $whereStr && $whereStr = " where " . $whereStr;
        $sql                   = $selectStr . $whereStr;
        $budgetAmountArr       = $this->sem_budget->getRsBySql($sql);
        $budgetAmount          = $budgetAmountArr[0];
        $pages                 = ceil($budgetCount / $page_size);
        $this->jsonResponse(array('results' => $budgetList, 'total_records' => $budgetCount, 'total_amounts' => $budgetAmount, 'page_index' => $current_page, 'total_pages' => $pages));
    }

    /**
     * 促销及优惠券报告列表
     */
    public function listPromCouponAction()
    {
        $condition    = $this->request->get('condition');
        $terms        = $this->request->get('terms');
        $page_size    = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $group        = trim($this->request->get('group'));
        $sort         = trim($this->request->get('sort'));
        $interval     = trim($this->request->get('interval')); //统计时间分段(day/week/month)
        $condition    = json_decode($condition, true);
        $terms        = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size    = $page_size ? $page_size : 10;
        $limit        = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 1000);
        $order        = $order ? $order : null;
        $sort         = json_decode($sort, true);

        $range      = array();
        $whereStr   = "1 = 1";
        $condition  = $condition ? $condition : array();
        $terms      = $terms ? $terms : array();
        $export_msg = array_merge($condition, $terms);
        if (isset($condition['startDate'])) {
            $range['date']['gte'] = strtotime($condition['startDate']) * 1000;
            $whereStr .= " and date >= '" . $condition['startDate'] . "'";
            unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
            $range['date']['lt'] = strtotime($condition['endDate']) * 1000;
            $whereStr .= " and date < '" . $condition['endDate'] . "'";
            unset($condition['endDate']);
        }
        isset($terms['date']) && $terms['date'] = strtotime($terms['date']) * 1000;

        //异步导出
        $xls_name = trim($this->request->get('xls_name'));
        if (!empty($xls_name)) {
            if (empty($export_msg['startDate']) || empty($export_msg['endDate'])) {
                $this->_errorResponse(DATA_NOT_FOUND, '请设置起止时间');
                return;
            }
            $export_cache               = array();
            $export_cache['xlsName']    = $xls_name;
            $export_cache['createTime'] = date("Y-m-d H:i:s");
            $export_cache['status']     = 0;
            $this->di->get('cas')->getRedis()->setex(
                "sem-export:" . $xls_name,
                86400 * 7,
                json_encode($export_cache)
            );
            $export_msg['xlsName'] = $xls_name;
            $kafka                 = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['stormPromExport']);
            $kafka->sendMsg(json_encode($export_msg));
            $this->jsonResponse(array('results' => $export_msg));
            return;
        }

        if (empty($group) && $limit['page_num'] > 0) {
            //进行分页处理
            if (empty($terms['chargeDepart'])) {
                $res            = $this->sem_report_svc->getPromCoupon(array(), array(), array('page_num' => -1, 'page_size' => 1000), "chargeDepart", null, null);
                $charge_departs = array_keys($res['list']);
                $charge_departs = array_unique(array_merge($charge_departs, $this->sem_budget->getChargedepart())); //费用承担部门
            } else {
                $charge_departs = $terms['chargeDepart'];
            }
            if (empty($terms['orderChannel'])) {
                $order_channels = array('驴妈妈前台', '驴妈妈后台', '无线APP', '无线WAP', '线下推广'); //下单渠道
            } else {
                $order_channels = array_intersect(array('驴妈妈前台', '驴妈妈后台', '无线APP', '无线WAP', '线下推广'), $terms['orderChannel']); //注意使用交集保证键不变
            }
            $num         = 0;
            $date_ranges = $ids = $prom_coupon = $cond = array();
            for ($d = $range['date']['gte']; $d < $range['date']['lt']; $d = $d + 86400000) {
                $date_ranges[] = date("Y-m-d", $d / 1000);
            }
//日期段
            isset($sort['date']) && $sort['date'] == "desc" && $date_ranges = array_reverse($date_ranges);
            $itemCount                                                      = count($charge_departs) * count($order_channels) * count($date_ranges);
            $start                                                          = ($limit['page_num'] - 1) * $limit['page_size'];
            $end                                                            = $limit['page_num'] * $limit['page_size'];
            foreach ($date_ranges as $date_range) {
                foreach ($charge_departs as $charge_depart) {
                    foreach ($order_channels as $key => $order_channel) {
                        if ($num++ < $start) {
                            continue;
                        }

                        if ($num > $end) {
                            break 3;
                        }

                        $cond['date'][$date_range]            = $date_range;
                        $cond['chargeDepart'][$charge_depart] = $charge_depart;
                        $cond['orderChannelCode'][$key + 1]   = $key + 1;
                        $ids[]                                = $id                                = $charge_depart . "-" . ($key + 1) . "-" . $terms['unitOfTime'] . "-" . $date_range;
                        $prom_coupon['list'][$id]             = array(
                            "id"                    => $id,
                            "bu"                    => "",
                            "buName"                => "",
                            "chargeDepartId"        => 0,
                            "chargeDepart"          => $charge_depart,
                            "orderChannelId"        => $key + 1,
                            "orderChannel"          => $order_channel,
                            "planSaleAmount"        => 0,
                            "promotionChargeAmount" => 0,
                            "promotionOrderAmount"  => 0,
                            "promotionPercent"      => 0,
                            "promotionRoi"          => 0,
                            "planCouponAmount"      => 0,
                            "couponChargeAmount"    => 0,
                            "couponOrderAmount"     => 0,
                            "couponPercent"         => 0,
                            "couponRoi"             => 0,
                            "bothPercent"           => 0,
                            "bothRoi"               => 0,
                            "date"                  => $date_range,
                        );
                    }
                }
            }
            $report_list = $this->sem_report_svc->getPromCoupon(array("_id" => $ids), null, array('page_num' => 1, 'page_size' => 1000), null, null, null);
            foreach ($report_list['list'] as $report) {
                $prom_coupon['list'][$report['id']]['bu']                    = $report['bu'];
                $prom_coupon['list'][$report['id']]['buName']                = $report['buName'];
                $prom_coupon['list'][$report['id']]['promotionChargeAmount'] = $report['promotionChargeAmount'];
                $prom_coupon['list'][$report['id']]['promotionOrderAmount']  = $report['promotionOrderAmount'];
                $prom_coupon['list'][$report['id']]['promotionRoi']          = $report['promotionChargeAmount'] ? round($report['promotionOrderAmount'] / $report['promotionChargeAmount'], 4) : 0;
                $prom_coupon['list'][$report['id']]['couponChargeAmount']    = $report['couponChargeAmount'];
                $prom_coupon['list'][$report['id']]['couponOrderAmount']     = $report['couponOrderAmount'];
                $prom_coupon['list'][$report['id']]['couponRoi']             = $report['couponChargeAmount'] ? round($report['couponOrderAmount'] / $report['couponChargeAmount'], 4) : 0;
                $prom_coupon['list'][$report['id']]['bothRoi']               = ($report['couponChargeAmount'] + $report['promotionChargeAmount'] > 0) ?
                round(($report['promotionOrderAmount'] + $report['couponOrderAmount']) / ($report['couponChargeAmount'] + $report['promotionChargeAmount']), 4) : 0;
            }
            $whereStr = "1 = 1";
            foreach ($cond as $key => $val) {
                $whereStr .= " and " . $key . " in('" . implode("','", $val) . "')";
            }

            $budget_list = $this->sem_budget->getBudgetList($whereStr, array('page_num' => 1, 'page_size' => 1000), null, null);
            foreach ($budget_list as $budget) {
                $_id = $budget['chargeDepart'] . "-" . $budget['orderChannelCode'] . "-" . $terms['unitOfTime'] . "-" . $budget['date'];
                if (!isset($prom_coupon['list'][$_id])) {
                    continue;
                }

                $prom_coupon['list'][$_id]['planSaleAmount']   = round($budget['planSaleAmount'], 2);
                $prom_coupon['list'][$_id]['promotionPercent'] = $budget['planSaleAmount'] ? round($prom_coupon['list'][$_id]['promotionChargeAmount'] / $budget['planSaleAmount'], 4) : 0;
                $prom_coupon['list'][$_id]['planCouponAmount'] = round($budget['planCouponAmount'], 2);
                $prom_coupon['list'][$_id]['couponPercent']    = $budget['planCouponAmount'] ? round($prom_coupon['list'][$_id]['couponChargeAmount'] / $budget['planCouponAmount'], 4) : 0;
                $prom_coupon['list'][$_id]['bothPercent']      = ($budget['planSaleAmount'] + $budget['planCouponAmount'] > 0) ?
                round(($prom_coupon['list'][$_id]['promotionChargeAmount'] + $prom_coupon['list'][$_id]['couponChargeAmount']) / ($budget['planSaleAmount'] + $budget['planCouponAmount']), 4) : 0;
            }
            $prom_coupon['pages']['itemCount'] = $itemCount;
            $prom_coupon['pages']['pageCount'] = intval(($itemCount - 1) / (empty($limit['page_size']) ? 10 : $limit['page_size']) + 1);
        } else {
            $prom_coupon = $this->sem_report_svc->getPromCoupon($terms, $range, $limit, $group, $sort, $interval);
            if (empty($group)) {
                //金额计划合计
                $selectStr                    = "select sum(planSaleAmount) as planSaleAmount,sum(planCouponAmount) as planCouponAmount  from sem_budget";
                empty($whereStr) && $whereStr = "1 = 1";
                !empty($terms['chargeDepart']) && $whereStr .= " and chargeDepart in('" . implode("','", $terms['chargeDepart']) . "')";
                !empty($terms['orderChannel']) && $whereStr .= " and orderChannel in('" . implode("','", $terms['orderChannel']) . "')";
                !empty($terms['orderChannelCode']) && $whereStr .= " and orderChannelCode in('" . implode("','", $terms['orderChannelCode']) . "')";
                $whereStr && $whereStr            = " where " . $whereStr;
                $sql                              = $selectStr . $whereStr;
                $budgetAmountArr                  = $this->sem_budget->getRsBySql($sql);
                $budgetAmount                     = $budgetAmountArr[0];
                $budgetAmount['planSaleAmount']   = intval($budgetAmount['planSaleAmount']);
                $budgetAmount['planCouponAmount'] = intval($budgetAmount['planCouponAmount']);
                $prom_coupon['list']              = array_merge($prom_coupon['list'], $budgetAmount);
            }
            if (empty($prom_coupon['list'])) {
                $this->_errorResponse(DATA_NOT_FOUND, '促销及优惠券报告不存在');
                return;
            }
        }
        $this->jsonResponse(array('results' => $prom_coupon['list'], 'total_records' => $prom_coupon['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $prom_coupon['pages']['pageCount']));
    }

    /**
     * 获取账户某个日期的消费数据
     * @author lixiumeng
     * @datetime 2018-01-03T09:32:57+0800
     * @return   [type]                   [description]
     */
    public function getSemAccountCostAction()
    {
        $date    = $this->request->get('date');
        $padding = $this->request->get('padding');

        if (empty($padding)) {
            $padding = 0;
        } else {
            $padding = 1;
        }
        $date = empty($date) ? date('Y-m-d', time() - 3600 * 24) : date("Y-m-d 00:00:00", strtotime($date));

        $condition = [
            'date' => $date,
        ];

        if (!preg_match('/20[0-9][0-9]-[0|1][0-9]-[0-3][0-9]/', $date)) {
            $this->jsonResponse(['error' => 1000, 'msg' => '请输入正确的日期格式:2017-12-31']);
        }
        $rt = $this->sem_report_svc->getAccountCostByAcccount($date, $padding);

        $this->jsonResponse($rt);
    }

    /**
     * 统计sem账户消费报表
     * @author lixiumeng
     * @datetime 2018-03-02T13:48:42+0800
     * @return   [type]                   [description]
     */
    public function statisticsSemAccountAction()
    {
        $this->csrv   = $this->di->get('cas')->get('sem_reoprt_all_service'); // 当前使用的service

        $date = $this->request->get('date');

        if (!preg_match('/^20[0-9][0-9]-[0|1][0-9]-[0-3][0-9]$/', $date)) {
            $this->jsonResponse(['error' => 1000, 'msg' => '请输入正确的日期格式:2017-12-31']);
        }

        $rt = $this->csrv->statisticsAccountCost($date);
        $this->jsonResponse($rt);
    }
}
