<?php

use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
/**
 * QA个人中心
 * User: sx
 * Date: 2016/6/20
 * Time: 16:29
 */
class QausercenterController extends ControllerBase
{
    private $admin_answer;
    private $answer;
    private $question;
    private $question_dest_rel;
    private $question_product_rel;
    private $tag;
    private $tag_category;
    private $adminReal;
    private $productInfo;
    private $qa_svc;
    private $base_svc;
    private $detail_svc;
    private $qaqs_svc;

    public function initialize(){
        $this->api = 'QaUserCenter';
        $this->admin_answer = $this->di->get('cas')->get('qaadminanswer-data-service');
        $this->answer = $this->di->get('cas')->get('qaanswer-data-service');
        $this->question = $this->di->get('cas')->get('qaquestion-data-service');
        $this->question_dest_rel = $this->di->get('cas')->get('qaquestiondestrel-data-service');
        $this->question_product_rel = $this->di->get('cas')->get('qaquestionproductrel-data-service');
        $this->tag = $this->di->get('cas')->get('qatag-data-service');
        $this->tag_category = $this->di->get('cas')->get('qatagcategory-data-service');
        $this->adminReal = $this->di->get('cas')->get('admin-real-service');
        $this->productInfo = $this->di->get('cas')->get('product-info-data-service');
        parent::initialize();

        $this->qa_svc = $this->di->get('cas')->get('qa_common_data_service');
        $this->base_svc = $this->di->get('cas')->get('dest_base_service');
        $this->detail_svc = $this->di->get('cas')->get('dest_detail_service');
        $this->qaqs_svc = $this->di->get('cas')->get('qa_question_statistics_data_service');
    }
    /**
         * 我的咨询
         * @param int $uid 用户ID
         * @param int $return_type 返回结果的类型 0:问答列表 1:只返回提问数量
         * @param int $page 页码
         * @param int $pageSize 每页显示条数
         * @return string | json
         * @example curl -i -X POST http://ca.lvmama.com/qausercenter/getUserQuestion
         */
        public function getUserQuestionAction(){
            $uid = isset($this->uid) ? $this->uid : 0;
            $return_type = isset($this->return_type) ? $this->return_type : 0;
            $page = isset($this->page) ? $this->page : 1;
            $pageSize = isset($this->pageSize) ? $this->pageSize : 15;
            if(!is_numeric($uid) || !$uid){
                $this->_errorResponse(10001,'请传入正确的用户ID');
            }
            if(!is_numeric($return_type) || $return_type > 1){
                $this->_errorResponse(10002,'请传入正确的返回结果类型');
            }
            if(!is_numeric($page) || $page < 1){
                $this->_errorResponse(10003,'请传入正确的页码');
            }
            if(!is_numeric($pageSize) || $pageSize < 0 || $pageSize > 30){
                $this->_errorResponse(10004,'请传入正确的每页显示条数(0到30之间)');
            }
        $_question_total = $this->question->getRsBySql('SELECT COUNT(id) AS n FROM qa_question WHERE uid = '.$uid.' AND del_status = 0',true);
        $total = isset($_question_total['n']) ? $_question_total['n'] : 0;
        $totalPage = $total > 0 ? ceil($total / $pageSize) : 1;
        $page = $page < 1 ? 1 : $page;
        $page = $page > $totalPage ? $totalPage : $page;
        $data = array('list' => array(),'pages' => array());
        if($return_type == 1){
            $data['pages'] = array('itemCount' => $total,'pageCount' => $totalPage,'page' => $page,'pageSize' => $pageSize);
        }else{
            $start_limit = ($page - 1) * $pageSize;
            $_question = $this->question->getRsBySql('SELECT * FROM qa_question WHERE uid = '.$uid.' AND del_status = 0 ORDER BY id DESC LIMIT '.$start_limit.','.$pageSize);
            foreach($_question as $k=>$v){
                //取得与问题相关的产品ID
                $product = $this->answer->getRsBySql('SELECT product_id FROM qa_question_product_rel WHERE question_id = '.$v['id'],true);
                if(isset($product['product_id'])){
                    $proInfo = $this->productInfo->getProductInfo($product['product_id']);
                    $_question[$k]['product_id'] = $product['product_id'];
                    $_question[$k]['productName'] = isset($proInfo['productName']) ? $proInfo['productName'] : '';
                    $_question[$k]['productUrl'] = isset($proInfo['url']) ? $proInfo['url'] : '';
                }else{
                    $_question[$k]['product_id'] = 0;
                    $_question[$k]['productName'] = '';
                    $_question[$k]['productUrl'] = '';
                }
                //取得问题的回答
                $answer = $this->answer->getRsBySql('SELECT * FROM qa_admin_answer WHERE question_id = '.$v['id'].' AND status = 1',true);
                if(isset($answer['admin_id'])){
                    $answer['admin_name'] = $this->adminReal->getAdminReal($answer['admin_id']);
                }
                $_question[$k]['answer'] = $answer ? $answer : new ArrayObject();
            }
            $data['list'] = $_question;
            $data['pages'] = array('itemCount' => $total,'pageCount' => $totalPage,'page' => $page,'pageSize' => $pageSize);
        }
        $this->_successResponse($data);
    }

