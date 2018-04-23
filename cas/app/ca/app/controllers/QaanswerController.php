<?php
use Lvmama\Common\Utils\UCommon;
/**
 * QA问题咨询
 * User: sx
 * Date: 2016/6/15
 * Time: 10:34
 * 问答相关接口
 */
class QaanswerController extends ControllerBase
{
    private $admin_answer;
    private $answer;
    private $question;
    private $question_dest_rel;
    private $question_product_rel;
    private $question_tag_rel;
    private $tag;
    private $tag_category;
    private $tag_product_rel;
    private $category = array();
    private $category_tags = array();
    private $qa_tube = 'lvmama_qa_question';

    public function initialize(){
        $this->api = 'qa';
        $this->admin_answer = $this->di->get('cas')->get('qaadminanswer-data-service');
        $this->answer = $this->di->get('cas')->get('qaanswer-data-service');
        $this->question = $this->di->get('cas')->get('qaquestion-data-service');
        $this->question_dest_rel = $this->di->get('cas')->get('qaquestiondestrel-data-service');
        $this->question_product_rel = $this->di->get('cas')->get('qaquestionproductrel-data-service');
        $this->question_tag_rel = $this->di->get('cas')->get('qaquestiontagrel-data-service');
        $this->tag = $this->di->get('cas')->get('qatag-data-service');
        $this->tag_category = $this->di->get('cas')->get('qatagcategory-data-service');
        $this->tag_product_rel = $this->di->get('cas')->get('qatagproductrel-data-service');
        //把分类标签对应关系读取出来
        $this->getCateTag();
        parent::initialize();
    }

    /**
     * 取得分类和标签的对应关系
     */
    private function getCateTag(){
        $category_data = $this->tag_category->getRsBySql('SELECT * FROM qa_tag_category WHERE `status` = 1');
        foreach($category_data as $v){
            if(!isset($this->category[$v['id']])){
                //查询属于此分类的标签
                $v['tag_info'] = $this->tag->getRsBySql('SELECT `id`,`name` FROM `qa_tag` WHERE `category_id` = '.$v['id'].' AND `status` = 1');
                $this->category[$v['id']] = $v;
            }
        }
        $this->getCateTagIds();
    }

