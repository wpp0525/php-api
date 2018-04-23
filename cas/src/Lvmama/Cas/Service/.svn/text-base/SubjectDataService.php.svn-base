<?php

namespace Lvmama\Cas\Service;

use Lvmama\Common\Utils\UCommon;
use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地-基础数据 服务类
 *
 * @author mac.zhao
 *
 */
class SubjectDataService extends DataServiceBase {

    const TABLE_NAME = 'mo_subject_relation';//对应数据库表
    const EXPIRE_TIME = 86400;
    /**
     * 添加
     *
     */
    public function insert($data) {
        if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){

        }
        $result = array('error'=>0, 'result'=>$id);
        return $result;
    }

    public function getSubjects($object_id,$object_type='DEST'){
        if(!$object_id) return array();
        $sql="SELECT * FROM ".self::TABLE_NAME." WHERE channel='lvyou'  AND `status`=99  AND object_type='".$object_type."' AND object_id=".$object_id;
        return $this->query($sql,'All');
    }

    public function getAllSubject(){
        $sql="SELECT * FROM  mo_subject ";
        return $this->query($sql,'All');
    }
    public function getPoiThem($dest_id){
        return $this->query("SELECT `subject_id`, `subject_name`, `main` FROM ".self::TABLE_NAME." WHERE `channel` = 'lvyou' AND `object_type` = 'DEST' AND `status` = 99 AND `object_id` = '{$dest_id}' ORDER BY `main` ASC",'All');
    }
    public function getSubjectRelationByType($subject_id,$object_type){
        $sql="SELECT * FROM ".self::TABLE_NAME." WHERE `status`=99 AND object_type='".$object_type."' AND subject_id=".$subject_id;
        return $this->query($sql,'All');
    }
    public function getDestSubList($dest_ids){
        $sql="SELECT subject_name,subject_id, COUNT(*) AS num FROM ".self::TABLE_NAME." WHERE `status`=99 AND object_type='DEST' AND object_id IN(".$dest_ids.") GROUP BY subject_id ORDER BY num DESC";
        return $this->query($sql,'All');
    }
    /**
     * 获得美食中的菜系
     * @param array $ids
     * @param string $object_type
     * @param string $channel
     * @return array
     */
    public function getSubjectName($ids = array(),$object_type = 'food',$channel = 'lvyou'){
        if(!$ids) return array();
        $return = array();
        $ids_str = implode(',',$ids);
        $sql = "SELECT subject_name,object_id FROM ".self::TABLE_NAME." WHERE `status`=99 AND object_type = '{$object_type}' AND channel = '{$channel}' AND object_id IN( {$ids_str} )";
        $redis_key = str_replace('{sql}',md5($sql),RedisDataService::REDIS_MODULE_SUBJECT_RELATION_NAME);
        $redis_data = $this->redis->get($redis_key);
        if($redis_data === false){
            $rs = $this->query($sql,'All');
            if($rs){
                foreach($rs as $v){
                    $return[$v['object_id']][] = $v['subject_name'];
                }
            }
            $this->redis->setex($redis_key,self::EXPIRE_TIME,json_encode($return));
        }else{
            $return = json_decode($redis_data,true);
        }
        return $return;
    }
}