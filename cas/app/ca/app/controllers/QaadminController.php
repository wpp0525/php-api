<?php

/**
 * QA管理后台
 * User: sx
 * Date: 2016/6/20
 * Time: 17:39
 */
class QaadminController extends ControllerBase
{
    private $admin_answer;
    private $answer;
    private $question;
    private $question_dest_rel;
    private $question_product_rel;
    private $tag;
    private $tag_category;

    public function initialize(){
        $this->api = 'QaAdmin';
        $this->admin_answer = $this->di->get('cas')->get('qaadminanswer-data-service');
        $this->answer = $this->di->get('cas')->get('qaanswer-data-service');
        $this->question = $this->di->get('cas')->get('qaquestion-data-service');
        $this->question_dest_rel = $this->di->get('cas')->get('qaquestiondestrel-data-service');
        $this->question_product_rel = $this->di->get('cas')->get('qaquestionproductrel-data-service');
        $this->question_tag_rel = $this->di->get('cas')->get('qaquestiontagrel-data-service');
        $this->tag = $this->di->get('cas')->get('qatag-data-service');
        $this->tag_category = $this->di->get('cas')->get('qatagcategory-data-service');
        parent::initialize();
    }
    /**
     * 问答标签的保存
     * @param int $id 标签ID
     * @param int $category_id 分类ID
     * @param int $name 标签名称
     * @param int $status 审核状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/saveTag
     */
    public function saveTagAction(){
        $id = isset($this->id) ? $this->id : 0;
        $category_id = isset($this->category_id) ? $this->category_id : 0;
        $name = isset($this->name) ? $this->name : '';
        $status = isset($this->status) ? $this->status : 1;
        if(!$category_id){
            $this->_errorResponse(10001,'请选择分类');
        }
        if(!$name){
            $this->_errorResponse(10002,'请填写名称');
        }
        $data = array(
            'category_id' => $category_id,
            'name' => $name,
            'status' => $status
        );
        if($id){
            $this->tag->update($id,$data) ? $this->_successResponse('保存成功') : $this->_errorResponse(10003,'保存失败');
        }else{
            $this->tag->insert($data) ? $this->_successResponse('添加成功') : $this->_errorResponse(10003,'添加失败');
        }
    }
    /**
     * 问答分类标签的保存
     * @param int $id 分类ID
     * @param int $name 分类名称
     * @param int $status 审核状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/saveTagCategory
     */
    public function saveTagCategoryAction(){
        $id = isset($this->id) ? $this->id : 0;
        $name = isset($this->name) ? $this->name : '';
        $status = isset($this->status) ? $this->status : 1;
        if(!$name){
            $this->_errorResponse(10001,'请填写分类名称');
        }
        $data = array(
            'name' => $name,
            'status' => $status
        );
        if($id){
            $this->tag_category->update($id,$data) ? $this->_successResponse('保存成功') : $this->_errorResponse(10002,'保存失败');
        }else{
            $this->tag_category->insert($data) ? $this->_successResponse('添加成功') : $this->_errorResponse(10002,'添加失败');
        }
    }
    /**
     * 管理员回答问题
     * @param int $id 回答ID
     * @param int $question_id 问题ID
     * @param int $admin_id 管理员ID
     * @param text $content 回答内容
     * @param int $status 审核状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/saveAdminAnswer
     */
    public function saveAdminAnswerAction(){
        $id = isset($this->id) ? $this->id : 0;
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $admin_id = isset($this->admin_id) ? $this->admin_id : 0;//应该是从session中获取
        $content = isset($this->content) ? $this->content : '';
        $status = isset($this->status) ? $this->status : 1;
        if(!$question_id){
            $this->_errorResponse(10001,'请传入正确的问题ID');
        }
        if(!$admin_id){
            $this->_errorResponse(10002,'请传入正确的管理员ID');
        }
        if(!$content){
            $this->_errorResponse(10003,'请传入答案内容');
        }
        $data = array(
            'question_id' => $question_id,
            'admin_id' => $admin_id,
            'content' => htmlspecialchars($content,ENT_QUOTES),
            'status' => $status
        );
        if($id){
            $data['update_time'] = time();
            $this->admin_answer->update($id,$data) ? $this->_successResponse('保存成功') : $this->_errorResponse(10004,'保存失败');
        }else{
            $data['create_time'] = $data['update_time'] = time();
            $this->admin_answer->insert($data) ? $this->_successResponse('添加成功') : $this->_errorResponse(10004,'添加失败');
        }
    }
    /**
     * 后台审核问题
     * @param int $id 问题ID
     * @param int $auditor_id 审核者ID
     * @param int $auditor_time 审核时间
     * @param int $status 审核状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/auditQuestion?id=15&auditor_id=20&status=2
     */
    public function auditQuestionAction(){
        $id = isset($this->id) ? $this->id : 0;//问题ID
        $auditor_id = isset($this->auditor_id) ? $this->auditor_id : 0;//审核者ID
        $status = isset($this->status)? $this->status : 0;//审核的状态
        if(!$id){
            $this->_errorResponse(10002,'请传入正确的问题ID');
        }
        if(!$auditor_id){
            $this->_errorResponse(10003,'请传入正确的审核者ID');
        }
        $data = array(
            'auditor_id' => $auditor_id,
            'audit_time' => time(),
            'main_status' => $status
        );
        $this->question->update($id,$data) ? $this->_successResponse('审核成功') : $this->_errorResponse(10004,'审核失败');
    }
    /**
     * 后台删除问题
     * @param int $id 问题ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/deleteQuestion?id=15
     */
    public function deleteQuestionAction(){
        $id = isset($this->id) ? $this->id : 0;//问题ID
        if(!$id){
            $this->_errorResponse(10002,'请传入正确的问题ID');
        }
        $data = array(
            'update_time' => time(),
            'del_status' => 1
        );
        $this->question->update($id,$data) ? $this->_successResponse('删除成功') : $this->_errorResponse(10003,'删除失败');
    }
    /**
     * 后台恢复删除了的问题
     * @param int $id 问题ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/resumeQuestion?id=15
     */
    public function resumeQuestionAction(){
        $id = isset($this->id) ? $this->id : 0;//问题ID
        if(!$id){
            $this->_errorResponse(10002,'请传入正确的问题ID');
        }
        $data = array(
            'update_time' => time(),
            'del_status' => 0
        );
        $this->question->update($id,$data) ? $this->_successResponse('恢复成功') : $this->_errorResponse(10003,'恢复失败');
    }
    /**
     * 将问题和标签管理
     * @param int $question_id 问题ID
     * @param int $tag_id 标签ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaadmin/addQuestionTag
     */
    public function addQuestionTagAction(){
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $tag_id = isset($this->tag_id) ? $this->tag_id : '';
        if(!$question_id || !is_numeric($question_id)){
            $this->_errorResponse(10002,'请传入正确的问题ID');
        }
        if(!$tag_id){
            $this->_errorResponse(10003,'请传入标签ID');
        }
        try{
            $tags = explode(',',$tag_id);
            $this->question_tag_rel->beginTransaction();
            foreach($tags as $t){
                if(is_numeric($t) && $t > 0){
                    //先判断是否存在,存在就不用插入,否则添加
                    $tmp = $this->question_tag_rel->getRsBySql('SELECT * FROM qa_question_tag_rel WHERE question_id = '.$question_id.' AND tag_id = '.$tag_id,true);
                    if($tmp) continue;
                    $data = array(
                        'question_id' => $question_id,
                        'tag_id' => $tag_id
                    );
                    $this->question_tag_rel->insert($data);
                }
                $this->question_tag_rel->beginTransaction();
            }
            $this->question_tag_rel->commit();
        }catch(\Pheanstalk\Exception $e){
            $this->question->rollBack();
        }
        $this->_successResponse('关联成功');
    }
}