    /**
     * 取得分类ID和属于它的标签ID的对应关系(用于跟新BU标签或者分类下的标签和问题对应关系时使用)
     */
    private function getCateTagIds(){
        foreach($this->category as $v){
            $this->category_tags[$v['id']] = array();
            foreach($v['tag_info'] as $t){
                $this->category_tags[$v['id']][] = $t['id'];
            }
        }
    }
    public function indexAction(){
        $this->_successResponse('you vist is ok!');
    }
    /**
     * 获取指定问题和对应的答案列表
     * @param int $product_id 产品ID
     * @param int $category_id 产品所属的分类ID
     * @param int $tag_id 需要查询的标签ID
     * @param int $page 页码
     * @param int $pageSize 每页显示条数(不能超过30条)
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getList
     */
    public function getListAction(){
        $product_id = isset($this->product_id) ? $this->product_id : 0;
        $category_id = isset($this->category_id) ? $this->category_id : 0;
        $tag_id = isset($this->tag_id) ? $this->tag_id : 0;
        $page = isset($this->page) ? $this->page : 1;
        $pageSize = isset($this->pageSize) ? $this->pageSize : 15;
        if(!$product_id || !is_numeric($product_id)){
            $this->_errorResponse(10001,'请传入正确的product_id');
        }
        if(!$category_id || !is_numeric($category_id)){
            $this->_errorResponse(10002,'请传入正确的category_id');
        }
        if(!isset($this->category_tags[$category_id]) || !$this->category_tags[$category_id]){
            $this->_errorResponse(10003,'分类'.$category_id.'下无相关标签');
        }
        $result = array('pages' => array('itemCount' => 0,'pageCount' => 0,'page' => $page,'pageSize' => $pageSize));
        foreach($this->category[$category_id]['tag_info'] as $val){
            if($val['name'] == '常见问题'){//问题可以不跟产品相关
                $sql = 'SELECT COUNT(`id`) AS n FROM `qa_question` q,(SELECT `question_id` FROM `qa_question_tag_rel` WHERE `tag_id` = '.$val['id'].') qt WHERE q.id = qt.question_id AND q.`main_status` = 5 AND q.`del_status` = 0';
            }else{//必须跟产品相关
                $sql = 'SELECT COUNT(`id`) AS n FROM `qa_question` q,(SELECT qt.`question_id` FROM `qa_question_tag_rel` qt,(SELECT question_id FROM  `qa_question_product_rel` WHERE product_id = '.$product_id.') qp WHERE qt.question_id = qp.question_id AND qt.`tag_id` = '.$val['id'].') rs WHERE q.id = rs.question_id AND q.`main_status` = 5 AND q.`del_status` = 0';
            }
            $questionByTagCount = $this->question_tag_rel->getRsBySql($sql,true);
            if($questionByTagCount['n']){
                $val['questionCount'] = $questionByTagCount['n'];
                $result[$val['id']] = $val;
                $tag_id = $tag_id ? $tag_id : $val['id'];
            }
        }
        $result['pages']['tag_id'] = $tag_id;
        if(isset($result[$tag_id]['questionCount'])){
            $result['pages']['itemCount'] = $result[$tag_id]['questionCount'];
            $result['pages']['pageCount'] = ceil($result[$tag_id]['questionCount'] / $pageSize);
            $page = $page > $result['pages']['pageCount'] ? $result['pages']['pageCount'] : $page;//防止页数越界
            $start_limit = ($page - 1) * $pageSize;
            if($result[$tag_id]['name'] == '常见问题'){
                $sql = 'SELECT q.`id` AS `question_id`,q.`uid`,q.`username`,q.`title`,q.`content`,q.`create_time`,q.`update_time` FROM `qa_question` q,(SELECT `question_id` FROM `qa_question_tag_rel` WHERE `tag_id` = '.$tag_id.') qt WHERE qt.question_id = q.id AND q.`main_status` = 5 AND q.`del_status` = 0 ORDER BY q.create_time DESC LIMIT '.$start_limit.','.$pageSize;
            }else{
                $sql = 'SELECT q.`id` AS `question_id`,q.`uid`,q.`username`,q.`title`,q.`content`,q.`create_time`,q.`update_time` FROM `qa_question` q,(SELECT qt.`question_id` FROM `qa_question_tag_rel` qt,(SELECT question_id FROM `qa_question_product_rel` WHERE product_id = '.$product_id.') qp WHERE qt.question_id = qp.question_id AND qt.`tag_id` = '.$tag_id.') rs WHERE rs.question_id = q.id AND q.`main_status` = 5 AND q.`del_status` = 0 ORDER BY q.create_time DESC LIMIT '.$start_limit.','.$pageSize;
            }
            $_question = $this->question->getRsBySql($sql);
            foreach($_question as $k=>$q){//每个问题的客服答案查出来
                $_question[$k]['username'] = UCommon::maskMobile($_question[$k]['username']);
                $_question[$k]['title'] = $q['title'];
                $_question[$k]['content'] = $q['content'];
                $_answer = $this->answer->getRsBySql("SELECT * FROM qa_admin_answer WHERE question_id = {$q['question_id']} AND  status = 1 ORDER BY id DESC",true);
                $_question[$k]['answer'] = $_answer ? $_answer : new ArrayObject();
            }
            $result[$tag_id]['list'] = $_question;
        }
        $this->_successResponse($result);
    }
    /**
     * 获取详情页指定问题和对应的答案
     * @param int $question_id 问题ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getDetailByQuestionId
     */
    public function getDetailByQuestionIdAction(){
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        if(!$question_id){
            $this->_errorResponse(10001,'请传入正确的问题ID');
        }
        $_question = $this->question->getRsBySql("SELECT * FROM qa_question WHERE id = {$question_id} AND del_status = 0 AND main_status = 5",true);
        if(!$_question){
            $this->_errorResponse(10002,'该问题不存在或者已被删除!');
        }
        $_question['username'] = UCommon::maskMobile($_question['username']);
        $_question['title'] = $_question['title'];
        $_question['content'] = $_question['content'];
        $_answer = $this->answer->getRsBySql("SELECT * FROM qa_answer WHERE question_id = {$question_id} AND main_status = 5 AND del_status = 0",true);
        if($_answer){
            $_answer['username'] = UCommon::maskMobile($_answer['username']);
            $_answer['content'] = $_answer['content'];
        }else{
            $_answer = new ArrayObject();
        }
        $data = array('question' => $_question,'answer' => $_answer);
        $this->_successResponse($data);
    }
    /**
     * 获取指定问题的答案(审核通过的)
     * @param int $question_id 问题ID
     * @param int $answer_type 回答者 0全部1管理员2普通用户
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getAnswerByQuestionId
     */
    public function getAnswerByQuestionIdAction(){
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $answer_type = isset($this->answer_type) ? $this->answer_type : 0;
        if(!$question_id){
            $this->_errorResponse(10001,'请传入正确的问题ID');
        }
        switch($answer_type){
            case 1:
                $rs = $this->admin_answer->getRsBySql('SELECT * FROM `qa_admin_answer` WHERE question_id = '.$question_id.' AND `status` = 1');
                break;
            case 2:
                $rs = $this->answer->getRsBySql('SELECT * FROM `qa_answer` WHERE question_id = '.$question_id.' AND main_status > 3 AND del_status = 0');
                break;
            default:
                $admin_result = $this->admin_answer->getRsBySql('SELECT * FROM `qa_admin_answer` WHERE question_id = '.$question_id.' AND `status` = 1');
                $user_result = $this->answer->getRsBySql('SELECT * FROM `qa_answer` WHERE question_id = '.$question_id.' AND main_status > 3 AND del_status = 0');
                $rs = array_merge($admin_result,$user_result);
        }
        foreach($rs as $k=>$v){
            if(isset($rs[$k]['username'])){
                $rs[$k]['username'] = UCommon::maskMobile($v['username']);
            }
            $rs[$k]['content'] = $v['content'];
        }
        $this->_successResponse($rs);
    }
    /**
     * 用户回答问题
     * @param int $id 回答ID
     * @param int $question_id 问题ID
     * @param int $uid 用户ID
     * @param int $username 用户名
     * @param text $content 回答内容
     * @param int $status 审核状态
     * @param int $del_status 删除状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/saveAnswer
     */
    public function saveAnswerAction(){
        $id = isset($this->id) ? $this->id : 0;
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $uid = isset($this->uid) ? $this->uid : 0;//应该是从session中获取
        $username = isset($this->username) ? $this->username : '';//此处的username应该是管理员名称?
        $content = isset($this->content) ? $this->content : '';
        $status = isset($this->status) ? $this->status : 1;
        $del_status = isset($this->del_status) ? $this->del_status : 0;
        if(!$question_id){
            $this->_errorResponse(10001,'请传入正确的问题ID');
        }
        if(!$uid){
            $this->_errorResponse(10002,'请传入正确的用户ID');
        }
        if(!$content){
            $this->_errorResponse(10003,'请传入内容');
        }
        $data = array(
            'question_id' => $question_id,
            'uid' => $uid,
            'username' => $username,
            'content' => htmlspecialchars($content,ENT_QUOTES),
            'main_status' => $status,
            'del_status' => $del_status
        );
        if($id){
            $data['update_time'] = time();
            if($this->answer->update($id,$data)){
                $data = array_merge($data,array('id' => $id));
                $this->beanstalk->useTube($this->qa_tube)->put(json_encode($data),1024);
                $this->_successResponse('保存成功');
            }else{
                $this->_errorResponse(10004,'保存失败');
            }
        }else{
            $data['create_time'] = $data['update_time'] = time();
            if($this->answer->insert($data)){
                $answert_id = $this->answer->lastInsertId();
                $data = array_merge($data,array('id' => $answert_id));
                $this->beanstalk->useTube($this->qa_tube)->put(json_encode($data),1024);
                $this->_successResponse('添加成功');
            }else{
                $this->_errorResponse(10004,'添加失败');
            }
        }
    }
    /**
     * 保存问题
     * @param int $id 问题ID
     * @param int $dest_id 目的地ID
     * @param int $product_id 产品ID
     * @param int $tag_id 标签ID
     * @param int $bu_id 产品对应的BU_ID
     * @param int $uid 用户ID
     * @param varchar $username 用户名
     * @param varchar $title 问题标题
     * @param text $content 问题内容
     * @param int $auditor_id 审核者ID
     * @param int $auditor_time 审核时间
     * @param int $status 审核状态
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/saveQuestion
     */
    public function saveQuestionAction(){
        $id = isset($this->id) ? $this->id : 0;
        $dest_id = isset($this->dest_id) ? $this->dest_id : 0;
        $product_id = isset($this->product_id) ? $this->product_id : 0;
        $tag_id = isset($this->tag_id) ? $this->tag_id : 0;
        $bu_id = isset($this->bu_id) ? $this->bu_id : 0;
        $uid = isset($this->uid) ? $this->uid : 0;
        $username = isset($this->username) ? $this->username : '';
        $title = isset($this->title) ? $this->title : '';
        $content = isset($this->content) ? $this->content : '';
        $auditor_id = isset($this->auditor_id) ? $this->auditor_id : 0;//审核者ID
        $auditor_time = isset($this->audit_time) ? $this->audit_time : 0;//审核时间
        $status = isset($this->status) ? $this->status : 0;//状态：0-用户发布，1-审核通过，2-审核不通过，3-审核通过且隐藏
        $del_status = isset($this->del_status) ? $this->del_status : 0;//'删除状态：0-未删除，1-删除'
        if(!$product_id){
            $this->_errorResponse(10004,'请传入正确的产品ID');
        }
        if(!$bu_id){
            $this->_errorResponse(10005,'请传入正确的BUID');
        }
        if(!$content){
            $this->_errorResponse(10006,'问题内容不能为空');
        }
        $data = array(
            'uid' => $uid,
            'username' => $username,
            'title' => htmlspecialchars($title,ENT_QUOTES),
            'content' => htmlspecialchars($content,ENT_QUOTES),
            'auditor_id' => $auditor_id,
            'audit_time' => $auditor_time,
            'main_status' => $status,
            'del_status' => $del_status
        );
        //新增
        if(!$id){
            $data['create_time'] = $data['update_time'] = time();
            try{
                $this->question->beginTransaction();
                $this->question->insert($data);
                $question_id = $this->question->lastInsertId();
                $question_dest_rel_id = $this->question_dest_rel->insert(
                    array('question_id' => $question_id,'dest_id' => $dest_id)
                );
                $question_product_rel_id = $this->question_product_rel->insert(
                    array('question_id' => $question_id,'product_id' => $product_id)
                );
                //BU就是特殊的tag_id
                if(!isset($this->category_tags[1]) || !in_array($bu_id,$this->category_tags[1])){
                    $this->_errorResponse(10002,'BU标签不存在');
                }
                $this->question_tag_rel->insert(
                    array('question_id' => $question_id,'tag_id' => $bu_id)
                );
                $this->question_tag_rel->insert(
                    array('question_id' => $question_id,'tag_id' => $tag_id)
                );
                $this->question->commit();
            }catch (\Pheanstalk\Exception $e){//异常回滚
                $this->question->rollBack();
            }
            if($question_id && $question_dest_rel_id && $question_product_rel_id){
                $data = array_merge($data,array('id' => $question_id));
                $this->beanstalk->useTube($this->qa_tube)->put(json_encode($data),1024);
                $this->_successResponse('添加成功');
            }else{
                $this->_errorResponse(10002,'添加失败');
            }
        }else{
            //先查下此问题是否真的存在
            $question_tmp = $this->question->get($id);
            if(!isset($question_tmp['id'])){
                $this->_errorResponse(10003,'问题ID不存在,请检查');
            }
            $data['update_time'] = time();
            try{
                $this->question->beginTransaction();
                $question_flag = $this->question->update($id,$data);
                $question_dest_rel_id_flag = $this->question_dest_rel->update($id,array('dest_id' => $dest_id));
                $question_product_rel_flag = $this->question_product_rel->update($id,array('product_id' => $product_id));
                //更新问题BU对应关系
                if(!isset($this->category_tags[1]) || !in_array($bu_id,$this->category_tags[1])){
                    $this->_errorResponse(10002,'BU无法更新,BU标签ID不存在');
                }
                $bu_data = $this->question_tag_rel->getRsBySql('SELECT id FROM qa_question_tag_rel WHERE question_id = '.$id.' AND tag_id IN('.implode(',',$this->category_tags[1]).')',true);
                if(isset($bu_data['id']) && $bu_data['id']){
                    $this->question_tag_rel->update($bu_data['id'],array(
                        'question_id' => $id,
                        'tag_id' => $bu_id
                    ));
                }
                //更新问题标签对应关系(只能做更新操作,不能新增,不能删除)
                $tag_data = $this->question_tag_rel->getRsBySql('SELECT id FROM qa_question_tag_rel WHERE question_id = '.$id.' AND tag_id NOT IN('.implode(',',array_merge($this->category_tags[1],$this->category_tags[2])).')',true);
                    if(isset($tag_data['id']) && $tag_data['id']){
                        $this->question_tag_rel->update(
                            $tag_data['id'],
                            array('question_id' => $id,'tag_id' => $tag_id)
                        );
                    }
                $this->question->commit();
            }catch(\Pheanstalk\Exception $e){
                $this->question->rollBack();
            }
            if($question_flag && $question_dest_rel_id_flag && $question_product_rel_flag){
                $data = array_merge($data,array('id' => $id));
                $this->beanstalk->useTube($this->qa_tube)->put(json_encode($data),1024);
                $this->_successResponse('保存成功');
            }else{
                $this->_errorResponse(10002,'保存失败');
            }
        }
    }

