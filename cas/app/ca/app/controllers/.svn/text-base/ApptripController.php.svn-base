<?php

use Lvmama\Cas\Service\BeanstalkDataService;

/**
 * APP游记 控制器
 *
 * @author zhta
 *
 */
class ApptripController extends ControllerBase {

    private $newtripsvc;

    public function initialize() {
        $this->newtripsvc = $this->di->get('cas')->get('travel_data_service');
        return parent::initialize();
    }

    /**
     * APP游记新增/修改
     *
     * @author zhta
     *
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
        if($this->memo) {
            $data['memo'] = $this->memo;
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
        if($this->status) {
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
                $this->newtripsvc->update(array("table"=>"app_travel","where"=>$where,"data"=>$data));
            }
        }else{
            $data["create_time"]=$now;
            $trip_data['table']="app_travel";
            $trip_data['data']=$data;
            $res=$this->newtripsvc->insert($trip_data);
            if($res["error"]==0){
                $this->trip_id=$res["result"];
            }
        }
        $trip_ext=$this->newtripsvc->select(array(
            "table"=>"app_travel_ext",
            'select' => 'id',
            'where' => array('travel_id'=>$this->trip_id)
        ));
        $trip_dest=$this->newtripsvc->select(array(
            "table"=>"app_travel_dest_rel",
            'select' => 'id',
            'where' => array('travel_id'=>$this->trip_id,'is_main'=>1)
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
        if($this->platform) {
            $ext_data['platform'] = $this->platform;
            $trip_flag=3;
        }
        if($this->device_no) {
            $ext_data['device_no'] = $this->device_no;
            $trip_flag=3;
        }
        if($this->version) {
            $ext_data['version'] = $this->version;
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
                $this->newtripsvc->update(array("table"=>"app_travel_ext","where"=>$ext_where,"data"=>$ext_data));
            }
        }else{
            $ext_data["create_time"]=$now;
            $ext_data["travel_id"]=$this->trip_id;
            $tripext_data['table']="app_travel_ext";
            $tripext_data['data']=$ext_data;
            $this->newtripsvc->insert($tripext_data);
        }
        if($this->dest_id) {
            $dest_data['dest_id'] = $this->dest_id;
            $trip_flag=4;
        }
        if($trip_dest["list"]) {
            if($trip_flag==4){
                $dest_where="id=".$trip_dest["list"][0]["id"];
                $this->newtripsvc->update(array("table"=>"app_travel_dest_rel","where"=>$dest_where,"data"=>$dest_data));
            }
        }else{
            $dest_data["create_time"]=$now;
            $dest_data["travel_id"]=$this->trip_id;
            $dest_data["is_main"]=1;
            $tripdest_data['table']="app_travel_dest_rel";
            $tripdest_data['data']=$dest_data;
            $this->newtripsvc->insert($tripdest_data);
        }
        $content = array(
            'tripid' => $this->trip_id,
        );
        $this->_successResponse($content);
    }

    /**
     * APP游记图片增改
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
        if($this->dest_id) {
            $data['dest_id'] = $this->dest_id;
            $img_flag=2;
        }
        if($this->memo) {
            $data['memo'] = $this->memo;
            $img_flag=2;
        }
        if($this->imgurl) {
            $data['url'] = $this->imgurl;
            $img_flag=2;
        }
        if($this->img_id) {
            $img_id=$this->img_id;
            if($img_flag==2){
                $where="id=".$img_id;
                $this->newtripsvc->update(array("table"=>"app_image","where"=>$where,"data"=>$data));
            }
        }else{
            $data["create_time"]=$now;
            $image_data['table']="app_image";
            $image_data['data']=$data;
            $res=$this->newtripsvc->insert($image_data);
            if($res["error"]==0){
                $img_id=$res["result"];
            }
        }
        $trip_image=$this->newtripsvc->select(array(
            "table"=>"app_travel_image_rel",
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
                $this->newtripsvc->update(array("table"=>"app_travel_image_rel","where"=>$image_where,"data"=>$image_rel_data));
            }
        }else{
            $image_rel_data["create_time"]=$now;
            $image_rel_data["image_id"]=$img_id;
            $trip_image_data['table']="app_travel_image_rel";
            $trip_image_data['data']=$image_rel_data;
            $this->newtripsvc->insert($trip_image_data);
        }
        $content = array(
            'imgid' => $img_id,
        );
        $this->_successResponse($content);
    }

    /**
     * APP游记点赞、收藏、评论
     *
     * @author zhta
     *
     */
    public function tripActAction() {
        $now = time();
        $data = array();
        if($this->parent_id) {
            $data['parent_id'] = $this->parent_id;
        }
        if($this->uid) {
            $data['uid'] = $this->uid;
        }
        if($this->username) {
            $data['username'] = $this->username;
        }
        if($this->trip_id) {
            $data['travel_id'] = $this->trip_id;
        }
        if($this->memo) {
            $data['memo'] = $this->memo;
        }
        if($this->id) {
            $id=$this->id;
            $where="id=".$id;
            $this->newtripsvc->update(array("table"=>"app_".$this->type,"where"=>$where,"data"=>$data));
        }else{
            $data["create_time"]=$now;
            $obj_data['table']="app_".$this->type;
            $obj_data['data']=$data;
            $res=$this->newtripsvc->insert($obj_data);
            if($res["error"]==0){
                $id=$res["result"];
            }
        }
        $content = array(
            'id' => $id,
        );
        $this->_successResponse($content);
    }

