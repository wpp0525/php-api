<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 16-9-28
 * Time: 下午4:50
 */

use Lvmama\Cas\Component\DaemonServiceInterface;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Cas\Service\QaCommonDataService;
use Lvmama\Common\Utils\Misc;

class CquestionWorkerService implements DaemonServiceInterface {

    /**
     * @var RedisAdapter
     */
    private $redis;

    /**
     * @var BeanstalkAdapter
     */
    private $beanstalk;

    private $qa_svc;

    public function __construct($di) {
        $this->redis = $di->get('cas')->getRedis();
        $this->beanstalk = $di->get('cas')->getBeanstalk();

        $this->qa_svc = $di->get('cas')->get('qa_common_data_service');
        $this->qa_svc->setReconnect(true);
    }
    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function Process($timestamp = null, $flag = null) {
    }

    /**
     * 问答系统-审核写入DestCqList
     *
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function processCquestionList($timestamp = null, $flag = null) {

        if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_CQA_LIST)->ignore('default')->reserve()) {
            try {
                if ($job_data = json_decode($job->getData(), true)) {

                    if($job_data['type'] == 'ALL'){
                        $all_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
                        $zero_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_NOANSWER;
                        $hot_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_HOT;
                    }else if($job_data['type'] == 'DEST'){
                        $all_key = str_replace('{dest_id}', $job_data['rkey'], RedisDataService::REDIS_QA_COMMUNITY_DEST_REL);
                        $zero_key = str_replace('{dest_id}', $job_data['rkey'],  RedisDataService::REDIS_QA_COMMUNITY_DEST_NOANSWER);
                        $hot_key = str_replace('{dest_id}', $job_data['rkey'],  RedisDataService::REDIS_QA_COMMUNITY_DEST_HOT);
                    }else if($job_data['type'] == 'TAG'){
                        $all_key = str_replace('{tag_id}', $job_data['rkey'], RedisDataService::REDIS_QA_COMMUNITY_TAG_REL);
                        $zero_key = str_replace('{tag_id}', $job_data['rkey'],  RedisDataService::REDIS_QA_COMMUNITY_TAG_NOANSWER);
                        $hot_key = str_replace('{tag_id}', $job_data['rkey'],  RedisDataService::REDIS_QA_COMMUNITY_TAG_HOT);
                    }

                    if($job_data['main_status'] == 5){
                        if($job_data['valid_answer'] > 0){
                            $this->redis->zRem($zero_key, $job_data['question_id']);
                        }else{
                            $this->redis->zAdd($zero_key, $job_data['update_time'], $job_data['question_id']);
                        }
                        $this->redis->zAdd($all_key, $job_data['update_time'], $job_data['question_id']);
                        $hot_num = $this->_getHotNum($job_data['question_id'], $job_data['valid_answer'], $job_data['recommend_status']);
                        $this->redis->zAdd($hot_key, $hot_num, $job_data['question_id']);

                    }else{
                        $this->redis->zRem($all_key, $job_data['question_id']);
                        $this->redis->zRem($zero_key, $job_data['question_id']);
                        $this->redis->zRem($hot_key, $job_data['question_id']);
                    }
                }
                unset($job_data);
//                var_dump('it is ok!');
// 				$this->beanstalk->delete($job);
            } catch (\Exception $ex) {
                echo $ex->getMessage() . ";" . $ex->getTraceAsString() . "\r\n";
            }
            $this->beanstalk->delete($job);
        }
        unset($job);
    }

    /**
     * 回答审核后更新question zero list
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function processCkCQzeroList($timestamp = null, $flag = null) {

        if ($job = $this->beanstalk->watch(BeanstalkDataService::BEANSTALK_CQA_ANSWER_CKLIST)->ignore('default')->reserve()) {
            try {
                if ($job_data = json_decode($job->getData(), true)) {

                    $q_key = str_replace('{id}', $job_data['question_id'], RedisDataService::REDIS_QA_QUESTION_INFO);
                    $tmp = $this->redis->hMGet($q_key, array('dest_id', 'update_time', 'main_status'));

                    if(!@$tmp['update_time'] || !@$tmp['main_status']){
                        $str = "id = {$job_data['question_id']}";
                        $res = $this->qa_svc->getRowByConditionSrt('qa_question', 'update_time, main_status', $str, 'one');
                        $update_time = $res['update_time'];
                        $main_status = $res['main_status'];
                    }else{
                        $update_time = $tmp['update_time'];
                        $main_status = $tmp['main_status'];
                    }

                    if($update_time){
                        $user_answer = str_replace('{uid}', $job_data['uid'], RedisDataService::REDIS_QA_COMMUNITY_USER_ANSWER_QID);
                        $this->redis->zAdd($user_answer, $update_time, $job_data['question_id']);
                    }

                    if($main_status == 5){
                        $q_a_key = str_replace('{id}', $job_data['question_id'], RedisDataService::REDIS_QA_COMMUNITY_QUESTION_ANSWER);
                        $answer_num = $this->redis->zCard($q_a_key);
                        if(!is_numeric($answer_num)) $answer_num = 0;

                        $redis_keys = array();
                        $redis_keys[] = RedisDataService::REDIS_QA_COMMUNITY_ALL_NOANSWER;
                        if(!@$tmp['dest_id']){
                            $temp = $this->qa_svc->getRowByCondition('qa_question_dest_rel', 'question_id', $job_data['question_id']);
                            if(!@$temp['dest_id']){
                                $redis_keys[] = str_replace('{dest_id}', $temp['dest_id'], RedisDataService::REDIS_QA_COMMUNITY_DEST_NOANSWER);
                            }
                        }

                        $tag_key = str_replace('{id}', $job_data['question_id'], RedisDataService::REDIS_QA_QUESTION_TAGS);
                        $tags = $this->redis->sMembers($tag_key);
                        if(!$tags || !is_array($tags)){
                            $tags = $this->qa_svc->findRelationByCondition('qa_question_tag_rel', 'tag_id', 'question_id', $job_data['question_id']);
                        }
                        if(!$tags || !is_array($tags)){
                            foreach($tags as $val){
                                $redis_keys[] = str_replace('{tag_id}', $val,  RedisDataService::REDIS_QA_COMMUNITY_TAG_NOANSWER);
                            }
                        }

                        if($answer_num < 1){
                            foreach($redis_keys as $key){
                                $this->redis->zAdd($key, $tmp['update_time'], $job_data['question_id']);
                            }
                        }else{
                            foreach($redis_keys as $key){
                                $this->redis->zRem($key, $job_data['question_id']);
                            }
                        }
                    }
                }
                unset($job_data);
// 				$this->beanstalk->delete($job);
            } catch (\Exception $ex) {
                echo $ex->getMessage() . ";" . $ex->getTraceAsString() . "\r\n";
            }
            $this->beanstalk->delete($job);
        }
        unset($job);
    }




    /**
     * 回答审核后更新question zero list
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::process()
     */
    public function processNewCqHotList($timestamp = null, $flag = null) {
        $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
        $total = $this->redis->zCard($redis_key);
        $total_page = ceil( $total / 50 );
        $this->excuteData(1, 50, $total_page);
    }