    /**
     * 根据产品ID取得标签相应的标签信息
     * @param $product_id
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getTagByProductId
     */
    public function getTagByProductIdAction(){
        $product_id = isset($this->product_id) ? $this->product_id : 0;
        if(!$product_id){
            $this->_errorResponse(10001,'请传入正确的产品ID');
        }
        $tag = $this->tag_product_rel->getTagByProductId($product_id);
        $tag_ids = array();
        foreach($tag as $t){
            $tag_ids[] = $t['tag_id'];
        }
        if(!$tag_ids){//没有相应的标签
            $this->_successResponse(array());
        }else{//取出标签信息
            $tag_info = $this->tag->getRsBySql('SELECT * FROM `qa_tag` WHERE `id` IN ('.implode(',',$tag_ids).') AND `status` = 1');
            $this->_successResponse($tag_info);
        }
    }
    public function getTagByQuestionIdAction(){
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        if(!$question_id){
            $this->_errorResponse(10001,'请传入正确的问题ID');
        }
        $tag = $this->question_tag_rel->getRsBySql('SELECT tag_id FROM `qa_question_tag_rel` WHERE question_id = '.$question_id);
        $tag_ids = array();
        foreach($tag as $v){
            $tag_ids[] = $v['tag_id'];
        }
        if(!$tag_ids){

        }else{

        }
    }
    /**
     * 根据标签ID取得相应的产品ID
     * @param $tag_id
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getProductByTagId
     */
    public function getProductByTagIdAction(){
        $tag_id = isset($this->tag_id) ? $this->tag_id : 0;
        if(!$tag_id){
            $this->_errorResponse(10001,'请传入正确的标签ID');
        }
        $product = $this->tag_product_rel->getProductIdByTagId($tag_id);
        $product_ids = array();
        foreach($product as $p){
            $product_ids[] = $p['product_id'];
        }
        $this->_successResponse($product_ids);
    }
    /**
     * 根据分类ID取得相应标签信息
     * @param $category_id
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/getTagByCategoryId
     */
    public function getTagByCategoryIdAction(){
        $category_id = isset($this->category_id) ? $this->category_id : 0;
        if(!is_numeric($category_id) || !$category_id){
            $this->_errorResponse(10001,'请传入正确的分类ID');
        }
        $tag = $this->tag->getRsBySql('SELECT `id`,`name` FROM `qa_tag` WHERE category_id = '.$category_id.' AND `status` = 1');
        $this->_successResponse($tag);
    }

