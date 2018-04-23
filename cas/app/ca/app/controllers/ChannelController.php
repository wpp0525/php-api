<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\UCommon as UCommon;
use Lvmama\Cas\Component\Kafka\Producer;

/**
 * 频道页 控制器
 *
 * @author gubuchun
 *
 */
class ChannelController extends ControllerBase
{

    private $redis;
    private $web_site;
    private $temp_channel;
    private $temp_channel_variable;

    public function initialize()
    {
        parent::initialize();
        $this->redis_svc = $this->di->get('cas')->get('redis_data_service');
        $this->redis = $this->di->get('cas')->getRedis();
        $this->web_site = $this->di->get('cas')->get('sub_web_site');
        $this->temp_channel = $this->di->get('cas')->get('temp_channel');
        $this->temp_channel_variable = $this->di->get('cas')->get('temp_channel_variable');
    }

    /***
     * 频道列表
     */
    public function channelListAction()
    {
        $where = $this->request->get('where');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $select = trim($this->request->get('select'));
        $order = trim($this->request->get('order'));
        $where = json_decode($where, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 500);
        $order = $order ? $order : null;
        $channel_info = $this->temp_channel->getDataList($where, $limit, $select, $order);
        if (empty($channel_info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $channel_info));
            return;
        }
        $total_records = $this->temp_channel->getTotal($where);
        $total_pages = intval(($total_records - 1) / $page_size + 1);
        $this->jsonResponse(array(
            'results' => $channel_info,
            'total_records' => intval($total_records),
            'page_index' => $current_page,
            'total_pages' => $total_pages
        ));
    }

    /***
     * 频道详情 频道id
     */
    public function channelOneAction()
    {
        $id = intval($this->request->get('id'));
        $where = array();
        !empty($id) && $where['channel_id'] = "=" . $id;
        !empty($where) && $info = $this->temp_channel->getDataOne($where);
        if (empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '数据不存在');
            return;
        }
        $this->_successResponse($info);
    }

    /***
     * 频道是否存在
     */
    public function channelTotalAction()
    {
        $where = $this->request->get('where');
        $where_arr = json_decode($where, true);
        !empty($where) && $info = $this->temp_channel->getTotal($where_arr);

        $this->_successResponse($info ? $info : 0);
    }

    /***
     * 频道增加
     */
    public function channelAddAction()
    {
        $post = $this->request->getPost();
        unset($post['api']);
        if (!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->temp_channel->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '模板新增失败');
            return;
        }
        $this->_successResponse($result);
    }

    /***
     * 频道更新
     */
    public function channelUpdateAction()
    {
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post) && !empty($id)) {
            $info = $this->temp_channel->getDataOne($id);
            if (!$info) $this->_errorResponse(OPERATION_FAILED, '关键字不存在');

            if (isset($info['template_id']) && isset($post['template_id']) && $info['template_id'] != $post['template_id']) {
                $this->temp_channel_variable->delAllVarByCid($id);
            }

            $post['update_time'] = time();
            $result = $this->temp_channel->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '模板更新失败');
            return;
        }
        $this->_successResponse($result);
    }

    /***
     * 频道删除
     */
    public function channelDeleteAction()
    {
        $id = intval($this->request->get('id'));
        if (!empty($id)) {
            $result = $this->temp_channel->delete($id);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '模板删除失败');
            return;
        }
        $this->_successResponse($result);
    }

    /************频道变量设置***********/
    /**
     * 获取频道所有变量
     */
    public function getVarAction()
    {
        $cid = intval($this->request->get('cid'));
        if (empty($cid)) {
            $this->_errorResponse(DATA_NOT_FOUND, '频道变量不存在');
            return;
        }
        $condition['channel_id'] = "=" . $cid;
        $channel_vars = $this->temp_channel_variable->getVarList($condition);
        if (empty($channel_vars)) {
            $this->_errorResponse(DATA_NOT_FOUND, '频道变量不存在');
            return;
        }
        $this->jsonResponse(array('results' => $channel_vars));
    }

    /**
     * 获取频道所有变量
     */
    public function getVariableAction()
    {
        $variable_id = intval($this->request->get('variable_id'));
        $channel_id = intval($this->request->get('channel_id'));
        $variable_name = trim($this->request->get('variable_name'));

        !empty($variable_id) && $condition['id'] = "=" . $variable_id;
        !empty($channel_id) && $condition['channel_id'] = "=" . $channel_id;
        !empty($variable_name) && $condition['variable_name'] = "='" . $variable_name . "'";
        $channel_vars = $this->temp_channel_variable->getVarList($condition);
        if (empty($channel_vars)) {
            $this->_errorResponse(DATA_NOT_FOUND, '频道变量不存在');
            return;
        }
        $this->jsonResponse(array('results' => $channel_vars));
    }

    /**
     * 频道变量新增
     */
    public function addVarAction()
    {
        $post = $this->request->getPost();

        unset($post['api']);
        if (!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->temp_channel_variable->insert($post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '频道变量新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result, 'error' => 0));
    }

    /**
     * 频道变量更新
     */
    public function updateVarAction()
    {
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if (!empty($post)) {
            $post['update_time'] = time();
            $result = $this->temp_channel_variable->update($id, $post);
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '频道变量更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 设置频道变量
     */
    public function setVarAction()
    {
        $post = $this->request->getPost();

        $var = $vars = $varids = $varnames = $varcnts = array();
        $cid = intval($post['cid']);
        if (empty($cid)) {
            $this->_errorResponse(DATA_NOT_FOUND, '频道信息不存在');
            return;
        }
        $condition['channel_id'] = "=" . $cid;
        $keyword_vars = $this->temp_channel_variable->getVarList($condition);
        foreach ($keyword_vars as $keyword_var) {
            $vars[] = $keyword_var['variable_name'];
            $varids[$keyword_var['variable_name']] = $keyword_var['variable_id'];
        }
        $vardatas = empty($post['vars']) ? array() : json_decode($post['vars'], true);
        foreach ($vardatas as $vardata) {
            $varnames[] = $vardata['variable_name'];
            $varcnts[$vardata['variable_name']] = $vardata;
        }
        $result = true;
        $bothvarnames = array_intersect($varnames, $vars);
        foreach ($bothvarnames as $varname) {
            $varid = $varids[$varname];
            $var['channel_id'] = $cid;
            $var['variable_name'] = trim($varname);
            $var['module_id'] = intval($varcnts[$varname]['module_id']);
            $var['variable_content'] = trim($varcnts[$varname]['variable_content']);
            $var['update_time'] = time();
            $result = $this->temp_channel_variable->update($varid, $var);
        }
        $newvarnames = array_diff($varnames, $vars);
        foreach ($newvarnames as $varname) {
            $var['channel_id'] = $cid;
            $var['variable_name'] = trim($varname);
            $var['module_id'] = intval($varcnts[$varname]['module_id']);
            $var['variable_content'] = trim($varcnts[$varname]['variable_content']);
            $var['create_time'] = $var['update_time'] = time();
            $result = $this->temp_channel_variable->insert($var);
        }
        $oldvarnames = array_diff($vars, $varnames);
        foreach ($oldvarnames as $varname) {
            $result = $this->temp_channel_variable->delVarByCid($cid, trim($varname));
        }
        if (empty($result)) {
            $this->_errorResponse(OPERATION_FAILED, '设置频道变量失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

}