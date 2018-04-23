<?php
/**
 * Created by PhpStorm.
 * User: hongwuji
 * Date: 2016/11/22
 * Time: 15:21
 * 专题分站内容控制器
 */
class SubjectsiteController extends ControllerBase{

    private $sub_list;
    private $sub_site;
    private $sub_web;
    private $sub_site_rel;

    /**
     * 初始化,加载数据层
     */
    public function initialize() {
        parent::initialize();
        $this->sub_list = $this->di->get('cas')->get('sub_list');
        $this->sub_site = $this->di->get('cas')->get('sub_site');
        $this->sub_web  = $this->di->get('cas')->get('sub_web_site');
        $this->sub_site_rel = $this->di->get('cas')->get('sub_site_rel');
    }

    /**
     * 专题列表
     */
    public function subListAction(){
        $page_size=$this->request->getPost('page_size');
        $page_num=$this->request->getPost('page_num');
        $where = $this->request->getPost('where');

        $where = json_decode($where,true);
        $page_size = $page_size?$page_size:10;
        $page_num = $page_num?$page_num:1;
        $result=$this->sub_list->getListByCondition($where,array('page_num'=>$page_num,'page_size'=>$page_size));
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     */
    public function subByIdAction(){
        $sub_id=$this->request->getPost('subject_id');
        if(!$sub_id){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
        $result=$this->sub_list->getOneById($sub_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 网站分站信息
     */
    public function webSiteListAction(){
        $page_size=$this->request->getPost('page_size');
        $page_num=$this->request->getPost('page_num');
        $where = $this->request->getPost('where');

        $where = json_decode($where,true);
        $page_size = $page_size?$page_size:10;
        $page_num = $page_num?$page_num:1;
        $result=$this->sub_web->getListByCondition($where,array('page_num'=>$page_num,'page_size'=>$page_size));
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据分站ID获取分站信息
     */
    public function webSiteByIdAction(){
        $website_id=$this->request->getPost('webwite_id');
        if(!$website_id){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
        $result=$this->sub_web->getOneById($website_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 专题分站信息
     */
    public function subSiteListAction(){
        $page_size=$this->request->getPost('page_size');
        $page_num=$this->request->getPost('page_num');
        $where = $this->request->getPost('where');

        $where = json_decode($where,true);
        $page_size = $page_size?$page_size:10;
        $page_num = $page_num?$page_num:1;

        $result=$this->sub_site->getListByCondition($where,array('page_num'=>$page_num,'page_size'=>$page_size));
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据专题分站ID获取分站信息
     */
    public function subSiteByIdAction(){
        $subsite_id=$this->request->getPost('subsite_id');
        if(!$subsite_id){
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
        $result=$this->sub_web->getOneById($subsite_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据专题ID获取关联专题分站
     */
    public function subSiteBySubAction(){
        $sub_id=$this->request->getPost('subject_id');
        $result=$this->sub_site_rel->getSiteListBySubId($sub_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据专题ID和专题分站 判断是否rel表已存在
     */
    public function subSiteBySiteAction(){
        $sub_id=$this->request->getPost('subject_id');
        $site_id=$this->request->getPost('subsite_id');
        $result=$this->sub_site_rel->getOneBySite($sub_id,$site_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据专题ID和专题分站 取其它分站关系
     */
    public function subByOtherSiteAction(){
        $sub_id=$this->request->getPost('subject_id');
        $site_id=$this->request->getPost('subsite_id');
        $result=$this->sub_site_rel->otherSiteGetList($sub_id,$site_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 根据专题ID和专题分站获取关联网站分站列表
     */
    public function webSiteBySiteAction(){
        $sub_id=$this->request->getPost('subject_id');
        $site_id=$this->request->getPost('subsite_id');
        $result=$this->sub_site_rel->getWebSiteByIds($sub_id,$site_id);
        if(!empty($result)){
            $this->jsonResponse($result);
        }else{
            $this->_errorResponse(DATA_NOT_FOUND,'数据不存在');
        }
    }

    /**
     * 数据插入
     */
    public  function insertDataAction(){
        $table_name=$this->request->getPost('table_name');
        $data=$this->request->getPost('data');
        $data = json_decode($data, true);

        if(empty($data) || !$table_name){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，插入失败');
        }else{
            $res=$this->sub_list->insert($data,$table_name);
            if($res){
                $this->jsonResponse($res);
            }
        }

    }

    /**
     * 更新数据
     */
    public function  updateDataAction(){
        $table_name=$this->request->getPost('table_name');
        $data=$this->request->getPost('data');
        $data = json_decode($data, true);

        $id=intval($data['id']);
        if(empty($data) || !$table_name){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，修改失败');
        }else{
            $res=$this->sub_list->update($id,$data,$table_name);
            if($res){
                $this->jsonResponse($res);
            }
        }
    }

    /**
     * 删除数据
     */
    public function  deleteDataAction(){
        $table_name=$this->request->getPost('table_name');
        $where=$this->request->getPost('where');
        $where = json_decode($where, true);

        if(empty($where) || !$table_name){
            $this->_errorResponse(DATA_NOT_FOUND,'数据有误，修改失败');
        }else{
            $res=$this->sub_list->delete($where,$table_name);
            if($res){
                $this->jsonResponse($res);
            }
        }
    }
}