    /**
     * 保存标签和产品或者问题的关系
     * @param id int 记录ID
     * @param product_id int 产品ID
     * @param question_id int 问题ID
     * @param tag_id int 标签ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/saveTagRel
     */
    public function saveTagRelAction(){
        $id = isset($this->id) ? $this->id : 0;
        $product_id = isset($this->product_id) ? $this->product_id : 0;
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $tag_id = isset($this->tag_id) ? $this->tag_id : 0;
        if($id && !is_numeric($id)){
            $this->_errorResponse(10001,'请传入正确的ID');
        }
        if(!is_numeric($product_id) && !is_numeric($question_id)){
            $this->_errorResponse(10001,'请传入product_id或者question_id,且为大于0的整数');
        }
        if(!$tag_id || !is_numeric($tag_id)){
            $this->_errorResponse(10001,'请传入正确的标签ID');
        }
        if($product_id){
            $data = array('product_id' => $product_id,'tag_id' => $tag_id);
            $product_flag = $id ? $this->tag_product_rel->update($id,$data) : $this->tag_product_rel->insert($data);
            if($product_flag){
                $this->_successResponse($id ? '保存成功' : '添加成功');
            }else{
                $this->_successResponse($id ? '保存失败' : '添加失败');
            }
        }
        if($question_id){
            $data = array('question_id' => $question_id,'tag_id' => $tag_id);
            $question_flag = $id ? $this->question_tag_rel->update($id,$data) : $this->question_tag_rel->insert($data);
            if($question_flag){
                $this->_successResponse($id ? '保存成功' : '添加成功');
            }else{
                $this->_successResponse($id ? '保存失败' : '添加失败');
            }
        }
    }

