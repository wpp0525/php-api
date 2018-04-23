<?php

use Lvmama\Common\Utils\Misc;
use Lvmama\Cas\Service\RedisDataService;
use Lvmama\Common\Utils\UCommon;

/**
 * 游记 控制器
 *
 * @author mac.zhao
 *
 */
class DestinfonewController extends ControllerBase {
    private $dest_base_svc;
    private $dest_detail_svc;
    private $recom_dest_svc;
    private $dest_relation_svc;
    private $dest_image_svc;
    private $mo_subject;
    private $dest_sumary_svc;
    private $destin_base_service_svc;
    private $redis;
    public function initialize() {
        parent::initialize();
        $this->dest_base_svc = $this->di->get('cas')->get('dest_base_service');
        $this->dest_detail_svc = $this->di->get('cas')->get('dest_detail_service');
        $this->recom_dest_svc  = $this->di->get('cas')->get('recom_dest_service');
        $this->dest_relation_svc =$this->di->get('cas')->get('dest_relation_service');
        $this->dest_image_svc = $this->di->get('cas')->get('dest_image_service');
        $this->mo_subject=$this->di->get('cas')->get('mo-subject');
        $this->dest_sumary_svc=$this->di->get('cas')->get('dest_sumary_service');
        $this->destin_base_service_svc = $this->di->get('cas')->get('destin_base_service');
        $this->redis=$this->di->get('cas')->getRedis();
    }

    /**
     * 输出目的地信息
     */
    public function destAllInfoAction(){
        $dest_id=$this->dest_id;
        var_dump($dest_id);die;
        $forcedb=intval($this->forcedb);
        $dest_info=$this->getDestAll($dest_id,intval($forcedb));
        if(!$dest_info) {$this->_errorResponse(DATA_NOT_FOUND,'该数据不存在');exit;}
        $this->jsonResponse($dest_info);
    }

