<?php 
use Lvmama\Cas\Service\RedisDataService;
/**
* 优惠券
* @author gaochunzheng
*/

class CouponController extends ControllerBase
{
    private $sj_template_subject_coupon_svc;
    private $temp_subject_coupon_rel;
	private $sj_template_subject_coupon_records_svc;
    private $tsrv_svc;
    protected $redis_svc;

	public function initialize()
    {
		parent::initialize();
        $this->sj_template_subject_coupon_svc = $this->di->get('cas')->get('sj_template_subject_coupon_service');
        $this->temp_subject_coupon_rel = $this->di->get('cas')->get('temp_subject_coupon_rel');
		$this->sj_template_subject_coupon_records_svc = $this->di->get('cas')->get('sj_template_subject_coupon_records_service');
        $this->tsrv_svc = $this->di->get('tsrv');
        $this->redis_svc = $this->di->get('cas')->get('redis_data_service');
	}

	/**
	 * 优惠券列表
	 */
	public function listAction()
	{
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns = trim($this->request->get('columns'));
        $order = trim($this->request->get('order'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 5);
        $order = $order ? $order : 'id desc';
        $coupon_list = $this->sj_template_subject_coupon_svc->getCouponList($condition, $limit, $columns, $order);
        if(empty($coupon_list)) {
        	$this->_errorResponse(DATA_NOT_FOUND, '优惠券列表为空！');
        	return;
        }
		
        if (!isset($_REQUEST['current_page'])) {
        	$this->jsonResponse(array('results' => $coupon_list));
        	return;
        }
        $coupon_total = $this->sj_template_subject_coupon_svc->getCouponTotal($condition);
        $total_pages = intval(($coupon_total-1)/$page_size+1);

        $this->jsonResponse(array('results' => $coupon_list, 'coupon_total' => intval($coupon_total), 'page_index' => $current_page, 'total_pages' => $total_pages));
	}

	/**
	 * 新增优惠券
	 */
	public function addAction()
	{
		$result = array();
		$post = $this->request->getPost();
        unset($post['api']);

        if(!empty($post['start_time'])){
            $post['start_time'] = strtotime($post['start_time']);
        }

        if(!empty($post['end_time'])){
            $post['end_time'] = strtotime($post['end_time']);
        }

        if(!empty($post)) {
        	$post['create_time'] = $post['update_time'] = time();
        	$result = $this->sj_template_subject_coupon_svc->insertCoupon($post);
        }
        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'优惠券新增失败！');
        	return;
        }
        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 编辑优惠券
	 */
	public function editAction()
	{
		$result = array();
		$post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);

        if(!empty($post['start_time'])){
            $post['start_time'] = strtotime($post['start_time']);
        }

        if(!empty($post['end_time'])){
            $post['end_time'] = strtotime($post['end_time']);
        }

        $info = $this->sj_template_subject_coupon_svc->getCouponById($id);

        if(!empty($info['result'])){
			$this->_errorResponse(OPERATION_FAILED, '优惠券不存在！');
			return;
        }
        
