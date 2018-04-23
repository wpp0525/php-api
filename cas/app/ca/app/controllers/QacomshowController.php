<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-7-15
 * Time: 下午3:00
 */
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\QaQuestionDataService;
use Lvmama\Cas\Service\QaQuestionStatisticsDataService;
use Lvmama\Cas\Service\BeanstalkDataService;

class QacomshowController extends ControllerBase {

    private $qa_svc;
    private $qaqs_svc;
    private $qaae_svc;

    private $tag_svc;
    private $base_svc;
    private $detail_svc;

    private $qaa_svc;
    private $redis;

    public $community_tag = array(
        'cate' => array(10 => '出行方式', 11 => '美食', 12 => '住宿', 13 => '购物', 14 => '交通', 15 => '指南信息'),
        'tag' => array(
            10 => array(49 => '自由行', 50 => '半自助游', 51 => '跟团游', 52 => '自驾', 53 => '徒步', 54 => '亲子', 55 => '度假', 56 => '蜜月',
                97 => '一日游', 98 => '二日游', 99 => '穷游'),
            11 => array(57 => '米其林', 58 => '特色餐厅', 59 => '夜市/排挡', 60 => '美食'),
            12 => array(61 => '青旅', 62 => '客栈', 63 => '民宿', 64 => '酒店', 65 => '度假村', 66 => '预定', 100 => '住宿'),
            13 => array(67 => '购物', 68 => '免税店', 69 => '奢侈品', 70 => '打折/折扣', 71 => '特产/纪念品', 72 => '奥特莱斯', 73 => '商圈',
                74 => '消费'),
            14 => array(75 => '飞机', 76 => '火车', 77 => '汽车', 78 => '租车/包车', 79 => '地铁', 80 => '出租车', 81 => '轮船/邮轮', 82 => '骑行', 101 => '交通'),
            15 => array(83 => '护照/通行证', 84 => '签证', 85 => '货币/汇率', 86 => '退税', 87 => '小费', 88 => '上网/电话卡', 89 => '行程/攻略',
                90 => '景点/门票', 91 => '气候', 92 => '语言', 93 => '安全', 94 => '邮政', 95 => '上网/电话卡', 96 => '摄影', 102 => '地址', 103 => '线路',
                104 => '时间', 105 => '简介'),
        )
    );

    public function initialize() {
        parent::initialize();
        $this->qa_svc = $this->di->get('cas')->get('qa_common_data_service');
        $this->redis_svc=$this->di->get('cas')->get('redis_data_service');
        $this->qaqs_svc = $this->di->get('cas')->get('qa_question_statistics_data_service');

        $this->qaae_svc = $this->di->get('cas')->get('qa_answer_ext_data_service');

        $this->base_svc = $this->di->get('cas')->get('dest_base_service');
        $this->detail_svc = $this->di->get('cas')->get('dest_detail_service');
        $this->tag_svc = $this->di->get('cas')->get('qatag-data-service');

        $this->qaa_svc = $this->di->get('cas')->get('qaanswer-data-service');
        $this->redis = $this->di->get('cas')->getRedis();
    }

    /**
     * 首页的广告位
     */
    public function getIndexAdListAction(){
        $where = array();
        $where['status'] = "1";
        // 组成查询全部条件
        $params_condition = array(
            'table' =>'qa_slideshow',
            'select' => 'id, title, img, url',
            'where' => $where,
            'order' => 'order_num desc',
            'limit' => '6'
        );
        // 查询输出结果 json 格式
        $res = $this->qa_svc->getByParams($params_condition);
        $this->qa_svc->messageOutput('200', $res['list']);
    }

    /**
     * 关联目的地的问题列表
     * 注：可考虑ZREVRANGEBYSCORE替换
     */
    public function getDestCQuestionListAction(){

        $dest_id = $this->dest_id;
        if(!$dest_id){
            $this->qa_svc->messageOutput('400');
        }
        $page = intval($this->page) > 0 ? intval($this->page) : 1;
        $per_num = intval($this->per_num) > 0 ? intval($this->per_num) : 10;
        $type = $this->type ? $this->type : '' ;
        $cache = $this->cache === false ? false : true ;

        if($cache){
            switch ($type){
                case 'zero':
                    $tmp = RedisDataService::REDIS_QA_COMMUNITY_DEST_NOANSWER;
                    break;
                case 'hot':
                    $tmp = RedisDataService::REDIS_QA_COMMUNITY_DEST_HOT;
                    break;
                default:
                    $tmp = RedisDataService::REDIS_QA_COMMUNITY_DEST_REL;
            }
            $redis_key = str_replace('{dest_id}', $dest_id, $tmp);
            $totle = $this->redis_svc->getZCard($redis_key);

            $begin = $totle - $page * $per_num;
            $end = $begin + $per_num - 1;
            $begin = $begin < 0 ? 0 : $begin;

            if($end >= 0){
                $res = $this->redis_svc->getZRange($redis_key, $begin, $end, true);
                arsort($res);
                $data = array();
                foreach($res as $val=>$rv){
                    $data[] = $this->getCqInfoByQid($val);
                }
            }

            $res = array(
                'pages' => array(
                    'itemCount' => $totle,
                    'pageCount' => ceil($totle/$per_num),
                    'page' => $page,
                    'pageSize' => $per_num,
                ),
                'data' => $data
            );

            $this->qa_svc->messageOutput('200', $res);
        }else{
            /*
             * 查询组成列表
             * 方案一：仅搜索单页
             * 方案二：修复列表redis
             */
        }

    }

