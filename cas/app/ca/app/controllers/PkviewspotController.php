<?php
use Lvmama\Common\Utils\UCommon as UCommon;
/**
 * PK景点接口控制器
 * 
 * @author win.shenxiang
 *
 */
class PkviewspotController extends PkController {
    public function initialize()
    {
        $this->api = 'viewspot';
        parent::initialize();
    }
    public function indexAction(){
        $this->succResponse(array('this is ok pages!'));
    }
    /**
     * 获取目的基本信息
     *
     * @example curl -i -X GET http://ca.lvmama.com/pkviewspot/getDestById/79/
     */
    public function getDestByIdAction($dest_id = 0){
        if(!$dest_id || !is_numeric($dest_id)){
            $this->_errorResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        $destination = $this->di->get('cas')->get('destination-data-service');
        $data = $destination->getDestById($dest_id);
        $this->succResponse($data);
    }
    /**
     * 获取目的地下面的景点POI
     *
     * @example curl -i -X GET http://ca.lvmama.com/pkviewspot/getViewspot/79/
     */
    public function getViewspotAction($dest_id = 0,$limit = 7){
        if(!$dest_id || !is_numeric($dest_id)){
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        if($limit <= 0 || $limit > 15){
            $this->errResponse(100002, '查询的数量有误，须在1到15之间!');
        }
        $destination = $this->di->get('cas')->get('destination-data-service');
        $dest = $destination->getDestById($dest_id);

        if(!$dest){
            $this->errResponse(100004, '没有找到相关的目的地!');
        }
        if(!isset($dest['dest_type']) || !in_array($dest['dest_type'],$this->_type)){
            $this->errResponse(100005, '目的地类型有误!');
        }
        $scenicviewspot = $this->di->get('cas')->get('scenicviewspot-data-service');
        $data = array();
        $viewspotarr = $scenicviewspot->getLists($dest_id,$limit);
        if($viewspotarr){
            $viewspotarr=UCommon::parseItem($viewspotarr,'viewspot_id');
            foreach($viewspotarr as $key=>$row){
                $viewspot_id[]=$key;
            }
            $data = $destination->getListByViewSpots($viewspot_id);
        }
        $total_num = count($data);
        //没有推荐景点的情况下
        if($limit - $total_num > 0){
            $_limit = $limit - $total_num;
            $surplus_data = $destination->getRsBySql("SELECT dest_id,dest_name,pinyin,dest_type,parents,img_url,parent_id,en_name,cancel_flag,stage,`range`,intro,star,abroad,url,ent_sight,count_been,count_want,g_longitude,g_latitude,longitude,latitude FROM ly_destination WHERE (dest_type='VIEWSPOT' OR (dest_type='SCENIC_ENTERTAINMENT' AND ent_sight='Y' )) AND cancel_flag='Y' AND showed='Y' AND parents LIKE '{$dest['parents']},%' ORDER BY count_want DESC,count_been DESC LIMIT {$_limit}");
            foreach($surplus_data as $k=>$v){
                $surplus_data[$k]['seq'] = 10000 + $k;
                $surplus_data[$k]['recommend_id'] = 10000 + $k;
            }
            $data = array_merge($data,$surplus_data);
        }
        $data=UCommon::parseItem($data,'dest_id');
        foreach($data as $k=>$v){
            if(!isset($data[$k])){
                $data[$k] = array();
            }
            if(!isset($data[$k]['seq'])){
                $data[$k]['seq'] = $viewspotarr[$k]['seq'];
            }
            if(!isset($data[$k]['recommend_id'])){
                $data[$k]['recommend_id'] = $viewspotarr[$k]['recommend_id'];
            }
        }
        $data=UCommon::array_sort($data,'seq','asc');
        $this->succResponse($data);
    }
    public function getViewspotNumAction($dest_id = 0){
        if(!$dest_id || !is_numeric($dest_id)){
            $this->errResponse(100001, '目的地ID必传且为整数类型');
        }
        $dest_id = intval($dest_id);
        $destination = $this->di->get('cas')->get('destination-data-service');
        $dest = $destination->getDestById($dest_id);
        if(!$dest){
            $this->errResponse(100002, '没有找到相关的目的地!');
        }
        if(!isset($dest['dest_type']) || !in_array($dest['dest_type'],$this->_type)){
            $this->errResponse(100003, '目的地类型有误!');
        }
        $data = $destination->getRsBySql("SELECT COUNT(dest_id) AS n FROM ly_destination WHERE (dest_type='VIEWSPOT' OR (dest_type='SCENIC_ENTERTAINMENT' AND ent_sight='Y' )) AND cancel_flag='Y' AND showed='Y' AND parents LIKE '{$dest['parents']},%'",true);
        $this->succResponse(isset($data['n']) ? $data['n'] : 0);
    }
}