        if(!empty($post)) {
        	$post['update_time'] = time();
        	$result = $this->sj_template_subject_coupon_svc->updateCouponById($id, $post);
        }

        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'优惠券更新失败！');
        	return;
        }

        $this->jsonResponse(array('result' => $result));
	}

	/**
	 * 优惠券删除
	 */
	public function deleteAction()
	{
		$result = array();
        $post = $this->request->getPost();
        $id = intval($post['id']);

        if(empty($id)) {
        	$this->_errorResponse(OPERATION_FAILED, '优惠券ID不能为空！');
            return;
        }

        $data = array('status' => 0);

        $result = $this->sj_template_subject_coupon_svc->deleteCouponById($id, $data);

        if(empty($result)) {
        	$this->_errorResponse(OPERATION_FAILED,'优惠券删除失败！');
        	return;
        }

        $this->jsonResponse(array('result' => $result));
	}

    public function couponByIdAction()
    {
        $result = array();
        //$id = intval($this->request->get('id'));
        $post = $this->request->getPost();
        $id = intval($post['id']);
        
        if(empty($id)) {
            $this->_errorResponse(OPERATION_FAILED, '优惠券ID不能为空！');
            return;
        }

        $result = $this->sj_template_subject_coupon_svc->getCouponById($id);
        if(empty($result)){
            $this->_errorResponse(OPERATION_FAILED, '优惠券不存在！');
            return;
        }

        $this->jsonResponse(array('result' => $result));
    }

    public function couponReceiveAction()
    {
        $result = array();

        $data['coupon_id'] = $this->request->get('coupon_id');

        if(empty($data['coupon_id'])) {
            $this->_errorResponse(OPERATION_FAILED, '优惠券ID不能为空！');
            return;
        }

        $data['subject_id'] = $this->request->get('subject_id');

        $data['uid'] = $this->request->get('user_id');
        if(empty($data['uid'])) {
            $this->_errorResponse(OPERATION_FAILED, '用户ID不能为空！');
            return;
        }

        $data['relation_id'] = $this->request->get('relation_id');
        if(empty($data['relation_id'])) {
            $this->_errorResponse(OPERATION_FAILED, '关联ID不能为空！');
            return;
        }

        $data['username'] = $this->request->get('user_name');

        if(!empty($data)) {
            $data['create_time'] = time();
            $result = $this->sj_template_subject_coupon_records_svc->insertCouponRecords($data);
        }

        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'优惠券领取记录新增失败！');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 优惠券列表
     */
    public function couponReceiveListAction()
    {
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns = trim($this->request->get('columns'));
        $order = trim($this->request->get('order'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 5);
        $order = $order ? $order : 'id desc';
        $coupon_list = $this->sj_template_subject_coupon_records_svc->getCouponReceiveList($condition, $limit, $columns, $order);
        if(empty($coupon_list)) {
            $this->_errorResponse(DATA_NOT_FOUND, '优惠券领取记录列表为空！');
            return;
        }
        
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $coupon_list));
            return;
        }
        $coupon_total = $this->sj_template_subject_coupon_records_svc->getCouponReceiveTotal($condition);
        $total_pages = intval(($coupon_total-1)/$page_size+1);

        $this->jsonResponse(array('results' => $coupon_list, 'coupon_total' => intval($coupon_total), 'page_index' => $current_page, 'total_pages' => $total_pages));
    }

    /**
     * 优惠券列表导出
     */
    public function listReportAction(){
        $condition = $this->request->get('condition');
        $page_size = intval($this->request->get('page_size'));
        $current_page = intval($this->request->get('current_page'));
        $columns = trim($this->request->get('columns'));
        $order = trim($this->request->get('order'));
        $group = trim($this->request->get('group'));
        $sort = trim($this->request->get('sort'));
        $have_report = intval($this->request->get('have_report'));
        $condition = json_decode($condition, true);
        $current_page = $current_page ? $current_page : 1;
        $page_size = $page_size ? $page_size : 10;
        $limit = isset($_REQUEST['current_page']) ? array('page_num' => $current_page, 'page_size' => $page_size) : array('page_num' => 1, 'page_size' => 10000);
        $order = $order ? $order : 'id desc';
        $sort = json_decode($sort, true);
        
        $range = $export_msg = array();

        $export_msg = $condition;
        
        //异步导出
        $xls_name = trim($this->request->get('xls_name'));

        if (!empty($xls_name)) {
            $report_list = $this->sj_template_subject_coupon_records_svc->getCouponReceiveList($condition, $limit, $columns, $order);
            
            if(empty($report_list)) {
                $this->_errorResponse(DATA_NOT_FOUND, '优惠券领取记录不存在!');
                return;
            }
            $export_cache = array();
            $export_cache['xlsName'] = $xls_name;
            $export_cache['createTime'] = date("Y-m-d H:i:s");
            $export_cache['status'] = 0;
            $this->di->get('cas')->getRedis()->setex(
                "sem-export:".$xls_name,
                86400*7,
                json_encode($export_cache)
            );
            $export_msg['group'] = $group;
            $export_msg['xlsName'] = $xls_name;
            $kafka = new \Lvmama\Cas\Component\Kafka\Producer($this->di->get("config")->kafka->toArray()['stormExport']);
            $this->jsonResponse(array('results' => $export_msg));
            return;
        }
    }

    public function checkCouponsAction()
    {
        $user_no = trim($this->request->get('user_no'));
        $subject_id = intval($this->request->get('subject_id'));
        $coupon_id = intval($this->request->get('coupon_id'));
        if(empty($user_no) || empty($subject_id) || empty($coupon_id)){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
        }
        $time = time();
        //从缓存中取出优惠券信息
        $info = $this->redis_svc->dataGet("subject-coupon:" . $coupon_id);
        if(empty($info)){
            //优惠券信息不在缓存中
            $info = $this->sj_template_subject_coupon_svc->getCouponById($coupon_id);
            if(empty($info)){
                $this->_errorResponse(OPERATION_FAILED, '优惠券不存在！');
                return;
            }else{
                //将优惠券信息存入缓存中
                $this->redis_svc->dataSet(
                    "subject:coupon:" . $coupon_id,
                    json_encode($info),
                    300);
            }
        }else{
            $info = json_decode($info, true);
        }
        //判断优惠券是否有效
        if(empty($info['status'])){
            $this->jsonResponse($info);
            $this->_errorResponse(OPERATION_FAILED, '优惠券无效！');
            return;
        }

        //判断优惠券是否在有效时间范围内
        if($info['start_time'] > $time || $info['end_time']+86400 < $time){
            $this->_errorResponse(OPERATION_FAILED, '优惠券领取活动不在规定时间范围内！');
            return;
        }

        //判断用户是否已经领取优惠券
        $coupon_records = $this->sj_template_subject_coupon_records_svc->getCouponRecords($user_no, $subject_id,$info['coupon_numb']);
        if(!empty($coupon_records)){
            $this->_errorResponse(OPERATION_FAILED, '已经领取优惠券，不能重复领取！');
            return;
        }

        $this->jsonResponse(array('results' => 1));
        return;
    }

    public function receiveCouponsAction()
    {
        $user_id = 0;
        $user_no = trim($this->request->get('user_no'));
        $coupon_numb = intval($this->request->get('coupon_numb'));
        $subject_id = intval($this->request->get('subject_id'));
        if(empty($user_no) || empty($subject_id) || empty($coupon_numb)){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
        }
        try{
            $user_info = $this->redis_svc->dataGet("subject:userInfo:" . $user_no);
            if(empty($user_info)){
                //用户信息不在缓存中,获取用户信息
                $params = array('userNo' => $user_no);
                $user_info = $this->tsrv_svc->exec('user/getByUserNo', $params);
            }else{
                $user_info = json_decode($user_info, true);
            }
            if(!empty($user_info)){
                //将用户信息存入缓存中
                $this->redis_svc->dataSet("subject:userInfo:" . $user_no, json_encode($user_info), 300);
                $user_id = $user_info['id'];
            }
            $data = array();
            if(!empty($user_id)){
                //优惠券绑定用户
                $data = $this->tsrv_svc->exec('user/bindingCouponToUser', array('params' => '{"userId":"'.$user_id.'", "couponId":"'. $coupon_numb .'"}'));

                $data = json_decode(str_replace("'", '"', $data), true);

                $record_data = array();
                if(!empty($data['success'])){
                    //记录领取记录
                    $record_data['relation_id'] = $data['result'];
                    $record_data['subject_id'] = $subject_id;
                    $record_data['uid'] = $user_no;
                    $record_data['coupon_id'] = $coupon_numb;
                    $record_data['username'] = $user_info['userName'];
                    $record_data['create_time'] = time();

                    $result = $this->sj_template_subject_coupon_records_svc->insertCouponRecords($record_data);
                    $this->jsonResponse(array('results' => '领取成功！', 'success' => true));
                }else{
                    $this->jsonResponse(array('results' => '领取失败！', 'success' => false));
                }
            }
        }catch(\Exception $e){
            $this->jsonResponse(array('results' => '领取失败！', 'success' => false));
        }
    }

    //批量领取验证
    public function checkBatchCouponsAction()
    {
        $user_no = trim($this->request->get('user_no'));
        $subject_id = intval($this->request->get('subject_id'));
        if (empty($user_no) || empty($subject_id)) {
            $this->_errorResponse(PARAMS_ERROR, '参数错误');
        }
        //已绑定优惠券
        $info = $this->temp_subject_coupon_rel->getBindValidCouponNum($subject_id);

        if(empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND, '未绑定有效优惠券');
        }
        //判断用户是否已经领取优惠券
        $coupon_records = $this->sj_template_subject_coupon_records_svc->getCouponRecordsBat($user_no, $subject_id);
        if(!empty($coupon_records)){
            $this->_errorResponse(OPERATION_FAILED, '已经领取优惠券，不能重复领取！');
            return;
        }

        $this->jsonResponse(array('results' => 200));
        return;
    }
    //批量领取
    public function receiveBatchCouponsAction()
    {
        $user_id = 0;
        $user_no = trim($this->request->get('user_no'));
        $subject_id = intval($this->request->get('subject_id'));
        if(empty($user_no) || empty($subject_id)){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
        }
        try{
            //用户信息不在缓存中,获取用户信息
            $params = array('userNo' => $user_no);
            $user_info = $this->tsrv_svc->exec('user/getByUserNo', $params);
            $data = array();
            if(!empty($user_info['id'])){
                //已绑定优惠券
                $info = $this->temp_subject_coupon_rel->getBindValidCouponList($subject_id);
                $okIds = $errorIds = array();
                foreach($info as $value){
                    //优惠券绑定用户
                    $data = $this->tsrv_svc->exec('user/bindingCouponToUser', array(
                        'params' => '{"userId":"'.$user_id.'", "couponId":"'. $value['coupon_numb'] .'"}'
                    ));

                    $data = json_decode(str_replace("'", '"', $data), true);

                    $record_data = array();

                    if(!empty($data['success'])){
                        //记录领取记录
                        $record_data['relation_id'] = $data['result'];
                        $record_data['subject_id'] = $subject_id;
                        $record_data['uid'] = $user_no;
                        $record_data['coupon_id'] = $value['coupon_numb'];
                        $record_data['username'] = $user_info['userName'];
                        $record_data['create_time'] = time();

                        $okIds[]=$value['coupon_numb'];
                        $this->sj_template_subject_coupon_records_svc->insertCouponRecords($record_data);
                    }else{
                        $errorIds[]=$value['coupon_numb'];
                    }
                }
                $this->jsonResponse(array('results' => 200, 'success' => array('ok'=>$okIds,'error'=>$errorIds)));
            }
        }catch(\Exception $e){
            $this->jsonResponse(array('results' => 500, 'success' => false));
        }
    }

    /**
     * 用户领取优惠券详情 2017/08/01
     * @param user_no string 32位用户id
     * @param subject_id int 专题id
     * @param coupon array 优惠券批次号
     * @return json
     */
    public function getCouponReceiveInfoAction(){
        $user_no = trim($this->request->get('user_no'));
        $subject_id = intval($this->request->get('subject_id'));
        $coupon = json_decode($this->request->get('coupon'),true);
        if (empty($user_no) || empty($coupon) || empty($subject_id)) {
            $this->_errorResponse(PARAMS_ERROR, '参数错误');
        }
        foreach($coupon as $k=>$v){
            foreach($v as $key=>$value){
            //已领取数量
            $condition = "`uid`='".$user_no."' AND `subject_id` = ".$subject_id." AND `coupon_id` IN (".$value['coupon'].")";
            $records_num = $this->sj_template_subject_coupon_records_svc->getCouponReceiveTotal($condition);
            if(!empty($records_num)) $coupon[$k][$key]['receive'] = 1;
        }}

        $this->_successResponse($coupon);
    }

    /***
     * 优惠券领取 2017/07/25
     * @param user_no string 32位用户id
     * @param subject_id int 专题id
     * @param couponIds string 优惠券批次号逗号分割
     * @return json
     */
    public function drawByBatchCouponAction(){
        $user_no = trim($this->request->get('user_no'));
        $subject_id = intval($this->request->get('subject_id'));
        $coupon_ids = $this->request->get('coupon_ids');
        if (empty($user_no) || empty($coupon_ids) || empty($subject_id)) {
            $this->_errorResponse(PARAMS_ERROR, '参数错误');
        }

        $couponId = explode(',',$coupon_ids);
        if(empty($couponId) || empty($couponId[0])){
            $this->_errorResponse(PARAMS_ERROR, '优惠券批次号不存在');
        }

        //优惠券批次号数量
        $coupon_num = count($couponId);
        //已领取数量
        $condition = "`uid`='".$user_no."' AND `subject_id` = ".$subject_id." AND `coupon_id` IN (".$coupon_ids.")";
        $records_num = $this->sj_template_subject_coupon_records_svc->getCouponReceiveTotal($condition);
        if($records_num >= $coupon_num){
            $this->_errorResponse(302, '优惠券已领过');
        }
        $records_list = $this->sj_template_subject_coupon_records_svc->getCouponReceiveList($condition,NULL,'coupon_id');
        foreach($records_list as $item){
            $exist = array_search($item['coupon_id'],$couponId);
            if($exist) {
                unset($couponId[$exist]);
            }
        }
        //用户信息 取短id
        $user_info = $this->getUserInfo($user_no);
        $user_id = $user_info['id'];
        //领取优惠券
        if(!empty($user_id)){
            //记录失败优惠券id 并返回
            $defeated_coupon_id = array();
            foreach($couponId as $item){
                //查询用户已经领取数量
                $count_data = $this->tsrv_svc->exec('user/countUserCoupon', array('params' => '{"userId":"'.$user_id.'", "couponId":"'. $item .'"}'));
                if($count_data['success']==1){
                    //优惠券绑定用户
                    $data = $this->tsrv_svc->exec('user/bindingCouponToUser', array('params' => '{"userId":"'.$user_id.'", "couponId":"'. $item .'"}'));
                    $data = json_decode(str_replace("'", '"', $data), true);

                    $record_data = array();
                    if(!empty($data['success'])){
                        //记录领取记录
                        $record_data['relation_id'] = $data['result'];
                        $record_data['subject_id'] = $subject_id;
                        $record_data['uid'] = $user_no;
                        $record_data['coupon_id'] = $item;
                        $record_data['username'] = $user_info['userName'];
                        $record_data['create_time'] = time();

                        $this->sj_template_subject_coupon_records_svc->insertCouponRecords($record_data);
                    }else{
                        $defeated_coupon_id[] = $item;
                    }
                }else{
                    $defeated_coupon_id[] = $item;
                }
            }
            if(count($defeated_coupon_id)==count($couponId)) $this->_errorResponse(302, '已经领取过了');
            $this->_successResponse(array('defeated'=>$defeated_coupon_id));
        }
        $this->_errorResponse(500, '领取失败');
    }

    /**
     * 获取用户信息
     * @param user_no 32位长ID
     * @return array
     */
    private function getUserInfo($user_no){
        $user_redis_key = "subject:userInfo:" . $user_no;
        $user_info = $this->redis_svc->dataGet($user_redis_key);
        if($user_info === false){
            //用户信息不在缓存中,获取用户信息
            $params = array('userNo' => $user_no);
            $user_info = $this->tsrv_svc->exec('user/getByUserNo', $params);
            if($user_info !== false) {
                $this->redis_svc->dataSet($user_redis_key, json_encode($user_info), 7200);
            }
        }else{
            $user_info = json_decode($user_info, true);
        }
        return $user_info;
    }

    /**
     * 根据专题id和优惠券批次号 获取领取总数和当天领取总数
     * @param subject_id int
     * @param coupon_numb int
     * @return json
     */
    public function getSubBindCouponTotalAction(){
        $subject_id = intval($this->request->get('subject_id'));
        $coupon_numb = intval($this->request->get('coupon_numb'));

        if (empty($coupon_numb) || empty($subject_id)) {
            $this->_errorResponse(PARAMS_ERROR, '参数错误');
        }

        $condition_sum = "`subject_id` = " . $subject_id . " AND `coupon_id` =" . $coupon_numb;

        $time_string = time();
        $start = mktime(0,0,0,date("m",$time_string),date("d",$time_string),date("Y",$time_string));
        $end = mktime(23,59,59,date("m",$time_string),date("d",$time_string),date("Y",$time_string));

        $condition_one = $condition_sum." AND `create_time` >= " . $start . " AND `create_time` <= " . $end;

        $records_sum = $this->sj_template_subject_coupon_records_svc->getCouponReceiveTotal($condition_sum);
        $records_one = $this->sj_template_subject_coupon_records_svc->getCouponReceiveTotal($condition_one);
        $data = array(
            'records_sum'=>$records_sum?$records_sum:0,
            'records_one'=>$records_one?$records_one:0,
        );
        $this->_successResponse($data);
    }
}