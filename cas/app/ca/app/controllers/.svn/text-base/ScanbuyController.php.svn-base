<?php

/**
 * 扫码购数据统计控制器
 * 
 * @author flash.guo
 *
 */
class ScanbuyController extends ControllerBase {
    private $scan_report_svc;
    public function initialize() {
        parent::initialize();
        $this->scan_report_svc = $this->di->get('cas')->get('scan_report_service');
    }
	
	/**
	 * 扫码购报告列表
	 */
	public function listReportAction() {
        $condition = $this->request->get('condition');
        $terms = $this->request->get('terms');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $group = trim($this->request->get('group'));
        $sort = trim($this->request->get('sort'));
        $interval = trim($this->request->get('interval'));//统计时间分段(day/week/month)
        $condition = json_decode($condition, true);
        $terms = json_decode($terms, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 10000);
        $sort = json_decode($sort, true);
        
        $range = array();
        $condition = $condition ? $condition : array();
        $terms = $terms ? $terms : array();
        if (isset($condition['startDate'])) {
        	$range['paymentTime']['gte'] = strtotime($condition['startDate'])*1000;
        	unset($condition['startDate']);
        }
        if (isset($condition['endDate'])) {
        	$range['paymentTime']['lt'] = strtotime($condition['endDate'])*1000;
        	unset($condition['endDate']);
        }
        isset($terms['paymentTime']) && $terms['paymentTime'] = strtotime($terms['paymentTime'])*1000;
        //用于判断是否新注册用户
        if (isset($condition['startRegDate'])) {
        	$range['registerDate']['gte'] = strtotime($condition['startRegDate'])*1000;
        	unset($condition['startRegDate']);
        }
        if (isset($condition['endRegDate'])) {
        	$range['registerDate']['lt'] = strtotime($condition['endRegDate'])*1000;
        	unset($condition['endRegDate']);
        }
        isset($terms['registerDate']) && $terms['registerDate'] = strtotime($terms['registerDate'])*1000;
        
        if ($group) {
        	$report_list = $this->scan_report_svc->getReportList($terms, $range, $limit, $group, $sort, $interval);
	        if(empty($report_list)) {
	        	$this->_errorResponse(DATA_NOT_FOUND,'扫码购报告不存在');
	        	return;
	        }
	        $total_records = empty($total_records) ? intval($report_list['pages']['itemCount']) : $total_records;
	        $total_pages = empty($total_pages) ? intval($report_list['pages']['pageCount']) : $total_pages;
        	$this->jsonResponse(array('results' => $report_list['list'], 'total_records' => intval($total_records), 'page_index' => $current_page, 'total_pages' => $total_pages));
        } else {
	        $report_list = $this->scan_report_svc->getReportList($terms, $range, $limit, $group, $sort, $interval);
	        if(empty($report_list)) {
	        	$this->_errorResponse(DATA_NOT_FOUND,'扫码购报告不存在');
	        	return;
	        }
	        $this->jsonResponse(array('results' => $report_list['list'], 'total_records' => $report_list['pages']['itemCount'], 'page_index' => $current_page, 'total_pages' => $report_list['pages']['pageCount']));
        }
	}
}

