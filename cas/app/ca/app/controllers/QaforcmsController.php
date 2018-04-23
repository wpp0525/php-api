<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-6-15
 * Time: 下午7:22
 */
use Lvmama\Common\Utils;
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\QaQuestionDataService;
use Lvmama\Cas\Service\QaQuestionStatisticsDataService;

class QaforcmsController extends ControllerBase {

    private $qa_svc;
    private $qaq_svc;
    private $qaqs_svc;

    private $product_bu_array = array(1 => '国内BU', 2 => '出境BU', 3 => '目的地BU', 4 => '门票BU', 5 => '商旅BU');
    private $product_cate_array = array(13 => '门票', 18 => '自由行', 24 => '跟团游（境内）', 28 => '当地游', 33 => '邮轮', 39 => '签证', 45 => 'wifi/电话卡');
    private $cate_tag_array = array(
        'cate' => array(3 => '门票', 4 => '自由行', 5 => '跟团游（境内）', 6 => '当地游', 7 => '邮轮', 8 => '签证', 9 => 'wifi/电话卡'),
        'tag' => array(
            '3' => array(13 => '常见问题', 14 => '付款支付', 15 => '活动促销', 16 => '取票入园', 17 => '景点相关'),
            '4' => array(18 => '常见问题', 19 => '付款支付', 20 => '活动促销', 21 => '酒店住宿', 22 => '往返交通', 23 => '景点相关'),
            '5' => array(24 => '常见问题', 25 => '付款支付', 26 => '活动促销', 27 => '酒店住宿'),
            '6' => array(28 => '常见问题', 29 => '付款支付', 30 => '活动促销', 31 => '交通接送', 32 => '导游'),
            '7' => array(33 => '常见问题', 34 => '付款支付', 35 => '活动促销', 36 => '签证办理', 37 => '线路相关', 38 => '邮轮信息'),
            '8' => array(39 => '常见问题', 40 => '付款支付', 41 => '活动促销', 42 => '材料提交', 43 => '办理时间', 44 => '取签送签'),
            '9' => array(45 => '常见问题', 46 => '付款支付', 47 => '活动促销', 48 => '设备拿取')
        ),
    );
//    private $sw_svc;

    public function initialize() {
        parent::initialize();
        $this->qa_svc = $this->di->get('cas')->get('qa_common_data_service');
        $this->qaqs_svc = $this->di->get('cas')->get('qa_question_statistics_data_service');
        $this->qaq_svc = $this->di->get('cas')->get('qaquestion-data-service');
//        $this->$sw_svc = $this->di->get('cas')->get('sensitive-word-data-service');
//        $this->redis = $redis;
        $this->redis_svc=$this->di->get('cas')->get('redis_data_service');
    }

