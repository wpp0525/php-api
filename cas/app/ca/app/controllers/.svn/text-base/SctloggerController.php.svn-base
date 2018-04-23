<?php

use Lvmama\Common\Utils\UCommon;

/**
 * SCT后台日志 控制器
 *
 * @author jianghu
 *
 */
class SctloggerController extends ControllerBase
{
    private $table_prefix = 'sct_logs';
    private $sct_logger_svc;
    private $sys_core;
    private $_table;

    public function initialize()
    {
        parent::initialize();
        $this->sct_logger_svc = $this->di->get('cas')->get('sct_logger_service');

        $this->sys_core = $this->di->get('cas')->get('sct_system_core');

        $this->create_table();
    }

    /**
     * 新增日志
     */
    public function addLoggerAction()
    {
        $action = $this->request->getPost('action');
        $controller = $this->request->getPost('controller');
        if (!$action)
            return false;
        if (stripos($action, 'ajax') === false) {
            $controller_1 = ucfirst($controller) . 'Controller';
            $action_1 = $action . 'Action';
            $where = "method = '{$action_1}' AND class_name = '{$controller_1}'";
            $log_data = $this->sys_core->getDataByConditionSrt('cms_action', 'log_status', $where, 'one');
            if (!$log_data || $log_data['log_status'] == 'N')
                return false;
        }
        $channel = $this->sys_core->getMenuIdByController($controller);
        $params = array(
            'table' => $this->_table,
            'data' => array(
                'uid' => $this->request->getPost('uid'),
                'username' => $this->request->getPost('username'),
                'controller' => $controller,
                'action' => $action,
                'url' => $this->request->getPost('url'),
                'post' => $this->request->getPost('post'),
                'get' => $this->request->getPost('get'),
                'create_time' => $this->request->getPost('create_time'),
                'ip' => $this->request->getPost('ip'),
                'channel' => $channel,
                'tbname' => $this->_table,
            )
        );
        $this->sct_logger_svc->insert($params);
    }

    /**
     * 返回日志列表
     */
    public function getLogListAction()
    {
        $start_time = $this->request->getPost('start_time');
        $end_time = $this->request->getPost('end_time');
        $uid = $this->request->getPost('uid');
        $username = $this->request->getPost('username');
        $controller = $this->request->getPost('controller');
        $action = $this->request->getPost('action');
        $channel = $this->request->getPost('channel');

        $controller_lc = lcfirst($controller);
        $controller_sub = strstr($controller_lc, 'Controller', true);
        $action_sub = strstr($action, 'Action', true);
        $where = array();
        if ($uid)
            $where[] = "uid = '{$uid}'";
        if ($username)
            $where[] = "username = '{$username}'";
        if ($controller)
            $where[] = "controller = '{$controller_sub}'";
        if ($action)
            $where[] = "(action = '{$action}' OR action = '{$action_sub}')";
        if ($channel)
            $where[] = "channel = '{$channel}'";
        if ($start_time)
            $where[] = "create_time >= '{$start_time}'";
        if ($end_time)
            $where[] = "create_time < '{$end_time}'";
        $where_str = implode(' AND ', $where);
        $params = array(
            'table_prefix' => $this->table_prefix,
            'select' => '`logs_id`,`username`,`controller`,`action`,`ip`,`channel`,`post`,`get`,`create_time`',
            'where' => $where_str,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'page' => array('page' => $this->request->getPost('page'), 'pageSize' => $this->request->getPost('page_size')),
        );
        $log_data = $this->sct_logger_svc->getLogList($params);
        $this->jsonResponse($log_data);
    }

    /**
     * 返回一条用户操作记录
     */
    public function getLogDataByLogIdAction()
    {
        $params = array(
            'table' => $this->_table,
            'select' => '*',
            'where' => array('logs_id' => $this->request->getPost('logs_id')),
            'limit' => 1
        );
        $log_data = $this->sct_logger_svc->select($params);

        $this->jsonResponse(array('result' => $log_data['list'] ? $log_data['list']['0'] : array()));
    }

    /**
     * 构造当前月份的日志表
     */
    private function create_table()
    {
        $tbname = $this->table_prefix . '_' . date('Ym', time());
        $this->sct_logger_svc->createTable($tbname);
        $this->_table = $tbname;
    }
}
