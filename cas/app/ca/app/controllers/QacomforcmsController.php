<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-8-5
 * Time: 上午11:46
 */

use Lvmama\Common\Utils;
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Cas\Service\QaQuestionDataService;
use Lvmama\Cas\Service\QaQuestionStatisticsDataService;
use Lvmama\Cas\Service\DestBaseDataService;
use Lvmama\Cas\Service\DestDetailDataService;

class QacomforcmsController extends ControllerBase {

    private $qa_svc;
    private $qaa_svc;
    private $qaq_svc;
    private $qaqs_svc;
    private $base_svc;
    private $qaae_svc;
    private $qaac_svc;
    private $detail_svc;

    public function initialize() {
        parent::initialize();
        $this->qa_svc = $this->di->get('cas')->get('qa_common_data_service');
        $this->qaqs_svc = $this->di->get('cas')->get('qa_question_statistics_data_service');
        $this->qaq_svc = $this->di->get('cas')->get('qaquestion-data-service');
        $this->redis_svc = $this->di->get('cas')->get('redis_data_service');
        $this->base_svc = $this->di->get('cas')->get('dest_base_service');

        $this->detail_svc = $this->di->get('cas')->get('dest_detail_service');

        $this->qaa_svc = $this->di->get('cas')->get('qaanswer-data-service');
        $this->qaae_svc = $this->di->get('cas')->get('qa_answer_ext_data_service');
        $this->qaac_svc = $this->di->get('cas')->get('qa_answer_comment_data_service');

    }

    /**
     * 首页轮播 - 列表
     * page -> 第几页
     */
    public function getAdListAction(){
        $page = $this->page ? $this->page : 1 ;

        $where = array();
        $where['status'] = ">|0";

        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_slideshow',
            'select' => '*',
            'where' => $where,
            'order' => 'status asc, order_num desc',
            'page' => array('pageSize' => 10, 'page' => $page)
        );

