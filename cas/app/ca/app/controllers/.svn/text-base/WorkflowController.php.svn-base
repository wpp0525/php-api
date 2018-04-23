<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-8-21
 * Time: 下午1:48
 */
class WorkflowController extends ControllerBase {

    const INPUT_DATA_ERROR = '10001';
    const OPERATE_EXCEPTION = '10002';

    private $sys_core;
    private $staff_base_svc;
    private $staff_role_svc;
    private $temp_subject;

    public function initialize() {
        parent::initialize();
        $this->sys_core = $this->di->get('cas')->get('sct_system_core');
        $this->staff_base_svc = $this->di->get('cas')->get('staff_base_service');
        $this->staff_role_svc = $this->di->get('cas')->get('staff_role_service');
        $this->temp_subject = $this->di->get('cas')->get('temp_subject');
    }

    /**
     * 根据wf_group获取
     */
    public function getWorkFlowByGroupAction(){
        $wf_group = $this->wf_group;
        $wfData = $this->sys_core->getAllByCondition('cms_work_flow', '*', $wf_group, 'wf_group');
        $this->jsonResponse(array('results' => $wfData));
    }

    /**
     * 获取单条行程
     */
    public function getOneWorkPointAction(){
        $id = $this->id;
        $wfData = $this->sys_core->getOneByCondition('cms_work_flow', '*', $id);
        $this->jsonResponse(array('results' => $wfData));
    }

    /**
     * 工作流程点数
     */
    public function getWorkPointNumAction(){
        $wf_group = $this->wf_group;
        $wfData = $this->sys_core->getOneByCondition('cms_work_flow', 'MAX(wf_order) as max_num', $wf_group, 'wf_group');
        $this->jsonResponse(array('results' => $wfData));
    }

    public function editWorkPointAction(){

        $id = $this->id;
        $wf_before = $this->wf_before;
        $wf_name = $this->wf_name;

        $wfData = $this->sys_core->operateDataById('cms_work_flow', array('wf_before' => $wf_before, 'wf_name' => $wf_name), $id);
        $this->jsonResponse(array('results' => $wfData));
    }

    /**
     * 工作流程点 绑定方法
     */
    public function bindMethodToWorkFlowAction(){
        $id = $this->id;
        $wf_actions = $this->wf_actions;
        $wfData = $this->sys_core->operateDataById('cms_work_flow', array('wf_actions'=>$wf_actions), $id);
        $this->jsonResponse(array('results' => $wfData));
    }


    public function findWorkFlowMethodAction(){
        $id = $this->id;
        $wfData = $this->sys_core->getOneByCondition('cms_work_flow', 'wf_actions', $id);
        if(!empty($wfData['wf_actions'])){
            $wf_actions = $wfData['wf_actions'];
            $action_ids = explode(',', $wf_actions);
        }else{
            $action_ids = array();
        }
        $this->jsonResponse($action_ids);
    }


    /**
     * 工作流程点 绑定管理员
     */
    public function bindManagersToWorkPointAction(){
        $id = $this->id;
        $managers = $this->managers;
        $wfData = $this->sys_core->operateDataById('cms_work_flow_info', array('bind_managers'=>$managers), $id);
        $this->jsonResponse(array('results' => $wfData));
    }

    public function findWorkPointManagersAction(){

        $id = $this->id;
        $wfData = $this->sys_core->getOneByCondition('cms_work_flow_info', 'bind_managers', $id);

        if(!empty($wfData['bind_managers'])){
            $managers = $wfData['bind_managers'];
            $manager_ids = explode(',', $managers);
        }else{
            $manager_ids = array();
        }

        $this->jsonResponse($manager_ids);
    }


    /**
     * 新建 work flow info
     */
    public function createWorkFlowInfoAction(){
        $groupId = $this->groupId;
        $itemId = $this->itemId;
        $authorId = $this->authorId;

        $init_data = array(
            'wf_group' => $groupId,
            'item_id' => $itemId,
            'author' => $authorId,
        );

        $wfCount = $this->sys_core->getDataByConditionSrt('cms_work_flow_info', 'COUNT(id) as num', " wf_group = '{$groupId}' AND item_id = '{$itemId}' ");
        if($wfCount && !empty($wfCount[0]['num'])){
            $this->_errorResponse(self::OPERATE_EXCEPTION, '数据已存在，如有需要点击修复！');
            die;
        }

        $wfData = $this->sys_core->getOneByCondition('cms_work_flow', 'MAX(wf_order) as max_num', $groupId, 'wf_group');
        $wfNum = $wfData['max_num'];

        $loopNum = 0;
        if($wfNum){
            for($i = 1; $i <= $wfNum; $i++){
                $data = array(
                    'wf_order' => $i
                );
                $post_array = array_merge($init_data, $data);
                $res = $this->sys_core->operateDataById('cms_work_flow_info', $post_array);
                if($res){
                    $loopNum += 1;
                }
            }
        }

        if($loopNum == $wfNum){
            $this->jsonResponse(array('error'=>0,'msg' => 'success'));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常，请稍后查看，如有需要点击修复！');
        }

    }


