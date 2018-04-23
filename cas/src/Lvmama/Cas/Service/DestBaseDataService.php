<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\Filelogger;

/**
 * 目的地-基础数据 服务类
 *
 * @author mac.zhao
 *
 */
class DestBaseDataService extends DataServiceBase {

    const TABLE_NAME = 'dest_base';//对应数据库表
    const PRIMARY_KEY='base_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    private $ttl = 43200;

    /**
     * 添加
     *
     */
    public function insert($data) {
        if($id = $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data)) ){
            $base_id=$this->getBaseIdByDestId($data['dest_id']);
            return $base_id;
        }
    }

    /**
     * 更新
     *
     */
    public function update($id, $data) {
        $whereCondition = 'dest_id = ' . $id;
        if($id = $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition) ) {
        }
    }

    /**
     * @purpose 根据主键获取基础数据
     * @param $id
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('base_id'=>"=".$id);
        $data=$this->getOne($where_condition,self::TABLE_NAME);
        return $data?$data:false;
    }
    /**
     * @purpose 根据目的地ID获取详细信息
     * @param $dest_id
     * @return bool|mixed
     */
    public function getOneByDestId($dest_id){
        if(!$dest_id) return false;
        $where_condition=array('cancel_flag'=>" = 1",'showed'=>" = 1",'dest_id'=>'='.$dest_id);
        $base_data=$this->getOne($where_condition,self::TABLE_NAME);
        return $base_data?$base_data:false;
    }
    public function getDestParents($dest_id,$parents=array()){
        if(!$dest_id) return false;
        $sql="SELECT * FROM ".self::TABLE_NAME." WHERE dest_id=".$dest_id;
        $dest_info=$this->query($sql);
        if($dest_info && $dest_info['parent_id']){
            $parents[]=$dest_info['parent_id'];
            $parents=$this->getDestParents($dest_info['parent_id'],$parents);
        }
        return $parents;
    }

    public function getChildsByPid($parent_id,$page=null){
        $where_condition=array('cancel_flag'=>" = 1",'showed'=>" = 1",'parent_id'=>'='.$parent_id);
        return $this->getList($where_condition,self::TABLE_NAME,$page,"base_id");
    }
    public function getBaseIdByDestId($dest_id){
        $sql="SELECT base_id FROM ".self::TABLE_NAME." WHERE dest_id=".$dest_id;
        $result= $this->query($sql);
        return $result['base_id'];
    }
    public function isDestValid($dest_id){
        if(!$dest_id) return false;
        $sql="SELECT count(1) as num FROM ".self::TABLE_NAME." WHERE dest_id=".$dest_id." AND cancel_flag=1 AND showed=1";
        $result=$this->query($sql);
        if($result['num']>0){
            return true;
        }else{
            return false;
        }
    }
    public function getDestIdsByDestName($dest_name){
        $sql="SELECT dest_id FROM dest_base WHERE dest_name LIKE '%".$dest_name."%'";
        return $this->query($sql,'All');
    }

    /**
     * @param string $dest_ids 以逗号分隔的字符串，或者一维数组
     * @return array
     *
     * @author libiying
     */
    public function getDestBaseByDestIds($dest_ids = ''){
        if(is_array($dest_ids)){
            $dest_ids = implode(',', $dest_ids);
        }
        $where = array('cancel_flag'=>" = 1",'showed'=>" = 1", 'dest_id' => ' in (' . $dest_ids . ')');
        return $this->getList($where, self::TABLE_NAME);
    }

    /**
     * 获取指定父亲的目的地
     * @param $parent_id
     * @param array $dest_type
     * @return $this|array|bool
     *
     * @author libiying
     */
    public function getDestsByParentId($parent_id, $dest_type = array()){
        if(!$parent_id || !is_array($dest_type)){
            return array();
        }
        $where = array('cancel_flag'=>" = 1",'showed'=>" = 1", 'parent_id' => ' = ' . $parent_id);
        if($dest_type){
            $str_types = '';
            foreach ($dest_type as $type){
                $str_types .= '"' . $type . '"' . ',';
            }
            $str_types = substr($str_types, 0, -1);
            $where = array_merge($where, array('dest_type' => ' in (' . $str_types . ')'));
        }
        return $this->getList($where, self::TABLE_NAME);
    }

    /**
     * 根据主题取目的地
     * @param $subject_ids
     * @param $dest_type
     * @param $p_base_id
     * @param int $forcedb
     * @return array|bool|mixed
     *
     * @author libiying
     */
    public function getDestsBySubjectIds($subject_ids, $dest_type, $p_base_id, $forcedb = 0){
        if(is_array($subject_ids)){
            $subject_ids = implode(',', $subject_ids);
        }
        $dest_type = strtolower($dest_type);

        $key = 'srv:getDestsBySubjectIds:' . $subject_ids . ':' . $dest_type . ':' . $p_base_id;
        $result = array();
        if(!$forcedb){
            $data = $this->redis->get($key);
            $result = unserialize($data);
        }
        if(!$result){
            $sql = "
            SELECT
                db.*
            FROM
                lmm_destination.dest_base AS db
            INNER JOIN lmm_module.mo_subject_relation AS msr ON msr.object_id = db.dest_id
            INNER JOIN lmm_destination.relation_dest_" . $dest_type . " as rdv  on rdv.pid = " . $p_base_id . " AND db.base_id = rdv.cid
            WHERE
                msr.channel = 'lvyou'
            AND msr.object_type = 'dest'
            AND msr.subject_id IN (" . $subject_ids . ")
            AND db.dest_type = '" . $dest_type . "'
            ";
            $result = $this->query($sql,'All');

            $data=serialize($result);
            $this->redis->set($key, $data);
            $this->redis->expire($key, $this->ttl);
        }
        return $result;
    }
    /**
     * 根据dest_id返回上推到指定类型的目的地基本信息(如入参已经是指定类型或者高于指定类型则返回其本身)
     * @author shenxiang
     * @param dest_id 需要查询的目的地ID
     * @param dest_type 需要返回的类型
     */
    public function getInfoByDestIdAndType($dest_id,$dest_type){
        $return = array();
        if(empty($dest_id) || !is_numeric($dest_id)) return $return;
        $vst_dest_base = $this->di->get('cas')->get('destin_base_service');
        $vst_dest_detail = $this->di->get('cas')->get('dest_detail_service');
        $dest_types = $vst_dest_base->getAllDestType();
        $base_id = $vst_dest_detail->getBaseIdByDestId($dest_id);
        if(empty($base_id)) return $return;
        $base_detail = $vst_dest_detail->getBaseInfoByBaseId($base_id);
        if(empty($base_detail['dest_type'])) return $return;
        $target_type_id = $dest_types[$dest_type]['dest_type_id'];
        $current_type_id = $dest_types[$base_detail['dest_type']]['dest_type_id'];
        if($current_type_id <= $target_type_id){
            return $base_detail;
        }
        return $this->getInfoByDestIdAndType($base_detail['parent_id'],$dest_type);
    }

    /**
     * 返回目的地ID对应其所属城市级行政区ID
     * @param $dest_id
     * @param $dest_type
     * @author shenxiang
     */
    public function getDistrictIdByProductIdAndUrlId($product_id,$url_id){
        $district_id = '';
        if(empty($product_id) || empty($url_id)) return $district_id;
        if(!is_numeric($product_id) || !is_numeric($url_id)) return $district_id;
        //如果产品与的行政区ID关系redis已经保存了就直接使用
        $redis_district_id = $this->redis->hget(RedisDataService::REDIS_PRODUCT_CITY_DISTRICT_ID,$product_id);
        if($redis_district_id){
            $district_id = $redis_district_id;
        }else{
            //景点上推到城市
            $base_detail = $this->getInfoByDestIdAndType($url_id,'CITY');
            if(!empty($base_detail['district_id'])){
                $district_id = $base_detail['district_id'];
                Filelogger::getInstance()->addLog('产品ID['.$product_id.'] url_id['.$url_id.']所属城市行政区为['.$district_id.']','INFO');
                //把已经查好关系缓存起来
                $this->redis->hset(RedisDataService::REDIS_PRODUCT_CITY_DISTRICT_ID,$product_id,$district_id);
                $this->redis->expire(RedisDataService::REDIS_PRODUCT_CITY_DISTRICT_ID,RedisDataService::REDIS_EXPIRE_ONE_MONTH);
            }
        }
        return $district_id;
    }
    /**
     * 补全产品池v2中district_id字段的值,如果district_id已经有值则跳过,使用url_id当dest_id上推到CITY级别
     * @author shenxiang
     * @param category_id
     */
    public function complementProductPoolV2DistrictId($category_id){
        $product_ids = $this->redis->zrange(RedisDataService::REDIS_GOODSLIB_CATEGORY.$category_id,0,-1);
        foreach($product_ids as $product_id){
            Filelogger::getInstance()->addLog('正在补全产品ID为:['.$product_id.']的行政区ID','INFO');
            $product_data = $this->redis->hgetall(RedisDataService::REDIS_PRODUCT_POOL_V2.$product_id);
            if(empty($product_data['url_id']) || !is_numeric($product_data['url_id']) || $product_data['district_id']) continue;
            $district_id = $this->getDistrictIdByProductIdAndUrlId($product_id,$product_data['url_id']);
            Filelogger::getInstance()->addLog('产品ID为:['.$product_id.']的行政区ID为['.$district_id.']','INFO');
            if($district_id) $this->redis->hset(RedisDataService::REDIS_PRODUCT_POOL_V2.$product_id,'district_id',$district_id);
        }
    }
}