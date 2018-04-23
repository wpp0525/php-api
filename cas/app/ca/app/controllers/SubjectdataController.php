<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\UCommon as UCommon;
use Lvmama\Cas\Component\Kafka\Producer;
/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class SubjectdataController extends ControllerBase {

    private $redis;
    private $web_site;
    private $temp_subject;
    private $temp_subject_web_rel;
    private $temp_subject_coupon_rel;
    private $temp_subject_variable;
    private $sj_template_enroll;

    public function initialize() {
        parent::initialize();
        $this->redis_svc = $this->di->get('cas')->get('redis_data_service');
        $this->redis = $this->di->get('cas')->getRedis();
        $this->web_site = $this->di->get('cas')->get('sub_web_site');
        $this->temp_subject = $this->di->get('cas')->get('temp_subject');
        $this->temp_subject_web_rel = $this->di->get('cas')->get('temp_subject_web_rel');
        $this->temp_subject_coupon_rel = $this->di->get('cas')->get('temp_subject_coupon_rel');
        $this->temp_subject_variable = $this->di->get('cas')->get('temp_subject_variable');
        $this->sj_template_enroll = $this->di->get('cas')->get('sj_template_enroll');

    }

    /***
     * 模版专题列表
     */
    public function subjectListAction(){
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
        $subject_info = $this->temp_subject->getDataList($where, $limit, $select, $order);
        if(empty($subject_info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $subject_info));
            return;
        }
        $total_records = $this->temp_subject->getTotal($where);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array(
            'results' => $subject_info,
            'total_records' => intval($total_records),
            'page_index' => $current_page,
            'total_pages' => $total_pages
        ));
    }

    /***
     * 模版专题详情 专题id
     */
    public function subjectOneAction() {
        $id = intval($this->request->get('id'));
        $where = array();
        !empty($id) && $where['subject_id'] = "=" . $id;
        !empty($where) && $info = $this->temp_subject->getDataOne($where);
        if(empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        $this->_successResponse($info);
    }

    /***
     * 模版专题返回父级数据 专题拼音和ID
     */
    public function subjectParentInfoAction() {
        $id = intval($this->request->get('id'));
        $pinyin = trim($this->request->get('pinyin'));
        $where = array();
        !empty($id)     && $where['subject_id'] = "=" . $id;
        !empty($pinyin) && $where['pinyin'] = "='" . $pinyin ."'";
        !empty($where)  && $info = $this->temp_subject->getDataOne($where);

        if(empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        if($info['parent_id']!=0){
            $where_parent['subject_id'] = "=" . $info['parent_id'];
            $parent_info = $this->temp_subject->getDataOne($where_parent);

            if(empty($parent_info)) {
                $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
                return;
            }
        }
        $this->_successResponse($parent_info?$parent_info:$info);
    }

    /***
     * 模版专题是否存在
     */
    public function subjectTotalAction() {
        $where = $this->request->get('where');
        $where_arr = json_decode($where, true);
        !empty($where) && $info = $this->temp_subject->getTotal($where_arr);

        $this->_successResponse($info?$info:0);
    }

    /***
     * 模版专题增加
     */
    public function subjectAddAction(){
        $post = $this->request->getPost();
        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->temp_subject->insert($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'模板新增失败');
            return;
        }
        $this->_successResponse($result);
    }

    /***
     * 模版专题更新
     */
    public function subjectUpdateAction(){
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post) && !empty($id)) {
            $info = $this->temp_subject->getDataOne($id);
            if(!$info) $this->_errorResponse(OPERATION_FAILED,'关键字不存在');

            if(isset($info['template_id']) && isset($post['template_id']) && $info['template_id'] != $post['template_id']) {
                $this->temp_subject_variable->delAllVarByKid($id);
            }

            $post['update_time'] = time();
            $result = $this->temp_subject->update($id, $post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'模板更新失败');
            return;
        }
        $this->_successResponse($result);
    }

    /***
     * 模版专题删除
     */
    public function subjectDeleteAction(){
        $id = intval($this->request->get('id'));
        if(!empty($id)) {
            $result = $this->temp_subject->delete($id);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'模板删除失败');
            return;
        }
        $this->_successResponse($result);
    }

    /***
     * 模版专题绑定城市分站
     */
    public function subjectBindWebAction(){
        $post = $this->request->getPost();
        unset($post['api']);
        $subject_id = $post['subject_id'];
        if(empty($subject_id)) {
            $this->_errorResponse(PARAMS_ERROR,'专题不存在');
            return;
        }
        $website_id_arr = json_decode($post['website_id'],true);
        if(!empty($website_id_arr)){
            $ids = $this->getOtherSubjectIds($subject_id);
            foreach($website_id_arr as $k=>$v){
                $where = "subject_id in ({$ids}) AND website_id = {$v}";
                $exist = $this->temp_subject_web_rel->getTotal($where);
                if(empty($exist)) {
                    $this->temp_subject_web_rel->insert(array(
                        'subject_id'=>$subject_id,
                        'website_id'=>$v,
                    ));
                }
            }
            $website_ids = implode(',',$website_id_arr);
            $del_where = "subject_id = {$subject_id} AND website_id not in ({$website_ids})";
            $this->temp_subject_web_rel->delete($del_where);
        }else{
            $del_where = "subject_id = {$subject_id}";
            $this->temp_subject_web_rel->delete($del_where);
        }
        $this->_successResponse('成功');
    }

    /***
     * 专题下的所有子专题id $subject_id
     */
    private function getOtherSubjectIds($subject_id){
        $data = $this->temp_subject->getDataOne(array('subject_id'=>' = '.$subject_id));
        if(!empty($data)){
            if(!empty($data['parent_id'])){
                $where['parent_id'] = ' = '.$data['parent_id'];
            }else{
                $where['parent_id'] = ' = '.$data['subject_id'];
            }
            $select = 'subject_id';
            $subject_info = $this->temp_subject->getDataList($where,NULL, $select,NULL);
            $id_arr = array();
            foreach($subject_info as $k=>$v){
                $id_arr[] = $v['subject_id'];
            }
            $ids = implode(',',$id_arr);
        }
        return $ids?$ids:0;
    }

    /***
     * 专题绑定城市分站的列表
     */
    public function subjectWebListAction(){
        $subject_id = intval($this->request->get('subject_id'));
        $data = $this->temp_subject->getDataOne(array('subject_id'=>' = '.$subject_id));
        if(!empty($data['parent_id'])) {
            $ids = $this->getOtherSubjectIds($subject_id);
            $where['subject_id'] = " in ( {$ids} ) ";
            $list = $this->temp_subject_web_rel->getDataList($where);
        }else{
            $this->_errorResponse(PARAMS_ERROR,'专题id不正确');
            return;
        }
        if(empty($list)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        $this->_successResponse($list);
    }

    /***
     * 根据专题父级id和城市名称获取专题
     */
    public function webGetPinyinAction(){
        $subject_id = intval($this->request->get('subject_id'));
        $web_site_city = trim($this->request->get('cityName'));

        //所有子级id
        $sub_ids = $this->getOtherSubjectIds($subject_id);
        if(!empty($sub_ids)){
            $info = $this->temp_subject->webGetPinyin($web_site_city,$sub_ids);
        }else{
            $this->_errorResponse(PARAMS_ERROR,'专题id不正确');
            return;
        }
        if(empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        $this->_successResponse($info);
    }

    /***
     * 专题已绑定优惠券关系列表
     */
    public function getBindCouponRelAllAction(){
        $sid = intval($this->request->get('id'));
        if (empty($sid)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题不存在');
            return;
        }
        $condition['subject_id'] = "=".$sid;
        $coupon_list = $this->temp_subject_coupon_rel->getDataList($condition);
        if(empty($coupon_list)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题没有绑定优惠券');
            return;
        }
        $this->jsonResponse(array('results' => $coupon_list));
    }

    /***
     * 专题已绑定优惠券关系列表
     */
    public function getBindCouponAllAction(){
        $sid = intval($this->request->get('id'));
        if (empty($sid)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题不存在');
            return;
        }
        $coupon_list = $this->temp_subject_coupon_rel->subjectGetBindCoupon($sid);
        if(empty($coupon_list)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题没有绑定优惠券');
            return;
        }
        $this->jsonResponse(array('results' => $coupon_list));
    }

    /***
     * 专题绑定优惠券设置
     */
    public function subjectBindCouponAction(){
        $post = $this->request->getPost();
        $sid = intval($post['sid']);
        if (empty($sid)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题不存在');
            return;
        }
        $coupon_ids = json_decode($post['coupon_ids'],true);
        $order_num  = json_decode($post['order_num'],true);
        //已绑定
        if(empty($coupon_ids)){
            $result = $this->temp_subject_coupon_rel->delBySidAll($sid);
        }else{
            $condition['subject_id'] = "=".$sid;
            $coupon_list = $this->temp_subject_coupon_rel->getDataList($condition);
            $exist_coupon = array();
            foreach($coupon_list as $item){
                $exist_coupon[] = $item['coupon_id'];
            }

            //更新
            $up_arr = array_intersect($coupon_ids, $exist_coupon);
            foreach ($up_arr as $key=>$couponId) {
                $data['order_num']   = intval($order_num[$key]);
                $result = $this->temp_subject_coupon_rel->update2($sid,$couponId,$data);
            }
            //新增
            $add_arr=array_diff($coupon_ids,$exist_coupon);
            foreach ($add_arr as $key=>$couponId) {
                $data['subject_id']  = $sid;
                $data['coupon_id']   = intval($couponId);
                $data['order_num']   = intval($order_num[$key]);
                $result = $this->temp_subject_coupon_rel->insert($data);
            }
            //删除
            $del_arr=array_diff($exist_coupon,$coupon_ids);
            foreach ($del_arr as $couponId) {
                $result = $this->temp_subject_coupon_rel->delSidBindCoupon($sid,intval($couponId));
            }
        }

        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'绑定优惠券失败');
            return;
        }
        $this->jsonResponse(array('results' => $result));
    }
    /************专题变量设置***********/
    /**
     * 获取专题所有变量
     */
    public function getVarAction() {
        $kid = intval($this->request->get('kid'));
        if (empty($kid)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题变量不存在');
            return;
        }
        $condition['subject_id'] = "=".$kid;
        $keyword_vars = $this->temp_subject_variable->getVarList($condition);
        if(empty($keyword_vars)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题变量不存在');
            return;
        }
        $this->jsonResponse(array('results' => $keyword_vars));
    }

    /**
     * 获取专题所有变量
     */
    public function getVariableAction() {
        $variable_id = intval($this->request->get('variable_id'));
        $subject_id = intval($this->request->get('subject_id'));
        $variable_name = trim($this->request->get('variable_name'));

        !empty($variable_id) && $condition['id'] = "=" . $variable_id;
        !empty($subject_id) && $condition['subject_id'] = "=" . $subject_id;
        !empty($variable_name) && $condition['variable_name'] = "='" . $variable_name ."'";
        $keyword_vars = $this->temp_subject_variable->getVarList($condition);
        if(empty($keyword_vars)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题变量不存在');
            return;
        }
        $this->jsonResponse(array('results' => $keyword_vars));
    }

    /**
     * 专题变量新增
     */
    public function addVarAction() {
        $post = $this->request->getPost();

        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = $post['update_time'] = time();
            $result = $this->temp_subject_variable->insert($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'专题变量新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result,'error' => 0));
    }

    /**
     * 专题变量更新
     */
    public function updateVarAction() {
        $post = $this->request->getPost();
        $id = intval($post['id']);
        unset($post['id'], $post['api']);
        if(!empty($post)) {
            $post['update_time'] = time();
            $result = $this->temp_subject_variable->update($id, $post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'专题变量更新失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     * 设置专题变量
     */
    public function setVarAction() {
        $post = $this->request->getPost();

        $var = $vars = $varids = $varnames = $varcnts = array();
        $kid = intval($post['kid']);
        if(empty($kid)) {
            $this->_errorResponse(DATA_NOT_FOUND,'专题信息不存在');
            return;
        }
        $condition['subject_id'] = "=".$kid;
        $keyword_vars = $this->temp_subject_variable->getVarList($condition);
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
            $var['subject_id'] = $kid;
            $var['variable_name'] = trim($varname);
            $var['module_id'] = intval($varcnts[$varname]['module_id']);
            $var['variable_content'] = trim($varcnts[$varname]['variable_content']);
            $var['update_time'] = time();
            $result = $this->temp_subject_variable->update($varid, $var);
        }
        $newvarnames = array_diff($varnames, $vars);
        foreach ($newvarnames as $varname) {
            $var['subject_id'] = $kid;
            $var['variable_name'] = trim($varname);
            $var['module_id'] = intval($varcnts[$varname]['module_id']);
            $var['variable_content'] = trim($varcnts[$varname]['variable_content']);
            $var['create_time'] = $var['update_time'] = time();
            $result = $this->temp_subject_variable->insert($var);
        }
        $oldvarnames = array_diff($vars, $varnames);
        foreach ($oldvarnames as $varname) {
            $result = $this->temp_subject_variable->delVarByKid($kid, trim($varname));
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'设置专题变量失败');
            return;
        }
        $this->jsonResponse(array('result' => $result));
    }

    /**
     *  报名模块接口 数据列表
     */
    public function enrollListAction(){
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

        $info = $this->sj_template_enroll->getDataList($where, $limit, $select, $order);
        if(empty($info)) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        if (!isset($_REQUEST['current_page'])) {
            $this->jsonResponse(array('results' => $info));
            return;
        }
        $total_records = $this->sj_template_enroll->getTotal($where);
        $total_pages = intval(($total_records-1)/$page_size+1);
        $this->jsonResponse(array(
            'results' => $info,
            'total_records' => intval($total_records),
            'page_index' => $current_page,
            'total_pages' => $total_pages
        ));
    }


    /**
     *  报名模块接口 根据条件是否存在
     */
    public function enrollTotalAction(){
        $where = $this->request->get('where');
        $where_arr = json_decode($where, true);
        !empty($where) && $info = $this->sj_template_enroll->getTotal($where_arr);

        $this->jsonResponse(array('result' => $info?$info:0,'error' => 0));
    }


    /**
     *  报名模块接口 添加数据
     */
    public function enrollAddAction(){
        $post = $this->request->getPost();

        unset($post['api']);
        if(!empty($post)) {
            $post['create_time'] = time();
            $result = $this->sj_template_enroll->insert($post);
        }
        if(empty($result)) {
            $this->_errorResponse(OPERATION_FAILED,'新增失败');
            return;
        }
        $this->jsonResponse(array('result' => $result,'error' => 0));
    }

}