    /**
     * 数据查询
     *
     * @author zhta
     *
     */
    public function selectDataAction() {
        if($this->type) {
            $data['table'] = "app_".$this->type;
        }
        if($this->select) {
            $data['select'] = $this->select;
        }
        if($this->where) {
            $data['where'] = json_decode($this->where, true);
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
            $data['page'] = json_decode($this->page, true);
        }
        $res=$this->newtripsvc->select($data);
        $this->_successResponse($res);
    }

    /**
     * 游记列表
     *
     * @author zhta
     *
     */
    public function selectTripListAction() {
        $where="";
        $limit="0,1000";
        if($this->where) {
            $where=$this->where;
        }
        if($this->page) {
            $page=json_decode($this->page, true);
            $start=($page["page"]-1)*$page["pageSize"];
            $limit=$start.",".$page["pageSize"];
        }
        $sql="SELECT
                  a.*,
                  b.`order_id`,
                  b.`product_id`,
                  b.`source`,
                  b.`platform`,
                  b.`device_no`,
                  b.`version`,
                  b.`commit_time`,
                  b.`main_status`,
                  b.`del_status`,
                  b.`fanli_status`,
                  c.`dest_id`,
                  c.`is_main`
                FROM
                  `tr_app_travel` a
                  LEFT JOIN `tr_app_travel_ext` b
                    ON a.`id` = b.`travel_id`
                  LEFT JOIN `tr_app_travel_dest_rel` c
                    ON a.`id` = c.`travel_id` ".$where." limit ".$limit;
        $res=$this->querySql($sql);
        if($this->page){
            $count_sql = "SELECT
                  count(*) as itemCount
                FROM
                  `tr_app_travel` a
                  LEFT JOIN `tr_app_travel_ext` b
                    ON a.`id` = b.`travel_id`
                  LEFT JOIN `tr_app_travel_dest_rel` c
                    ON a.`id` = c.`travel_id` ".$where;
            $count_res=$this->querySql($count_sql);
            $itemCount = $count_res["list"][0]['itemCount'];
            $res['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $page["pageSize"]),
                'page' => $page["page"],
                'pageSize' => $page["pageSize"]
            );
        }
        $this->_successResponse($res);
    }

    /**
     * 图片列表
     *
     * @author zhta
     *
     */
    public function selectImgListAction() {
        $where="";
        $limit="0,1000";
        if($this->where) {
            $where=$this->where;
        }
        if($this->page) {
            $page=json_decode($this->page, true);
            $start=($page["page"]-1)*$page["pageSize"];
            $limit=$start.",".$page["pageSize"];
        }
        $sql="SELECT
                  a.*,
                  b.`travel_id`
                FROM
                  `tr_app_image` a
                  LEFT JOIN `tr_app_travel_image_rel` b
                    ON a.`id` = b.`image_id` ".$where." limit ".$limit;
        $res=$this->querySql($sql);
        if($this->page){
            $count_sql = "SELECT
                  count(*) as itemCount
                FROM
                  `tr_app_image` a
                  LEFT JOIN `tr_app_travel_image_rel` b
                    ON a.`id` = b.`image_id` ".$where;
            $count_res=$this->querySql($count_sql);
            $itemCount = $count_res["list"][0]['itemCount'];
            $res['pages'] = array(
                'itemCount' => $itemCount,
                'pageCount' => ceil($itemCount / $page["pageSize"]),
                'page' => $page["page"],
                'pageSize' => $page["pageSize"]
            );
        }
        $this->_successResponse($res);
    }

    /**
     * 数据删除(物理删除)
     *
     * @author zhta
     *
     */
    public function deleteTripAction() {
        if($this->type) {
            $type= $this->type;
            $data["table"]="app_".$this->type;
        }
        if($this->id) {
        	$id = $this->id;
            $data["where"]=array("id"=>$this->id);
        }
        switch ($type) {
            case 'trip':
                $data1["table"]="app_travel";
                $data1["where"]=array("id"=>$id);
                $res1=$this->newtripsvc->delete($data1);
                $data2["table"]="app_travel_dest_rel";
                $data2["where"]=array("travel_id"=>$id);
                $res2=$this->newtripsvc->delete($data2);
                $data3["table"]="app_travel_ext";
                $data3["where"]=array("travel_id"=>$id);
                $res3=$this->newtripsvc->delete($data3);
                $data4["table"]="app_travel_image_rel";
                $data4["where"]=array("travel_id"=>$id);
                $res4=$this->newtripsvc->delete($data4);
                $res=array('error' => $res1["error"],'result' => $res1["result"].";".$res2["result"].";".$res3["result"].";".$res4["result"]);
                break;
            case 'image':
                $data1["table"]="app_image";
                $data1["where"]=array("id"=>$id);
                $res1=$this->newtripsvc->delete($data1);
                $data2["table"]="app_travel_image_rel";
                $data2["where"]=array("image_id"=>$id);
                $res2=$this->newtripsvc->delete($data2);
                $res=array('error' => $res1["error"],'result' => $res1["result"].";".$res2["result"]);
                break;
            default:
                $res=$this->newtripsvc->delete($data);
                break;
        }
        $this->_successResponse($res);
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
}