<?php
namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;

/**
 * 目的地详细数据 服务类
 */
class DestDetailDataService extends DataServiceBase{

     private  $dest_type;
     const    TABLE_PREX='dest_';  //目的地详情表固定前缀


    public function setDestType($dest_type){
        $this->dest_type=$dest_type;
    }
    /**
     * @purpose 插入数据
     * @param $data   数据
     * @param $table_name  详情表表名
     * @return array
     * @throws \Exception
     */
    public function insert($data,$table_name){
        $table_name=$this->dest_type?$this->dest_type:$table_name;
        $is_exist=$this->isTableExist($table_name);
        if($is_exist){
            if($id = $this->getAdapter()->insert($table_name, array_values($data), array_keys($data)) ){

            }
            $result = array('error'=>0, 'result'=>$id);
            return $result;
        }else{
            throw new \Exception($table_name."表未定义");
        }
    }
    /**
     * 根据目的地ID取得新库的base_id
     * @param dest_id 目的地ID
     * return int
     */
    public function getBaseIdByDestId($dest_id){
        if(!$dest_id || !is_numeric($dest_id)){
            throw new \Exception('请填写正确的dest_id');
        }
        $key = RedisDataService::REDIS_BASE_DESTID;
        $result = $this->redis->hGetAll($key.$dest_id);
        if(!$result){
            $table_name = self::TABLE_PREX.'base';
            $result = $this->getOne(array('dest_id' => '='.$dest_id),$table_name);
            if(!$result) return 0;
            foreach($result as $k=>$v){
                $this->redis->hset($key.$dest_id,$k,$v);
            }
        }
        return isset($result['base_id']) ? $result['base_id'] : 0;
    }
    /**
     * 根据base_id取得其基本信息
     * @param base_id
     * return array
     */
    public function getBaseInfoByBaseId($base_id = 0){
        if(!$base_id || !is_numeric($base_id)){
            throw new \Exception('请填写正确的dest_id');
        }
        $key = $key = RedisDataService::REDIS_DEST_BASEID;
        $result = $this->redis->hGetAll($key.$base_id);
        if(!$result){
            $table_name = self::TABLE_PREX.'base';
            $result = $this->getOne(array('base_id' => '='.$base_id),$table_name);
            foreach($result as $k=>$v){
                $this->redis->hset($key.$base_id,$k,$v);
            }
        }
        return isset($result['base_id']) ? $result : array();
    }

    /**
     * @param $base_id
     * @param null $dest_type
     * @return bool|mixed|null
     * @throws \Exception
     */
    public function getDestDetailByBaseId($base_id,$dest_type=null){
        if(!$dest_type){
            throw new \Exception('未定义表名');
        }
        $dest_type=$this->initDestType($dest_type);
        $table_name=self::TABLE_PREX.$dest_type;
        $result=$this->getOne(array('base_id'=>"=".$base_id),$table_name);
        return $result?$result:null;
    }
    /**
     * 更新
     *
     */
    public function update($id, $data,$table) {
        $whereCondition = 'base_id = ' . $id;
        if($id = $this->getAdapter()->update($table, array_keys($data), array_values($data), $whereCondition) ) {
        }
    }
    public function addWantAndGo($dest_id,$type){
        if(!$dest_id) return false;
        if($type=='been'){
            $sql="SELECT dest_type,base_id FROM dest_base  WHERE  dest_id=".$dest_id;
            $result=$this->query($sql);
            $dest_type=$this->initDestType($result['dest_type']);
            $sql2="SELECT count_been FROM ".self::TABLE_PREX.$dest_type.' WHERE base_id='.$result['base_id'];
            $old=$this->query($sql2);
            $this->update($result['base_id'],array('count_been'=>$old['count_been']+1),self::TABLE_PREX.$dest_type);
        }else{
            $sql="SELECT dest_type,base_id FROM dest_base  WHERE dest_id=".$dest_id;
            $result=$this->query($sql);
            $dest_type=$this->initDestType($result['dest_type']);
            $sql2="SELECT count_want FROM ".self::TABLE_PREX.$dest_type.' WHERE base_id='.$result['base_id'];
            $old=$this->query($sql2);
            $this->update($result['base_id'],array('count_want'=>$old['count_want']+1),self::TABLE_PREX.$dest_type);
        }
    }
    /**
     * 目的地类型初始化
     * @param $dest_type
     * @return string
     */
    private function initDestType($dest_type){
        switch($dest_type) {
            case 'CONTINENT':
                $dest_type = 'state';
                break;
            case 'SPAN_COUNTRY':
            case 'SPAN_PROVINCE':
            case 'SPAN_CITY':
            case 'SPAN_COUNTY':
            case 'SPAN_TOWN':
                $dest_type='special';
                break;
            default :
                $dest_type=strtolower($dest_type);
                break;
        }
        return $dest_type;
    }

    public function getCountData($base_id,$dest_type){
        if(!$base_id || !$dest_type) return false;
        $dest_type=$this->initDestType($dest_type);
        $table_name=self::TABLE_PREX.$dest_type;
        $sql="SELECT count_been,count_want FROM ".$table_name." WHERE base_id=".$base_id;
        return $this->query($sql);

    }

    /**
     * @param $base_ids string|array 以英文逗号分隔的base_id字符串 或者 一维数组
     * @param $dest_type string base_id对应的目的地类型
     * @return array
     */
    public function getDestsList($base_ids, $dest_type){
        if(is_array($base_ids)){
            $base_ids = implode(',', $base_ids);
        }
        $dest_type = strtolower($dest_type);

        $sql = "SELECT * FROM dest_" . $dest_type . " WHERE base_id IN (" . $base_ids . ");";

        $data = $this->query($sql, 'All');
        return $data;
    }
}