    /**
     * 删除标签关系
     * @param id int 记录ID
     * @param product_id int 产品ID
     * @param question_id int 问题ID
     * @param tag_id int 标签ID
     * @return string | json
     * @example curl -i -X POST http://ca.lvmama.com/qaanswer/delTagRel
     */
    public function delTagRelAction(){
        $id = isset($this->id) ? $this->id : 0;
        $product_id = isset($this->product_id) ? $this->product_id : 0;
        $question_id = isset($this->question_id) ? $this->question_id : 0;
        $tag_id = isset($this->tag_id) ? $this->tag_id : 0;
        if($id && !is_numeric($id)){
            $this->_errorResponse(10001,'请传入正确的ID');
        }
        if(!is_numeric($product_id) && !is_numeric($question_id)){
            $this->_errorResponse(10001,'请传入product_id或者question_id,且为大于0的整数');
        }
        if(!$tag_id || !is_numeric($tag_id)){
            $this->_errorResponse(10001,'请传入正确的标签ID');
        }
        if($product_id){
            $data = array('product_id' => $product_id,'tag_id' => $tag_id);
            $product_flag = $id ? $this->tag_product_rel->deleteTag(array('id' => $id)) : $this->tag_product_rel->deleteTag($data);
            if($product_flag){
                $this->_successResponse('删除成功');
            }else{
                $this->_successResponse('删除失败');
            }
        }
        if($question_id){
            $data = array('question_id' => $question_id,'tag_id' => $tag_id);
            $question_flag = $id ? $this->question_tag_rel->deleteTag(array('id' => $id)) : $this->question_tag_rel->deleteTag($data);
            if($question_flag){
                $this->_successResponse('删除成功');
            }else{
                $this->_successResponse('删除失败');
            }
        }
    }
}