<?php

class MessageController extends ControllerBase {

    private $msg;
    private $group = array('SYSTEM','ASSET','ACTIVE','SOCIAL');

    public function initialize() {
        parent::initialize();
        $this->msg = $this->di->get('cas')->get('message-data-service');
    }

    public function indexAction(){
        $this->_errorResponse(100002, '缺少方法名');
    }

    /**
     * 根据uid获取用户未读消息数
     */
    public function getUnreadCountAction(){
        $uid = $this->uid;
        $count = $this->msg->getUnreadCount($uid);
        $this->jsonResponse(array('result' => $count));
    }

    /**
     * 根据uid,type类型获取用户 系统/资产/活动/互动 未读消息数
     */
    public function getTypeUnreadCountAction(){
        $uid = $this->uid;
        $type = $this->type;
        if($type != ''){
            if(!in_array($type, $this->group)) $this->_errorResponse(PARAMS_ERROR,'参数type类型错误');
            $typecode = $this->msg->getGroupCode($type);
            $type = $typecode->$type;
        }
        $count = $this->msg->getTypeUnreadCount($uid, $type);
        $this->jsonResponse(array('result' => $count));
    }

    /**
     * 根据mid获取消息详情
     */
    public function getMsgDetailAction(){
        $mid = $this->mid;
        $msg = $this->msg->getMsgDetail($mid);
        $this->jsonResponse(array('result' => $msg));
    }

    /**
     * 消息删除
     */
    public function msgDeleteAction(){
        $mid = $this->mid;
        $ret = $this->msg->msgDelete($mid);
        $this->jsonResponse(array('result' => $ret));
    }

    /**
     * 根据uid、type、unread获取用户消息概况(非消息具体内容)
     */
    public function getMsgByUidAction(){
        $uid = $this->uid;
        $type = $this->type ? $this->type : '';
        $unread = $this->unread ? $this->unread : 0;
        $page = $this->page ? $this->page : 1;
        $pageSize = $this->pageSize ? $this->pageSize : 10;
        if($type != ''){
            if(!in_array($type, $this->group)) $this->_errorResponse(PARAMS_ERROR,'参数type类型错误');
            $typecode = $this->msg->getGroupCode($type);
            $type = $typecode->$type;
        }
        $msgData = $this->msg->getMsgByUid($uid, $type, $unread, $page, $pageSize);
        $count = $this->msg->getMsgNumByUid($uid, $type, $unread);
        $amount = $this->msg->getMsgNumByUid($uid, $type);
        $groupCount = $this->msg->getGroupCountByUid($uid);
        $this->jsonResponse(array('result' => $msgData, 'count' => array('Count' => intval($count['Count']), 'PageNum' => intval($page), 'PageSize' => intval($pageSize), 'PageCount' => ceil($count['Count']/10), 'Amount' => intval($amount['Count'])), 'groupCount' => $groupCount));
    }

    /**
     * 阅读消息，更改消息状态status=1
     */
    public function msgReadAction(){
        $mid = $this->mid;
        $ret = $this->msg->msgRead($mid);
        $this->jsonResponse(array('result' => $ret));
    }

    /**
     * 根据用户uid获取所有消息(未读+已读+已删除)
     */
    public function getAllMsgByUidAction(){
        $uid = $this->uid;
        $page = $this->page ? $this->page : 1;
        $pageSize = $this->pageSize ? $this->pageSize : 10;
        $type_code = $this->msg->getTypeGroup();
        $msgData = $this->msg->getAllMsgByUid($uid, $page, $pageSize);
        foreach($msgData['data'] as $k => $v){
            $msgData['data'][$k]['type'] = $this->group[$type_code->$v['type']-1];
        }
        $msgData['count']['count'] = intval($msgData['count']['count']);
        $msgData['count']['pageNum'] = intval($page);
        $msgData['count']['pageSize'] = intval($pageSize);
        $msgData['count']['pageCount'] = ceil($msgData['count']['count']/$pageSize);
        $this->jsonResponse($msgData);
    }

}