    /**
     * 用户中心 - 产品问答 - 汇总数据
     * uid => 32位md5 uid
     *
     */
    public function getCQUcenterMyAction(){

        $uid = isset($this->uid) ? $this->uid : '';
        if(strlen($uid) != 32 || !$uid){
            $this->_errorResponse(10001,'请传入正确的用户ID');
        }

        $redis_key_f = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
        $redis_key_a = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
        $redis_key_q = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_QUESTION);

        $total_question = $this->redis_svc->getSCard($redis_key_q);
        $total_answer = $this->redis_svc->getSCard($redis_key_a);
        $total_follow = $this->redis_svc->getSCard($redis_key_f);

        $res = array(
            'question' => $total_question,
            'answer' => $total_answer,
            'follow' => $total_follow,
        );

        $this->_successResponse($res);
    }



    /**
     * 用户中心 - 产品问答
     * uid => 32位md5 uid
     *
     */
    public function getCQuestionUcenterAction(){

        $uid = isset($this->uid) ? $this->uid : '';
        $type = !empty($this->type) ? $this->type : 'question';
        $page = !empty($this->page) ? $this->page : 1;
        $pageSize = !empty($this->pageSize) ? $this->pageSize : 10;

        if(strlen($uid) != 32 || !$uid){
            $this->_errorResponse(10001,'请传入正确的用户ID');
        }

        $default_type = array('question', 'answer', 'follow');
        if(!in_array($type, $default_type)){
            $this->_errorResponse(10002,'请传入正确的返回结果类型');
        }

        if(!is_numeric($page) || $page < 1){
            $this->_errorResponse(10003,'请传入正确的页码');
        }

        if(!is_numeric($pageSize) || $pageSize < 0 || $pageSize > 30){
            $this->_errorResponse(10004,'请传入正确的每页显示条数(0到30之间)');
        }

        switch($type){
            case 'follow':
                $redis_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
                break;
            case 'answer':
                $redis_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
                break;
            default:
                $redis_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_QUESTION);
        }

        //==============================================
        // 加个页码判断
        $total = $this->redis_svc->getSCard($redis_key);
        $res['pages'] = array(
            'itemCount' => $total,
            'pageCount' => ceil($total/$pageSize),
            'page' => $page,
            'pageSize' => $pageSize
        );
        //==============================================

        if($page > $res['pages']['pageCount']){
            $this->_errorResponse(10003,'请传入正确的页码');
        }

        $begin = ($page - 1) * $pageSize;
        $condition_array = array(
            'limit' => array($begin, 10),
            'sort' => 'desc',
        );
        $ids = $this->redis_svc->dataSort($redis_key, $condition_array);

        if($ids && is_array($ids)){
            if($type == 'answer'){
                foreach($ids as $k => $val){
                    $res['list'][$k]['answer'] = $this->getAnswerInfo($val);
                    if($res['list'][$k]['answer']['update_time']){
                        $res['list'][$k]['answer']['update_time'] = $res['list'][$k]['answer']['update_time'].'000';
                    }
                    if($res['list'][$k]['answer'] && $res['list'][$k]['answer']['question_id']){
                        $res['list'][$k]['question'] = $this->getQuestionInfo($res['list'][$k]['answer']['question_id']);
                        if($res['list'][$k]['question']['update_time']){
                            $res['list'][$k]['question']['update_time'] = $res['list'][$k]['question']['update_time'].'000';
                        }
                    }else{
                        $res['list'][$k]['question'] = array();
                    }
                }
            }else{
                foreach($ids as $k => $val){
                    $res['list'][$k]['question'] = $this->getQuestionInfo($val);
                    if($res['list'][$k]['question']['update_time']){
                        $res['list'][$k]['question']['update_time'] = $res['list'][$k]['question']['update_time'].'000';
                    }
                }
            }
        }

        $this->_successResponse($res);
    }

    /**
     * 获取问题信息
     * @param $val
     * @return array
     */
    private function getQuestionInfo($val){
        $data = array();
        // 获取基本信息
        $temp_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_QUESTION_INFO);
        $tmp = $this->redis_svc->dataHgetall($temp_key);

        $condition = array(
            'base' => array('id', 'title', 'main_status', 'recomment_status', 'uid', 'username','update_time'),
            'ext' => array('pv', 'valid_answer'),
            'dest' => array('dest_id', 'dest_id', 'pinyin', 'dest_name'),
        );
        $need_save = 1;
        if($tmp && is_array($tmp)){
            $tmp_key = array_keys($tmp);
            $base_status = array_diff($condition['base'], $tmp_key);
            $ext_status = array_diff($condition['ext'], $tmp_key);
            $dest_status = array_diff($condition['dest'], $tmp_key);
            if(!$base_status && !$ext_status && !$dest_status){
                $need_save = 0;
                $data = $tmp;
            }else{
                $res = $this->getCqInfoByDB($base_status, $ext_status, $dest_status, $val);
                $data = array_merge($tmp, $res);
            }
        }else{
            $res = $this->getCqInfoByDB($condition['base'], $condition['ext'], $condition['dest'], $val);
            $data = $res;
        }

        if($need_save && $data){
            $this->redis_svc->dataHmset($temp_key, $data, false);
        }

        // 获取标签
        $redis_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_QUESTION_TAGS);
        $tag_ids = $this->redis_svc->dataSMembers($redis_key);

        $all_tags = $this->getTags();
        $data['tag_ids'] = $this->getTagInfo($tag_ids, $all_tags, $val);

        return $data;
    }

    /**
     * TAG
     * @param $tag_ids
     * @param $all_tags
     * @param $id
     * @return array
     */
    private function getTagInfo($tag_ids, $all_tags, $id) {
        if(!$tag_ids || !is_array($tag_ids)){
            $tag_ids = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $id);

            // redis qa:question:{id}:tags 添加
            if($tag_ids && is_array($tag_ids)){
                $redis_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_QUESTION_TAGS);
                $this->redis_svc->dataSAdd($redis_key, $tag_ids);
            }
        }

        $res = array();
        if($tag_ids && is_array($tag_ids)){
            foreach($tag_ids as $tag_id){
                if(isset($all_tags[$tag_id])){
                    $res[$tag_id] = $all_tags[$tag_id];
                }
            }
        }
        return $res;
    }

    /**
     * 产品问答tag
     * @return array
     */
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

    /**
     * 从数据库中查询redis中没有的必要数据
     * @param array $base_status
     * @param array $ext_status
     * @param array $dest_status
     * @param $id
     * @return array
     */
    private function getCqInfoByDB($base_status = array(), $ext_status = array(), $dest_status = array(), $id){
        $data = array();
        if($base_status && is_array($base_status)){
            $res = $this->qa_svc->getRowByCondition('qa_question', 'id', $id);
            $data = $res;
        }
        if($ext_status && is_array($ext_status)){
            $res = $this->qa_svc->getRowByCondition('qa_question_ext', 'question_id', $id);
            $data['pv'] = isset($res['pv']) && intval($res['pv']) > 0 ? intval($res['pv']) : 0;
            $data['valid_answer'] = isset($res['valid_answer']) && intval($res['valid_answer']) > 0 ? intval($res['valid_answer']) : 0;
        }
        if($dest_status && is_array($dest_status)){

            $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $id);
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
        }

        return $data;
    }

    /**
     * 获取回答信息
     * @param $val
     * @return array
     */
    private function getAnswerInfo($val){
        $data = array();

        $temp_key = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);
        $tmp = $this->redis_svc->dataHgetall($temp_key);

        $condition = array(
            'base' => array('id', 'content', 'main_status', 'uid', 'username','update_time'),
            'like' => array('liked_num')
        );

        $need_save = 1;
        if($tmp && is_array($tmp)){

            $tmp_key = array_keys($tmp);
            $base_status = array_diff($condition['base'], $tmp_key);

            if($base_status){
                $data = $this->getCqAnswerInfo($base_status, $val);
            }else{
                $need_save = 0;
                $data = $tmp;
            }
        }else{
            $data = $this->getCqAnswerInfo($condition['base'], $val);
        }

        if($need_save && $data){
            $this->redis_svc->dataHmset($temp_key, $data, false);
        }

        if($data){
            $key_comment = str_replace('{id}', $val, RedisDataService::REDIS_QA_COMMUNITY_ANSWER_COMMENT);
            $comment_num = $this->redis_svc->getZCard($key_comment);
            $data['comment_num'] = $comment_num;
            $data['valid_comment'] = $comment_num;
        }

        return $data;
    }

    /**
     * 获取回答基本信息
     * @param array $base_status
     * @param $id
     * @return array
     */
    private function getCqAnswerInfo($base_status = array(), $id){
        if($base_status && is_array($base_status)){
            $res = $this->qa_svc->getRowByCondition('qa_answer', 'id', $id);
        }
        return $res?$res:array();
    }

    /**
     * 删除answer
     */
    public function deleteCQAnswerUcAction(){
        $uid = isset($this->uid) ? $this->uid : '';
        $aid = isset($this->aid) ? $this->aid : '';

        if(strlen($uid) != 32 || !$uid){
            $this->_errorResponse(10001,'请传入正确的用户ID');
        }

        if(!is_numeric($aid) || $aid < 1){
            $this->_errorResponse(10005,'请传入正确的回答ID');
        }

        $temp_key = str_replace('{id}', $aid, RedisDataService::REDIS_QA_COMMUNITY_ANSWER);

        $data = $this->qa_svc->getHashDataFromRedis($temp_key, array('question_id'));
        if(isset($data['question_id']) && $data['question_id'] > 0){
            $key_hot = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
            $key_list = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
        }else{
            $data = $this->qa_svc->getRowByCondition('qa_answer', 'id', $aid);
            if(isset($data['question_id']) && $data['question_id'] > 0){
                $key_hot = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER_HOT);
                $key_list = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
            }else{
                // 出错
                $this->_errorResponse(10006,'服务器出错或数据有误，稍后重试！');
            }
        }

        $key_aid = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_ID);
        $key_qid = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);

        $this->redis_svc->dataZRem($key_hot, $aid);
        $this->redis_svc->dataZRem($key_list, $aid);

        $this->redis_svc->dataSRem($key_aid, $aid);

        $_total = $this->question->getRsBySql("SELECT COUNT(`id`) AS n FROM `qa_answer` WHERE  `uid` = '{$uid}' AND `id` <> '{$aid}' AND `del_status` = 0 AND `question_id` = '{$data['question_id']}' ",true);
        if(isset($_total['n']) && $_total['n'] < 1){
            $this->redis_svc->dataSRem($key_qid, $data['question_id']);
        }

        $res2 = $this->qa_svc->operateDataById('qa_answer', array('del_status'=>1), $aid);
        $tmp = $temp_res = $this->qaqs_svc->updateExtInfo($data['question_id']);

        $temp_data = array('valid_answer' => $tmp['valid_answer']);
        $redis_key = str_replace('{id}', $data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
        $this->qa_svc->setHashDataToRedis($temp_data, $redis_key);


        $tmp_data = $this->getQuestionInfo($data['question_id']);

        $this->updateCqListRedis($data['question_id'], $tmp_data, $tmp_data['dest_id'], array_keys($tmp_data['tag_ids']));

        $res = array(
            'message' => '删除成功！',
        );

        $this->_successResponse($res);

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


    /**
     * 删除关注
     */
    public function deleteCQfollowUcAction(){

        $uid = isset($this->uid) ? $this->uid : '';
        $qid = isset($this->qid) ? $this->qid : '';

        if(strlen($uid) != 32 || !$uid){
            $this->_errorResponse(10001,'请传入正确的用户ID');
        }

        if(!is_numeric($qid) || $qid < 1){
            $this->_errorResponse(10005,'请传入正确的问题ID)');
        }

        $params = array(
            'table' => "qa_question_follow",
            'where' => " `question_id` = '{$qid}' AND `uid` = '{$uid}'"
        );
        $this->qa_svc->deleteData($params);

        $temp_key = str_replace('{uid}', $uid, RedisDataService::REDIS_QA_COMMUNITY_USER_FOLLOW);
        $this->redis_svc->dataSRem($temp_key, $qid);

        $res = array(
            'message' => '取消关注成功！',
        );

        $this->_successResponse($res);
    }

}

