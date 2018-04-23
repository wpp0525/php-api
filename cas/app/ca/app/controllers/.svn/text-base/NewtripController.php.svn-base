<?php

use Lvmama\Cas\Service\BeanstalkDataService;
use Lvmama\Common\Utils\UCommon;
use Lvmama\Cas\Service\RedisDataService;

/**
 * 新游记 控制器
 *
 * @author zhta
 *
 */
class NewtripController extends ControllerBase {

    private $newtripsvc;
    private $tripsvc;

    private $tripdatasvc;

    public function initialize() {
        $this->newtripsvc = $this->di->get('cas')->get('travel_data_service');
        $this->tripsvc = $this->di->get('cas')->get('trip-data-service');
        $this->redis_svc=$this->di->get('cas')->get('redis_data_service');
        $this->tripdatasvc = $this->di->get('cas')->get('trip-data-service');

        return parent::initialize();
    }

    /**
     * 游记主表数据增改
     *
     * @author zhta
     *
     * @example curl -i -X POST -d "uid=1&title=test" http://ca.lvmama.com/newtrip/info-update/json/2/3/4
     */
    public function createInfoAction() {
        $now = time();
        $data = array();
        $ext_data=array();
        $data['update_time']=$now;
        $ext_data['update_time']=$now;
        $trip_flag=1;
        if($this->uid) {
            $data['uid'] = $this->uid;
            $trip_flag=2;
        }
        if($this->username) {
            $data['username'] = $this->username;
            $trip_flag=2;
        }
        if($this->title) {
            $data['title'] = $this->title;
            $trip_flag=2;
        }
        if($this->seo_title) {
            $data['seo_title'] = $this->seo_title;
            $trip_flag=2;
        }
        if($this->summary) {
            $data['summary'] = $this->summary;
            $trip_flag=2;
        }
        if($this->thumb) {
            $data['thumb'] = $this->thumb;
            $trip_flag=2;
        }
        if($this->start_time) {
            $data['start_time'] = $this->start_time;
            $trip_flag=2;
        }
        if($this->publish_time) {
            $data['publish_time'] = $this->publish_time;
            $trip_flag=2;
        }
        if($this->order_num) {
            $data['order_num'] = $this->order_num;
            $trip_flag=2;
        }
        if($this->losc_inner) {
            $data['losc_inner'] = $this->losc_inner;
            $trip_flag=2;
        }
        if($this->losc_outer) {
            $data['losc_outer'] = $this->losc_outer;
            $trip_flag=2;
        }
        if($this->status || $this->status==="0") {
            $data['status'] = $this->status;
            $trip_flag=2;
        }
        if($this->recommend_status) {
            $data['recommend_status'] = $this->recommend_status;
            $trip_flag=2;
        }
        if($this->trip_id) {
            if($trip_flag==2){
                $where="id=".$this->trip_id;
                $this->newtripsvc->update(array("table"=>"travel","where"=>$where,"data"=>$data));
            }
        }else{
            $data["create_time"]=$now;
            $trip_data['table']="travel";
            $trip_data['data']=$data;
            $res=$this->newtripsvc->insert($trip_data);
            if($res["error"]==0){
                $this->trip_id=$res["result"];
            }
        }
        $trip_ext=$this->newtripsvc->select(array(
            "table"=>"travel_ext",
            'select' => 'id',
            'where' => array('travel_id'=>$this->trip_id)
        ));
        if($this->order_id) {
            $ext_data['order_id'] = $this->order_id;
            $trip_flag=3;
        }
        if($this->product_id) {
            $ext_data['product_id'] = $this->product_id;
            $trip_flag=3;
        }
        if($this->source || $this->source==="0") {
            $ext_data['source'] = $this->source;
            $trip_flag=3;
        }
        if($this->platform || $this->platform==="0") {
            $ext_data['platform'] = $this->platform;
            $trip_flag=3;
        }
        if($this->device_no) {
            $ext_data['device_no'] = $this->device_no;
            $trip_flag=3;
        }
        if($this->port) {
            $ext_data['port'] = $this->port;
            $trip_flag=3;
        }
        if($this->commit_time) {
            $ext_data['commit_time'] = $this->commit_time;
            $trip_flag=3;
        }
        if($this->main_status || $this->main_status==="0") {
            $ext_data['main_status'] = $this->main_status;
            $trip_flag=3;
        }
        if($this->del_status || $this->del_status==="0") {
            $ext_data['del_status'] = $this->del_status;
            $trip_flag=3;
        }
        if($this->fanli_status || $this->fanli_status==="0") {
            $ext_data['fanli_status'] = $this->fanli_status;
            $trip_flag=3;
        }

        if($trip_ext["list"]) {
            if($trip_flag==3){
                $ext_where="id=".$trip_ext["list"][0]["id"];
                $this->newtripsvc->update(array("table"=>"travel_ext","where"=>$ext_where,"data"=>$ext_data));
            }
        }else{
            $ext_data["create_time"]=$now;
            $ext_data["travel_id"]=$this->trip_id;
            $tripext_data['table']="travel_ext";
            $tripext_data['data']=$ext_data;
            $this->newtripsvc->insert($tripext_data);
        }

        if($this->trip_id && $this->main_status && $this->main_status==1){
            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_DEST)->put(json_encode(array("id"=>$this->trip_id)));
            $this->beanstalk->useTube(BeanstalkDataService::BEANSTALK_TRAVEL_CONTENT_4_SENSITIVEWORD)->put(json_encode(array("id"=>$this->trip_id, 'uid' => $this->uid)));
        }
        $content = array(
            'tripid' => $this->trip_id,
        );
        $this->_successResponse($content);
    }

    public function getDestInfoAction(){

        $destName = $this->destName;
        $destId   = $this->destId;


        $data = array("list" => array());
        if($destName) {
            $data = $this->tripdatasvc->select(array(
                'table' => 'ly_destination',
                'select' => 'dest_id,dest_name,dest_type,dest_type_name,parent_name',
                'where' => array('dest_name' => $destName, 'cancel_flag' => 'Y', 'showed' => 'Y',
                    'dest_type' => array('IN', "('COUNTRY','CITY','SCENIC','SPAN_PROVINCE','PROVINCE')")),
                'limit' => '1',
            ));
        }elseif($destId){

            $data = $this->tripdatasvc->select(array(
                'table' => 'ly_destination',
                'select' => 'dest_id,dest_name,dest_type,dest_type_name,parent_name',
                'where' => array('dest_id' => $destId),
                'limit' => '1',
            ));
        }

        $this->_successResponse($data);
    }

    /**
     * 游记章节增改
     *
     * @author zhta
     *
     */
    public function createContentAction() {
        $now = time();
        $data = array();
        $data['update_time']=$now;
        $dest_data['update_time']=$now;
        $content_flag=1;
        if($this->title) {
            $data['title'] = $this->title;
            $content_flag=2;
        }
        if($this->trip_id) {
            $data['travel_id'] = $this->trip_id;
            $content_flag=2;
        }
        if($this->content) {
            $data['content'] = $this->content;
            $content_flag=2;
        }
        if($this->order_num) {
            $data['order_num'] = $this->order_num;
            $content_flag=2;
        }
        if($this->sync_status || $this->sync_status==="0") {
            $data['sync_status'] = $this->sync_status;
            $content_flag=2;
        }
        if($this->content_id) {
            if($content_flag==2){
                $where="id=".$this->content_id;
                $this->newtripsvc->update(array("table"=>"travel_content","where"=>$where,"data"=>$data));
            }
        }else{
            $data["create_time"]=$now;
            $content_data['table']="travel_content";
            $content_data['data']=$data;
            $res=$this->newtripsvc->insert($content_data);
            if($res["error"]==0){
                $this->content_id=$res["result"];
            }
        }
        if($this->dest_id || $this->dest_id==="0") {
            $dest_data['dest_id'] = $this->dest_id;
            $content_flag=3;
        }
        if($this->dest_type || $this->dest_type==="0") {
            if($this->dest_type==="0"){
                $dest_data['dest_type'] ="";
            }else{
                $dest_data['dest_type'] = $this->dest_type;
            }
            $content_flag=3;
        }
        if($this->is_main || $this->is_main==="0") {
            $dest_data['is_main'] = $this->is_main;
            $content_flag=3;
        }
        $dest_data['travel_content_id']=$this->content_id;
        if($this->travel_content_id){
            if($content_flag==3){
                $content_where="id=".$this->travel_content_id;
                $this->newtripsvc->update(array("table"=>"travel_content_dest_rel","where"=>$content_where,"data"=>$dest_data));
            }
        }else{
            $dest_data["create_time"]=$now;
            $dest_data["travel_id"]=$this->trip_id;
            $content_dest_data['table']="travel_content_dest_rel";
            $content_dest_data['data']=$dest_data;
            $this->newtripsvc->insert($content_dest_data);
        }
        $content = array(
            'contentid' => $this->content_id,
        );
        $this->_successResponse($content);
    }

    /**
     * 游记图片增改
     *
     * @author zhta
     *
     */
    public function createImageAction() {
        $now = time();
        $data = array();
        $data['update_time']=$now;
        $image_rel_data["update_time"]=$now;
        $img_flag=1;
        if($this->dest_id || $this->dest_id==="0") {
            $data['dest_id'] = $this->dest_id;
            $img_flag=2;
        }
        if($this->width) {
            $data['width'] = $this->width;
            $img_flag=2;
        }
        $data['url'] = $this->imgurl;
        $img_data=$this->newtripsvc->select(array(
            "table"=>"image",
            'select' => 'id',
            'where' => array('url'=>$data['url'])
        ));
        if($img_data["list"]) {
            $img_id=$img_data["list"][0]["id"];
            if($img_flag==2){
                $where="id=".$img_id;
                $this->newtripsvc->update(array("table"=>"image","where"=>$where,"data"=>$data));
            }
        }else{
            $data["create_time"]=$now;
            $image_data['table']="image";
            $image_data['data']=$data;
            $res=$this->newtripsvc->insert($image_data);
            if($res["error"]==0){
                $img_id=$res["result"];
            }
        }
        $trip_image=$this->newtripsvc->select(array(
            "table"=>"travel_image_rel",
            'select' => 'id',
            'where' => array('image_id'=>$img_id)
        ));
        if($this->trip_id) {
            $image_rel_data['travel_id'] = $this->trip_id;
            $img_flag=3;
        }
        if($trip_image["list"]) {
            if($img_flag==3){
                $image_where="id=".$trip_image["list"][0]["id"];
                $this->newtripsvc->update(array("table"=>"travel_image_rel","where"=>$image_where,"data"=>$image_rel_data));
            }
        }else{
            $image_rel_data["create_time"]=$now;
            $image_rel_data["image_id"]=$img_id;
            $trip_image_data['table']="travel_image_rel";
            $trip_image_data['data']=$image_rel_data;
            $this->newtripsvc->insert($trip_image_data);
        }
        $content = array(
            'imgid' => $img_id,
        );
        $this->_successResponse($content);
    }

    /**
     * 游记查询
     *
     * @author zhta
     *
     */
    public function selectTripAction() {
        if($this->table) {
            $data['table'] = $this->table;
        }
        if($this->select) {
            $data['select'] = $this->select;
        }
        if($this->where) {
            $data['where'] = unserialize($this->where);
        }
        if($this->order) {
            $data['order'] = $this->order;
        }
        if($this->group) {
            $data['group'] = $this->group;
        }
        if($this->limit) {
            $data['limit'] = $this->limit;
        }
        if($this->page) {
            $data['page'] = unserialize($this->page);
        }
        $res=$this->newtripsvc->select($data);
        $this->_successResponse($res);
    }

    /**
     * 游记删除(物理删除)
     *
     * @author zhta
     *
     */
    public function deleteTripAction() {
        if($this->table) {
            $data['table'] = $this->table;
        }
        if($this->where) {
            $data['where'] = unserialize($this->where);
        }
        $res=$this->newtripsvc->delete($data);
        $this->_successResponse($res);
    }

    /**
     * 游记删除(逻辑删除：修改状态)
     */
    public function deleteTravelAction()
    {
        //先判断游记ID和用户ID是否都正确，正确则修改
        if (!$this->trip_id || !$this->uid)
            $this->_errorResponse(100010, '缺少参数');
        //TODO 此处应该用事务提交，以后修改
        $select_res = $this->newtripsvc->select(array(
            'table' => 'travel',
            'select' => 'id',
            'where' => array('id' => $this->trip_id, 'uid' => $this->uid),
            'limit' => '1',
        ));

        if ($select_res['list']) {
            $this->newtripsvc->update(array(
                'table' => 'travel',
                'where' => "`id` = '{$this->trip_id}' AND `uid` = '{$this->uid}'",
                'data' => array('status' => '0'),
            ));
            $this->newtripsvc->update(array(
                'table' => 'travel_ext',
                'where' => "`travel_id` = '{$this->trip_id}'",
                'data' => array('del_status' => '1'),
            ));
        }
        //TODO
        $this->_successResponse(array('删除成功'));
    }

    /**
     * 原生SQL
     *
     * @author zhta
     *
     */
    public function queryTripAction() {
        if($this->sql) {
            $res=$this->querySql($this->sql);
        }
        $this->_successResponse($res);
    }

    /**
     * 执行SQL语句
     * @param $sql
     * @return mixed
     */
    private function querySql($sql){
        return $this->newtripsvc->querySql($sql);
    }

    /**
     * 返回目的地相关游记
     * 用于视频游记
     * 相关逻辑：取关联到目的地的游记。优先精华，按发布时间从新到旧排序
     */
    public function getTripDataForVideoByDestIdAction(){
        if(!$this->dest_id)
            $this->_errorResponse(100010,'缺少参数');
        $sql = "SELECT t.`id`,t.`title`,t.`thumb` FROM `tr_travel_dest_rel` as td INNER JOIN `tr_travel` AS t ON t.`id` = td.`travel_id` WHERE t.`status` = '1' AND td.`dest_id`={$this->dest_id} GROUP BY t.`id` ORDER BY t.`recommend_status` DESC,t.`publish_time` DESC LIMIT 0, 4";
        $res = $this->querySql($sql);
        $this->_successResponse($res);
    }

    /**
     * 游记推荐接口
     *
     * @author zhta
     *
     */
    public function getRecommendTripAction() {
        $trip_id=$this->trip_id;
        $trip_data=array();
        $firstDest=$this->newtripsvc->select(array(
            "table"=>"travel_dest_rel",
            'select' => 'dest_id',
            'where' => array('is_main'=>1,'travel_id'=>$trip_id)
        ));
        if($firstDest["list"]){
            $trips = $this->newtripsvc->select(array(
                "table"=>"travel_dest_rel",
                'select'=>'`travel_id`',
                'where'=>array("dest_id"=>$firstDest["list"][0]["dest_id"],'travel_id'=>array("!=",$trip_id)),
            ));
            if($trips["list"]){
                $trip_ids="";
                foreach($trips["list"] as $k=>$v){
                    if($trip_ids){
                        $trip_ids=$v["travel_id"];
                    }else{
                        $trip_ids=$trip_ids.",".$v["travel_id"];
                    }
                }
            }
            if($trip_ids){
                $hot_trips = $this->newtripsvc->select(array(
                    "table"=>"travel",
                    'select'=>'`id`,`thumb`,`title`',
                    'where'=>array("status"=>1,'id'=>array("IN","(".$trip_ids.")")),
                    'order'=>"`publish_time` DESC",
                    'limit'=>"0,3"
                ));
            }
            if($hot_trips["list"]){
                foreach($hot_trips["list"] as $k=>$row){
                    $temp["tripId"]=$row["id"];
                    $temp["thumb"]=$row["thumb"];
                    $temp["title"]=$row["title"];
                    $temp["tarces"]=array();
                    $sql = "SELECT b.`dest_id`,a.`title` FROM `tr_travel_content` a,`tr_travel_content_dest_rel` b WHERE a.`travel_id`=b.`travel_id` AND b.`dest_type`='VIEWSPOT' AND b.`dest_id`!=0 AND a.`travel_id`={$row["id"]}";
                    $trace_list = $this->querySql($sql);
                    if($trace_list["list"]){
                        foreach($trace_list["list"] as $kk=>$vv){
                            $trace_temp["destId"]=$vv["dest_id"];
                            $trace_temp["destName"]=$vv["title"];
                            $trace_temp["destUrl"]="http://www.lvmama.com/lvyou/poi/sight-".$vv["dest_id"].".html";
                            $temp["tarces"][]=$trace_temp;
                        }
                    }
                    $trip_data[]=$temp;
                }
            }
        }
        $this->_successResponse($trip_data);
    }

    /**
     * 目的地游记接口
     *
     * @author zhta
     *
     */
    public function getTripByDestAction() {
        $dest_id=$this->dest_id;
        $page=$this->page?$this->page:1;
        $pageSize=$this->pageSize?$this->pageSize:20;
        $redis_key = RedisDataService::REDIS_DEST_TRIP_IDS.$dest_id;
        $redis_data = $this->redis_svc->getZrevrange($redis_key, ($page-1)*$pageSize, $page*$pageSize-1);
        $totle = $this->redis_svc->getZCard($redis_key);
        $data["list"]=array();
        if($totle>0){
            $trip_ids=array();
            foreach($redis_data as $k => $v){
                $trip_ids[]=$v;
            }
            $tripids = implode(',', $trip_ids);

            //取游记收益
            $travel_bonus_data = $this->tripsvc->select(array(
                'table' => 'ly_bonus',
                'select' => 'trip_id,SUM(commission_amt) AS amt',
                'where' => array('remit_status' => '99','type' => array('IN',"('order','page','admin','act_trip','hot_trip')"),'trip_id' => array('IN',"({$tripids})")),
                'group' => 'trip_id',
            ));
            $travel_bonus_data = UCommon::parseItem($travel_bonus_data['list'],'trip_id');

            //标签数据
            $tag_item_data = $this->tripsvc->select(array(
                'table' => 'ly_tag_item',
                'select' => 'tag_id,object_id',
                'where' => array('object_type' => 'trip','object_id' => array('IN',"({$tripids})")),
            ));
            $tag_id_arr = $travel_tag_data = array();
            foreach ($tag_item_data['list'] as $item) {
                if(!in_array($item['tag_id'],$tag_id_arr))
                    $tag_id_arr[] = $item['tag_id'];
            }
            if($tag_id_arr) {
                $tag_id_str = implode(',', $tag_id_arr);
                $tag_data = $this->tripsvc->select(array(
                    'table' => 'ly_tag',
                    'select' => 'tag_id,tag_name,tag_type',
                    'where' => array('status' => '99', 'tag_id' => array('IN', "({$tag_id_str})")),
                ));
                $tag_data = UCommon::parseItem($tag_data['list'], 'tag_id');
                foreach ($tag_item_data['list'] as $key => $item) {
                    if($tag_data[$item['tag_id']]['tag_type'] != 'tag')
                        continue;
                    $travel_tag_data[$item['object_id']][] = $tag_data[$item['tag_id']]['tag_name'];
                }
            }

            //游记评论数
            $comment_data = $this->tripsvc->select(array(
                'table' => 'ly_trip_statistics',
                'select' => 'trip_id,comment_num',
                'where' => array('type' => 'total','trip_id' => array('IN',"({$tripids})")),
            ));
            $travel_comment_data = UCommon::parseItem($comment_data['list'],'trip_id');

            //主表数据
//            $sql1="SELECT `id`,`title`,`summary`,`username`,`thumb`,`publish_time`,`start_time` FROM `tr_travel` WHERE `id` IN ({$tripids})";
//            $trip_data=$this->querySql($sql1);
            //2017-12-8 修改 调用redis   --Q
            foreach ($redis_data as $re_trip_id) {
                $redis_key = $redis_key = str_replace('{travel_id}', $re_trip_id, RedisDataService::REDIS_TRAVEL_LIST_DATA);
                $trip_data['list'][] = $this->redis_svc->dataHgetall($redis_key);
            }

            //内容表数据
            $content_data=$this->querySql("SELECT `id`,`travel_id`,`title` FROM `tr_travel_content` WHERE `travel_id` IN ({$tripids})");
			foreach($content_data['list'] as $k => $row){
				$content_dest_rel = $this->querySql('SELECT `dest_id` FROM `tr_travel_content_dest_rel` WHERE `travel_content_id` = '.$row['id']);
                $rel_dest_id = array();
                foreach($content_dest_rel['list'] as $rel_k => $rel_row){
                    $rel_dest_id[] = $rel_row['dest_id'];
                }
                $content_data['list'][$k]['dest_id'] = implode(',',$rel_dest_id);
                unset($content_data['list'][$k]['id']);
			}

            //游记图片关联表数据
            $sql3="SELECT `travel_id`,COUNT(*) AS img_count FROM `tr_travel_image_rel` WHERE `travel_id` IN ({$tripids}) GROUP BY `travel_id`";
            $img_data=$this->querySql($sql3);

            //数据整合
            if(!empty($trip_data["list"]) && !isset($trip_data['error'])){
                $trip_data=UCommon::parseItem($trip_data["list"],"id");
                $content_data=UCommon::parseItem($content_data["list"],"travel_id");
                $img_data=UCommon::parseItem($img_data["list"],"travel_id");
                foreach($trip_data as $trip_id => $trip_row){
                    $redis_key = str_replace('{travel_id}',$trip_id,RedisDataService::REDIS_TRAVEL_VIEW_NUM);
                    $redis_view_num = $this->redis_svc->dataGet($redis_key);
                    $trip_data[$trip_id]["trace"]=array();
                    if(isset($content_data[$trip_id])) $trip_data[$trip_id]["trace"]=$content_data[$trip_id];
                    $trip_data[$trip_id]['bonus'] = isset($travel_bonus_data[$trip_id]) ? $travel_bonus_data[$trip_id]['amt'] : '0.00';
                    $trip_data[$trip_id]['tag'] = isset($travel_tag_data[$trip_id]) ? $travel_tag_data[$trip_id] : array();
                    $trip_data[$trip_id]['pageCount'] = $redis_view_num ? $redis_view_num : '0';
                    $trip_data[$trip_id]['commentCount'] = isset($travel_comment_data[$trip_id]) ? $travel_comment_data[$trip_id]['comment_num'] : '0';
                    $trip_data[$trip_id]["img_count"] = $img_data[$trip_id]["img_count"] ? $img_data[$trip_id]["img_count"]:0;
                }
                $data["list"]=$trip_data;
            }
            $data["pages"]=array('itemCount'=>$totle,'pageCount'=>ceil($totle/$pageSize),'page'=>$page,'pageSize'=>$pageSize);
        }
        $this->_successResponse($data);
    }
}