    public function editChildByItemIdAction(){

        $dataType = $this->dataType;
        $itemId = $this->itemId;
        $childId = $this->childId;
        $groupId = $this->groupId;

        $wfData = $this->sys_core->getOneByCondition('cms_work_flow_info', 'child_item_ids', $itemId, 'item_id');

        if($wfData && is_array($wfData)){
            $childs = $wfData['child_item_ids'];
        }

        $child_ids = array();
        if($childs){
            $child_ids = explode(",", $childs);
        }

        $needDb = 0;
        if($dataType == 'del'){
            $key = array_search($childId, $child_ids);
            if($key){
                unset($child_ids[$key]);
                $needDb = 1;
            }
        }else{
            if(!in_array($childId, $child_ids)){
                $child_ids[] = $childId;
                $needDb = 1;
            }
        }

        if($needDb){
            $childs = implode(',', $child_ids);
            $conditionSrt = " wf_group = '{$groupId}' AND item_id = '{$itemId}' ";
            $res = $this->sys_core->updateDataByConditionSrt('cms_work_flow_info', array('child_item_ids' => $childs), $conditionSrt);

        }else{
            $res = 200;
        }

        if($res == 200){
            $this->jsonResponse(array('error'=>0,'msg' => 'success'));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常，请稍后再试！');
        }



    }


    /**
     * 修复工作节点
     */
    public function repireWorkFlowInfoAction(){

        $groupId = $this->groupId;
        $itemId = $this->itemId;
//        $wfNum = $this->wfNum;
        $authorId = $this->authorId;

        $init_data = array(
            'wf_group' => $groupId,
            'item_id' => $itemId,
            'author' => $authorId,
        );

        $where = " wf_group = '{$groupId}' AND item_id = '{$itemId}' ";
        $oldData = $this->sys_core->getDataByConditionSrt('cms_work_flow_info', '*', $where);

        $wfData = $this->sys_core->getOneByCondition('cms_work_flow', 'MAX(wf_order) as max_num', $groupId, 'wf_group');
        $wfNum = $wfData['max_num'];

        if($groupId == 1){
            $where2 = " parent_id = {$itemId} ";
            $cids = $this->temp_subject->getDataList($where2, null, 'subject_id');
            $ids = array();
            if($cids && is_array($cids)){
                foreach($cids as $vid){
                    $ids[] = $vid['subject_id'];
                }
                unset($cids);
                $cids_str = implode(',', $ids);
            }else{
                $cids_str = '';
            }
        }


        $datas = array();
        foreach($oldData as $data){
            $datas[$data['wf_order']] = $data;
        }
        $num = 0;
        if($wfNum){
            for($i = 1; $i <= $wfNum; $i++){
                if(empty($datas[$i])){
                    $data = array(
                        'wf_order' => $i,
                        'child_item_ids' => $cids_str
                    );
                    $post_array = array_merge($init_data, $data);
                    $res = $this->sys_core->operateDataById('cms_work_flow_info', $post_array);
                    if($res){
                        $num += 1;
                    }
                }else{
                    if($cids_str != $datas[$i]['child_item_ids']){
                        $data = array(
                            'child_item_ids' => $cids_str
                        );
                        $this->sys_core->operateDataById('cms_work_flow_info', $data, $datas[$i]['id']);
                    }
                    unset($datas[$i]);
                    $num += 1;
                }
            }
        }

        if(count($datas)>0){

            $ids = array();
            foreach($datas as $val){
                $ids[] = $val['id'];
            }
            $where = " id IN (".implode(',', $ids).") ";
            $params = array(
                'table' => 'cms_work_flow_info',
                'where' => $where
            );
            $this->sys_core->deleteData($params);
        }

        if($num == $wfNum){
            $this->jsonResponse(array('error'=>0,'msg' => 'success'));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常，请稍后查看，如有需要点击修复！');
        }

    }



    public function findMethodIdAction(){

        $action_route = $this->action_route;
        $return = array();
        if($action_route){
            $where = " `action_route` = '".$action_route."' ";
            $res = $this->sys_core->getDataByConditionSrt('cms_action', 'id', $where, 'one');
            if($res && !empty($res['id'])){
                $return['action_id'] = $res['id'];
            }
            $this->jsonResponse($return);
        }else{
            $this->_errorResponse(self::INPUT_DATA_ERROR, '未获取到必要参数！');
        }


    }



    public function updateFinishStatusAction(){
        $groupId = $this->groupId;
        $itemId = $this->itemId;
        $wfOrder = $this->wfOrder;
        $fstatus = $this->finishStatus == 'Y' ? 'Y' : 'N';

        $conditionSrt = " wf_group = '{$groupId}' AND item_id = '{$itemId}' AND wf_order = '{$wfOrder}' ";
        $res = $this->sys_core->updateDataByConditionSrt('cms_work_flow_info', array('finish_status' => $fstatus), $conditionSrt);
        if($res == 200){
            $this->jsonResponse(array('error'=>0,'msg' => 'success'));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常，请稍后再试！');
        }

    }