    /**
     * 查询答案基础信息
     */
    public function getCQAnswerBaseAction(){
        $id = $this->id;
        $select = " id, uid, content ";
        $where = " id = {$id}";
        $res = $this->qa_svc->getRowByConditionSrt('qa_answer', $select, $where, 'one');

        $this->qa_svc->messageOutput('200', $res);
    }


    /**
     * 获取问答社区问题详情
     */
    public function getCQuestionShowAction(){
        // 问题本身基础信息
        $qid = $this->question_id;

        $data = $this->getCqInfoByQid($qid);

        $this->setPvUp($qid);

        if(is_array($data)){
//            var_dump($data);
//            echo $data['valid_answer'];die;
            $data['show_time'] = date("Y-m-d H:i:s", $data['update_time']);
            if($data['valid_answer'] > 0){
                $tmp = $this->getAnswerList($qid);
                if($tmp){
                    $data['answer_list'] = $tmp;
                }
            }
        }

        $this->qa_svc->messageOutput('200', $data);
    }

    public function getAnswerListByTypeAction(){
        $qid = $this->qid;
        $page = $this->page;
        $size = $this->size;
        $type = $this->type?$this->type:'time';

//        echo '1111';die;
        $data = $this->getAnswerList($qid, $page, $size, $type);
        $this->qa_svc->messageOutput('200', $data);

    }