    /**
     * 整合目的地全部数据
     * @param $dest_id
     * @param $forcedb
     * @return array|bool
     */
    private function getDestAll($dest_id,$forcedb=null){
        $base_info=$this->getBaseInfo($dest_id,$forcedb);
        UCommon::dump($base_info);
        if(!$base_info) return false;
        $dest_type=$this->initDestType($base_info['dest_type']);
        $detail_info=$this->getDetailInfo($base_info['base_id'],$dest_type,$forcedb);

        if($detail_info){
            unset($detail_info['base_id']);
            $result=array_merge($base_info,$detail_info);
        }else{
            $result=$base_info;
        }
        if($result && isset($result['img_url'])){
            $img_url=$this->dest_image_svc->getCoverByObject($dest_id);
            $result['img_url']=$img_url;
        }
        return $result;
    }
    /**
     * 根据目的地ID获取基础信息
     * @param $dest_id
     * @param null $forcedb
     * @return array|bool
     */
    private function getBaseInfo($dest_id,$forcedb=null){
        if(!$dest_id) return false;
        $redis_key=RedisDataService::REDIS_DEST_BASE_DESTID.$dest_id;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataHgetall($redis_key);
            if(!isset($result['base_id'])){
                $this->redis->del(RedisDataService::REDIS_DEST_BASE_DESTID.$dest_id);
                $result=array();
            }
        }
        if(!$result){
            $result=$this->dest_base_svc->getOneByDestId($dest_id);
            if($result && $result['showed'] && $result['cancel_flag']) {
                $address=$this->dest_sumary_svc->getDestAddress($dest_id);
                $result['address']=$address['address'];
                $ttl=$this->redisConfig['ttl']['lvyou_dest_parents']?$this->redisConfig['ttl']['lvyou_dest_baseinfo']:null;
                $this->redis_svc->dataHmset($redis_key,$result,$ttl);
            }else{
                return false;
            }
        }
        return $result;
    }

    /**
     * 根据base_id获取详细信息
     * @param $base_id
     * @param $dest_type
     * @param null $forcedb
     * @return array|bool
     */
    private function getDetailInfo($base_id,$dest_type,$forcedb=null){
        if(!$base_id || !$dest_type)  return false;
        $redis_key=RedisDataService::REDIS_DEST_DETAIL_BASEID.$base_id;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataHgetall($redis_key);
        }
        if(!$result){
            $result=$this->dest_detail_svc->getDestDetailByBaseId($base_id,$dest_type);
            if(!$result) return false;
            $ttl=$this->redisConfig['ttl']['lvyou_dest_detail']?$this->redisConfig['ttl']['lvyou_dest_detail']:null;
            $this->redis_svc->dataHmset($redis_key,$result,$ttl);
        }
        return $result;
    }

    /**
     * 目的地类型初始化
     * @param $dest_type
     * @return string
     */
    private function initDestType($dest_type){
        if(!$dest_type) return '';
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

    public function getRecomDestAction(){
        $dest_id=intval($this->dest_id);
        $forcedb=$this->forcedb;
        $forcedb=intval($forcedb);
        $page_num=$this->pn;
        $page_size=$this->ps?$this->ps:10;
        $limit=intval($this->limit);
        $recom_type=$this->recom_type;
        $recom_dest_ids=$this->getRecomDestIds($dest_id,$forcedb,$limit?$limit:array('page_size'=>intval($page_size),'page_num'=>intval($page_num)),$recom_type);
        if(!$recom_dest_ids) {$this->_errorResponse(DATA_NOT_FOUND,'该数据不存在');exit;}
        if($recom_dest_ids){
            foreach($recom_dest_ids as $id){
                $result[$id]=$this->getDestAll($id,$forcedb);
            }
            $this->jsonResponse($result);
        }

    }
    public function getRecomDestMultAction(){
        $dest_id_str=$this->dest_id;
        $forcedb=$this->forcedb;
        $forcedb=intval($forcedb);
        $page_num=$this->pn;
        $page_size=$this->ps?$this->ps:10;
        $limit=intval($this->limit);
        $recom_type=$this->recom_type;
        if($dest_id_str){
            $dest_ids=explode(',',$dest_id_str);
        }
        foreach($dest_ids as $dest_id){
            $recom_dest_ids[$dest_id]=$this->getRecomDestIds($dest_id,$forcedb,$limit?$limit:array('page_size'=>intval($page_size),'page_num'=>intval($page_num)),$recom_type);
        }
        if(!$recom_dest_ids) {$this->_errorResponse(DATA_NOT_FOUND,'该数据不存在');exit;}
        if($recom_dest_ids){
            foreach($recom_dest_ids as $key=>$row){
                if(!empty($row)){
                    foreach($row as $r){
                        $result[$key][$r]=$this->getDestAll($r,$forcedb);
                    }
                }
            }
            $this->jsonResponse($result);
        }
    }
//    /**
//     * 获取二级导航里的主推荐目的地ID集合
//     * @param $dest_id
//     * @param $forcedb
//     * @param $page
//     * @return array
//     */
//    private function getRecomDestIds($dest_id,$forcedb,$page,$recom_type){
//        $redis_key=RedisDataService::REDIS_RECOM_DEST_IDS.$dest_id.$recom_type;
//        $recom_dest_ids=array();
//        if(!$forcedb){
//            $recom_dest_ids=$this->redis_svc->getRecomDestIds($redis_key,$page);
//        }
//        if(!$recom_dest_ids){
//            $recom_dest_ids=$this->recom_dest_svc->getRecomDest($dest_id,$recom_type);
//            if(!$recom_dest_ids)  return false;
//            $ttl=$this->redisConfig['ttl']['lvyou_recom_dest_ids']?$this->redisConfig['ttl']['lvyou_recom_dest_ids']:null;
//            $this->redis_svc->insertRecomDestIds($redis_key,$recom_dest_ids,$ttl);
//            $recom_dest_ids=$this->parseRecomIds($recom_dest_ids,$page);
//        }
//        return $recom_dest_ids;
//    }
    public function destParentsAction(){
        $dest_id=intval($this->dest_id);
        $forcedb=$this->forcedb;
        $forcedb=intval($forcedb);
        $redis_key=RedisDataService::REDIS_DEST_PARENTS.$dest_id;
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataHgetall($redis_key);
        }
        if(!$result){
            $result=$this->dest_base_svc->getDestParents($dest_id);
            if(!$result) {$this->_errorResponse(DATA_NOT_FOUND,'该数据不存在');exit;}
            $ttl=$this->redisConfig['ttl']['lvyou_dest_parents']?$this->redisConfig['ttl']['lvyou_dest_parents']:null;
            $this->redis_svc->dataHmset($redis_key,$result,$ttl);
        }
        if($result){
            foreach($result as $key=>$row){
                $dest_info=$this->getDestAll($row);
                if($dest_info){
                    $parents[$key]=$dest_info;
                }
            }
        }
        $this->jsonResponse($parents);
    }
    public function destListByIdsAction(){
        $dest_id=$this->dest_id;
        $forcedb=$this->forcedb;
        $forcedb=intval($forcedb);
        if($dest_id){
            $dest_id=explode(',',$dest_id);
            foreach($dest_id as $id){
                $result[]=$this->getDestAll($id,$forcedb);
            }
        }
        $this->jsonResponse($result);
    }
    public function getStaticNumByDestIdAction(){
        $dest_id=$this->dest_id;
        $forcedb=$this->forcedb;
        $forcedb=intval($forcedb);
        $res=array();
        if($img_num=$this->getImageNumByDestId($dest_id,$forcedb)){
            $res['img_num']=$img_num;
        }
        if($view_num=$this->getViewNumByDestId($dest_id,$forcedb)){
            $res['view_num']=$view_num;
        }
        $this->jsonResponse($res);
    }

    private function getViewNumByDestId($dest_id,$forcedb){
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataGet('dest:viewnum:dest_id:'.$dest_id);
        }
        if(!$result){
            $base_id=$this->dest_base_svc->getBaseIdByDestId($dest_id);
            $result=$this->dest_relation_svc->getViewNumByBaseId($base_id);
            if($result){
                $this->redis_svc->dataSet('dest:viewnum:dest_id:'.$dest_id,$result,'23000');
            }
        }
        return intval($result);
    }
    private  function getImageNumByDestId($dest_id,$forcedb){
        $result=array();
        if(!$forcedb){
            $result=$this->redis_svc->dataGet('dest:imagenum:dest_id:'.$dest_id);
        }
        if(!$result){
            $result=$this->dest_image_svc->getImageNumById($dest_id);
            if($result){
                $this->redis_svc->dataSet('dest:imagenum:dest_id:'.$dest_id,$result,'23000');
            }
        }
        return intval($result);
    }
    public function addCountAction(){
        $dest_id=$this->dest_id;
        $type=$this->type;
        $this->dest_detail_svc->addWantAndGo($dest_id,$type);
        $this->_successResponse('success');
    }

    public function getDestCountDataAction(){
        $dest_id=$this->dest_id;
        $dest_type=$this->dest_type;
        $base_id=$this->dest_base_svc->getBaseIdByDestId($dest_id);
        $result=$this->dest_detail_svc->getCountData($base_id,$dest_type);
        $this->jsonResponse($result);
    }

    public function getRecomDestIds($dest_id,$recom_type,$dest_type=''){
        $recom_ids=$this->recom_dest_svc->getRecomDest($dest_id,$recom_type,$dest_type);
        if($recom_ids){
            foreach($recom_ids as $key=>$row){
                if(!$this->dest_base_svc->isDestValid($row['dest_id'])){
                    unset($recom_ids[$key]);
                }
            }
        }
        return $recom_ids;
    }

    /**
     * 获取目的地景点列表数据
     */
    public function getDestChildAction(){
        $dest_id=$this->dest_id;   //目的地ID
        $forcedb=intval($this->forcedb);  //是否强制查询数据库
        $page_num=intval($this->pn);      //分页页码
        $page_size=intval($this->ps?$this->ps:10);  //分页大小
        $limit=intval($this->limit);   //不分页的情况限定数量
        $recom_type=$this->recom_type; //推荐类型
        $dest_type=$this->initDestType($this->dest_type);   //目的地的类型
        $page=$limit?$limit:array('page_size'=>$page_size,'page_num'=>$page_num);
        $result=$this->getDestChildList($dest_id,$page,$recom_type,$dest_type,$forcedb);
        $this->jsonResponse($result);
    }

    public function getDestChildMultAction(){
        $dest_id=$this->dest_id;   //目的地ID
        $forcedb=intval($this->forcedb);  //是否强制查询数据库
        $page_num=intval($this->pn);      //分页页码
        $page_size=intval($this->ps?$this->ps:10);  //分页大小
        $limit=intval($this->limit);   //不分页的情况限定数量
        $recom_type=$this->recom_type; //推荐类型
        $dest_type=$this->initDestType($this->dest_type);   //目的地的类型
        $page=$limit?$limit:array('page_size'=>$page_size,'page_num'=>$page_num);
        if($dest_id){
            $dest_id_arr=explode(',',$dest_id);
            $result=array();
            foreach($dest_id_arr as $id){
                $result[$id]=$this->getDestChildList($id,$page,$recom_type,$dest_type,$forcedb);
            }
        }
        $this->jsonResponse($result);
    }

    /**
     * @param $dest_id
     * @param $page
     * @param $recom_type
     * @param $dest_type
     * @param string $forcedb
     * @return array
     */
    private function getDestChildList($dest_id,$page,$recom_type,$dest_type,$forcedb=''){
        $result=array();
        if(is_array($page)){
            $redis_key=RedisDataService::REDIS_DEST_CHILD_LIST.$dest_id.$recom_type.$dest_type.$page['page_num'].$page['page_size'];
        }else{
            $redis_key=RedisDataService::REDIS_DEST_CHILD_LIST.$dest_id.$recom_type.$dest_type.$page;
        }
        //缓存KEY
        //查询缓存数据
        if(!$forcedb){
            $result=$this->redis_svc->getArrayData($redis_key);
        }
        if(!$result){
            //后台推荐的数据
            $recom_ids=$this->getRecomDestIds($dest_id,$recom_type)?$this->getRecomDestIds($dest_id,$recom_type):array();
            $base_id=$this->dest_base_svc->getBaseIdByDestId($dest_id);
            if($recom_ids){
                $recom_view=array();
                foreach($recom_ids as $key=>$row){
                        $recom_view[]=$this->getDestAll($row['dest_id']);
                }
                //非分页数据
                if(!is_array($page)){
                    //当后台推荐的数量大于限定数量时
                    if(count($recom_ids)>=$page){
                        $view_ids=array_slice($recom_ids,0,$page); //ID数组
                        foreach($view_ids as $key=>$row){
                            $result[]=$this->getDestAll($row['dest_id']);
                        }
                    }else{
                        $poor=intval($page-count($recom_ids));//推荐未足的差值
                        $view_list=$this->dest_relation_svc->getDestChildList($base_id,$poor,$dest_type,$recom_ids);
                        if($view_list){
                            $result=array_merge($recom_view,$view_list);
                        }else{
                            $result=$recom_view;
                        }
                    }
                }else{
                    //有分页的情况
                    if((count($recom_view)-($page['page_num']-1)*$page['page_size'])>=$page['page_size']){
                        $result=array_slice($recom_view,($page['page_num']-1)*$page['page_size'],$page['page_size']);
                    }else{
                        if(count($recom_view)-($page['page_num']-1)*$page['page_size']>=0){
                            $poor=$page['page_size']-(count($recom_view)-($page['page_num']-1)*$page['page_size']);
                            $recom_view=array_slice($recom_view,($page['page_num']-1)*$page['page_size'], $page['page_size']);
                            $view_list=$this->dest_relation_svc->getDestChildList($base_id,$poor,$dest_type,$recom_ids);
                            if(!$view_list){
                                $view_list=array();
                            }
                            $result=array_merge($recom_view,$view_list);
                        }else{
                            $poor=($page['page_num']-2)*$page['page_size']+($page['page_size']-count($recom_view)).','.$page['page_size'];
                            $result=$this->dest_relation_svc->getDestChildList($base_id,$poor,$dest_type,$recom_ids);
                        }

                    }
                }
            }else{
                $result=$this->dest_relation_svc->getDestChildList($base_id,$page,$dest_type,'');
            }
            $this->redis_svc->setArrayData($redis_key,$result,7200);
        }
        return $result;
    }

    public function destIndexImageListAction()
    {
        $dest_ids = trim($this->dest_ids);
        if(empty($dest_ids)){
            $this->_errorResponse(PARAMS_ERROR, '参数dest_ids不能为空！');
        }

        $limit = intval($this->num);

        //从缓存中获取数据
        $redis_key = RedisDataService::REDIS_DEST_INDEX_IMAGE_NEW_LIST.$dest_ids.$limit;
        $elite_img = $this->redis_svc->getArrayData($redis_key);
        if(empty($elite_img)){//缓存数据为空，从数据库取数据
            $elite_img = $this->dest_image_svc->getImgByIds($dest_ids, $limit);
            $this->redis_svc->setArrayData($redis_key, $elite_img, 500);
        }

        $this->jsonResponse($elite_img);
    }


    public function destViewspotGroupBySubAction(){
        $base_id=$this->base_id;
        $num=$this->num;
        $dest_num=$this->dest_num;
        $viewspot_ids=$this->getViewSpotIds($base_id);
        $viewspot_ids_str=implode(',',$viewspot_ids);
        $subject_list=$this->mo_subject->getDestSubList($viewspot_ids_str);
        $result=array();
        if($num){
            $subject_list=array_slice($subject_list,0,$num);
            foreach($subject_list as $subject){
                $subject_dest=$this->redis_svc->getListData(RedisDataService::REDIS_SUBJECT_DEST_LIST.$subject['subject_id'],0,-1);
                $dest_list=array_intersect($viewspot_ids,$subject_dest);
                if($dest_list){
                    if(count($dest_list)>=$dest_num){
                        $dest_list=array_slice($dest_list,0,$dest_num);
                    }
                    foreach($dest_list as $k=>$dest_id){
                        $result[$subject['subject_id']]['name']=$subject['subject_name'];
                        $result[$subject['subject_id']]['num']=$subject['num'];
                        $result[$subject['subject_id']]['dest_list'][]=$this->getDestAll($dest_id);
                    }
                    $tmp=array_diff($viewspot_ids,$dest_list);
                    unset($viewspot_ids);
                    $viewspot_ids=$tmp;
                }
            }
        }
        $this->jsonResponse($result);
    }
    private function getViewSpotIds($base_id){
        if(!$base_id) return array();
        $redis_key=RedisDataService::REDIS_DEST_VIEWSPOT_LIST.$base_id;
        $viewspot_ids=$this->redis_svc->getListData($redis_key,0,-1);
        if($viewspot_ids) return $viewspot_ids;
        $viewspots=$this->dest_relation_svc->getDestChildList($base_id,'','viewspot','');
        if($viewspots){
            foreach($viewspots as $view){
                $viewspot_ids[]=$view['dest_id'];
                $this->redis_svc->setListData($redis_key,$view['dest_id'],3600);
            }
            return $viewspot_ids;
        }
    }

    public function destSubjectListAction(){
        $subject_list = $viewspot_ids = array();

        $dest_id = intval($this->dest_id);
        if(empty($dest_id)){
            $this->_errorResponse(PARAMS_ERROR, '参数dest_id不能为空！');
        }
        //从缓存中获取数据
        $redis_key = RedisDataService::REDIS_DEST_VIEWSPOT_NEW_LIST.$dest_id;
        $viewspot_ids = $this->redis_svc->getListData($redis_key, 0, -1);
        if(empty($viewspot_ids)){//缓存数据为空，从数据库取数据
            $viewspots = $this->destin_base_service_svc->getDestChildList($dest_id, 'viewspot');
            if(!empty($viewspots)){
                foreach($viewspots as $view){
                    $viewspot_ids[] = $view['dest_id'];
                    //数据存入缓存
                    $this->redis_svc->setListData($redis_key, $view['dest_id'], 3600);
                }
            }
        }
        
        if(!empty($viewspot_ids)){
            $viewspot_ids_str = implode(',', $viewspot_ids);
            $subject_list = $this->mo_subject->getDestSubList($viewspot_ids_str);
        }

        $this->jsonResponse($subject_list);
    }

    public function destDistrictAction(){
        $dis_pid=$this->dis_pid;
        $dest_type=$this->dest_type;
        $result=$this->dest_relation_svc->getDistrictByPid($dis_pid,$dest_type);
        if($result){
            foreach($result as $value){
                $dis_ids[]=$value['district_id'];
            }
            $dis_ids_str=implode(',',$dis_ids);
            $list=$this->dest_relation_svc->getViewNumByDisId($dis_ids_str);
            if($list){
                $list=UCommon::parseItem($list,'district_id');
                foreach($result as $r){
                    if($list[$r['district_id']]['num']){
                        $list[$r['district_id']]['dest_name']=$r['dest_name'];
                    }
                }
                $list=UCommon::array_sort($list,'num','DESC');
            }
        }
        $this->jsonResponse($list);
    }

    public function getDestIdsByNameLike($dest_name){
        if(!$dest_name) return array();
        $redis_key=RedisDataService::REDIS_DEST_VIEWSPOT_LIST.$dest_name;
        $dest_ids=$this->redis_svc->getListData($redis_key,0,-1);
        if($dest_ids) return $dest_ids;
        $dest_ids=$this->dest_base_svc->getDestIdsByDestName($dest_name);
        if($dest_ids){
            foreach($dest_ids as $view){
                $viewspot_ids[]=$view['dest_id'];
                $this->redis_svc->setListData($redis_key,$view['dest_id'],4800);
            }
            return $viewspot_ids;
        }
    }

    public function destViewListByPidAndNameAction(){
        $dest_name=$this->dest_name;
        $base_id=$this->base_id;
        $pn=$this->pn;
        $ps=$this->ps;
        $result=array();
        $viewpspot_ids=$this->getViewSpotIds($base_id);
        if(!$viewpspot_ids) {$this->jsonResponse($result);die();}
        $name_like_ids=$this->getDestIdsByNameLike($dest_name);
        if(!$name_like_ids) {$this->jsonResponse($result);die();}
        $dest_list=array_intersect($viewpspot_ids,$name_like_ids);
        if(!$dest_list) {$this->jsonResponse($result);die();}
        $use_ids=array_slice($dest_list,($pn-1)*$ps,$ps);
        foreach($use_ids as $key=>$dest_id){
            $result['list'][]=$this->getDestAll($dest_id);
        }
        $result['total']=count($dest_list);
        $this->jsonResponse($result);

    }

    public function viewListByTagAction(){
        $base_id=$this->base_id;
        $pn=$this->pn;
        $ps=$this->ps;
        $tag=unserialize($this->tag_condition);
        $viewspot_all=$this->getViewSpotIds($base_id);
        if($tag['theme']){
            $sub_ids=$this->getViewListBySubId($viewspot_all,$tag['theme']);
            $dest['total']=count($sub_ids);
            $result=$sub_ids;
        }
        if($tag['area']){
            $dis_ids=$this->getViewListByDis($viewspot_all,$tag['area']);
            $dest['total']=count($dis_ids);
            $result=$dis_ids;
        }
        if($tag['area'] && $tag['theme'] ){
            $result=array_intersect($sub_ids,$dis_ids);
            $dest['total']=count($result);
        }
        if($result){
            $result=array_slice($result,($pn-1)*$ps,$ps);
            foreach($result as $dest_id){
                $dest['list'][]=$this->getDestAll($dest_id);
            }
        }
        $this->jsonResponse($dest);
    }
    private function getViewListBySubId($viewspot_all,$subject_id){
        $result=array();
        if(!$viewspot_all) { return $result;}
        $subject_dest=$this->redis_svc->getListData(RedisDataService::REDIS_SUBJECT_DEST_LIST.$subject_id,0,-1);
        if(!$subject_dest) {return $result;}
        $result=array_intersect($viewspot_all,$subject_dest);
        return $result;

    }
    public function getViewListByDis($viewspot_all,$dis_id){
        $result=array();
        if(!$viewspot_all) { return $result;}
        $dis_dest=$this->dest_relation_svc->getViewByDis($dis_id);
        if(!$dis_dest) {return $result;}
        foreach($dis_dest as $id){
            $dest_ids[]=$id['dest_id'];
        }
        $result=array_intersect($viewspot_all,$dest_ids);
        return $result;
    }
    public function restListByDisAction(){
        $base_id=$this->base_id;
        $dis=$this->dis;
        $num=$this->num;
        $rest_all=$this->getRestIdsByPid($base_id);
        if(!$rest_all) {$this->_errorResponse(DATA_NOT_FOUND,'该数据不存在');exit;}
        $dis_ids=$this->getRestByDis($rest_all,$dis);
        if($dis_ids){
            $result=array_slice($dis_ids,0,$num);
            foreach($result as $dest_id){
                $dest['list'][]=$this->getDestAll($dest_id);
            }
        }
        $this->jsonResponse($dest);
     }
    public function getRestByDis($rest_all,$dis_id){
        $result=array();
        if(!$rest_all) { return $result;}
        $dis_dest=$this->dest_relation_svc->getViewByDis($dis_id);
        if(!$dis_dest) {return $result;}
        foreach($dis_dest as $id){
            $dest_ids[]=$id['dest_id'];
        }
        $result=array_intersect($rest_all,$dest_ids);
        return $result;
    }
    public function getRestIdsByPid($base_id){
        if(!$base_id) return array();
        $redis_key=RedisDataService::REDIS_DEST_REST_LIST.$base_id;
        $rest_ids=$this->redis_svc->getListData($redis_key,0,-1);
        if($rest_ids) return $rest_ids;
        $rest_ids=$this->dest_relation_svc->getDestChildList($base_id,'','restaurant','');
        if($rest_ids){
            foreach($rest_ids as $view){
                $rest_ids[]=$view['dest_id'];
                $this->redis_svc->setListData($redis_key,$view['dest_id'],3600);
            }
            return $rest_ids;
        }
    }
}