    public function allowShowLinkAction(){
        $adminId = $this->adminId;
        $groupId = $this->groupId;
        $itemId = $this->itemId;
        $wfOrder = $this->wfOrder;

        $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常！请检查是否需要修复流程！');
        // 判断是否有前置动作
        $where = " wf_group = '{$groupId}' AND wf_order = '{$wfOrder}' ";
        $wfData = $this->sys_core->getDataByConditionSrt('cms_work_flow', 'wf_before, wf_actions', $where, 'one');

        if(!empty($wfData['wf_before'])){
            $where1 = " wf_group = '{$groupId}' AND item_id = '{$itemId}'  AND wf_order in ({$wfData['wf_before']}) ";
            $wfiData = $this->sys_core->getDataByConditionSrt('cms_work_flow_info', 'finish_status', $where1);
        }

        if($wfiData && is_array($wfiData)){

            $isallow = 1;
            foreach($wfiData as $wfi){
                if($wfi['finish_status'] == 'N'){
                    $isallow = -1;
                    continue;
                }
            }
            if($isallow == -1){
                $this->_errorResponse(self::OPERATE_EXCEPTION, '前置节点未设置完成，请等待前置节点！');
            }else{

                $where2 = " wf_group = '{$groupId}' AND wf_order = '{$wfOrder}' AND item_id = '{$itemId}' ";
                $userData = $this->sys_core->getDataByConditionSrt('cms_work_flow_info', 'bind_managers, author', $where2, 'one');

                if($userData && is_array($userData)){
                    if($adminId == $userData['author']){
                        $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data'=>1));
                    }elseif($userData['bind_managers']){
                        $ids = explode(',', $userData['bind_managers']);
                        if(in_array($adminId, $ids)){
                            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => 1));
                        }else{
                            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => -1));
                        }
                    }else{
                        $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => -1));
                    }
                }else{
                    $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常！请检查是否需要修复流程！');
                }

            }

        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常！请检查是否需要修复流程！');
        }

    }

    public function getWorkFlowPointListAction(){

        $groupId = $this->groupId;
        $itemId = $this->itemId;
        $where1 = " wf_group = '{$groupId}' AND item_id = '{$itemId}' ";
        $wfiData = $this->sys_core->getDataByConditionSrt('cms_work_flow_info', '*', $where1);
        if($wfiData && is_array($wfiData)){
            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => $wfiData));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常！请检查是否需要修复流程！');
        }

    }

    public function findMethodByGroupAction(){
        $groupId = $this->groupId;

        $where = " wf_group = '{$groupId}' ";
        $wfiData = $this->sys_core->getDataByConditionSrt('cms_work_flow', 'wf_actions', $where, 'all');

        if($wfiData && is_array($wfiData)){
            $return = array();
            foreach($wfiData as $v){
                $return = array_merge($return, explode(',',$v['wf_actions']));
            }
            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => $return));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行出现异常！请检查是否需要修复流程！');
        }
    }

    public function isAllowAction(){
        $groupId = $this->groupId;
        $staffId = $this->staffId;
        $controllerName = $this->controllerName;
        $methodName = $this->methodName;
        $someParams = unserialize($this->someParams);
        $someKey = $this->someKey;

        if(!$groupId || !$staffId || !$controllerName || !$methodName || !$someKey){
            $this->jsonResponse(array('error'=>self::OPERATE_EXCEPTION,'msg' => 'wrong', 'data' => -1));
        }

        $where = " wf_group = '{$groupId}' ";
        $wfiData = $this->sys_core->getDataByConditionSrt('cms_work_flow', 'wf_actions', $where, 'all');



        if($wfiData && is_array($wfiData)){
            $blackMethod = array();
            foreach($wfiData as $k => $v){
                $tmp = array();
                if($v['wf_actions']){
                    $tmp = explode(',',$v['wf_actions']);
                    foreach($tmp as $v2){
                        $blackMethod[$v2] = $k+1;
                    }
                }
            }

//            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => $blackMethod));
            if(!$blackMethod){
                $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => 1));
            }else{
                $url = $controllerName.'|'.$methodName;
                $methodId = $this->sys_core->getMethodIdByRoute($url);

                if(in_array($methodId, array_keys($blackMethod))){

                    if(!empty($someParams[$someKey])){
                        $orderNo = $blackMethod[$methodId];
                        $itemId = $someParams[$someKey];
                        if($someKey == 'subjectId' && !empty($someParams['parentId'])){
                            $itemId = $someParams['parentId'];
                        }

                        $wf_tmp = $this->sys_core->getBindWFStaff($groupId, $itemId, $orderNo);

                        if($wf_tmp){
                            if(in_array($staffId, $wf_tmp)){
                                $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => 1));
                            }else{
                                $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => -1));
                            }
                        }else{
                            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => -1));
                        }
                    }

                }else{
                    $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => 1));
                }

            }

        }else{
            $this->jsonResponse(array('error'=>0,'msg' => 'success', 'data' => 1));
        }





    }



}
