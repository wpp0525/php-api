<?php

namespace Lvmama\Cas\Service;

use Phalcon\Db\AdapterInterface;
use Phalcon\DiInterface;
use Lvmama\Cas\Service\DataServiceBase;
use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 目的地-基础数据 服务类
 *
 * @author mac.zhao
 *
 */
class ImageDataService extends DataServiceBase {

    const TABLE_NAME = 'ly_elite_image';//对应数据库表

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
     * 获取目的地总图片数量 从老的图片库获取
     * @return bool|mixed|null|string
     */
    public function getDestAllImages(){
        $where_condition=array('object_type'=>"="."'dest'");
        $total=$this->getTotalBy($where_condition,self::TABLE_NAME);
        return $total;
    }
    public function getImageList($page=null){
        if(!$page){
            $sql="select * from ".self::TABLE_NAME;
            return $this->query($sql,'All');
        }else{
            return $this->getList(array(),self::TABLE_NAME,$page);
        }
    }

    public function getCoverByObject($object_id,$object_type='dest'){
        if(!$object_id) return false;
        $sql="SELECT img_url FROM ".self::TABLE_NAME." WHERE  object_type='".$object_type."' AND  object_id=".$object_id." AND cover='Y' ";
        $result=$this->query($sql);
        return $result['img_url'];
    }

    /**
     * 目的地改版补全封面图
     * @param $object_id
     * @param string $object_type
     * @return bool
     */
    public function getCoverByDestId($object_id,$object_type='dest'){
        if(!$object_id) return false;
        $sql = "SELECT img_url FROM ".self::TABLE_NAME." WHERE object_type='".$object_type."' AND  object_id=".$object_id." ORDER BY cover ASC, seq ASC LIMIT 1";
        $result=$this->query($sql);
        return $result['img_url'];
    }


    //获取目的地的图片
    public function getListById($object_id,$object_type='dest',$pages = array()){
        if(!isset($pages['page']) || $pages['page'] < 1) $pages['page'] = 1;
        if(!isset($pages['pageSize']) || $pages['pageSize'] < 1 || $pages['pageSize'] > 50) $pages['pageSize'] = 15;
        $total = $this->getImageNumById($object_id,$object_type);
        $totalPage = ceil($total / $pages['pageSize']);
        $pages['page'] = $pages['page'] > $totalPage ? $totalPage : $pages['page'];
        $start = ($pages['page'] - 1) * $pages['pageSize'];
        $sql = "SELECT title,img_url FROM ".self::TABLE_NAME." WHERE object_type = '{$object_type}' AND object_id = {$object_id} ORDER BY cover ASC LIMIT {$start},{$pages['pageSize']}";
        $list = $this->query($sql,'All');
        foreach($list as $k => $v){
            $list[$k]['trip_title'] = $v['title'];
            $list[$k]['segment_id'] = '';
            $list[$k]['original_time'] = '';
            $list[$k]['camera'] = '';
            $list[$k]['trip_id'] = 0;
            $list[$k]['memo'] = '';
            $list[$k]['longitude'] = '';
            $list[$k]['latitude'] = '';
            $list[$k]['praiseCount'] = '';
            $list[$k]['commentCount'] = '';
            $list[$k]['is_praise'] = '';
            $list[$k]['is_comment'] = '';
            $list[$k]['shareCount'] = '';
            $list[$k]['latitude'] = '';
        }
        $pages['pageCount'] = $totalPage;
        $pages['itemCount'] = (int)$total;
        return array('list' => $list,'pages' => $pages);
    }
    public function getImageNumById($object_id,$object_type='dest'){
        if(!$object_id || !is_numeric($object_id)) return 0;
        $sql='SELECT COUNT(1) AS num FROM '.self::TABLE_NAME.' WHERE object_type=\''.$object_type.'\' AND object_id='.$object_id;
        $result = $this->query($sql);
        return isset($result['num']) ? intval($result['num']) : 0;
    }
    public function getImgById($object_id,$num=5,$object_type='dest'){
        if(!$object_id) return array();
        $sql="SELECT image_id,object_id,title,img_url,create_name,cover FROM ".self::TABLE_NAME." WHERE object_type='".$object_type."' AND object_id=".$object_id." ORDER BY cover ASC LIMIT ".$num;
        return $this->query($sql,'All');
    }
    /**
     * @param $dest_id
     * @param int $limit
     * @return array
     * 根据目的地ID获取精选图
     */
    public function getDestEliteImgByDestid($dest_id,$limit=0,$object_type='dest'){
        $data = $this->query("SELECT * FROM ".self::TABLE_NAME." WHERE `object_id`={$dest_id} AND object_type='".$object_type."' ORDER BY seq ASC LIMIT ".$limit,'All');
        foreach($data as $key=>$item){
            $data[$key]['image']=substr($item['img_url'],1);
        }
        return $data ? $data : array();
    }

    public function getImgByIds($object_ids, $num = 5, $object_type = 'dest')
    {
        $image_list = array();
        
        $dest_ids_arr = explode(',', $object_ids);

        if(count($dest_ids_arr) == 1){
            $image = $this->getImgById($dest_ids_arr[0], $num, $object_type);
            if(!empty($image)){
                $image_list[$dest_ids_arr[0]] = $image;
            }
        }else{
            foreach($dest_ids_arr as $item){
                $image = $this->getImgById($item, $num, $object_type);
                if(!empty($image)){
                    $image_list[$item] = $image;
                }
            }
        }

        return $image_list;
    }
}