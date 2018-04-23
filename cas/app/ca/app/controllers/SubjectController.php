<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class SubjectController extends ControllerBase {
    private $mo_subject;
    private $reids;
    public function initialize() {
        parent::initialize();
        $this->mo_subject=$this->di->get('cas')->get('mo-subject');
        $this->redis = $this->di->get('cas')->getRedis();
    }





    public function destSubjectAction(){
        $dest_id=$this->dest_id;
        $forcedb=intval($this->forcedb);
        $subject_list=$this->getSubject($dest_id,'DEST',$forcedb);
        $this->jsonResponse($subject_list);
    }
    private function getSubject($object_id,$object_type='DEST',$forcedb=1){
        if(!$object_id || !$object_type) return array();
        $subject_list=array();
        $redis_key=RedisDataService::REDIS_OBJECT_SUBJECT_LIST.$object_id.'object_type:'.$object_type;
        if(!$forcedb){
            $subject_list=$this->redis_svc->getArrayData($redis_key);
        }
        if(!$subject_list || !is_array($subject_list)){
            $subject_list=$this->mo_subject->getSubjects($object_id,$object_type);
            if($subject_list || is_array($subject_list) && !isset($subject_list['error'])){
                $this->redis_svc->setArrayData($redis_key,$subject_list,7200);
            }
        }
        return $subject_list;
    }

    public function setSubjectRelationAction(){
        set_time_limit(0);
        $subject_list=$this->mo_subject->getAllSubject();
        if($subject_list){
            foreach($subject_list as $key=>$row){
                $dest_list=$this->mo_subject->getSubjectRelationByType($row['subject_id'],'DEST');
                if($dest_list){
                    $redis_key=RedisDataService::REDIS_SUBJECT_DEST_LIST.$row['subject_id'];
                    $this->redis->del($redis_key);
                    foreach($dest_list as $dest_id){
                        $this->redis_svc->setListData($redis_key,$dest_id['object_id']);
                    }
                }
            }
        }
    }
}
