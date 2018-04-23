<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class ApidataController extends ControllerBase {
    private $dest_api_svc;
    public function initialize() {
        parent::initialize();
        $this->dest_api_svc=$this->di->get('cas')->get('dest_api_service');
    }

    public function destSearchPrdAllAction(){
        $dest_name=$this->dest_name;
        $type=$this->type;
        $num=intval($this->num);
        $forcedb=intval($this->forcedb);
        $dest_abroad=intval($this->dest_abroad);
        $product=array();
        if($type=='all'){
            if($dest_abroad==1){
                $youlun=json_decode($this->getSearchProduct($dest_name,'YOULUN',$num,$forcedb),true); //游轮
                $product['youlun']=$youlun?$youlun:'';
            }
            $group=json_decode($this->getSearchProduct($dest_name,'GROUP',$num,$forcedb),true);//跟团游产品
            $scenictour=json_decode($this->getSearchProduct($dest_name,'SCENICTOUR',$num,$forcedb),true); //景+酒产品
            $freetour=json_decode($this->getSearchProduct($dest_name,'FREETOUR',$num,$forcedb),true); //机+酒
            $product['group']=$group;
            $product['scenictour']=$scenictour;
            $product['freetour']=$freetour;
        }else{
            $product=json_decode($this->getSearchProduct($dest_name,$type,$num,$forcedb));
        }
        $this->jsonResponse($product);
    }
    /**
     *  跟团游产品
     * @param $dest_name
     * @param $num
     * @param $type
     * @return str
     */
    private function getSearchProduct($dest_name,$type,$num,$forcedb){
        $redis_key=RedisDataService::REDIS_API_DATA.$dest_name.$type.$num;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataGet($redis_key);
        }
        if(!$result){
            $result=$this->dest_api_svc->getProductByDestAndType($dest_name,$type,$num);
            if($result){
                $ttl=$this->redisConfig['ttl']['lvyou_api_data']?$this->redisConfig['ttl']['lvyou_api_data']:null;
                $this->redis_svc->dataSet($redis_key,$result,$ttl);
            }
        }
        return $result?$result:false;
    }
    private function getSearchTicketByDestId($dest_id,$num,$forcedb){
        if(!$dest_id) return false;
        $result=$this->getSearchProduct($dest_id,'TICKET',$num,$forcedb);
        return $result;
    }

    public function destTicketsMultAction(){
        $dest_id=$this->dest_id;
        $num=intval($this->num);
        $forcedb=intval($this->forcedb);
        $result=array();
        if($dest_id){
            $dest_id=explode(',',$dest_id);
        }
        foreach($dest_id as $id){
            $tmp=json_decode($this->getSearchTicketByDestId($id,$num,$forcedb));
            if(!empty($tmp)){
                $result[$id]=$tmp;
            }

        }
        $this->jsonResponse($result);
    }
    public function destLineMultAction(){
        $dest_name=$this->dest_name;
        $num=intval($this->num);
        $forcedb=intval($this->forcedb);
        $result=array();
        if($dest_name){
            $dest_name=explode(',',$dest_name);
        }
        foreach($dest_name as $name){
            $result[$name]=json_decode($this->getSearchProduct($name,'SCENICTOUR',$num,$forcedb));
        }
        $this->jsonResponse($result);
    }
}