    /**
     * 问题回答列表
     * @param $qid
     * @param int $page
     * @param int $size
     * @param $type
     * @return array
     */
    private function getAnswerList($qid, $page = 1, $size = 10, $type=''){
        if($type == 'hot'){
            $tmp_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
        }else{
            $tmp_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
        }
        $total = $this->redis_svc->getZCard($tmp_key);

        $begin = $total - $page * $size;
        $end = $begin + $size - 1;
        $begin = $begin < 0 ? 0 : $begin;

        $data = array();
        if($end >= 0){
            $res = $this->redis_svc->getZRange($tmp_key, $begin, $end, true);
//            echo json_encode($res); die;
            arsort($res);
            foreach($res as $val => $rv){
                $temp_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                $tmp = $this->redis_svc->dataHgetall($temp_key);
                if(is_array($tmp)){
                    $tmp['show_time'] = date("Y-m-d H:i:s", $tmp['update_time']);
                    $tmp_key_comment = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT);
                    $tmp['comment_num'] = $this->redis_svc->getZCard($tmp_key_comment);
                    $data[] = $tmp;
                }
            }
        }
        $return = array(
            'pages' => array(
                'itemCount' => $total,
                'pageCount' => ceil($total/$size),
                'page' => $page,
                'pageSize' => $size,
            ),
            'data' => $data
        );
        return $return;
    }

    /**
     * 获取问答社区问题详情
     */
    public function getCQuestionInfoAction(){

        // 问题本身基础信息
        $qid = $this->question_id;
//        $qid = 8522;
//        echo $qid; die;
        $temp_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_QUESTION_INFO);
        $res = array();
        $res['info'] = $this->redis_svc->dataHgetall($temp_key);

        $this->setPvUp($qid);

        // 获取标签
        $redis_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_QUESTION_TAGS);
        $res['tag_ids'] = $this->redis_svc->dataSMembers($redis_key);

        // 获取回答
        $tmp_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
        $res['res_totle'] = $this->redis_svc->getZCard($tmp_key);
        $data = array();
        if($res['res_totle'] > 0){
            $begin = $res['res_totle'] - 10 ;
            $end = -1 ;
            $answer_list_tmp = $this->redis_svc->getZRange($tmp_key, $begin, $end, true);
            arsort($answer_list_tmp);

            foreach($answer_list_tmp as $val => $rv){
                $temp_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                $data[] = $this->redis_svc->dataHgetall($temp_key);
            }
        }
        $res['res_list'] = $data;

        $this->qa_svc->messageOutput('200', $res);

    }

    /**
     *
     */
    public function getCQuestionChildList(){

        $key_id = $this->key_id;
        $page = intval($this->page) > 0 ? intval($this->page) : 1;
        $per_num = intval($this->per_num) > 0 ? intval($this->per_num) : 10;
        $type = strtoupper($this->type) == 'ANSWER' ? 'ANSWER' : 'COMMENT' ;
        $cache = $this->cache === false ? false : true ;

        if($cache){

            if($type == 'ANSWER'){
                $tmp = RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER;
                $tmp2 = RedisDataService::REDIS_QA_COMMUNITY_ANSWER;
            }else{
                $tmp = RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT;
                $tmp2 = RedisDataService::REDIS_QA_COMMUNITY_COMMENT;
            }

            $data = array();
            $redis_key = str_replace('{id}', $key_id, $tmp);
            $totle = $this->redis_svc->getZCard($redis_key);
            $data['res_totle'] = $totle;

            $begin = $totle - $page * $per_num ;
            $end = $begin + $per_num ;
            $res = $this->redis_svc->getZRange($redis_key, $begin, $end, true);
            arsort($res);

            foreach($res as $val =>$rv){
                $temp_key = str_replace('{id}', $val, $tmp2);
                $data['res_list'][] = $this->redis_svc->dataHgetall($temp_key);
            }

            $this->qa_svc->messageOutput('200', $data);

        }else{


        }

    }


    /**
     * 新建/修改问题
     */
    public function updateQuestionAction(){

        $key = intval($this->id) ? intval($this->id) : '';

        $time = time();
        // qa_question
        $data = array();
        $data['title'] = $this->title;
        $data['content'] = $this->content;
        $data['uid'] = $this->uid;
        $data['username'] = $this->username;
        $data['main_status'] = 2;
        if(!$key){
            $data['create_time'] = $time;
        }
        $data['update_time'] = $time;

//        echo json_encode($data); die;
        $res = $this->qa_svc->operateDataById('qa_question', $data, $key);

        if($res){

            // qa_question_dest_rel
            $dest_id = intval($this->dest_id) ? intval($this->dest_id) : '';
            if($dest_id){
                $params = array(
                    'table' => 'qa_question_dest_rel',
                    'where' =>"question_id = '{$res}'",
                );
                $dest_array = array(
                    "type" => "DEST",
                    "rkey" => $dest_id
                );
//            echo json_encode($dest_array_bt); die;
                $this->qa_svc->deleteData($params);
                $this->qa_svc->operateDataById('qa_question_dest_rel', array('question_id'=>$res, 'dest_id'=>$dest_id));
            }

            // qa_question_tag_rel
            $tag_all = array();
            foreach($this->community_tag['tag'] as $val){
                $tag_all = $tag_all +$val;
            }
            $tags = explode(';', $this->tags);
            array_pop($tags);
            $params = array(
                'table' => 'qa_question_tag_rel',
                'where' =>"question_id = '{$res}'",
            );
            $this->qa_svc->deleteData($params);
            $tags_array = array();

            foreach($tags as $val){
                $new_key = array_search($val, $tag_all);
                if($new_key){
                    $tags_array[] = array(
                        "type" => "TAG",
                        "rkey" => $new_key
                    );
                    $this->qa_svc->operateDataById('qa_question_tag_rel', array('question_id'=>$res,'tag_id'=>$new_key));
                }
            }

            // qa_question_ext
            if($key){

                // 更新问题列表
                $temp_res = $this->qaqs_svc->updateExtInfo($res);
                $cqinfo = array(
                    'main_status' => 2,
                    'update_time' => $time,
                    'valid_answer' => $temp_res['valid_answer'],
                );

                // 写入redis qa:question:{id} 更新问题基本信息
                $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_INFO);
                $this->qa_svc->setHashDataToRedis($cqinfo, $redis_key);

                $cqinfo['recommend_status'] = 0;
                $this->updateCqListRedis($res, $cqinfo, $dest_id, $tags);

            }else{

                $data = array();
                $data['question_id'] = $res;
                $data['ip'] = $this->ip;
                $data['create_time'] = $time;
                $data['update_time'] = $time;
                $this->qa_svc->operateDataById('qa_question_ext', $data);
            }

            // 更新用户回答数
            $tmp_key = str_replace('{uid}', $this->uid, RedisDataService::REDIS_QA_COMMUNITY_USER_QUESTION);
            $this->redis_svc->dataSAdd($tmp_key, $res);
        }

        $this->qa_svc->messageOutput('200', array('id'=>$res));

    }

    public function updateAnswerAction(){

        $key = intval($this->id) ? intval($this->id) : '';
        $time = time();

        $data = array();
        $data['content'] = $this->content;
        $data['question_id'] = $this->question_id;
        $data['uid'] = $this->uid;
        $data['username'] = $this->username;
        $data['main_status'] = 2;
        $data['update_time'] = $time;
        if(!$key){
            $data['create_time'] = $time;
        }

        $res = $this->qa_svc->operateDataById('qa_answer', $data, $key);

        if($res){
            // 更新问题表状态
            $tmp = $this->qaqs_svc->updateExtInfo($data['question_id']);
//            $qid = $data['question_id'];
            // 更新回答状态
            if($key){
                $this->qaae_svc->updateStatistics($res);

                // 更新 question 相关
                $redis_key = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                $this->qa_svc->setHashDataToRedis(array('valid_answer'=>$tmp['valid_answer']), $redis_key);

                // 对应问题的答案列表
                $redis_key2 = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
                $this->redis_svc->dataZRem($redis_key2, $key);


                // 写入redis qa:answer:{id}
                $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                $this->qa_svc->setHashDataToRedis($data, $redis_key);

                // 更新列表
                //准备更新列表数据
                $temp_key = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                $tmp_data = $this->redis_svc->dataHgetall($temp_key);
                if(!$tmp_data['dest_id']){
                    $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $data['question_id']);
                    $tmp_data['dest_id'] = $temp['dest_id'];
                }
                if(!$tmp_data['main_status'] || !$tmp_data['update_time'] || !$tmp_data['recommend_status']){
                    $question = $this->qa_svc->getRowByCondition('qa_question', 'id', $data['question_id']);
                    $tmp_data['main_status'] = $question['main_status'];
                    $tmp_data['update_time'] = $question['update_time'];
                    $tmp_data['recommend_status'] = $question['recommend_status'];
                }
                if(!$tmp_data['valid_answer']){
                    $tmp_data['valid_answer'] = $tmp['valid_answer'];
                }
                // redis qa:question:{id}:tags 取数据
                $redis_key2 = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_TAGS);
                $tag_ids = $this->redis_svc->dataSMembers($redis_key2);

                $this->updateCqListRedis($data['question_id'], $tmp_data, $tmp_data['dest_id'], $tag_ids);

            }else{
                $data2 = array();
                $data2['answer_id'] = $res;
                $data2['ip'] = $this->ip;
                $data2['create_time'] = $time;
                $data2['update_time'] = $time;
                $this->qa_svc->operateDataById('qa_answer_ext', $data2);
            }

            // 更新用户回答数
            $tmp_key1 = str_replace('{uid}', $this->uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
            $tmp_key2 = str_replace('{uid}', $this->uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);
            $this->redis_svc->dataSAdd($tmp_key1, $res);
            $this->redis_svc->dataSAdd($tmp_key2, $data['question_id']);

        }
        $this->qa_svc->messageOutput('200', array('id'=>$res));

    }


    public function getCQCommentPageAction(){
        $answer_id = $this->answer_id;
        $page = $this->page;
        $res = $this->getCommentList($answer_id, $page, 5);
        $this->qa_svc->messageOutput('200',$res);
    }

    /**
     * @param $aid
     * @param int $page
     * @param int $size
     * @return array
     */
    private function getCommentList($aid, $page = 1, $size = 5){
        $tmp_key = str_replace('{id}', $aid, RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT);
        $total = $this->redis_svc->getZCard($tmp_key);

        $begin = $total - $page * $size;
        $end = $begin + $size - 1;
        $begin = $begin < 0 ? 0 : $begin;

        $data = array();
        if($end >= 0){
            $res = $this->redis_svc->getZRange($tmp_key, $begin, $end, true);
            arsort($res);
            foreach($res as $val=>$rv){
//                const REDIS_QA_COMMUNITY_COMMENT= 'qa:comment:{id}';
                $temp_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_COMMENT);
                $tmp = $this->redis_svc->dataHgetall($temp_key);
                if(is_array($tmp)){
                    $tmp['show_time'] = date("Y-m-d H:i:s", $tmp['update_time']);
                    $data[] = $tmp;
                }
            }
        }
        $return = array(
            'pages' => array(
                'itemCount' => $total,
                'pageCount' => ceil($total/$size),
                'page' => $page,
                'pageSize' => $size,
            ),
            'data' => $data
        );
        return $return;
    }



    public function updateAnswerCommentAction(){

        $key = intval($this->id) ? intval($this->id) : '';
        $time = time();

        $data = array();
        $data['content'] = $this->content;
        $data['answer_id'] = $this->answer_id;
        $data['uid'] = $this->uid;
        $data['username'] = $this->username;
        $data['commented_uid'] = $this->commented_uid;
        $data['commented_username'] = $this->commented_username;
        $data['main_status'] = 2;
        $data['update_time'] = $time;
        if(!$key){
            $data['create_time'] = $time;
        }

        $res = $this->qa_svc->operateDataById('qa_answer_comment', $data, $key);

        if($res){
            // 更新回答状态
            $this->qaae_svc->updateStatistics($data['answer_id']);

        }
        $this->qa_svc->messageOutput('200', array('id'=>$res));

    }

    /**
     * 更新pv
     * @param $key
     * @return mixed
     */
    private function setPvUp($key){

        $redis_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_QUESTION_INFO);
        $info = $this->qa_svc->getHashDataFromRedis($redis_key, array('pv'));
        $pv_num = 1;
        if($info['pv']){
            $pv_num = $pv_num + $info['pv'];
        }
        $this->qa_svc->setHashDataToRedis(array('pv' => $pv_num), $redis_key);

        $temp_key = str_replace('{id}', $key, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_PV);
        $name = date("Ymd", time());
        $res = $this->redis_svc->dataZIncrBy($temp_key, 1, $name);
        return $res;

    }

    /**
     * 获取总排序的值
     */
    public function getAllQuestionListAction(){
        $page = intval($this->page) > 0 ? intval($this->page) : 1;
        $per_num = intval($this->per_num) > 0 ? intval($this->per_num) : 10;
        $type = $this->type ? $this->type : '' ;

        switch ($type){
            case 'zero':
                $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_NOANSWER;
                break;
            case 'hot':
                $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_HOT;
                break;
            default:
                $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
        }

        $totle = $this->redis_svc->getZCard($redis_key);
        $begin = $totle - $page * $per_num;
        $end = $begin + $per_num - 1;
        $begin = $begin < 0 ? 0 : $begin;

        if($end >= 0){

            $res = $this->redis_svc->getZRange($redis_key, $begin, $end, true);
            arsort($res);

            $data = array();
            foreach($res as $val => $rv){
                $data[] = $this->getCqInfoByQid($val);
            }

        }
        $res = array(
            'pages' => array(
                'itemCount' => $totle,
                'pageCount' => ceil($totle/$per_num),
                'page' => $page,
                'pageSize' => $per_num,
            ),
            'data' => $data
        );

        $this->qa_svc->messageOutput('200', $res);
    }


    public function getTagCQuestionListAction(){

        $tag_id = $this->tag_id;
        $page = intval($this->page) > 0 ? intval($this->page) : 1;
        $per_num = intval($this->per_num) > 0 ? intval($this->per_num) : 10;
        $type = $this->type ? $this->type : '' ;

        switch ($type){
            case 'zero':
                $tmp = RedisDataService::REDIS_QA_COMMUNITY_TAG_NOANSWER;
                break;
            case 'hot':
                $tmp = RedisDataService::REDIS_QA_COMMUNITY_TAG_HOT;
                break;
            default:
                $tmp = RedisDataService::REDIS_QA_COMMUNITY_TAG_REL;
        }

        $redis_key = str_replace('{tag_id}', $tag_id, $tmp);

        $totle = $this->redis_svc->getZCard($redis_key);
        $begin = $totle - $page * $per_num;
        $end = $begin + $per_num - 1;
        $begin = $begin < 0 ? 0 : $begin;

        if($end >= 0){

            $res = $this->redis_svc->getZRange($redis_key, $begin, $end, true);
            arsort($res);

            $data = array();
            foreach($res as $val=>$rv){
                $data[] = $this->getCqInfoByQid($val);
            }

        }
        $res = array(
            'pages' => array(
                'itemCount' => $totle,
                'pageCount' => ceil($totle/$per_num),
                'page' => $page,
                'pageSize' => $per_num,
            ),
            'data' => $data
        );

        $this->qa_svc->messageOutput('200', $res);

    }


    /**
     * 查询是否关注过
     */
    public function isFollowedAction(){
        $uid = $this->uid;
        $id = $this->question_id;
        $temp_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);

        $res = $this->redis_svc->dataSiSMember($temp_key, $id);

        if($res){
            $str = 1;
        }else{
            $str = 0;
        }

        $this->qa_svc->messageOutput('200', array('data'=>$str));
    }


    /**
     * 判断是否回答过
     */
    public function isAnsweredAction(){
        $uid = $this->uid;
        $id = $this->question_id;
        $temp_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);

        $res = $this->redis_svc->dataSiSMember($temp_key, $id);

        if($res){
            $temp_key_1 = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
            $temp_key_2 = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
//            echo $uid; die;
            $aids1 = $this->redis->Smembers($temp_key_1);
            $aids2 = $this->redis_svc->getZRange($temp_key_2, 0, -1, false);
            $aids = array_intersect($aids1,$aids2);
            if($aids){
                $aids = array_values($aids);
                $str = $aids[0];
            }else{
                $str = 0;
            }
        }else{
            $str = 0;
        }
        $this->qa_svc->messageOutput('200', array('data'=>$str));
    }





    public function isLikedAction(){
        $uid = $this->uid;
        $id = $this->id;
//        echo json_encode($uid);die;
        $res = $this->qa_svc->findInfoByRel('qa_answer_like', $uid, 'answer_id', $id);
        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     * 关注/赞同
     */
    public function setFollowAction(){

        $uid = $this->uid;
        $id = $this->id;
        $type = $this->type == 'follow' ? 'follow' : 'like';
        $oper = $this->oper == 'add' ? 'add' : 'del';

        if($type == 'follow'){
            $table = 'qa_question_follow';
            $col = 'question_id';
        }else{
            $table = 'qa_answer_like';
            $col = 'answer_id';
        }

        $res = $this->qa_svc->findInfoByRel($table, $uid, $col, $id);

        if($res && $oper == 'add'){
            $this->qa_svc->messageOutput('200', array('res' => 'followed'));
        }elseif(!$res && $oper == 'del'){
            $this->qa_svc->messageOutput('200', array('res' => 'unfollowed'));
        }else{

            if($oper == 'add'){
                if($type == 'follow'){
                    $temp_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
                    $this->redis_svc->dataSAdd($temp_key, $id);
                }else{

                    $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                    $num = $this->qa_svc->getHashDataFromRedis($redis_key, array('liked_num'));
                    $new_num = intval($num['liked_num']) + 1;
                    $this->qa_svc->setHashDataToRedis(array('liked_num'=>$new_num), $redis_key);
                }

                $data = array();
                $time = time();
                $data[$col] = $id;
                $data['uid'] = $uid;
                $data['update_time'] = $time;
                $data['create_time'] = $time;
                $this->qa_svc->operateDataById($table, $data);

            }else{
                if($type == 'follow'){
                    $temp_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
                    $this->redis_svc->dataSRem($temp_key, $id);
                }

                $params = array(
                    'table' => $table,
                    'where' => "{$col} = '{$id}' AND uid = '{$uid}'"
                );
                $this->qa_svc->deleteData($params);
            }

            $this->qa_svc->messageOutput('200', array('res' => 'success'));

        }

    }

    public function getUserTopAction(){

        $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ANSWER_TOP5;
        $top = $this->redis_svc->getZRange($redis_key, 0, -1, true);

        if(!$top){
            $res = $this->qaa_svc->getAnswerTop5();
            $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1-time();

//            echo $endToday; die;
            if(is_array($res)){
                foreach($res as $val){
                    $this->redis_svc->dataZAdd($redis_key, $val['top'], $val['uid'], $endToday);
                    $uid = $val['uid'];
                    $top[$uid] = (int)$val['top'];
                }
            }
        }else{
            arsort($top);
        }
//
//        var_dump($top);
        $this->qa_svc->messageOutput('200', $top);

    }

    public function getTotalQandUAction(){

        $tmp_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
        $total = $this->redis_svc->getZCard($tmp_key);
        $return['q_num'] = $total;
        $res = $this->qaa_svc->getTotalUserNum();
        $return['u_num'] = $res['totaluser'];

        $this->qa_svc->messageOutput('200', $return);
    }


    /**
     * 获取用户信息
     */
    public function getUserDataAction(){
        $uid = $this->uid;
        $temp_key1 = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_QUESTION);
        $temp_key2 = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
        $temp_key3 = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
        $res = array();
        $res['question'] = $this->redis_svc->getSCard($temp_key1);
        $res['answer'] = $this->redis_svc->getSCard($temp_key2);
        $res['follow'] = $this->redis_svc->getSCard($temp_key3);

        $this->qa_svc->messageOutput('200', $res);
    }

    /**
     * 通过ids获取 列表信息
     */
    public function getCQuestionByIdsAction(){
        $ids_str = $this->ids_str;
        $ids = explode('|', $ids_str);
//        var_dump($ids);
        $data = array();
        foreach($ids as $val){
            $tmp = $this->getCQInfoById($val);
            if(count($tmp) > 1){
                $data[] = $tmp;
            }
        }
        $this->qa_svc->messageOutput('200', $data);
    }

    /**
     * id查询详情
     * @param $id
     * @return array
     */
    private function getCQInfoById($id){
        $data = array();
        // 获取基本信息
        $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
        $data = $this->redis_svc->dataHgetall($temp_key);

        // 获取标签
        $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_TAGS);
        $data['tag_ids'] = $this->redis_svc->dataSMembers($redis_key);
        return $data;
    }



    /**
     * 获取问题的基本信息
     * 返回字段：问题ID id, 问题标题 title, 问题内容 content, 用户ID uid, 用户名 username, 用户头像 user_img
     *          推荐状态 recommend_status, 审核状态 main_status, 提问时间 update_time, 有效回答数 valid_answer,
     *          总的浏览数 pv, 关联目的地ID dest_id, 目的地 base_id, 目的地拼音 pinyin, 目的地名称 dest_name
     *          关联的标签ID tag_ids,
     *          若改为多目的地另行增加 dest_list
     * @param $id
     * @return array
     */
    private function getCqInfoByQid($id){

        $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
        $data = $this->redis_svc->dataHgetall($temp_key);
        // 获取标签
        $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_TAGS);
        $data['tag_ids'] = $this->redis_svc->dataSMembers($redis_key);

        $is_complete = $this->checkCqInfoComplete($data);

        if($is_complete){
            foreach($is_complete as $val){
                if($val == 'cq_base'){
                    $base = $this->getCQBase($id);
                    $data = $data + $base;
                }else if($val == 'cq_tags'){
                    $tags = $this->getCQTags($id);
//                    $data = $data + $tags;
                    $data['tag_ids'] = $tags;
                }else if($val == 'cq_ext'){
                    $ext = $this->getCQExt($id);
                    $data = $data + $ext;
                }else if($val == 'cq_dest'){
                    $dest = $this->getCQDest($id, @$data['dest_id']);
                    $data = $data + $dest;
                }else if($val == 'cq_user'){
//                    $user = $this->getCQUser($id);
//                    $data = $data + $user;
                }
            }
        }
        return $data;
    }

    public function getOneByQIdAction(){
        $qid = $this->qid;
        $data = $this->getCqInfoByQid($qid);

        $this->qa_svc->messageOutput('200', $data);
    }

    /**
     * 删除回答/评论
     */
    public function delCQCommentAction(){

        $id = $this->item_id;
        $type = $this->type;
        $uid = $this->uid;

        if($type == "answer"){

            $res = $this->qa_svc->getRowByCondition('qa_answer', 'id', $id);
            if(is_array($res)){
                if($res['uid'] == $uid){
                    $res2 = $this->qa_svc->operateDataById('qa_answer', array('del_status'=>1), $id);
                    $tmp = $temp_res = $this->qaqs_svc->updateExtInfo($res['question_id']);

                    $redis_key2 = str_replace('{id}', $res['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
                    $redis_key_hot = str_replace('{id}', $res['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
                    $this->redis_svc->dataZRem($redis_key2, $id);
                    $this->redis_svc->dataZRem($redis_key_hot, $id);

                    $temp_data = array('valid_answer' => $tmp['valid_answer']);
                    $redis_key = str_replace('{id}', $res['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                    $this->qa_svc->setHashDataToRedis($temp_data, $redis_key);

                    $tmp_data = array('del_status' => 1);
                    $redis_key3 = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                    $this->qa_svc->setHashDataToRedis($tmp_data, $redis_key3);

                    //准备更新列表数据
                    $temp_key = str_replace('{id}', $res['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                    $tmp_data = $this->redis_svc->dataHgetall($temp_key);
                    if(!$tmp_data['dest_id']){
                        $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $res['question_id']);
                        $tmp_data['dest_id'] = $temp['dest_id'];
                    }
                    if(!$tmp_data['main_status'] || !$tmp_data['update_time'] || !$tmp_data['recommend_status']){
                        $question = $this->qa_svc->getRowByCondition('qa_question', 'id', $res['question_id']);
                        $tmp_data['main_status'] = $question['main_status'];
                        $tmp_data['update_time'] = $question['update_time'];
                        $tmp_data['recommend_status'] = $question['recommend_status'];
                    }
                    if(!$tmp_data['valid_answer']){
                        $tmp_data['valid_answer'] = $tmp['valid_answer'];
                    }
                    // redis qa:question:{id}:tags 取数据
                    $redis_key2 = str_replace('{id}', $res['question_id'], RedisDataService::REDIS_QA_QUESTION_TAGS);
                    $tag_ids = $this->redis_svc->dataSMembers($redis_key2);

                    $redis_key4= str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
                    $this->redis_svc->dataSRem($redis_key4, $id);

                    $str = "`uid` = '{$uid}' AND `question_id` = '{$res['question_id']}' AND `del_status` = 0 ";
                    $temp = $this->qa_svc->getRowByConditionSrt('qa_answer', 'id', $str, 'all');
                    if(!$temp || count($temp) == 0){
                        $redis_key5= str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);
                        $this->redis_svc->dataSRem($redis_key5, $res['question_id']);
                    }

                    $this->updateCqListRedis($res['question_id'], $tmp_data, $tmp_data['dest_id'], $tag_ids);

                    if($res2){
                        $this->qa_svc->messageOutput('200', array(),'删除成功！');
                    }else{
                        $this->qa_svc->messageOutput('500');
                    }
                }else{
                    $this->qa_svc->messageOutput('200', array(),'您无权删除此项内容');
                }
            }

        }elseif($type == "comment"){

            $res = $this->qa_svc->getRowByCondition('qa_answer_comment', 'id', $id);
            if(is_array($res)){
                if($res['uid'] == $uid){
                    $res2 = $this->qa_svc->operateDataById('qa_answer_comment', array('del_status'=>1), $id);
                    $tmp = $this->qaae_svc->updateStatistics($res['answer_id']);

                    $redis_key2 = str_replace('{id}', $res['answer_id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT);
                    $this->redis_svc->dataZRem($redis_key2, $id);

                    $temp_data = array('valid_comment' => $tmp['valid_comment']);
                    $redis_key = str_replace('{id}', $res['answer_id'], RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
                    $this->qa_svc->setHashDataToRedis($temp_data, $redis_key);

                    $qid_array = $this->qa_svc->getHashDataFromRedis($redis_key, array('quesiton_id'));
                    if(is_array($qid_array) && $qid_array){
                        $qid = $qid_array['question_id'];
                        $redis_key_hot = str_replace('{id}', $qid, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
                        $this->redis_svc->dataZAdd($redis_key_hot, $tmp['valid_comment'], $res['answer_id']);
                    }

                    $tmp_data = array('del_status' => 1);
                    $redis_key3 = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_COMMENT);
                    $this->qa_svc->setHashDataToRedis($tmp_data, $redis_key3);

                    if($res2){
                        $this->qa_svc->messageOutput('200', array(),'删除成功！');
                    }else{
                        $this->qa_svc->messageOutput('500');
                    }
                }else{
                    $this->qa_svc->messageOutput('200', array(),'您无权删除此项内容');
                }
            }
        }

    }


    /**
     * 查询CQ基础信息并补充到redis
     * @param $id
     * @return mixed
     */
    private function getCQBase($id){
        // 读库查询
        $select = "`id`, `title`, `content`, `uid`, `username`, `recommend_status`, `main_status`, `update_time`";
        $where = "`id` = {$id}";
        $res = $this->qa_svc->getRowByConditionSrt('qa_question',  $select, $where, 'one');
        // 写入redis
        if(is_array($res) && $res){
            $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
            $this->qa_svc->setHashDataToRedis($res, $temp_key);
        }
        return $res;
    }

    /**
     * 查询CQ TAGS信息并补充到redis
     * @param $id
     * @return mixed
     */
    private function getCQTags($id){
        // 读库查询
        $tag_ids = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', "tag_id", 'question_id', $id);
        // 写入redis
        if(is_array($tag_ids) && $tag_ids){
            $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_TAGS);
            $this->redis_svc->dataSAdd($redis_key, $tag_ids);
        }
        return $tag_ids;
    }

    /**
     * 查询CQ EXT信息并补充到redis
     * @param $id
     * @return mixed
     */
    private function getCQExt($id){
        // 读库查询
        $select = "`valid_answer`, `pv`";
        $where = "`question_id` = {$id}";
        $res = $this->qa_svc->getRowByConditionSrt('qa_question_ext',  $select, $where, 'one');
        // 写入redis
        if(is_array($res) && $res){
            $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
            $this->qa_svc->setHashDataToRedis($res, $temp_key);
        }
        return $res;
    }

    /**
     * 查询CQ DEST信息并补充到redis
     * @param $id
     * @param $dest_id
     * @return array
     */
    private function getCQDest($id, $dest_id){
//        echo $dest_id; die;
        if(!$dest_id || $dest_id == "NO DATA"){
            $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $id);
            $dest_id = intval($temp['dest_id']) ? intval($temp['dest_id']) : 0 ;
        }
        $return = array();
        if($dest_id){
            $dest_base = $this->base_svc->getOneByDestId($dest_id);
            if($dest_base){
                if($dest_base['dest_type'] && $dest_base['base_id'] && $dest_base['dest_name']){
                    $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                    if($base['pinyin']){
                        $return['dest_id'] = $dest_id;
                        $return['pinyin'] = $base['pinyin'];
                        $return['dest_name'] = $dest_base['dest_name'];
                        $return['base_id'] = $dest_base['base_id'];
                    }
                }
            }
        }
        // 写入redis
        if($return){
            $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_INFO);
            $this->qa_svc->setHashDataToRedis($return, $temp_key);
        }
        return $return;
    }


    /**
     * @param $id
     * @return mixed
     */
    private function getCQUser($id){

//        $url="http://login.lvmama.com/nsso/ajax/userinfo/getUserInfo.do?userIDs=".$uid;
//        $user = $this->api($url);
//        if($user['code']==200){
//        $data['username'] = $user['data']['userAvatars'][0]['userName'];
//        }

    }

    private function checkCqInfoComplete($data){
        $return = array();
//        var_dump($data);
        if($data){
            if(!$data['tag_ids']){
                $return[] = 'cq_tags';
            }
            if(!@$data['dest_id'] || !@$data['dest_name'] || !@$data['pinyin'] || !@$data['base_id']){
                $return[] = 'cq_dest';
            }
            if(!@$data['pv'] === '' || !@$data['valid_answer'] === ''){
                $return[] = 'cq_ext';
            }
            if(!@$data['title'] || !@$data['id'] || !@$data['content'] || !@$data['uid'] || !@$data['username'] || !@$data['recommend_status'] === '' || !@$data['main_status'] || !@$data['update_time']){
                $return[] = 'cq_base';
            }
            if(!@isset($data['user_img'])){
//                $return[] = 'cq_user';
            }
        }else{
            $return = array('cq_base', 'cq_tags', 'cq_ext', 'cq_dest', 'cq_user');
        }
        return $return;
    }


    /**
     * 获取目的地信息
     */
    public function getDestInfoByIdAction(){
        $dest_id = $this->dest_id;
        $data=array();
        $dest_base = $this->base_svc->getOneByDestId($dest_id);
        if($dest_base){
            if($dest_base['dest_type'] && $dest_base['base_id'] && $dest_base['dest_name']){
                $base = $this->detail_svc->getDestDetailByBaseId($dest_base['base_id'], $dest_base['dest_type']);
                $data = $dest_base + $base;
            }
        }
        $this->qa_svc->messageOutput('200', $data);
    }

    public function getOneTagInfoAction(){
        $tag_id = $this->tag_id;
        $temp_key = str_replace('{tag_id}', $tag_id, RedisDataService::REDIS_QA_COMMUNITY_TAG);
        $data = $this->redis_svc->dataHgetall($temp_key);
        if(!$data){
            $data = $this->tag_svc->getById($tag_id);
            if($data){
                $this->qa_svc->setHashDataToRedis($data, $temp_key);
            }
        }
        $this->qa_svc->messageOutput('200', $data);
    }




    public function getOneTagListAction(){
        // commnuity | product | all
//        $range = $this->range ? $this->range : 'commnuity';
        // default | user | all
        $type = $this->type ? $this->type : 'default';

        if($type == 'default'){
            $where_str = ' `id` > 9 AND `id` <16 AND `status` = 1 ';
        }
        $cates = $this->qa_svc->getRowByConditionSrt('qa_tag_category', '*', $where_str, 'all');
        if($cates && is_array($cates)){
            foreach($cates as $val){
                $key = $val['id'];
                $return['cate'][$key] = $val['name'];
                $where = " `category_id` = '{$key}' AND `status` = 1 ";
                $tags = $this->qa_svc->getRowByConditionSrt('qa_tag', '*', $where, 'all');
                if($tags && is_array($tags)){
                    foreach($tags as $tag){
                        $key2 = $tag['id'];
                        $return['tag'][$key][$key2] = $tag['name'];
                    }
                }
            }
        }

        $this->qa_svc->messageOutput('200', $return);

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
}