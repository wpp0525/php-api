<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/12/12
 * Time: 11:08
 */

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\UCommon;
class FencepoiController extends ControllerBase{

    private $svc;
    public function initialize() {
        parent::initialize();
        $this->svc = $this->di->get('cas')->get('fence_poi_data');
    }

    public function createAction(){
        $data=$this->request->getPost('data');
        $insert_data=json_decode($data,true);
        if(empty($insert_data)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，插入失败');
        }else{
            $res=$this->svc->insert($insert_data);
            if($res){
                $this->jsonResponse($res);
            }
        }

    }

    public function getlistAction(){
        $page_size=$this->request->getPost('page_size');
        $page_num=$this->request->getPost('page_num');
        $where = $this->request->getPost('where');
        $where = json_decode($where,true);
        $page_size = $page_size?$page_size:10;
        $page_num = $page_num?$page_num:1;
        $result=$this->svc->getListByCondition($where,array('page_num'=>$page_num,'page_size'=>$page_size));
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }
    public function updateAction(){
        $data=$this->request->getPost('data');
        $insert_data=json_decode($data,true);
        $id=intval($insert_data['id']);
        if(empty($insert_data)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，修改失败');
        }else{
            $res=$this->svc->update($id,$insert_data);
            if($res){
                $this->jsonResponse($res);
            }
        }
    }
    public function deleteAction(){
        $id=$this->request->getPost('id');
        if(empty($id)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，修改失败');
        }else{
            $res=$this->svc->delete($id);
            if($res){
                $this->jsonResponse($res);
            }
        }
    }

    public  function getFenceAction(){
        $lati=$this->request->get('latitude');
        $long=$this->request->get('longitude');
        $result=$poi_level1=$poi_level2=array();
        /*根据用户坐标获取范围内的POI点*/
        $fence_list=$this->getFenceList(array('longitude'=>$long,'latitude'=>$lati));
        if(empty($fence_list)){
            $this->_errorResponse(DATA_NOT_FOUND,'无数据');
        }
        /*判断用户是否在这些POI点的围栏范围内*/
        $in_fence=array();
        foreach($fence_list as $key=>$row){
            if($lati<=$row['max_lati'] && $lati>=$row['min_lati'] && $long<=$row['max_long'] && $long>=$row['min_long']){
                $in_fence[]=$row;
            }
        }
        if($in_fence){
            if(count($in_fence)>1){
                foreach($in_fence as $key=>$row){
                    if($row['fence_level']==2){
                        $poi_level2[]=$row;
                    }
                }
                if(!empty($poi_level2)){
                    if(count($poi_level2)==1){
                        $in_fence=$poi_level2[0];
                    }else{
                        $distance=array();
                        foreach($poi_level2 as $item){
                            $distance[$item['id']]=UCommon::getDistBetweenTwoPoint($lati,$long,$item['latitude'],$item['longitude']);
                            $new_level2[$item['id']]=$item;
                        }
                        asort($distance);
                        $shortest_key=key($distance);
                        $in_fence=$new_level2[$shortest_key];
                    }

                }
            }else{
                $in_fence=$in_fence[0];
            }
            if($in_fence['fence_level']==2){
                $is_infence=false;
                if($in_fence['array_id']){
                    $where="poi_id=".$in_fence['array_id'];
                    $p_fence=$this->svc->getListByCondition($where);
                    if($p_fence['total']>0){
                        $in_fence_level2=$p_fence['list'][0];
                        if($lati<=$in_fence_level2['max_lati'] && $lati>=$in_fence_level2['min_lati'] && $long<=$in_fence_level2['max_long'] && $long>=$in_fence_level2['min_long']){
                            $is_infence=true;
                        }
                    }
                }
                $result['level1_poi']['id']=$in_fence_level2['id'];
                $result['level1_poi']['name']=$in_fence_level2['fence_name'];
                $result['level1_poi']['is_infence']=$is_infence?'Y':'N';
                $result['level1_poi']['poi_id']=$in_fence_level2['poi_id'];
                $result['level2_poi']['id']=$in_fence['id'];
                $result['level2_poi']['name']=$in_fence['fence_name'];
            }else{
                $result['level1_poi']['id']=$in_fence['id'];
                $result['level1_poi']['name']=$in_fence['fence_name'];
                $result['level1_poi']['poi_id']=$in_fence['poi_id'];
                $result['level2_poi']=(object)array();
            }
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'无数据');
        }
    }
    public function getFenceList($position,$position_size=10000){
        return $this->svc->getFenceListByPosition($position,$position_size);
    }
    public function getFenceByNameAction(){
        $name=$this->request->get('name');
        if(!$name) {
            $this->_errorResponse(DATA_NOT_FOUND,'无参数');
        }
        $result=$this->svc->getFenceByName($name);
        if(!$result){
            $this->_errorResponse(DATA_NOT_FOUND,'无数据');
        }else{
            $this->jsonResponse($result);
        }
    }

    public function getFenceByIdAction(){
        $id=$this->request->get('id');
        if(!$id) {
            $this->_errorResponse(DATA_NOT_FOUND,'无参数');
        }
        $result=$this->svc->getFenceById($id);
        if(!$result){
            $this->_errorResponse(DATA_NOT_FOUND,'无数据');
        }else{
            $this->jsonResponse($result);
        }
    }
}