    private function excuteData($page_num = 1, $page_size = 50, $total_page){
        if($page_num <= $total_page){

            $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
            $begin = ($page_num-1) * $page_size;
            $end = $page_num*$page_size-1;
            $qids = $this->redis->zrange($redis_key, $begin, $end);

            foreach($qids as $qid){
                $question_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_QUESTION_INFO);
                $tmp = $this->redis->hMGet($question_key, array('dest_id', 'valid_answer', 'recommend_status', 'update_time'));

                $base_init = array('question_id' => $qid, 'valid_answer' => 0, 'recommend_status' => 0, 'main_status' => 5, 'update_time' => 0);
                if(@$tmp['update_time']){
                    $base_init['update_time'] = $tmp['update_time'];
                }else{
                    $temp = $this->qa_svc->getRowByCondition('qa_question', 'id', $qid);
                    if($temp && is_array($temp)){
                        if(@$temp['update_time']){
                            $base_init['update_time'] = $temp['update_time'];
                        }else{
                            continue;
                        }
                    }
                }
                if(@$tmp['valid_answer'] && is_numeric(@$tmp['valid_answer'])){
                    $base_init['valid_answer'] = $tmp['valid_answer'];
                }
                if(@$tmp['recommend_status'] && is_numeric(@$tmp['recommend_status'])){
                    $base_init['recommend_status'] = $tmp['recommend_status'];
                }

                $all_arr = array('type' => 'ALL');
                $bt_dest_array = array_merge($base_init, $all_arr);
                $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_dest_array));

                if(@$tmp['dest_id']){
                    $dest_arr = array('type' => 'DEST', 'rkey' => $tmp['dest_id']);
                    $bt_dest_array = array_merge($base_init, $dest_arr);
                    $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_dest_array));
                }

                $tag_key = str_replace('{id}', $qid, RedisDataService::REDIS_QA_QUESTION_TAGS);
                $tags = $this->redis->sMembers($tag_key);
                if($tags){
                    foreach($tags as $tag){
                        $tag_array = array(
                            'type' => 'TAG',
                            'rkey' => $tag,
                        );
                        $bt_tag_array = array_merge($base_init, $tag_array);
                        $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_CQA_LIST)->put(json_encode($bt_tag_array));
                    }
                }

            }

            $current_page = $page_num + 1;

            if($page_num == $total_page){
                $redis_key = RedisDataService::REDIS_QA_COMMUNITY_ALL_REL;
                $total = $this->redis->zCard($redis_key);
                $total_page = ceil( $total / 50 );
            }

            sleep(5);
            $this->excuteData($current_page, $page_size, $total_page);
        }else{
            echo 'job done';
            exit;
        }
    }


    protected function _getHotNum($id, $answer = 0, $recommend = 0){
        $temp_key = str_replace('{id}', $id, RedisDataService::REDIS_QA_COMMUNITY_QUESTION_PV);
        $time = time();
        $date = array();
        for($i=1; $i<8; $i++){
            $date[] = date("Ymd", $time-$i*86400);
        }
        $pv_7d = 0;
        foreach($date as $val){
            $tmp = $this->redis->zScore($temp_key, $val);
            if(!$tmp || !is_numeric($tmp)){ $tmp = 0; }
            $pv_7d = $pv_7d + $tmp;
        }
        $hot = $pv_7d + $answer * 10 + $recommend * 100;
        return $hot;
    }


    /**
     * @see \Lvmama\Cas\Component\DaemonServiceInterface::shutdown()
     */
    public function shutdown($timestamp = null, $flag = null) {
        // nothing to do
    }

}