        // 查询输出结果 json 格式
        $res = $this->qa_svc->getByParams($params_condition);
        $this->qa_svc->messageOutput('200', $res);

    }

    /**
     * 新建广告位
     */
    public function addNewAdAction(){

        $this->verifySignCode($this->code, $this->sign);
        $data = array();
        $data['title'] = $this->title;
        $data['order_num'] = intval($this->order_num) > 9999 ? 9999 : intval($this->order_num) ;

        $data['url'] = strpos(trim($this->url), 'http://') === 0 ? trim($this->url) : 'http://'.trim($this->url) ;
        $data['img'] = $this->img;
        $data['status'] = $this->status != 1 ? 9 : 1 ;

        $data = $this->makeExtTime($data);
        $res = $this->qa_svc->operateDataById('qa_slideshow', $data);
        $this->qa_svc->messageOutput('200', $res);

    }

    /**
     * 修改广告位
     */
    public function editAdAction(){

        $this->verifySignCode($this->code, $this->sign);

        $data = array();
        $data['title'] = $this->title;
        $data['order_num'] = intval($this->order_num) > 9999 ? 9999 : intval($this->order_num) ;
        $data['url'] = strpos(trim($this->url), 'http://') === 0 ? trim($this->url) : 'http://'.trim($this->url) ;
        $data['img'] = $this->img;
        $data['status'] = $this->status != 1 ? 9 : 1 ;
        $id = $this->id;
        $data = $this->makeExtTime($data, $id);

        $res = $this->qa_svc->operateDataById('qa_slideshow', $data, $id);
        $this->qa_svc->messageOutput('200', $res);

    }

    /**
     * 删除一条广告位数据
     */
    public function delAdAction(){
        $this->verifySignCode($this->code, $this->sign);
        $data = array();
        $data['status'] = 0 ;
        $id = $this->id;
//        $data = $this->makeExtTime($data, $id);
        $res = $this->qa_svc->operateDataById('qa_slideshow', $data, $id);
        $this->qa_svc->messageOutput('200', $res, '删除成功！');
    }

    /**
     * 取单条数据
     */
    public function getOneAdAction(){
        $id = $this->id;
        if(!$id) $this->qa_svc->messageOutput('400');
        $res = $this->qa_svc->getOneById('qa_slideshow', $id);
        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     * 编辑广告排序
     */
    public function editAdOrderAction(){
        $this->verifySignCode($this->code, $this->sign);

        $listorder = unserialize($this->listorder);
        if(is_array($listorder)){
            foreach($listorder as $key => $value){
                $data = array();
                $data['order_num'] = intval($value);
                $this->qa_svc->operateDataById('qa_slideshow', $data, $key);
            }
        }

    }

    /**
     * 问答社区 for cms - 问题管理 - 问题审核列表
     * select 不验证是否合法
     * 相关sql : SELECT * FROM qa_question AS q
     *              LEFT JOIN qa_question_tag_rel AS qpr ON qpr.`question_id` = q.`id`
     *              WHERE q.`main_status` > 2 AND del_status = 0
     *              GROUP BY q.`id` HAVING MIN(qpr.`tag_id`) > 5
     *              ORDER BY q.`update_time` DESC LIMIT 0,15;
     * 临时方案sql : SELECT * FROM qa_question WHERE title <> '' AND main_status > 2 AND del_status = 0;
     * @author liuhongfei
     */
    public function getCommunityQuestionListAction(){

        $question_id= $this->question_id;  // qa_question.id

        $str_question_id = "";
        if($question_id){
            if(strpos($question_id, '|')){
                $ids = explode('|', $question_id);
                $str_question_id = "IN ('".implode("', '", $ids)."')";
            }else{
                $str_question_id = "= ".$question_id;
            }
        }

        $search = array('q.del_status' => "= 0", 'q.title' => "<> ''");
        $search['q.main_status'] = intval($this->main_status) > 1 ? "= ".intval($this->main_status) : "> 1";  // qa_question.main_status
        $search['q.update_time'] = $this->begin ? "> ".$this->begin : "";  // qa_question.update_time
        $search['q.update_time_end'] = $this->end ? "< ".$this->end : "";  // qa_question.update_time
        $search['q.id'] = $str_question_id;
        $search['q.uid'] = $this->uid ? "= '".$this->uid."'" : '' ; // qa_question.uid
        $search['q.username'] = $this->username ? "= '".$this->username."'" : '' ;  // qa_question.username
        $search['q.auditor_id'] = $this->auditor_id ? "= ".$this->auditor_id : '' ;  // qa_question.auditor_id

        if($this->recommend == 'all'){
            $search['q.recommend_status'] = '' ;
        }else if($this->recommend == '1'){
            $search['q.recommend_status'] = '= 1' ;
        }else{
            $search['q.recommend_status'] = '= 0' ;
        }
        $search = array_diff($search, array(''));

        $tag_id = $this->tag_id;    // qa_question_tag_rel
        $dest_id = $this->dest_id;  // qa_question_dest_rel.dest_id
        $page_num = $this->page ? $this->page : 1;
        $page = array('page' => $page_num, 'pageSize' => 15);

        // 查询基本信息
        $res = $this->qaq_svc->getCommunityQuestionCheckData($search, $tag_id, $dest_id, $page);

        // 调用redis 获取 sensitiveWord（敏感词） 和 swtime（敏感词更新时间）
        // 查询相关关系信息
        foreach($res['list'] as $key => $val){
            // 先查询redis数据
            $redis_key = str_replace('{id}', $val['id'], RedisDataService::REDIS_QA_QUESTION_INFO);
            $data = $this->qa_svc->getHashDataFromRedis($redis_key, array('sensitiveWord', 'time', 'dest_id', 'dest_name', 'base_id', 'dest_type', 'pinyin'));

            if($data['sensitiveWord']){
                $swords = json_decode(urldecode($data['sensitiveWord']));
                if(!empty($swords))
                    $res['list'][$key]['sensitiveWord'] = implode(', ', $swords);
                $res['list'][$key]['swtime'] = $data['time'];
            }

            if(empty($data['dest_id']) || empty($data['dest_name']) || empty($data['base_id']) || empty($data['dest_type']) || empty($data['pinyin'])){

                $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $val['id']);
                if(!empty($temp['dest_id'])){
                    $dest_base = $this->base_svc->getOneByDestId($temp['dest_id']);
                    if(!empty($dest_base['base_id']) && !empty($dest_base['dest_name']) && !empty($dest_base['dest_type'])){
                        $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                        if(!empty($base['base_id']) && !empty($base['pinyin'])){
                            $res['list'][$key]['dest_id'] = $temp['dest_id'];
                            $res['list'][$key]['dest_name'] = $dest_base['dest_name'];
                            $res['list'][$key]['base_id'] = $dest_base['base_id'];
                            $res['list'][$key]['dest_type'] = $dest_base['dest_type'];
                            $res['list'][$key]['pinyin'] = $base['pinyin'];

                            $add = array();
                            $add['dest_id'] = $temp['dest_id'];
                            $add['dest_name'] = $dest_base['dest_name'];
                            $add['base_id'] = $dest_base['base_id'];
                            $add['dest_type'] = $dest_base['dest_type'];
                            $add['pinyin'] = $base['pinyin'];
                            $this->qa_svc->setHashDataToRedis($add, $redis_key);
                            unset($add);
                            unset($temp);
                            unset($dest_base);
                            unset($base);
                        }
                    }
                }

            }else{
                $res['list'][$key]['dest_id'] = $data['dest_id'];
                $res['list'][$key]['dest_name'] = $data['dest_name'];
                $res['list'][$key]['base_id'] = $data['base_id'];
                $res['list'][$key]['dest_type'] = $data['dest_type'];
                $res['list'][$key]['pinyin'] = $data['pinyin'];
            }

            // redis qa:question:{id}:tags 添加
            $redis_key2 = str_replace('{id}', $val['id'], RedisDataService::REDIS_QA_QUESTION_TAGS);
            $tmp = $this->redis_svc->dataSMembers($redis_key2);

            if(count($tmp) == 0){
                $tag_ids = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $val['id']);
                $res['list'][$key]['tag_ids'] = $tag_ids;
                // redis qa:question:{id}:tags 添加
                $this->redis_svc->dataSAdd($redis_key2, $tag_ids);
            }else{
                $res['list'][$key]['tag_ids'] = $tmp;
            }
        }

        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     *  更新问题的主状态
     *  必选参数：id，auditor_id，audit_time，main_status，sign，code
     *  输出：json
     * @author liuhongfei
     */
    public function updateQuestionMainStatusAction(){

        $this->verifySignCode($this->code, $this->sign);

        $key = 0;
        if($this->id){
            $key = $this->id;
        }

        $params = array();
        if($this->auditor_id && $this->audit_time && $this->main_status){
            $params['auditor_id'] = $this->auditor_id;
            $params['audit_time'] = $this->audit_time;
            $params['main_status'] = $this->main_status;
        }

        if($key > 0 && !empty($params)){

            // 修改 qa_question 审核相关
            $res = $this->qa_svc->operateDataById('qa_question', $params, $key);
            // 补全 qa_question_statistics
            $this->qaqs_svc->updateExtInfo($key);

            // redis qa:question:{id} 记录
            // 数据整理
            $data = array();
            $question = $this->qa_svc->getRowByCondition('qa_question', 'id', $key);

            if($question && is_array($question)){

                $data['id'] = $question['id'];    // question_id
                $data['title'] = $question['title'];      // 问题标题
                $data['content'] = $question['content'];      // 问题内容
                $data['username'] = $question['username'];    // 用户名
                $data['recommend_status'] = $question['recommend_status'];    // 是否推荐
                $data['uid'] = $question['uid'];      // 用户id
                $data['update_time'] = $question['update_time'];      // 最后修改时间 -> 问题排序时间
                $data['main_status'] = $question['main_status'];      // 审核状态
                // 查询 dest_id
                $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $key);
                if($temp['dest_id']){
                    $dest_base = $this->base_svc->getOneByDestId($temp['dest_id']);
                    if($dest_base && is_array($dest_base)){
                        $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                        if($base && is_array($base)){
                            $data['dest_id'] = $temp['dest_id'];
                            $data['dest_name'] = $dest_base['dest_name'];
                            $data['base_id'] = $dest_base['base_id'];
                            $data['dest_type'] = $dest_base['dest_type'];
                            $data['pinyin'] = $base['pinyin'];
                        }
                    }
                }

                $ext = $this->qaqs_svc->getOneByQId($key);
                if($ext){
                    $data['valid_answer'] = $ext['valid_answer'];
                    $data['pv'] = $ext['pv'];
                }else{
                    $data['valid_answer'] = 0;
                    $data['pv'] = 0;
                }

                unset($question);
                unset($temp);

                // 写入redis qa:question:{id}
                $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_INFO);
                $this->qa_svc->setHashDataToRedis($data, $redis_key);

                // redis qa:question:{id}:tags 添加
                $data2 = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $key);
                $redis_key2 = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_TAGS);
                $this->redis_svc->dataDelete($redis_key2);
                $this->redis_svc->dataSAdd($redis_key2, $data2);

                // 更新所有列表
                $this->updateCqListRedis($key, $data, $data['dest_id'], $data2);

                $this->qa_svc->messageOutput('200', array('id' => $res), "数据更新成功！");
            }else{
                $this->qa_svc->messageOutput('501');
            }

        }else{
            $this->qa_svc->messageOutput('400');
        }

    }


    /**
     * 后台社区问答 - 回答列表
     */
    public function getCommunityAnswerListAction(){

        $search = array('del_status' => "= 0");
        $search['main_status'] = intval($this->main_status) > 1 ? "= ".intval($this->main_status) : "> 1";
        $search['update_time'] = $this->begin ? "> ".$this->begin : "";
        $search['update_time_end'] = $this->end ? "< ".$this->end : "";
        $search['question_id'] = intval($this->question_id) > 0 ? "= ".intval($this->question_id) : "" ;
        $search['id'] = intval($this->answer_id) > 0 ? "= ".intval($this->answer_id) : "" ;
        $search['uid'] = $this->uid ? "= '".$this->uid."'" : '' ; // qa_question.uid
        $search['username'] = $this->username ? "= '".$this->username."'" : '' ;
        $search['auditor_id'] = $this->auditor_id ? "= ".$this->auditor_id : '' ;
        $search = array_diff($search, array(''));

        $page_num = $this->page ? $this->page : 1;
        $page = array('page' => $page_num, 'pageSize' => 15);
        $res = $this->qaa_svc->getCommunityAnswerCheckData($search, $page);

        foreach($res['list'] as $key => $val){

            $redis_key = str_replace('{id}', $val['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
            $res['list'][$key]['question_info'] = $this->qa_svc->getHashDataFromRedis($redis_key, array('content', 'title', 'update_time', 'main_status', 'dest_name'));

            $redis_key2 = str_replace('{id}', $val['id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
            $tmp = $this->qa_svc->getHashDataFromRedis($redis_key2, array('sensitiveWord', 'time'));
            $res['list'][$key]['sensitiveWord'] = $tmp['sensitiveWord'];
            $res['list'][$key]['swtime'] = $tmp['time'];

        }

        $this->qa_svc->messageOutput('200', $res);
    }


    public function getCommunityCommentListAction(){

        $search = array('del_status' => "= 0");
        $search['main_status'] = intval($this->main_status) > 1 ? "= ".intval($this->main_status) : "> 1";
        $search['update_time'] = $this->begin ? "> ".$this->begin : "";
        $search['update_time_end'] = $this->end ? "< ".$this->end : "";
        $search['answer_id'] = intval($this->answer_id) > 0 ? "= ".intval($this->answer_id) : "" ;
        $search['id'] = intval($this->comment_id) > 0 ? "= ".intval($this->comment_id) : "" ;
        $search['uid'] = $this->uid ? "= '".$this->uid."'" : '' ; // qa_question.uid
        $search['username'] = $this->username ? "= '".$this->username."'" : '' ;
        $search['auditor_id'] = $this->auditor_id ? "= ".$this->auditor_id : '' ;
        $search = array_diff($search, array(''));

        $page_num = $this->page ? $this->page : 1;
        $page = array('page' => $page_num, 'pageSize' => 15);
        $res = $this->qaac_svc->getAnswerCommentCheckData($search, $page);

        foreach($res['list'] as $key => $val){

            $redis_key = str_replace('{id}', $val['answer_id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
            $res['list'][$key]['answer_info'] = $this->qa_svc->getHashDataFromRedis($redis_key, array('id','content', 'update_time', 'main_status'));

            $redis_key2 = str_replace('{id}', $val['id'], RedisDataService::REDIS_QA_COMMUNITY_COMMENT);
            $tmp = $this->qa_svc->getHashDataFromRedis($redis_key2, array('sensitiveWord', 'time'));
            $res['list'][$key]['sensitiveWord'] = $tmp['sensitiveWord'];
            $res['list'][$key]['swtime'] = $tmp['time'];

        }

        $this->qa_svc->messageOutput('200', $res);
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
     *  更新回答的主状态
     *  必选参数：id，auditor_id，audit_time，main_status，sign，code
     *  输出：json
     * @author liuhongfei
     */
    public function updateAnswerMainStatusAction(){

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

            $res = $this->qa_svc->operateDataById('qa_answer', $params, $key);
            // 更新 qa_answer_ext qa_question_ext
            $ae_temp = $this->qaae_svc->updateStatistics($key);
            // redis qa:question:{id} 记录
            // 数据整理
            $data = array();
            $answer = $this->qa_svc->getRowByCondition('qa_answer', 'id', $key);
            if(is_array($answer) && $answer){

                $data['id'] = $answer['id'];    // question_id
                $data['content'] = $answer['content'];      // 问题内容
                $data['username'] = $answer['username'];    // 用户名
                $data['question_id'] = $answer['question_id'];    // 用户名
                $data['uid'] = $answer['uid'];      // 用户id
                $data['update_time'] = $answer['update_time'];      // 最后修改时间 -> 问题排序时间
                $data['main_status'] = $answer['main_status'];      // 审核状态
                $data['del_status'] = $answer['del_status'];      // 是否删除
                unset($answer);

                // 写入redis qa:answer:{id}
                $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                $this->qa_svc->setHashDataToRedis($data, $redis_key);

                // 更新 qa_question 及redis
                $temp_res = $this->qaqs_svc->updateExtInfo($data['question_id']);
                $temp_data = array('valid_answer' => $temp_res['valid_answer']);
                $redis_key = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                $this->qa_svc->setHashDataToRedis($temp_data, $redis_key);

                // 对应问题的答案列表
                $redis_key2 = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
                $redis_key3 = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);

                if($data['main_status'] == 5){
                    $this->redis_svc->dataZAdd($redis_key2, $data['update_time'], $key);
                    if(isset($ae_temp['valid_comment'])){
                        $num_tmp = $ae_temp['valid_comment'];
                    }else{
                        $num_tmp = 0;
                    }
                    $this->redis_svc->dataZAdd($redis_key3, $num_tmp, $key);
                }else{
                    $this->redis_svc->dataZRem($redis_key2, $key);
                    $this->redis_svc->dataZRem($redis_key3, $key);
                }

                //准备更新列表数据
                $temp_key = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                $tmp = $this->redis_svc->dataHgetall($temp_key);
                if(!$tmp['dest_id']){
                    $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $data['question_id']);
                    $tmp['dest_id'] = $temp['dest_id'];
                }
                if(!$tmp['main_status'] || !$tmp['update_time'] || !$tmp['recommend_status']){
                    $question = $this->qa_svc->getRowByCondition('qa_question', 'id', $data['question_id']);
                    $tmp['main_status'] = $question['main_status'];
                    $tmp['update_time'] = $question['update_time'];
                    $tmp['recommend_status'] = $question['recommend_status'];
                }
                if(!$tmp['valid_answer']){
                    $tmp['valid_answer'] = $temp_res['valid_answer'];
                }

                // redis qa:question:{id}:tags 添加
                $redis_key2 = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_TAGS);
                $tag_ids = $this->redis_svc->dataSMembers($redis_key2);
                if(!$tag_ids || !is_array($tag_ids)){
                    $tag_ids = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $key);
                    $this->redis_svc->dataDelete($redis_key2);
                    $this->redis_svc->dataSAdd($redis_key2, $tag_ids);
                }

                $this->updateCqListRedis($data['question_id'], $tmp, $tmp['dest_id'], $tag_ids);

            }

            $this->qa_svc->messageOutput('200', array('id' => $res), "数据更新成功！");

        }else{
            $this->qa_svc->messageOutput('400');
        }

    }


    /**
     * 评论审核
     */
    public function updateCommentMainStatusAction(){

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

            $res = $this->qa_svc->operateDataById('qa_answer_comment', $params, $key);

            // redis qa:question:{id} 记录
            // 数据整理
            $data = array();
            $tmp = $this->qa_svc->getRowByCondition('qa_answer_comment', 'id', $key);

            if($tmp && is_array($tmp)){

                $data['id'] = $tmp['id'];    // question_id
                $data['content'] = $tmp['content'];      // 问题内容
                $data['username'] = $tmp['username'];    // 用户名
                $data['answer_id'] = $tmp['answer_id'];    // 用户名
                $data['uid'] = $tmp['uid'];      // 用户id
                $data['update_time'] = $tmp['update_time'];      // 最后修改时间 -> 问题排序时间
                $data['main_status'] = $tmp['main_status'];      // 审核状态
                $data['commented_username'] = $tmp['commented_username'];    // 用户名
                $data['commented_uid'] = $tmp['commented_uid'];      // 用户id
                $data['del_status'] = $tmp['del_status'];      // 用户id

                // 更新 qa_answer_ext
                $temp_res = $this->qaae_svc->updateStatistics($tmp['answer_id']);
                $temp_data = array('valid_comment' => $temp_res['valid_comment']);
                $redis_key = str_replace('{id}', $tmp['answer_id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                $this->qa_svc->setHashDataToRedis($temp_data, $redis_key);
                $qid_array = $this->qa_svc->getHashDataFromRedis($redis_key, array('question_id'));
                if(is_array($qid_array) && $qid_array){
                    $qid = $qid_array['question_id'];
//                    echo $qid;die;
                    $redis_key_hot = str_replace('{id}', $qid, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
                    $this->redis_svc->dataZAdd($redis_key_hot, $temp_res['valid_comment'], $tmp['answer_id']);
                }
                unset($tmp);

                // 写入redis qa:answer:{id}
                $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_COMMUNITY_COMMENT);
                $this->qa_svc->setHashDataToRedis($data, $redis_key);

                // 对应问题的答案列表
                $redis_key2 = str_replace('{id}', $data['answer_id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT);
                // 如果答案审核通过
                if($data['main_status'] == 5){
                    $this->redis_svc->dataZAdd($redis_key2, $data['update_time'], $key);
                }else{
                    $this->redis_svc->dataZRem($redis_key2, $key);
                }

                $this->qa_svc->messageOutput('200', array('id' => $res), "数据更新成功！");

            }else{
                $this->qa_svc->messageOutput('501');
            }


        }else{
            $this->qa_svc->messageOutput('400');
        }

    }


    /**
     * 获取单条数据
     * @author liuhongfei
     */
    public function getOneInfoAction(){

        $id = intval($this->id);
        $type = $this->type;
        $cache = $this->cache ? true : false ;

        $type_all = array('comment', 'answer', 'question');

        if(!$id || !$type || !in_array($type, $type_all)){
            $this->qa_svc->messageOutput('400');
        }else{
            $table = $type  == 'comment' ? 'qa_answer_'.$type : 'qa_'.$type;
            if($cache){
                // 后续补充 redis
            }else{
                $res = $this->qa_svc->getOneById($table, $id);
                if($type == 'question'){
                    $ext = $this->qaqs_svc->getOneByQId($id);
                }else if($type == 'answer'){
                    if($res['question_id']){
                        $ext = $this->qa_svc->getOneById('qa_question', $res['question_id']);
                    }
                }
            }

            $this->qa_svc->messageOutput('200', array('base'=>$res, 'ext'=>$ext));
        }

    }

    /**
     * -----------------------------------
     * 写入敏感词数据到redis
     * @author liuhongfei
     * -----------------------------------
     */
    public function setSensitiveWordAction(){

        $this->verifySignCode($this->code, $this->sign);

        $data = json_decode($this->data, true);
        $type = $this->type;

        if($data['id'] <= 0){
            $this->qa_svc->messageOutput('400');
        }

        if($type == 'question'){
            $redis_key = str_replace('{id}', $data['id'], RedisDataService::REDIS_QA_QUESTION_INFO);
        }elseif($type == 'answer'){
            $redis_key = str_replace('{id}', $data['id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
        }else{
            $redis_key = str_replace('{id}', $data['id'], RedisDataService::REDIS_QA_COMMUNITY_COMMENT);
        }
        $data['time'] = time();

        // 写入redis
        $set_type = $this->qa_svc->setHashDataToRedis($data, $redis_key);

        // 写入状态返回
        if($set_type){
            $this->qa_svc->messageOutput('200');
        }else{
            $this->qa_svc->messageOutput('500');
        }

    }

//
//    /**
//     * get热度值
//     * @param $id
//     * @param int $answer
//     * @param int $recommend
//     * @return int
//     */
//    private function getHotNum($id, $answer=0, $recommend=0){
//        $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_PV);
//        $time = time();
//        $date = array();
//        for($i=1; $i<8; $i++){
//            $date[] = date("Ymd", $time-$i*86400);
//        }
//        $pv_7d = 0;
//        foreach($date as $val){
//            $tmp = $this->redis_svc->dataZScore($temp_key, $val);
//            if(!$tmp){ $tmp = 0; }
//            $pv_7d = $pv_7d + $tmp;
//        }
//        $hot = $pv_7d + $answer * 10 + $recommend * 100;
//        return $hot;
//    }
//

    public function getCommunityTagAction(){
        $where = " `status` = 1 AND `id` > 9 ";
        $cate = $this->qa_svc->getAllByCondition('qa_tag_category', $where);
//        var_dump($cate); die;
        $data = array();
        foreach($cate as $val){
            $temp_key = $val['id'];
            $data['cate'][$temp_key] = $val['name'];
            $where_temp = " `status` = 1 AND `category_id` = ".$val['id'];
            $temp_tag = $this->qa_svc->getAllByCondition('qa_tag', $where_temp);
//            echo json_encode($temp_tag); die;
            foreach($temp_tag as $v){
                $tmp_key = $v['id'];
                $redis_key = str_replace('{tag_id}', $tmp_key, RedisDataService::REDIS_QA_COMMUNITY_TAG);
                $this->qa_svc->setHashDataToRedis($v, $redis_key);
                $data['tag'][$temp_key][$tmp_key] = $v['name'];
            }
        }
        $this->qa_svc->messageOutput('200', $data);
    }


    /**
     * 管理员修改问题的目的地标签
     */
    public function setDestByAdminAction(){
        $this->verifySignCode($this->code, $this->sign);
        $dest_id = $this->dest_id;
        $id = $this->question_id;

        if(!$id || !$dest_id){
            $this->qa_svc->messageOutput('400');
        }else{
            $params = array(
                'table' => 'qa_question_dest_rel',
                'where' => " question_id = {$id}"
            );
            $this->qa_svc->deleteData($params);

            $data = array(
                'question_id' => $id,
                'dest_id' => $dest_id
            );
            $res =$this->qa_svc->operateDataById('qa_question_dest_rel', $data);

            if($res){
                $dest_base = $this->base_svc->getOneByDestId($dest_id);
                if($dest_base && is_array($dest_base)){
                    $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                    if($base && is_array($base)){
                        $tmp['dest_id'] = $dest_id;
                        $tmp['dest_name'] = $dest_base['dest_name'];
                        $tmp['base_id'] = $dest_base['base_id'];
                        $tmp['dest_type'] = $dest_base['dest_type'];
                        $tmp['pinyin'] = $base['pinyin'];
                    }
                    // 写入redis qa:question:{id}
                    $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
                    $this->qa_svc->setHashDataToRedis($tmp, $redis_key);
                }
            }

            $this->qa_svc->messageOutput('200',$data);
        }

    }

    /**
     * 管理员修改问题的Tag
     */
    public function setTagsByAdminAction(){

        $this->verifySignCode($this->code, $this->sign);
        $tags = $this->tags;
        $id = $this->question_id;
        if(!$id || !$tags){
            $this->qa_svc->messageOutput('400');
        }else{
            $tag_id = explode('|',$tags);
            if(count($tag_id) > 0){
                $return = 1;

                $params = array(
                    'table' => 'qa_question_tag_rel',
                    'where' => " question_id = {$id} "
                );
                $this->qa_svc->deleteData($params);

                foreach($tag_id as $tv){
                    $data = array(
                        'question_id' => $id,
                        'tag_id' => $tv
                    );
                    $res =$this->qa_svc->operateDataById('qa_question_tag_rel', $data);

                    $return = $return*$res;
                }

                if($return > 1){
                    $redis_key2 = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_TAGS);
                    $this->redis_svc->dataDelete($redis_key2);
                    $this->redis_svc->dataSAdd($redis_key2, $tag_id);

                    $this->qa_svc->messageOutput('200', $return);
                }else{
                    $this->qa_svc->messageOutput('500');
                }

            }
        }

    }


    /**
     * 调用beanstalk更新列表
     * @param $qid
     * @param $cqinfo
     * @param string $dest_id
     * @param array $tag_ids
     */
    private function updateCqListRedis($qid, $cqinfo, $dest_id = '', $tag_ids = array()){

        $base_array = array(
            'question_id' => $qid,
            'main_status' => $cqinfo['main_status'],
            'update_time' => $cqinfo['update_time'],
            'valid_answer' => $cqinfo['valid_answer'],
            'recommend_status' => $cqinfo['recommend_status']
        );
        if($dest_id){
            $dest_array = array(
                'type' => 'DEST',
                'rkey' => $dest_id,
            );
            $bt_dest_array = array_merge($base_array, $dest_array);
            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_dest_array));
        }

        if($tag_ids){
            foreach($tag_ids as $nv){
                $tag_array = array(
                    'type' => 'TAG',
                    'rkey' => $nv,
                );
                $bt_tag_array = array_merge($base_array, $tag_array);
                $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_tag_array));
            }
        }

        $bt_all_array = array_merge($base_array, array('type' => 'ALL'));
        $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_all_array));

    }


    public function setQuestionBaseByAdminAction(){

        $this->verifySignCode($this->code, $this->sign);
        $id = $this->question_id;
        $data['recommend_status'] = $this->recommend_status;
        $data['title'] = $this->title;
        $data['content'] = $this->content;
        $data['main_status'] = $this->main_status;

        $res =$this->qa_svc->operateDataById('qa_question', $data, $id);

        if($res){
            $this->qa_svc->messageOutput('200', $res);
        }else{
            $this->qa_svc->messageOutput('500');
        }

    }

    public function setAnswerByAdminAction(){

        $this->verifySignCode($this->code, $this->sign);
        $id = $this->answer_id;
        $data['content'] = $this->content;
        $data['main_status'] = $this->main_status;

        $res =$this->qa_svc->operateDataById('qa_answer', $data, $id);

        if($res){
            $this->qa_svc->messageOutput('200', $res);
        }else{
            $this->qa_svc->messageOutput('500');
        }

    }


    /**
     *
     */
    public function importQaFromRedisAction(){

        $id = $this->id;

        $user_key_1 = RedisDataService::REDIS_QA_COMMUNITY_IMPORT_USER1;
        $user_key_2 = RedisDataService::REDIS_QA_COMMUNITY_IMPORT_USER2;
        $content_key = RedisDataService::REDIS_QA_COMMUNITY_IMPORT_CONTENT;
        $config_key = RedisDataService::REDIS_QA_COMMUNITY_IMPORT_CONFIG;
        $log_key = RedisDataService::REDIS_QA_COMMUNITY_IMPORT_RESLOG;

        $imconfig = $this->redis_svc->dataHmget($config_key, array('per_ask_time', 'per_answer_time'));

//        $import_num = $this->redis_svc->getHlen($content_key);
        $import_keys = $this->redis_svc->getHkeys($content_key);

        if(!$id){
            $find_id = min($import_keys);
        }elseif(in_array($id, $import_keys)){
            $find_id = $id;
        }

        if($find_id){

            // 详情
            $temp = $this->redis_svc->dataHmget($content_key, array($find_id));
            $data = unserialize($temp[$find_id]);

            // tag
            $tags = $this->getTags();

            // 提问用户
            $user_ask = $this->getRandUser($user_key_1);

            if($data && is_array($data)){

                if($data['question']){
                    $data['question']['uid'] = $user_ask['uid'];
                    $data['question']['username'] = $user_ask['username'];
                    $data['question']['main_status'] = 5;
                }

//                var_dump($data['question']); die;
                $res = $this->qa_svc->operateDataById('qa_question', $data['question']);

                $this->redis_svc->dataHdel($content_key, $find_id);

                if($res){
                    $max_ask = $imconfig['per_ask_time'] ? $imconfig['per_ask_time'] - 2 : 8;
                    if($user_ask['times'] > $max_ask){
                        $this->redis_svc->dataZRem($user_key_1, $user_ask['uid'].'-'.$user_ask['username']);

                    }else{
                        $new_times = ($user_ask['times'] + 1) * 10000 + rand(0, 9999);
                        $this->redis_svc->dataZAdd($user_key_1, $new_times, $user_ask['uid'].'-'.$user_ask['username']);
                    }

                    $log_num = $this->redis_svc->dataHmget($log_key, array('res_num'));
                    $nlog = $log_num['res_num'] + 1;
                    $this->redis_svc->dataHmset($log_key, array('res_num' => $nlog), false);


                    // 更新用户回答数
                    $tmp_key = str_replace('{uid}', $user_ask['uid'], RedisDataService::REDIS_QA_COMMUNITY_USER_QUESTION);
                    $this->redis_svc->dataSAdd($tmp_key, $res);

                    if($data['dest_tag']){
                        $dest = array('question_id' => $res, 'dest_id' => intval($data['dest_tag']));
                        $this->qa_svc->operateDataById('qa_question_dest_rel', $dest);

                        $dest_base = $this->base_svc->getOneByDestId($data['dest_tag']);
                        if(!empty($dest_base['base_id']) && !empty($dest_base['dest_name']) && !empty($dest_base['dest_type'])){
                            $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                            if(!empty($base['base_id']) && !empty($base['pinyin'])){
                                $data['question']['dest_id'] = $data['dest_tag'];
                                $data['question']['dest_name'] = $dest_base['dest_name'];
                                $data['question']['base_id'] = $dest_base['base_id'];
                                $data['question']['dest_type'] = $dest_base['dest_type'];
                                $data['question']['pinyin'] = $base['pinyin'];
                                unset($dest_base);
                                unset($base);
                            }
                        }

                    }

                    if($data['tag']){
                        $tags_temp = array_keys(array_intersect($tags, $data['tag']));
                        foreach($tags_temp as $val){
                            $this->qa_svc->operateDataById('qa_question_tag_rel', array('question_id' => $res, 'tag_id' => $val));
                        }

                        // redis qa:question:{id}:tags 添加
                        $redis_key2 = str_replace('{id}', $res, RedisDataService::REDIS_QA_QUESTION_TAGS);
                        $this->redis_svc->dataSAdd($redis_key2, $tags_temp);
                    }

                    if($data['answers']){
                        foreach($data['answers'] as $v){

                            if($v){
                                // 回答用户
                                $user_answer = $this->getRandUser($user_key_2);

                                $ans = array();
                                $ans['content'] = $v;
                                $ans['question_id'] = $res;
                                $ans['uid'] = $user_answer['uid'];
                                $ans['username'] = $user_answer['username'];
                                $ans['main_status'] = 5;

                                $time = time();
                                $new_time = rand($data['question']['update_time'], $time);
                                $ans['update_time'] = $new_time;
                                $ans['create_time'] = $new_time;

                                $res2 = $this->qa_svc->operateDataById('qa_answer', $ans);

                                $sm_tmp = $this->qaae_svc->updateStatistics($res2);


                                $max_answer = $imconfig['per_answer_time'] ? $imconfig['per_answer_time'] - 2 : 8;
                                if($user_answer['times'] > $max_answer){
                                    $this->redis_svc->dataZRem($user_key_2, $user_answer['uid'].'-'.$user_answer['username']);
                                }else{
                                    $new_times = ($user_answer['times'] + 1) * 10000 + rand(0, 9999);
                                    $this->redis_svc->dataZAdd($user_key_2, $new_times, $user_answer['uid'].'-'.$user_answer['username']);
                                }

                                // 写入answer缓存
                                $redis_key_a = str_replace('{id}', $res2, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                                $this->qa_svc->setHashDataToRedis($ans, $redis_key_a);

                                // 写入answer缓存
                                $redis_key_aid = str_replace('{uid}', $user_answer['uid'], RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
                                $redis_key_qid = str_replace('{uid}', $user_answer['uid'], RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);
                                $this->redis_svc->dataSAdd($redis_key_aid, $res2);
                                $this->redis_svc->dataSAdd($redis_key_qid, $res);

                                // 对应问题的答案列表
                                $redis_key_a2 = str_replace('{id}', $res, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
                                $redis_key_a3 = str_replace('{id}', $res, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
                                $this->redis_svc->dataZAdd($redis_key_a2, $ans['update_time'], $res2);
                                $this->redis_svc->dataZAdd($redis_key_a3, 0, $res2);
                            }

                        }
                    }

                    // 更新问题表状态
                    $tmp = $this->qaqs_svc->updateExtInfo($res);
                    if($data['ext']){
                        $res3 = $this->qa_svc->operateDataById('qa_question_ext', array('pv' => $data['ext']), $tmp['ext_id']);
                        $data['question']['pv'] = $data['ext'];
                    }

                    // 写入redis qa:question:{id} 更新问题基本信息
                    $data['question']['valid_answer'] = $tmp['valid_answer'];
                    $data['question']['recommend_status'] = 0;
                    $redis_key = str_replace('{id}', $res, RedisDataService::REDIS_QA_QUESTION_INFO);
                    $this->qa_svc->setHashDataToRedis($data['question'], $redis_key);

                    $this->updateCqListRedis($res, $data['question'], $data['dest_tag'], $tags_temp);

                }

                $nid = $find_id+1;
//                sleep(2);
                header("location: http://ca.lvmama.com/qacomforcms/import-qa-data/?id=".$nid);

            }

        }else{
            if($id > max($import_keys)){
                echo 'ok';
            }else{
                $nid = $id+1;
                header("location: http://ca.lvmama.com/qacomforcms/import-qa-data/?id=".$nid);
            }
        }

    }

    private function getRandUser($key){
        $temp = $this->redis_svc->getZCard($key);
        $begin = rand(0, $temp - 1);
        $user = $this->redis_svc->getZRange($key, $begin, $begin, true);

        $key = key($user);
        $val = $user[$key];

        $key_array = explode('-', $key);
        $user_tmp = array();
        $user_tmp['uid'] = $key_array[0];
        $user_tmp['username'] = $key_array[1];
        $user_tmp['times'] = intval($val/10000);

        if($user_tmp){
            return $user_tmp;
        }else{
            $this->getRandUser($key);
        }

    }


    private function getTags(){

        $where_str = ' `category_id` > 9 AND `category_id` <16 AND `status` = 1 ';
        $tags = $this->qa_svc->getRowByConditionSrt('qa_tag', '*', $where_str, 'all');

        if($tags){
            $res = array();
            foreach($tags as $val){
                $res[$val['id']] = $val['name'];
            }
        }

        return $res;

    }



}