    /**
     * =============================
     *  获取审核页面数据
     *  输出：json
     * @author liuhongfei
     * =============================
     */
    public function getCheckListAction(){

        if($this->request->isPost()) {

            //  验证来源
            $this->checkSignCode();
            // 数据处理
            $page =  $this->request->getPost('page')?$this->request->getPost('page'):'1';

            $params = array();
            $params['q.main_status'] = $this->request->getPost('main_status')?$this->request->getPost('main_status'):'>|1';
            $params['q.del_status'] = $this->request->getPost('del_status')?$this->request->getPost('del_status'):'0';
            $params['q.uid'] = $this->request->getPost('uid')?$this->request->getPost('uid'):"";
            $params['q.username'] = $this->request->getPost('username')?$this->request->getPost('username'):"";
            $where = array_diff($params, array(''));

            $wherein = array();
            $params_product_id = $this->request->getPost('product_id')?$this->request->getPost('product_id'):'';
            if(strpos($params_product_id, '|')){
                $a_ppid = explode('|', $params_product_id);
                $wherein['qpr.product_id'] = "('".implode("', '", $a_ppid)."')";
            }elseif($params_product_id){
                $where['qpr.product_id'] = $params_product_id;
            }

            $params_auditor_id = $this->request->getPost('auditor_id')?$this->request->getPost('auditor_id'):'';
            if(strpos($params_auditor_id, '|')){
                $a_paid = explode('|', $params_auditor_id);
                $wherein['q.auditor_id'] = "('".implode("', '", $a_paid)."')";
            }elseif($params_auditor_id){
                $where['q.auditor_id'] = $params_auditor_id;
            }

            $params_tag_id = $this->request->getPost('tag_id')?$this->request->getPost('tag_id'):'';
            if(strpos($params_tag_id, '|')){
                $a_tid = explode('|', $params_tag_id);
                $wherein['qtr.tag_id'] = "('".implode("', '", $a_tid)."')";
            }elseif($params_tag_id){
                $where['qtr.tag_id'] = $params_tag_id;
            }

            $where['qtr2.tag_id'] = ">|12";

            $between = array();
            if($this->request->getPost('begin') && $this->request->getPost('end')){
                $between[] = array(
                    'key'=>'q.update_time',
                    'type' => 'BETWEEN',
                    'value' => array($this->request->getPost('begin'), $this->request->getPost('end'))
                );
            }

            // 组成查询全部条件
            $params_condition = array(
                'table' =>'qa_question q',
                'select' => 'q.id, q.uid, q.username, q.content, q.auditor_id, q.update_time, q.main_status, qpr.product_id, qtr.tag_id, qtr2.tag_id as tag',
//                'select' => 'q.*, qpr.product_id, qtr.tag_id',
                'join' => array(
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_question_product_rel qpr',
                        'on' => 'q.id = qpr.question_id',
                    ),
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_question_tag_rel qtr',
                        'on' => 'q.id = qtr.question_id',
                    ),
                    array(
                        'type' => 'INNER',
                        'table' => 'qa_question_tag_rel qtr2',
                        'on' => 'q.id = qtr2.question_id',
                    )
                ),
                'in' =>$wherein,
                'where' => $where,
                'between' => $between,
                'order' => 'q.update_time desc',
                'group' => 'q.id',
                'page' => array('pageSize' => 15, 'page' => $page)
            );

            // 查询输出结果 json 格式
            $res = $this->qa_svc->getByParams($params_condition);

            // 调用redis 获取 sensitiveWord（敏感词） 和 swtime（敏感词更新时间）
            if(is_array($res['list'])){
                foreach($res['list'] as $key => $val){
                    $result = array();
                    $swords = '';
                    $redis_key = str_replace('{id}', $val['id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                    $result = $this->redis_svc->dataHgetall($redis_key);
                    if($result){
                        $swords = json_decode(urldecode($result['sensitiveWord']));
                        if(!empty($swords))
                            $res['list'][$key]['sensitiveWord'] = implode(', ', $swords);
                        $res['list'][$key]['swtime'] = $result['time'];
                    }
                }
            }

            $this->qa_svc->messageOutput('200', $res);

        }else{
            $this->qa_svc->messageOutput('400');
        }

    }

    /**
     * -----------------------------------
     * 写入敏感词数据到redis
     * @author liuhongfei
     * -----------------------------------
     */
    public function setQuestionSensitiveWordAction(){
        // 如果不存在 问题id 返回错误
        if(!$this->id)
            $this->qa_svc->messageOutput('400');

        $this->verifySignCode($this->code, $this->sign);

        // 数据整理
        $data = array();
        $data['id'] = $this->id;    // question_id
        $data['content'] = $this->content;      // 问题内容
        $data['sensitiveWord'] = $this->sensitiveWord;      // 敏感词
        $data['time'] = time();     // 敏感词更新时间
        $data['username'] = $this->username;    // 用户名
        $data['uid'] = $this->uid;      // 用户id
        $data['update_time'] = $this->update_time;      // 最后修改时间 -> 问题排序时间
        $data['main_status'] = $this->main_status;

        // 查询 product_id
        $res = $this->qa_svc->getRowByCondition('qa_question_product_rel', 'question_id', $data['id']);
        $data['product_id'] = $res['product_id'];

        // 写入redis
        $redis_key = str_replace('{id}', $data['id'], RedisDataService::REDIS_QA_QUESTION_INFO);
        $set_type = $this->qa_svc->setHashDataToRedis($data, $redis_key);

        // 写入状态返回
        if($set_type){
            $this->qa_svc->messageOutput('200');
        }else{
            $this->qa_svc->messageOutput('500');
        }

    }

