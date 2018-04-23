<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Common\Utils\UCommon;

/**
 * 大目的地关键词分类 服务类
 *
 * @author flash.guo
 *
 */
class SeoDestCategoryDataService extends DataServiceBase {

    const TABLE_NAME = 'seo_dest_category';//对应数据库表
    const PRIMARY_KEY = 'category_id'; //对应主键，如果有
    const PV_REAL = 2;
    const LIKE_INIT = 3;

    /**
     * 添加大目的地关键词分类
     * @param $data 添加数据
     * @return bool|mixed
     */
    public function insert($data) {
        return $this->getAdapter()->insert(self::TABLE_NAME, array_values($data), array_keys($data));
    }

    /**
     * 更新大目的地关键词分类
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function update($id, $data) {
        $whereCondition = 'category_id = ' . $id;
        return $this->getAdapter()->update(self::TABLE_NAME, array_keys($data), array_values($data), $whereCondition);
    }

    /**
     * 删除大目的地关键词分类
     * @param $id 编号
     * @param $data 更新数据
     * @return bool|mixed
     */
    public function delete($id) {
        $whereCondition = 'category_id = ' . $id;
        return $this->getAdapter()->delete(self::TABLE_NAME, $whereCondition);
    }

    /**
     * @purpose 根据条件获取大目的地关键词分类
     * @param $where_condition 查询条件
     * @param $limit 查询条数
     * @return array|mixed
     */
    public function getCateList($where_condition, $limit = NULL){
        $data=$this->getList($where_condition, self::TABLE_NAME, $limit);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取大目的地关键词分类总数
     * @param $where_condition 查询条件
     * @return array|mixed
     */
    public function getCateTotal($where_condition){
        $data=$this->getTotalBy($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据条件获取一条大目的地关键词分类
     * @param $where_condition 查询条件
     * @return bool|mixed
     */
    public function getOneCate($where_condition){
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }

    /**
     * @purpose 根据主键获取一条大目的地关键词分类
     * @param $id 编号
     * @return bool|mixed
     */
    public function getOneById($id){
        $where_condition=array('category_id'=>"=".$id);
        $data=$this->getOne($where_condition, self::TABLE_NAME);
        return $data?$data:false;
    }
    
    /**
     * @purpose 根据名称获取一条大目的地关键词分类
     * @param $name 名称
     * @return bool|mixed
     */
    public function getOneByName($name){
        if(!$name) return false;
        $where_condition=array('category_name'=>"='".$name."'");
        $base_data=$this->getOne($where_condition, self::TABLE_NAME);
        return $base_data?$base_data:false;
    }
    /**
     * 根据分类取的TDK数据
     *
     */
    public function getTdkByCategory($category_id,$dest_id,$dest_name,$keyword,$word_root){
        $data = $this->getOneById($category_id);
        $type = isset($data['category_tdk']) ? $data['category_tdk'] : '';
        if(!$type){
            $type = 'seotdk_dujia_keywords_lvyou';
        }
        $url = 'http://www.lvmama.com/pet_topic/tdk/queryTDK.do?key='.$type.'&destId='.$dest_id;
        $redis_key = str_replace('{params}',md5($url.$category_id.$dest_name.$keyword.$word_root),RedisDataService::REDIS_SEO_TDK_DATA);
        $tdk = $this->redis->hGetAll($redis_key);
        if(!$tdk){
            $tdk_string = UCommon::curl($url,'GET');
            if($tdk_string == 'null') return array();
            $tdk_string = str_replace(
                array('{zhangkeyword}','{cigen}','{mudidi}'),
                array($keyword,$word_root,$dest_name),
                $tdk_string
            );
            $tdk = json_decode($tdk_string,true);
            if($tdk){
                $this->redis->hmset($redis_key,$tdk);
                $this->redis->expire($redis_key,rand(7200,28800));
            }else{
                $tdk = array();
            }
        }
        return $tdk;
    }
    /**
     *获取无搜索版公共头部
     */
    public function getCommonHeader(){
        $redis_key = RedisDataService::REDIS_SEO_NOSEARCH_HEADER;
        $data = $this->redis->get($redis_key);
        if(!$data){
            $data = UCommon::curl('http://www.lvmama.com/homePage/newHomeHead.do?channelPageNew=dujia&newHome=Y','GET');
            if(strpos($data,'驴妈妈旅游网')){
                $this->redis->setex($redis_key,86400,$data);
            }
        }
        return $data;
    }
}