<?php

use Lvmama\Common\Utils\UCommon as UCommon;
/**
 * 站点管理 控制器
 *
 *
 */
class WebsiteController extends ControllerBase {

    private $redis;

    private $dist_base_svc;

    public function initialize() {
        parent::initialize();
        $this->redis = $this->di->get('cas')->getRedis();
        $this->dist_base_svc = $this->di->get('cas')->get('dist_base_service');

    }

    /**
     * 分站绑定站点列表
     * spm精确到key_id
     * other 不为空取其它key_id数据
     */
    public function subWebListAction(){
        $spm = $this->request->get('spm');
        $other = $this->request->get('other');
        $spm_str = UCommon::spreadRule($spm);
        $channel_id = $spm_str['channel_id'];
        $route_id = $spm_str['route_id'];
        $key_id = $spm_str['key_id'];

        if($spm_str == false
            || empty($channel_id) || empty($route_id) || empty($key_id)){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }

        $website_spm_srv = $this->di->get('cas')->get('ch_website_spm_rel');

        $data[] = "channel_id =".$channel_id;
        $data[] = "route_id =".$route_id;
        if(!empty($other)) {
            $data[] = "key_id !=".$key_id;
        }else{
            $data[] = "key_id =".$key_id;
        }
        $data_where = implode(' AND ',$data);
        $info = $website_spm_srv->getDataList($data_where,null,'website_id');
        if(empty($info)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        $data = array();
        foreach($info as $value){
            $data[] = $value['website_id'];
        }
        $this->jsonResponse($data);
    }

    /**
     * 分站绑定站点
     */
    public function subWebBindAction(){
        $spm = $this->request->getPost('spm');
        $website_id = $this->request->getPost('website_id');
        $spm_str = UCommon::spreadRule($spm);
        $channel_id = $spm_str['channel_id'];
        $route_id = $spm_str['route_id'];
        $key_id = $spm_str['key_id'];

        if( $spm_str == false || empty($channel_id) || empty($route_id) || empty($key_id) ){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }
        $website_spm_srv = $this->di->get('cas')->get('ch_website_spm_rel');
        $data = array();
        $data[] = "channel_id =".$channel_id;
        $data[] = "route_id =".$route_id;
        $data[] = "key_id =".$key_id;
        $data_where = implode(' AND ',$data);
        $website_id_arr = json_decode($website_id,true);
        if(!empty($website_id_arr)){
            foreach($website_id_arr as $k=>$v){
                $where = $data_where.' AND website_id ='.$v;
                $exist = $website_spm_srv->getTotal($where);
                if(empty($exist)) {
                    $website_spm_srv->insert(array(
                        'website_id'=>$v,
                        'channel_id'=>$channel_id,
                        'route_id'=>$route_id,
                        'key_id'=>$key_id,
                    ));
                }
            }
            $website_ids = implode(',',$website_id_arr);
            $del_where = $data_where." AND website_id not in ({$website_ids})";
            $website_spm_srv->deleteByWhere($del_where);
        }else{
            $website_spm_srv->deleteByWhere($data_where);
        }
        $this->_successResponse('成功');

    }


    /***
     * @param spm           string spm码精确到路由
     * @param website_id    int    分站id
     * @return int key_id   页面id
     */
    public function getKeyIdByTypeWebsiteIdAction(){
        $spm = $this->request->get('spm');
        $website_id = intval($this->request->get('website_id'));
        if( empty($spm) || empty($website_id) ){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);
        $channel_id = $spm_str['channel_id'];
        $route_id = $spm_str['route_id'];

        if( $spm_str == false || empty($channel_id) || empty($route_id) ){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }
        $website_spm_srv = $this->di->get('cas')->get('ch_website_spm_rel');
        $where = "channel_id =". $channel_id ." AND route_id =". $route_id ." AND website_id =". $website_id;

        $info = $website_spm_srv->getDataOne($where);
        
        if(empty($info)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }

        $this->_successResponse($info['key_id']);

    }

    /**
     * 行政区id 关系往上找 到省级
     * spm精确到路由
     *
     * 返回 key_id
     */
    public function getParentToChinaByDistAction(){
        $district_id = intval($this->request->get('district_id'));
        $spm = $this->request->get('spm');
        if( empty($spm) || empty($district_id) ){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }
        $spm_str = UCommon::spreadRule($spm);
        $channel_id = $spm_str['channel_id'];
        $route_id = $spm_str['route_id'];

        if( $spm_str == false || empty($channel_id) || empty($route_id) ){
            $this->_errorResponse(PARAMS_ERROR,'参数错误');
            return;
        }

        $China_info = $this->dist_base_svc->getOneDist(array('district_name'=>"='中国'"));

        if(empty($China_info['district_id'])) {
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }

        $parents = $district_id;
        $distarr = array();
        loop_start:
        if ($parents <> $China_info['district_id']) {
            $parent = $this->dist_base_svc->getOneDist(array('district_id' => "=" . $parents));
            if (!empty($parent['parent_id']) && $parent['parent_id'] <> $parents) {
                $distarr[] = $parents;

                $parents = intval($parent['parent_id']);
                goto loop_start;
            }
        }
        if(empty($distarr)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        if(count($distarr)==1){
            $where = " website_id = ".$distarr[0];
        }else{
            $ids = implode(',',$distarr);
            $where = " website_id IN ( ".$ids." )";
        }

        $website_spm_srv = $this->di->get('cas')->get('ch_website_spm_rel');
        $where .= " AND channel_id =". $channel_id ." AND route_id =". $route_id;

        $info = $website_spm_srv->getDataList($where,1,'key_id','website_id DESC');

        if(empty($info)){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
            return;
        }
        $this->jsonResponse(array('results' => $info));

    }
}