    /**
     * ------------------------------------------------------------
     *  更新问题的主状态
     *  必选参数：id，auditor_id，audit_time，main_status，sign，code
     *  输出：json
     * @author liuhongfei
     * ------------------------------------------------------------
     */
    public function updateQuestionMainStatusAction(){

        $this->verifySignCode($this->code, $this->sign);

        $key = 0;
        $params = array();

        if($this->id){
            $key = $this->id;
        }
        if($this->auditor_id && $this->audit_time && $this->main_status){
            $params['auditor_id'] = $this->auditor_id;
            $params['audit_time'] = $this->audit_time;
            $params['main_status'] = $this->main_status;
        }

        if($key > 0 && !empty($params)){

            $res = $this->qa_svc->operateDataById('qa_question', $params, $key);

            // 补全 qa_question_statistics
            $this->qaqs_svc->initStatistics($key);

            // redis qa:question:{id} 记录
            // 数据整理
            $data = array();
            $question = $this->qa_svc->getRowByCondition('qa_question', 'id', $key);
            $data['id'] = $question['id'];    // question_id
            $data['content'] = $question['content'];      // 问题内容
            $data['username'] = $question['username'];    // 用户名
            $data['uid'] = $question['uid'];      // 用户id
            $data['update_time'] = $question['update_time'];      // 最后修改时间 -> 问题排序时间
            $data['main_status'] = $question['main_status'];      // 审核状态
            // 查询 product_id
            $temp = $this->qa_svc->getRowByCondition('qa_question_product_rel', 'question_id', $key);
            $data['product_id'] = $temp['product_id'];

            unset($question);
            unset($temp);

            // 写入redis qa:question:{id}
            $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_INFO);
            $set_type = $this->qa_svc->setHashDataToRedis($data, $redis_key);

            // redis qa:question:{id}:tags 添加
            $data2 = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $key);
            $redis_key2 = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_TAGS);
            $this->redis_svc->dataSAdd($redis_key2, $data2);

            $this->qa_svc->messageOutput('200', array('id' => $res), "数据更新成功！");

        }else{
            $this->qa_svc->messageOutput('400');
        }

    }

    /**
     * 通过分类名称获取标签
     * @author liuhongfei
     *  need update
     */
    public function getTabByCateNameAction(){

        if($this->request->isPost()) {

            $this->checkSignCode();

            $cate_name = $this->request->getPost('cate_name');
            if(!$cate_name){
                $this->qa_svc->messageOutput('400');
            }

            $cn_arr = explode('|', $cate_name);
            $status_array = array(0, 1);

            foreach($cn_arr as $v){

                $params = array();
                $params['tc.name'] = $v;
                $params['t.status'] = in_array($this->request->getPost('status'),$status_array)?$this->request->getPost('status'):'1';
                $where = array_diff($params, array(''));

                // 组成查询全部条件
                $params_condition = array(
                    'table' =>'qa_tag t',
                    'select' => 't.*, tc.id as eeee',
                    'join' => array(
                        array(
                            'type' => 'LEFT',
                            'table' => 'qa_tag_category tc',
                            'on' => 'tc.id = t.category_id',
                        ),
                    ),
                    'where' => $where,
                );

                $data = $this->qa_svc->getByParams($params_condition);

                if(count($cn_arr)>1){
                    $res['list'][$v] = $data['list'];
                }else{
                    $res = $data;
                }

            }

            // 查询输出结果 json 格式
            $this->qa_svc->messageOutput('200', $res);

        }else{
            $this->qa_svc->messageOutput('400');
        }

    }

    public function updateRelationAction(){

        if($this->request->isPost()) {
            $this->checkSignCode();

            $table = $this->request->getPost('table');
            $relation_ids = $this->request->getPost('relation_ids');
            $relation_key = $this->request->getPost('relation_key');
            $index_id = $this->request->getPost('index_id');
            $index_key = $this->request->getPost('index_key');

            $old_rel = $this->qa_svc->findRelationByCondition($table, $relation_key, $index_key, $index_id);
            $new_rel = explode('|', $relation_ids);

            $del= array_diff($old_rel, $new_rel);
            $add= array_diff($new_rel, $old_rel);

            if(count($del) > 0){
                foreach($del as $d_val){
                    $params = array(
                        'table' => $table,
                        'where' => "`{$index_key}` = {$index_id} AND `{$relation_key}` = $d_val"
                    );
                    $this->qa_svc->delete($params);
                    unset($params);
                }
            }
            if(count($add) > 0){
                foreach($add as $a_val){
                    $temp = array();
                    $temp[$index_key] = $index_id;
                    $temp[$relation_key] = $a_val;
                    $this->qa_svc->operateDataById($table, $temp);
                    unset($temp);
                }
            }

            $this->qa_svc->messageOutput('200');

        }else{
            $this->qa_svc->messageOutput('400');
        }
    }



    private function checkSignCode(){
        $sign = $this->request->getPost('sign');
        $code = $this->request->getPost('code');

        if($code != md5($sign.'qaforcms')){
            $this->qa_svc->messageOutput('300');
        }
    }

    /**
     *
     *
     * @author liuhongfei
     */
    public function replyAuditorAnswerAction(){

        $this->verifySignCode($this->code, $this->sign);

        $qid = $this->question_id;
        $aid = $this->answer_id;
        $admin_answer = json_decode($this->admin_answer, true);

        if(!$qid){
            $this->qa_svc->messageOutput('400');
        }

        $admin_answer['question_id'] = $qid;
        $data = array();
        if($aid){
            $data = $this->makeExtTime($admin_answer, $aid);
            $this->qa_svc->operateDataById('qa_admin_answer', $data, $aid);
        }else{
            $data = $this->makeExtTime($admin_answer);
            $this->qa_svc->operateDataById('qa_admin_answer', $data);
        }

        $this->qaqs_svc->updateStatistics($qid);

        $hide = $this->is_hide;
        $main_status = $this->main_status;

        $ques_info = array();
        $ques_info['acontent'] = $admin_answer['content'];      // 审核状态
        $ques_info['astatus'] = 1;      // 审核状态

//        echo $hide."====".$main_status; die;
        if($hide == 'Y' && $main_status != 4){
            $ques_info['main_status'] = 4;


            // 修改审核数据
            $update = array();
            $update['auditor_id'] = $admin_answer['admin_id'];
            $update['audit_time'] = time();
            $update['main_status'] = 4;
//            var_dump($update);die;
            $this->qa_svc->operateDataById('qa_question', $update, $qid);

        }

        if($hide != 'Y'){
            // 写入 redis
            // const REDIS_QA_PRODUCT_TAG_REL = 'qa:pro_rel:tag:{tag_id}_{product_id}';
            // const REDIS_QA_PRODUCT_CATE_REL = 'qa:pro_rel:cate:{cate_id}_{product_id}';
            $tag_id = $this->tag_id;
            $old_tag = $this->old_tag;

            $data2 = $this->qa_svc->findRelationByCondition('qa_question_product_rel', 'product_id', 'question_id', $qid);
            $update_time = $this->update_time;
            if(!$old_tag){
                // 新增两条
                $redis_key1 = str_replace(array('{tag_id}','{product_id}'), array($tag_id, $data2[0]), RedisDataService::REDIS_QA_PRODUCT_TAG_REL);
                $this->redis_svc->dataZAdd($redis_key1, $update_time, $qid);

                $cate_id = $this->cate_id;
                $redis_key2 = str_replace(array('{cate_id}','{product_id}'), array($cate_id, $data2[0]), RedisDataService::REDIS_QA_PRODUCT_CATE_REL);
                $this->redis_svc->dataZAdd($redis_key2, $update_time, $qid);

            }elseif($tag_id != $old_tag){
                // 删除一条
                $redis_key1 = str_replace(array('{tag_id}','{product_id}'), array($old_tag, $data2[0]), RedisDataService::REDIS_QA_PRODUCT_TAG_REL);
                $this->redis_svc->dataZRem($redis_key1, $qid);
            }

        }

        // 写入redis qa:question:{id}
        $redis_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_QUESTION_INFO);
        $set_type = $this->qa_svc->setHashDataToRedis($ques_info, $redis_key);

        $this->qa_svc->messageOutput('200');

    }

    /**
     * 拼接时间
     * @author liuhongfei
     */
    private function makeExtTime($data = array(), $key = ''){
        $time = time();
        if($key){
            $init_time = array(
                'update_time' => $time
            );
        }else{
            $init_time = array(
                'create_time' => $time,
                'update_time' => $time
            );
        }
        return array_merge($init_time,$data);
    }







    /**
     * ------------------------------
     * 获取单条问题数据
     * @author liuhongfei
     * ------------------------------
     */
    public function getQuestionInfoAction(){

        $this->verifySignCode($this->code, $this->sign);
        $question_id = intval($this->id);

        if(!$question_id){
            $this->qa_svc->messageOutput('400');
        }else{
            $res = $this->qa_svc->getOneById('qa_question', $question_id);
            $this->qa_svc->messageOutput('200', $res);
        }

    }


    /**
     * ---------------------------------
     * 获取常见问题列表
     * @author liuhongfei
     * ---------------------------------
     */
    public function getCommonQuestionAction(){

        $this->verifySignCode($this->code, $this->sign);

        $page = $this->page?intval($this->page):'1';
        $limit =$this->limit?intval($this->limit):'15';
        $tag1 = json_decode($this->tag1);
        $tag2 = json_decode($this->tag2);

        $res = $this->qaq_svc->getCommonQuestion($tag1, $tag2, $page, $limit);

        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     * ====================
     * 获取单条常见问题信息
     * @author liuhongfei
     * ====================
     */
    public function getOneCommonQuestionAction(){

        $this->verifySignCode($this->code, $this->sign);

        $id =$this->id;
        if(!$id){
            $this->qa_svc->messageOutput('400');
        }

        $params = array(
            'q.del_status' => "0",
            'aa.status' => "1",
            'q.id' => $id
        );

        $wherein = array();
        $wherein['qtr1.tag_id'] = "('".implode("', '", array_keys($this->product_bu_array))."')";
        $wherein['qtr2.tag_id'] = "('".implode("', '", array_keys($this->product_cate_array))."')";

        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_question q',
            'select' => 'q.id, q.content as qcontent, aa.id as aid, aa.content as acontent, qtr1.tag_id as tag1, qtr2.tag_id as tag2',
            'join' => array(
                array(
                    'type' => 'LEFT',
                    'table' => 'qa_admin_answer aa',
                    'on' => 'q.id = aa.question_id',
                ),
                array(
                    'type' => 'LEFT',
                    'table' => 'qa_question_tag_rel qtr1',
                    'on' => 'q.id = qtr1.question_id',
                ),
                array(
                    'type' => 'LEFT',
                    'table' => 'qa_question_tag_rel qtr2',
                    'on' => 'q.id = qtr2.question_id',
                )
            ),
            'in' =>$wherein,
            'where' => $params,
            'between' => '',
            'order' => '',
            'group' => '',
            'page' => array()
        );

        $res = $this->qa_svc->getByParams($params_condition);

        if(is_array($res['list'])){
            $this->qa_svc->messageOutput('200', $res['list'][0]);
        }else{
            $this->qa_svc->messageOutput('200', array());
        }

    }

    /**
     * =======================
     *  获取回答页面数据
     *  输出：json
     * @author liuhongfei
     * =======================
     */
    public function getQuestionAnswerListAction(){

        //  验证来源
        $this->verifySignCode($this->code, $this->sign);

        // 数据处理
        $page =  $this->page?$this->page:'1';

        $params = array();

        $params['q.main_status'] = $this->main_status?$this->main_status:'>|1';
        $params['q.del_status'] = $this->del_status?$this->del_status:'0';
        $params['q.uid'] = $this->uid?$this->uid:'';
        $params['q.username'] = $this->username?$this->username:'';
        $params['qs.valid_answer']  = $this->count_answer?$this->count_answer:'';
        $where = array_diff($params, array(''));

        $wherein = array();
        $params_product_id = $this->product_id?$this->product_id:'';
        if(strpos($params_product_id, '|')){
            $a_ppid = explode('|', $params_product_id);
            $wherein['qpr.product_id'] = "('".implode("', '", $a_ppid)."')";
        }elseif($params_product_id){
            $where['qpr.product_id'] = $params_product_id;
        }

        $params_auditor_id = $this->auditor_id?$this->auditor_id:'';
        if(strpos($params_auditor_id, '|')){
            $a_paid = explode('|', $params_auditor_id);
            $wherein['aa.admin_id'] = "('".implode("', '", $a_paid)."')";
        }elseif($params_auditor_id){
            $where['aa.admin_id'] = $params_auditor_id;
        }

        $select_str = "";

        $bu = $this->bu?$this->bu:'';
        if($bu){
            $where['qtr1.tag_id'] = $bu;
            $select_str .= ", qtr1.tag_id as bu";
            $bu_array = array(
                'type' => 'INNER',
                'table' => 'qa_question_tag_rel qtr1',
                'on' => 'q.id = qtr1.question_id',
            );
        }else{
            $bu_array = array();
        }

        $tag_id = $this->tag_id?$this->tag_id:'';
        $tag_array = array(
            'type' => 'INNER',
            'table' => 'qa_question_tag_rel qtr2',
            'on' => 'q.id = qtr2.question_id',
        );
        if(strpos($tag_id, '|')){
            $a_tid = explode('|', $tag_id);
            $wherein['qtr2.tag_id'] = "('".implode("', '", $a_tid)."')";
            $select_str .= ", qtr2.tag_id";
        }elseif($tag_id){
            $where['qtr2.tag_id'] = $tag_id;
            $select_str .= ", qtr2.tag_id";
        }else{
            $tag_array = array();
        }

        $between = array();
        if($this->begin && $this->end){
            $between[] = array(
                'key'=>'q.update_time',
                'type' => 'BETWEEN',
                'value' => array($this->begin, $this->end)
            );
        }

        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_question q',
//                'select' => 'q.*, qpr.product_id, qtr.tag_id',
            'select' => 'q.*, qpr.product_id, qs.valid_answer as valid_answers, aa.id as aid, aa.content as acontent, aa.admin_id as a_admin_id, aa.update_time as aupdate_time '.$select_str,
            'join' => array(
                array(
                    'type' => 'INNER',
                    'table' => 'qa_question_product_rel qpr',
                    'on' => 'q.id = qpr.question_id',
                ),
                $bu_array,
                $tag_array,
                array(
                    'type' => 'LEFT',
                    'table' => 'qa_admin_answer aa',
                    'on' => 'q.id = aa.question_id',
                ),
                array(
                    'type' => 'LEFT',
                    'table' => 'qa_question_ext qs',
                    'on' => 'q.id = qs.question_id',
                )
            ),
            'in' =>$wherein,
            'where' => $where,
            'between' => $between,
            'order' => 'q.update_time DESC',
            'group' => 'q.id',
            'page' => array('pageSize' => 15, 'page' => $page)
        );

        // 查询输出结果 json 格式
        $res = $this->qa_svc->getByParams($params_condition);

        if($res['list'] && is_array($res['list'])){
            foreach($res['list'] as $key => $val){
                $tmp_where = "question_id = ".$val['id']." AND tag_id > 5 ";
                $tag_id = $this->qa_svc->getRowByConditionSrt('qa_question_tag_rel', 'tag_id', $tmp_where, 'one');
//                var_dump($tag_id);die;
                if($tag_id){
                    $res['list'][$key]['tag_id'] = $tag_id['tag_id'];
                }
            }
        }

        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     * -----------------------------------
     * 新建/修改常见问题
     * @author liuhongfei
     * -----------------------------------
     */
    public function operOneCommonQuestionAction(){

        $this->verifySignCode($this->code, $this->sign);

        // 数据处理
        $question_id = $this->question_id;
        $a_id = $this->answer_id;
        $question = json_decode($this->question, true);
        $admin_answer = json_decode($this->admin_answer, true);

        $nbu = $this->new_bu;
        $ntag = $this->new_tag;

        $obu = $this->old_bu;
        $otag = $this->old_tag;

        if($question_id && $question){
            $data = array();
            $data = $this->makeExtTime($question, $question_id);
            $time = $data['update_time'];
            $res = $this->qa_svc->operateDataById('qa_question', $data, $question_id);
        }elseif($question){
            $data = array();
            $data = $this->makeExtTime($question);
            $time = $data['update_time'];
            $res = $this->qa_svc->operateDataById('qa_question', $data);
        }else{
            $res = $question_id;
        }

        if($res){
            $admin_answer['question_id'] = $res;
            $data = array();
            if($a_id){
                $data = $this->makeExtTime($admin_answer, $a_id);
                $this->qa_svc->operateDataById('qa_admin_answer', $data, $a_id);
            }else{
                $data = $this->makeExtTime($admin_answer);
                $this->qa_svc->operateDataById('qa_admin_answer', $data);
            }
        }

        $ques_info = array();
        $ques_info['id'] = $res;    // question_id
        $ques_info['content'] = $question['content'];      // 问题内容
        $ques_info['update_time'] = $time;      // 最后修改时间 -> 问题排序时间
        $ques_info['main_status'] = 5;      // 审核状态
        $ques_info['acontent'] = $admin_answer['content'];      // 审核状态
        $ques_info['astatus'] = 1;      // 审核状态

        // 写入redis qa:question:{id}
        $redis_key = str_replace('{id}', $res, RedisDataService::REDIS_QA_QUESTION_INFO);
        $set_type = $this->qa_svc->setHashDataToRedis($ques_info, $redis_key);

        $this->qaqs_svc->updateStatistics($res);

        if($obu != $nbu || $otag != $ntag){

            $del_ids = array();
            $add_ids = array();
            if($obu != $nbu){
                $del_ids[] = $obu;
                $add_ids[] = $nbu;
            }
            if($otag != $ntag){
                $del_ids[] = $otag;
                $add_ids[] = $ntag;
            }

            $redis_key2 = str_replace('{id}', $res, RedisDataService::REDIS_QA_QUESTION_TAGS);
            // 删掉旧的
            foreach($del_ids as $vd){
                $params = array(
                    'table' => 'qa_question_tag_rel',
                    'where' => "question_id = '{$res}' AND tag_id = '{$vd}'"
                );
                $this->qa_svc->deleteData($params);
                $this->redis_svc->dataSRem($redis_key2, $vd);
            }
            // 增加新的
            foreach($add_ids as $va){
                $data = array(
                    'question_id' => $res,
                    'tag_id' => $va
                );
                $this->qa_svc->operateDataById('qa_question_tag_rel', $data);
                $this->redis_svc->dataSAdd($redis_key2, $va);
            }

            // REDIS_QA_PRODUCT_BU_REL = 'qa:pro_rel:bu:{bu_id}_{tag_id}';
            $redis_key3 = str_replace(array('{bu_id}','{tag_id}'), array($obu, $otag), RedisDataService::REDIS_QA_PRODUCT_BU_REL);
            $this->redis_svc->dataZRem($redis_key3, $res);

        }

        if($time){
            // REDIS_QA_PRODUCT_BU_REL = 'qa:pro_rel:bu:{bu_id}_{tag_id}';
            $redis_key4 = str_replace(array('{bu_id}','{tag_id}'), array($nbu, $ntag), RedisDataService::REDIS_QA_PRODUCT_BU_REL);
            $this->redis_svc->dataZAdd($redis_key4, $time, $res);
        }

        $this->qa_svc->messageOutput('200');

    }

    /**
     * ---------------------------
     * 删除常见问题
     * @author liuhongfei
     * ---------------------------
     */
    public function delOneCommonQuestionAction(){

        $this->verifySignCode($this->code, $this->sign);

        $id =$this->id;
        $del_tag =$this->del_tag;

        if(!$id || !$del_tag){
            $this->qa_svc->messageOutput('400');
        }

        // 获取 数据条数
        $params = array(
            'question_id' => $id,
//            'tag_id' => '<>|'.$del_tag
            'tag_id' => "<|6"
        );

        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_question_tag_rel',
            'select' => '*',
            'where' => $params,
        );

        $res = $this->qa_svc->getByParams($params_condition);

        if($res['list']){
            $bu_tag_ids = array_keys($this->product_bu_array);
            if(in_array($res['list'][0]['tag_id'], $bu_tag_ids)){
                // 这里删除 是修改 qa_question.del_status = 1  不修改 qa_admin_answer.status =0
                $data = array('del_status' => '1');
                $data = $this->makeExtTime($data, $id);
                $res2 = $this->qa_svc->operateDataById('qa_question', $data, $id);

                // 修改 redis del_status = 1
                $ques_info['del_status'] = 1;      // 审核状态
                // 写入redis qa:question:{id}
                $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
                $set_type = $this->qa_svc->setHashDataToRedis($ques_info, $redis_key);

                // 删除REDIS_QA_PRODUCT_BU_REL = 'qa:pro_rel:bu:{bu_id}_{tag_id}';
                $redis_key3 = str_replace(array('{bu_id}','{tag_id}'), array($res['list'][0]['tag_id'], $del_tag), RedisDataService::REDIS_QA_PRODUCT_BU_REL);
                $this->redis_svc->dataZRem($redis_key3, $id);
            }
        }

        $this->qa_svc->messageOutput('200');
    }


    /**
     * @author liuhongfei
     */
    public function getQuestionAnswerInfoAction(){

        $this->verifySignCode($this->code, $this->sign);

        $qid = $this->qid;
        $aid = $this->aid;

        $data = array();
        $data['question'] = $this->qa_svc->getOneById('qa_question', $qid);
        if($aid){
            $data['answer'] = $this->qa_svc->getOneById('qa_admin_answer', $aid);
        }

        $params = array(
            'table' =>'qa_question_tag_rel qtr',
            'select' => 'tc.id',
            'join' => array(
                array(
                    'type' => 'INNER',
                    'table' => 'qa_tag t',
                    'on' => 'qtr.tag_id = t.id',
                ),
                array(
                    'type' => 'INNER',
                    'table' => 'qa_tag_category tc ',
                    'on' => 'tc.id = t.category_id',
                ),
            ),
            'where' => array(
                'qtr.question_id' =>$qid,
                'qtr.tag_id' => ">|5",
            ),
        );
        $res_temp1 = $this->qa_svc->getByParams($params);
//        var_dump($res_temp1);die;

        $data['cate_id'] = $res_temp1['list'][0]['id'];

        // 获取 数据条数
        $params = array(
            'question_id' => $qid,
        );

        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_question_tag_rel',
            'select' => 'tag_id',
            'where' => $params,
        );

        $res_temp2 = $this->qa_svc->getByParams($params_condition);

        if(is_array($res_temp2['list'])){
            foreach($res_temp2['list'] as $val){
                if($val['tag_id'] < 6){
                    $data['bu_id'] = $val['tag_id'];
                }else{
                    $data['tag_id']= $val['tag_id'];
                }
            }
        }

        $this->qa_svc->messageOutput('200', $data);

    }

    /**
     * -----------------------------
     *  判断请求来源是否合法
     * @author liuhongfei
     * -----------------------------
     */
    private function verifySignCode($code, $sign){
        if($code != md5($sign.'qaforcms')){
            $this->qa_svc->messageOutput('300');
        }
        return true;
    }
}
