<?php
/**
 * Created by PhpStorm.
 * User: liuhongfei
 * Date: 17-5-4
 * Time: 上午10:26
 */
use Lvmama\Common\Utils\UCommon;

class SctsystemcoreController extends ControllerBase {

    const INPUT_DATA_ERROR = '10001';
    const OPERATE_EXCEPTION = '10002';

    private $sys_core;

    public function initialize() {
        parent::initialize();
        $this->sys_core = $this->di->get('cas')->get('sct_system_core');
    }

    /**
     * 自动修复 action
     */
    public function repaireFunctionAction(){

        $controllers = json_decode($this->request->getPost('controller'), true);
        $methods = json_decode($this->request->getPost('method'), true);

        $res = array();
        if($controllers && is_array($controllers)){
            $res['controller'] = $this->repaireClass($controllers);
        }else{
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
            die;
        }


        if($methods && is_array($methods)){
            $res['method'] = $this->repaireActive($methods);
        }else{
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
            die;
        }

//        $this->jsonResponse($res['method']); die;
        $this->jsonResponse($res);
    }

    /**
     * 修复 cms_action_controller
     */
    private function repaireClass($controllers){

        // 获取全部controllers
        $contrs = $this->getAllClass('cms_action_controller', "*", 'id', " del_status != 'Y' ");

//        return $contrs; die;

        // 格式处理
        $fm_contrs = array();
        if($contrs){
            foreach($contrs as $contr){
                $fm_contrs[$contr['controller']] = $contr;
            }
//            unset($contrs);
        }
        $fm_contrs_keys = array_keys($fm_contrs);

        // 处理提交数据 并计数
        $new = $tot = $del = $up =  0;
        $back_array = array();
        foreach($controllers as $controller){

            if(!$controller['controller']) continue;
            if(!$controller['comment']) $controller['comment'] = $controller['controller'];

            $tot++;

            if(in_array($controller['controller'], $fm_contrs_keys)){
                if($fm_contrs[$controller['controller']]['comment'] == $controller['controller'] && $controller['comment'] != $controller['controller']){
                    $controller['repaire'] = 'X';
                    $res = $this->sys_core->operateDataById('cms_action_controller', array('comment' => $controller['comment'], 'repaire' => $controller['repaire']), $fm_contrs[$controller['controller']]['id']);
                }
                if($res > 0){
                    $controller['msg'] = 'RENAMED';
                    $tmp_c = array_merge($fm_contrs[$controller['controller']],$controller);
                    $back_array['repaire'][] = $tmp_c;
                    $up += 1;
                }
                unset($res);
                unset($fm_contrs[$controller['controller']]);
            }else{

                $controller['repaire'] = 'N';
                $controller['del_status'] = 'N';
                $res = $this->sys_core->operateDataById('cms_action_controller', $controller);
                if($res > 0){
                    $controller['id'] = $res;
                    $back_array['repaire'][] = $controller;
                    $new += 1;
                }
                unset($res);
            }
        }
        unset($fm_contrs_keys);

        if(count($fm_contrs) > 0){
            $del_data = $this->delUnusefulByIds('cms_action_controller', $fm_contrs);
            $back_array['delete'] = $del_data;
            if(!empty($del_data['success'])){
                $del = count($del_data['success']);
            }
        }

        return array('tot' => $tot, 'del' => $del, 'new' => $new, 'up' => $up, 'data'=>$back_array);

    }

    private function getAllClass($table_name, $select = "*", $order_key = 'id', $where = '1'){

        // 查询处理现有数据
        $params = array(
            'table' =>$table_name,
            'select' => $select,
            'where' => $where,
            'order' => "{$order_key} ASC"
        );
        $contrs = $this->sys_core->getAllByParams($params);

        return $contrs ? $contrs : array();

    }

    private function delUnusefulByIds($table_name, $data_array, $key='id', $type = 'X'){

        $del = array();
        foreach($data_array as $da){
            $res = $this->sys_core->operateDataById($table_name, array('del_status' => $type), $da['id'], $key);
            if($res > 0){
                $da['del_status'] = $type;
                $del['success'][] = $da;
            }else{
                $del['fail'][] = $da;
            }
        }unset($data_array);
        return $del;
    }

    private function repaireActive($methods){

        // 获取全部ACTive
        $actions= $this->getAllClass('cms_action', '*', 'id', " del_status != 'Y' ");

        // 格式处理
        $fm_actions = array();
        if($actions){
            foreach($actions as $action){
                $fm_actions[$action['class_name'].'-'.$action['method']] = $action;
            }
//            unset($actions);
        }

        $fm_actions_keys = array_keys($fm_actions);

        // 处理提交数据 并计数
        $new = $tot = $del = $up =  0;
        $back_array = array();
        foreach($methods as $method){

            if(!$method['class_name'] || !$method['method']) continue;
            if(!$method['action_name']) $method['action_name'] = $method['method'];

            $method['action_route'] = strtolower(substr($method['class_name'], 0, -10)."|".substr($method['method'], 0, -6));
            $method['letter'] = strtolower(substr($method['method'], 0, 1));
            $tot++;

            if(in_array($method['class_name'].'-'.$method['method'], $fm_actions_keys)){
                if($fm_actions[$method['class_name'].'-'.$method['method']]['action_name'] == $method['method'] && $method['action_name'] != $method['method']){
                    $data_array = array('action_name' =>$method['action_name'], 'repaire' => 'X');
                    if($method['params']){
                        $data_array['params'] = $method['params'];
                    }
                    $res = $this->sys_core->operateDataById('cms_action', $data_array, $fm_actions[$method['class_name'].'-'.$method['method']]['id']);
                }elseif($method['params']){
                    $this->sys_core->operateDataById('cms_action', array('params' =>$method['params']), $fm_actions[$method['class_name'].'-'.$method['method']]['id']);
                }

                if($res > 0){
                    $method['msg'] = 'RENAMED';
                    $tmp_a = array_merge($fm_actions[$method['class_name'].'-'.$method['method']],$method);
                    $back_array['repaire'][] = $tmp_a;
                    $up += 1;
                }
                unset($res);
                unset($fm_actions[$method['class_name'].'-'.$method['method']]);

            }else{
//return $method;
                $method['repaire'] = 'N';
                $method['del_status'] = 'N';
                $res = $this->sys_core->operateDataById('cms_action', $method);

//                return $method;
                if($res > 0){
                    $method['id'] = $res;
                    $back_array['repaire'][] = $method;
                    $new += 1;
                }
                unset($res);

            }
        }
        unset($fm_contrs_keys);

        if(count($fm_actions) > 0){
            $del_data = $this->delUnusefulByIds('cms_action', $fm_actions);
            $back_array['delete'] = $del_data;
            if(!empty($del_data['success'])){
                $del = count($del_data['success']);
            }
        }

        return array('tot' => $tot, 'del' => $del, 'new' => $new, 'up' => $up, 'data'=>$back_array);

    }


    /**
     * 修改controller/action名称
     */
    public function reNameAction(){

        $type = $this->request->getPost('data_type');
        $id = intval($this->request->getPost('id'));
        $newname = $this->request->getPost('new_name');

        if(!$newname || !$id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
            die;
        }

        $table = 'cms_action_controller';
        $colname = 'comment';
        if($type == 'action'){
            $table = 'cms_action';
            $colname = 'action_name';
        }

        $params[$colname] = $newname;
        $res = $this->sys_core->operateDataById($table, $params, $id);
        if($res > 0){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }

    }


    public function classListAction(){
        $show_type = $this->request->getPost('show_type') == 'all' ? 'all' : 'useful';
        $res = $this->getList('cms_action_controller', '*', $show_type);
        $this->jsonResponse($res);
    }

    public function methodListAction(){
        $show_type = $this->request->getPost('show_type') == 'all' ? 'all' : 'useful';
        $res = $this->getList('cms_action', '*', $show_type);
        $this->jsonResponse($res);
    }

    private function getList($table_name, $select = "*", $show_type = 'all'){
        $where = ' 1';
        if($show_type != 'all'){
            if($table_name == 'cms_action'){
                $where = " `action_name` != `method` ";
            }else{
                $where = " `comment` != `controller` ";
            }
        }
        $sdata = $this->sys_core->getDataByConditionSrt($table_name, $select, $where);
        return $sdata ? $sdata : array();

    }

    public function roleListAction(){

        $status = $this->request->getPost('status');
        $where = ' 1';
        if($status == 1){
            $where = " `status` = '{$status}' ";
        }
        $rdata = $this->sys_core->getDataByConditionSrt('cms_role', 'role_id, role_name, role_desc', $where);
        $this->jsonResponse($rdata);
    }

    public function classListPageAction(){
        $show_type = $this->request->getPost('show_type') == 'all' ? 'all' : 'useful';
        $page = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;


        $res = $this->getListPage('cms_action_controller', '*', $page, 'id', 'ASC');

        $this->jsonResponse($res);
    }

    public function methodListPageAction(){

        $show_type = $this->request->getPost('show_type') == 'all' ? 'all' : 'useful';
        $page = intval($this->request->getPost('page')) ? intval($this->request->getPost('page')) : 1;
        $cname = $this->request->getPost('cname');
        $del_status = $this->request->getPost('del_status') ? $this->request->getPost('del_status') : '';
        $permission_status = trim($this->request->getPost('permission_status')) ? trim($this->request->getPost('permission_status')) : '';

        if(!$cname){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
            die;
        }else{
            $where = array();
            $where['class_name'] = "=|{$cname}";
            if($del_status){
                if($del_status == 'Y'){
                    $where['del_status'] = "=|Y";
                }else{
                    $where['del_status'] = "!=|Y";
                }
            }
            if($permission_status){
                $where['permission_status'] = "=|{$permission_status}";
            }
//
//            $this->jsonResponse($where); die;
            $res = $this->getListPage('cms_action', '*', $page, 'id', 'ASC', $where);

            $this->jsonResponse($res);
        }

    }



    private function getListPage($table, $select, $page, $order = 'id', $order_type = 'ASC', $where = array()){
        // 组成查询全部条件
        $params_condition = array(
            'table' =>$table,
            'select' => $select,
            'order' => " {$order} {$order_type} ",
            'page' => array('pageSize' => 10, 'page' => $page)
        );
        if(!empty($where))
            $params_condition['where'] = $where;
        // 查询输出结果 json 格式
//        return $params_condition;
        $res = $this->sys_core->getByParams($params_condition);
        return $res;
    }

    public function getRelationListAction(){

        $type = $this->request->getPost('type');

        if($type == 'rc'){
            $table_name = 'cms_role_controller';
            $select = " role_id, controller_id AS item_id";
            $where = "1";
        }elseif($type == 'ra'){
            $cid= $this->request->getPost('cid');
            $table_name = 'cms_role_permission';
            $select = " role_id, action_id AS item_id";
            $where = " controller_id = {$cid}";
        }

        if($table_name){
            $sdata = $this->sys_core->getDataByConditionSrt($table_name, $select, $where);
        }

        $res = array();
        if($sdata && is_array($sdata)){
            foreach($sdata as $data){
                $res[$data['item_id']][] = $data['role_id'];
            }
        }

        $this->jsonResponse($res);
    }

//private function getList($table_name, $select = "*", $show_type = 'all'){
//    $where = ' 1';
//    if($show_type != 'all'){
//        if($table_name == 'cms_action'){
//            $where = " `action_name` != `method` ";
//        }else{
//            $where = " `comment` != `controller` ";
//        }
//    }
//    $sdata = $this->sys_core->getDataByConditionSrt($table_name, $select, $where);
//    return $sdata ? $sdata : array();
//
//}



    public function savaCMNameAction(){

        $type = $this->request->getPost('type');
        $id = $this->request->getPost('id');
        $name = $this->request->getPost('name');
        $params = array();

        if(!$id || !$name){
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
            die;
        }

        if($type == 'controller'){
            $table = 'cms_action_controller';
            $params['comment'] = $name;
        }elseif($type == 'method'){
            $table = 'cms_action';
            $params['action_name'] = $name;
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }

        $res = $this->sys_core->operateDataById($table, $params, $id);
        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }
    }


    public function savaGroupPermissionAction(){

        $action_type = $this->request->getPost('action_type') == 'insert' ? 'insert' : 'delete';
        $type = $this->request->getPost('type');
        $params = $del_params = array();

        if($type == 'gc'){
            $table = 'cms_role_controller';
            $params['role_id'] = $this->request->getPost('group_id');
            $params['controller_id'] = $this->request->getPost('action');
            $params['controller'] = $this->request->getPost('action_name');
            $del_where = " `role_id` = '{$params['role_id']}' AND `controller_id` = '{$params['controller_id']}' ";
        }elseif($type == 'ga'){
            $table = 'cms_role_permission';
            $params['role_id'] = $this->request->getPost('group_id');
            $params['action_id'] = $this->request->getPost('action');
            $params['action_method'] = $this->request->getPost('action_name');
            $params['controller_id'] = $this->request->getPost('cid');
            $del_where = " `role_id` = '{$params['role_id']}' AND `action_id` = '{$params['action_id']}' ";
        }elseif($type == 'ra'){
            $table = 'cms_staff_permission';
            $params['staff_id'] = $this->request->getPost('group_id');
            $params['action_id'] = $this->request->getPost('action');
            $params['action_method'] = $this->request->getPost('action_name');
            $del_where = " `staff_id` = '{$params['staff_id']}' AND `action_id` = '{$params['action_id']}' ";
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
            die;
        }

        $params['creator_id'] = $this->request->getPost('creator_id');
        $params['creator_name'] = $this->request->getPost('creator_name');
        $params['create_time'] = time();
        $params['create_ip'] = $this->request->getPost('create_ip');

        if($action_type == 'insert'){
            unset($del_where);
            $res = $this->sys_core->operateDataById($table, $params);
            unset($params);
        }else{
            unset($params);
            $del_params = array(
                'table' => $table,
                'where' => $del_where,
            );
            unset($del_where);
            $res = $this->sys_core->deleteData($del_params);
            unset($del_params);
        }

        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }

    }

    public function getOneClassAction(){
        $id = $this->request->getPost('id');
        if(!$id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
        }else{
            $res = $this->sys_core->getOneByCondition('cms_action_controller', '*', $id);
            $this->jsonResponse($res);
        }
    }


    public function getMenuListAction(){

        $page = intval(trim($this->request->getPost('page'))) ? intval(trim($this->request->getPost('page'))) : 1 ;
        $parent_id = intval(trim($this->request->getPost('parent_id'))) ? intval(trim($this->request->getPost('parent_id'))) : 0 ;
        $level = intval(trim($this->request->getPost('level')));

        $params_condition = array(
            'table' =>'cms_menu',
            'select' => '*',
            'order' => " sort_num ASC ",
            'page' => array('pageSize' => 15, 'page' => $page)
        );

        // 一级菜单
        if(!$level){
            $params_condition['where'] = array(
                'parent_id' => "=|0",
                'group_id' => "=|0",
            );
        }elseif($level == 1){
            $params_condition['where'] = array(
                'parent_id' => "=|0",
                'group_id' => "=|{$parent_id}",
            );
        }else{
            $params_condition['where'] = array(
                'parent_id' => "=|{$parent_id}",
                'group_id' => "<>|0",
            );
        }

        $res = $this->sys_core->getByParams($params_condition);
        $this->jsonResponse($res);

    }


    public function getMenuChildNumAction(){
        $parent_id = intval(trim($this->request->getPost('parent_id'))) ? intval(trim($this->request->getPost('parent_id'))) : 0 ;
        $level = intval(trim($this->request->getPost('level')));

        // 初始数据
        $params = array(
            'table' =>'cms_menu',
            'group' => ''
        );
        if($level == 0){
            $params['select'] = " group_id AS item_id, COUNT(group_id) AS item_num";
            $params['group'] = " group_id ";
        }elseif($level == 1){
            $params['select'] = " parent_id AS item_id, COUNT(parent_id) AS item_num";
            $params['group'] = " parent_id ";
        }else{
            $this->jsonResponse(array());
            die;
        }

        $res = $this->sys_core->getAllByParams($params);

        if($res && is_array($res)){
            $ret = array();
            foreach($res as $val){
                $ret[$val['item_id']] = $val['item_num'];
            }

            $this->jsonResponse($ret);

        }else{
            $this->jsonResponse(array());
        }


    }

    public function savaMenuColAction(){

        $type = $this->request->getPost('type');
        $item_id = $this->request->getPost('item_id');
        $item_value = $this->request->getPost('item_value');
        $params = array();

        if(!$item_id){
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
            die;
        }

        if($type == 'menu'){
            $table = 'cms_menu';
            $params['sort_num'] = $item_value;
            $key = 'menu_id';
        }elseif($type == 'menu_show_status'){
            $table = 'cms_menu';
            $params['status'] = 0;
            if($item_value == 'show'){
                $params['status'] = 1;
            }
            $key = 'menu_id';
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }

        $res = $this->sys_core->operateDataById($table, $params, $item_id, $key);

        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }
    }

    public function operateMenuAction(){

        $menu_id = intval(trim($this->request->getPost('menu_id'))) ? intval(trim($this->request->getPost('menu_id'))) : 0 ;
        $level = intval(trim($this->request->getPost('level'))) ? intval(trim($this->request->getPost('level'))) : 0 ;

        $params = array();


        $type = trim($this->request->getPost('type')) ? trim($this->request->getPost('type')) : 'info' ;
        if($type == 'bind'){
            $params['action_id'] = $this->request->getPost('action_id');
        }else{
            $params['menu_name'] = $this->request->getPost('menu_name');
            $params['menu_key'] = $this->request->getPost('menu_key');
            $params['sort_num'] = intval($this->request->getPost('sort_num'));
//
//            if(intval($this->request->getPost('group_id')))
//                $params['group_id'] = intval($this->request->getPost('group_id'));
//            if(intval($this->request->getPost('parent_id')))
//                $params['parent_id'] = intval($this->request->getPost('parent_id'));

            $params['group_id'] = intval($this->request->getPost('group_id'))?intval($this->request->getPost('group_id')):0;
            $params['parent_id'] = intval($this->request->getPost('parent_id'))?intval($this->request->getPost('parent_id')):0;

            $params['function_path'] = $this->request->getPost('function_path')?$this->request->getPost('function_path'):"";
        }

        if(!$menu_id){

            $params['creator_id'] = $this->request->getPost('creator_id');
            $params['creator_name'] = $this->request->getPost('creator_name');
            $params['create_ip'] = intval($this->request->getPost('create_ip'));
            $params['create_time'] = time();

        }else{
            $params['update_time'] = time();
        }
//        echo json_encode($params); die;

        $res = $this->sys_core->operateDataById('cms_menu', $params, $menu_id, 'menu_id');

        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }
    }



    public function editMenuAction(){

        $menu_id = intval(trim($this->request->getPost('menu_id'))) ? intval(trim($this->request->getPost('menu_id'))) : 0 ;

        $params['menu_name'] = trim($this->request->getPost('menu_name'));
        $params['sort_num'] = intval($this->request->getPost('sort_num'));

        $params['group_id'] = intval($this->request->getPost('group_id')) ? intval($this->request->getPost('group_id')) : 0;
        $params['parent_id'] = intval($this->request->getPost('parent_id')) ? intval($this->request->getPost('parent_id')) : 0;

        $params['function_path'] = trim($this->request->getPost('function_path')) ? trim($this->request->getPost('function_path')) : "";

        if($this->request->getPost('params_value')){
            $params['params_value'] = $this->request->getPost('params_value');
        }


        if(trim($this->request->getPost('action_route'))){
            $where = " `action_route` = '".trim($this->request->getPost('action_route'))."' ";
            $res = $this->sys_core->getDataByConditionSrt('cms_action', 'id', $where, 'one');
            if($res && !empty($res['id'])){
                $params['action_id'] = $res['id'];
            }else{
                $params['action_id'] = 0;
            }
        }


        // 不存在 menu_id 为新增
        if(!$menu_id){

            $params['menu_key'] = trim($this->request->getPost('menu_key')) ? trim($this->request->getPost('menu_key')) : 0 ;
            $params['creator_id'] = $this->request->getPost('creator_id');
            $params['creator_name'] = $this->request->getPost('creator_name');
            $params['create_ip'] = intval($this->request->getPost('create_ip'));
            $params['create_time'] = time();

            if($params['group_id'] && $params['parent_id']){
                // 删掉2级菜单的 路径和...动作
                $conditionSrt2 = " `menu_id` = {$params['parent_id']}";
                $setData2 = array(
                    'function_path' =>'',
                    'action_id' => 0
                );
                $this->sys_core->updateDataByConditionSrt("cms_menu", $setData2, $conditionSrt2);
            }
        }else{
            $params['update_time'] = time();

            $old_group = intval($this->request->getPost('old_group')) ? intval($this->request->getPost('old_group')) : 0;
            $old_parent = intval($this->request->getPost('old_parent')) ? intval($this->request->getPost('old_parent')) : 0;

            // 有变化确定更换父级 要更新子级菜单项
            if($params['group_id'] != $old_group || $params['parent_id'] != $old_parent){
                if($params['group_id'] == 0 && $params['parent_id'] == 0){
                    // two case : 1. 2 level => 1 level 2. 3 level => 1 level
                    // case 1 old 3 level => 2 level
                    // case 2 no change
                    $setData = array(
                        'group_id' => $menu_id,
                        'parent_id' => 0,
                    );
                    $conditionSrt = " `parent_id` = $menu_id";
                }elseif($params['group_id'] > 0 && $params['parent_id'] == 0){
                    // three case : 1. 1 level => 2 level    2. 2 level => 2 level 3. 3 level => 2 level
                    // case 1 : old 2 => 3
                    // case 2 : old 3 => 3 ( only change group_id )
                    // case 3 : no change
                    $setData = array(
                        'group_id' => $params['group_id'],
                        'parent_id' => $menu_id,
                    );
                    $updata_2l = 0;
                    if($old_group == 0){ // case 1
                        $conditionSrt = " `group_id` = $menu_id";
                    }elseif($old_group > 0){  // case 2
                        $conditionSrt = " `parent_id` = $menu_id";
                        $updata_2l = 1;
                        $cleanId = $menu_id;
                    }
                }else{
                    $updata_2l = 1;
                    $cleanId = $params['parent_id'];
                }

                if($setData){
                    $temp_res = $this->sys_core->updateDataByConditionSrt("cms_menu", $setData, $conditionSrt);

                    if($temp_res == 500){
                        $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
                        die;
                    }
                }

                if($updata_2l){
                    // 删掉2级菜单的 路径和...动作
                    $conditionSrt2 = " `menu_id` = $cleanId";
                    $setData2 = array(
                        'function_path' =>'',
                        'action_id' => 0
                    );
                   $this->sys_core->updateDataByConditionSrt("cms_menu", $setData2, $conditionSrt2);
                }
            }

        }

        $res = $this->sys_core->operateDataById('cms_menu', $params, $menu_id, 'menu_id');

        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }


    }




    public function getOneMenuAction(){

        $menu_id = intval(trim($this->request->getPost('menu_id')));

        if(!$menu_id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '参数有误');
            die;
        }

        $res = $this->sys_core->getOneByCondition('cms_menu', '*', $menu_id, 'menu_id');

        if($res){
            $this->jsonResponse($res);
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试');
        }

    }

    public function findParentsMenuAction(){

        $parent_id = intval(trim($this->request->getPost('parent_id')));
        $level = intval(trim($this->request->getPost('level'))) ? intval(trim($this->request->getPost('level'))) : 1;


        $params_condition = array(
            'table' =>'cms_menu',
            'select' => 'menu_id, menu_name, group_id',
            'where' => " parent_id = 0 ",
            'order' => " sort_num ASC, menu_id ASC",
        );

        if($level == 1){
            $params_condition['where'] .= "AND group_id = 0";
        }

        $res = $this->sys_core->getAllByParams($params_condition);

        if($res && is_array($res)){
            $this->jsonResponse($res);
        }else{
            $this->jsonResponse(array());
        }

    }

    public function getAllSystemAction(){
        $params_condition = array(
            'table' =>'cms_menu',
            'select' => '*',
            'order' => " sort_num ASC ",
            'where' => array(
                'parent_id' => "=|0",
                'group_id' => "=|0",
            )
        );
        $res = $this->sys_core->getByParams($params_condition);
        if($res && is_array($res) && !empty($res['list'])){
            $this->jsonResponse($res['list']);
        }else{
            $this->jsonResponse(array());
        }
    }


    /**
     * controller bind menu_id
     */
    public function classBindSystemIdAction(){

        $controller_id = intval(trim($this->request->getPost('controller_id')));
        $system_id = intval(trim($this->request->getPost('system_id')));

        if(!$controller_id || !$system_id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误！');
            die;
        }

        $res = $this->sys_core->operateDataById('cms_action_controller', array('system_id' => $system_id), $controller_id);
        if($res > 0){
            $this->jsonResponse(array('code' => 200, 'result' => '绑定成功！'));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试！');
        }

    }

    /**
     * 更新日志状态
     */
    public function updateLogStatusAction()
    {
        $id = $this->request->getPost('id');
        $log_status = $this->request->getPost('log_status');
        $type = $this->request->getPost('type');
        if($type == 'controller') {
            $this->sys_core->operateDataById('cms_action_controller', array('log_status' => $log_status), $id);
            $controller_data = $this->sys_core->getOneByCondition('cms_action_controller','controller',$id);
            $this->sys_core->operateDataById('cms_action', array('log_status' => $log_status), "'{$controller_data['controller']}'",'class_name');
        }elseif($type == 'action'){
            $this->sys_core->operateDataById('cms_action', array('log_status' => $log_status), $id);
            $action_data = $this->sys_core->getOneByCondition('cms_action','class_name',$id);
            $this->sys_core->operateDataById('cms_action_controller', array('log_status' => 'N'), "'{$action_data['class_name']}'",'controller');
        }
    }

    /**
     * 根据条件获取控制器名称
     */
    public function getControllerNameByParamsAction()
    {
        $controller_str = $this->request->getPost('controller_str');
        $params = array(
            'table' =>'cms_action_controller',
            'select' => 'controller,comment',
            'where' => "controller IN {$controller_str}",
        );
        $res = $this->sys_core->getAllByParams($params);

        $this->jsonResponse(UCommon::parseItem($res,'controller'));
    }

    /**
     * 根据条件获取方法名称
     */
    public function getActionNameByParamsAction()
    {
        $controller = $this->request->getPost('controller');
        $action = $this->request->getPost('action');

        $where = "method = '{$action}' AND class_name = '{$controller}'";
        $res  = $this->sys_core->getDataByConditionSrt('cms_action', 'action_name', $where, 'one');
        $this->jsonResponse($res);
    }
    public function updateDelstatusAction(){

        $id = $this->id;
        $type = $this->type;
        $del_status = $this->del_status;

        if($type == 'class'){
            $table_name = 'cms_action_controller';
        }else{
            $table_name = 'cms_action';
        }

        $res = $this->sys_core->operateDataById($table_name, array('del_status' => $del_status), $id);

        if($res > 0){
            $this->jsonResponse(array('error' => 0, 'error_description' => ''));
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试！');
        }

    }

    public function getCMenuAction(){
        $menu_id = intval(trim($this->request->getPost('menu_id')));

        if(!$menu_id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '参数有误');
            die;
        }

        // 查询处理现有数据
        $params = array(
            'table' => 'cms_menu',
            'select' => ' COUNT(1) as count',
            'where' => " `group_id` = $menu_id OR `parent_id` = $menu_id ",
        );

        $res = $this->sys_core->getAllByParams($params);

        if($res && is_array($res)){
            $res = $res[0];
        }else{
            $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试！');
        }

        $this->jsonResponse(array('error' => 0, 'error_description' => '', 'result' => $res));
    }


    public function delMenuAction(){

        $menu_id = intval(trim($this->request->getPost('menu_id')));
        $force = trim($this->request->getPost('force')) == 'Y' ? 'Y' : 'N';
        if(!$menu_id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '参数有误');
            die;
        }

        $where = "  `menu_id` = $menu_id";
        if($force == 'Y'){
            $where .= " OR `group_id` = $menu_id OR `parent_id` = $menu_id ";
        }

        $params = array(
            'table' => 'cms_menu',
            'where' => $where
        );
        $result = $this->sys_core->deleteData($params);

        $this->jsonResponse(array('error' => 0, 'error_description' => '', 'result' => $result));


    }


    /**
     * 查询子级的 最后一级的 层级数
     */
    public function findChildLevelAction(){

        $menuId = intval(trim($this->request->getPost('menu_id')));
        $groupId = intval(trim($this->request->getPost('group_id')));

        $params = array(
            'table' =>'cms_menu',
            'where' => '1',
            'group' => ''
        );
        $childLastLevel = 0;

        if($groupId > 0){
            // 二级菜单
            $params['where'] = " `group_id` = $groupId AND `parent_id` = $menuId ";
            $result = $this->sys_core->getSimplyCount($params);

            if($result['itemCount']) $childLastLevel = 3;

        }else{
            // 一级菜单
            // group_id
            $params['where'] = " `group_id` = $menuId AND `parent_id` != 0 ";
            $result = $this->sys_core->getSimplyCount($params);

            if($result['itemCount']){
                $childLastLevel = 3;
            }else{
                $params['where'] = " `group_id` = $menuId AND `parent_id` = 0 ";
                $result2 = $this->sys_core->getSimplyCount($params);
                if($result2['itemCount']) $childLastLevel = 2;
            }

        }

        $this->jsonResponse(array('error' => 0, 'error_description' => '', 'result' => array('childLastLevel' => $childLastLevel)));

    }

    public function showMenuTreeAction(){

        $level = intval(trim($this->request->getPost('showLevel')));

        $params_condition = array(
            'table' =>'cms_menu',
            'select' => 'menu_id, menu_name, group_id',
            'where' => " 1 ",
            'order' => " sort_num ASC, menu_id ASC",
        );

        if($level == 2){
            $params_condition['where'] = " `parent_id` = 0 ";
        }elseif($level == 1){
            $params_condition['where'] = " `group_id` = 0 ";
        }

        if($level > 0){
            $res = $this->sys_core->getAllByParams($params_condition);
        }else{
            $res = 0;
        }

        $this->jsonResponse(array('error' => 0, 'error_description' => '', 'result' => $res));

    }

    public function getOneMethodAction(){
        $id = $this->request->getPost('id');
        if(!$id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
        }else{
            $res = $this->sys_core->getOneByCondition('cms_action', '*', $id);
            $this->jsonResponse($res);
        }
    }


    /**
     * 根据系统ID获取控制器
     */
    public function getControllerByMenuIdAction()
    {
        $menu_id = $this->request->getPost('menu_id');
        $res = array();
        if(!$menu_id)
            $this->jsonResponse($res);
        $where = "system_id = '{$menu_id}'";
        $res  = $this->sys_core->getDataByConditionSrt('cms_action_controller', 'controller,comment', $where);
        $this->jsonResponse($res);
    }

    /**
     * 根据控制器获取方法名
     */
    public function getActionByControllerAction()
    {
        $controller = $this->request->getPost('controller');
        $res = array();
        if(!$controller)
            $this->jsonResponse($res);
        $where = "class_name = '{$controller}'";
        $res  = $this->sys_core->getDataByConditionSrt('cms_action', 'method,action_name', $where);
        $this->jsonResponse($res);
    }








    public function setPermissionParamsAction(){

        $id = $this->request->getPost('id');
        $permission_params = $this->request->getPost('permission_params');

        if(!$id){
            $this->_errorResponse(self::INPUT_DATA_ERROR, '输入的参数有误');
        }else{

            $setData = array();

            if($permission_params && $permission_params != 'NOTHING'){
                $setData['permission_status'] = 'S';
                $setData['permission_params'] = $permission_params;
//                $setData['permission_params'] = json_decode($permission_params, true);
//                $setData['permission_params'] ='111';
            }else{
                $methodData = $this->sys_core->getOneByCondition('cms_action', '*', $id);

                if($methodData['permission_status'] != 'N'){
                    $setData['permission_status'] = 'Y';
                }else{
                    $setData['permission_status'] = 'N';
                }
                $setData['permission_params'] = '';
            }
//
//                $this->jsonResponse($setData); die;

            $res = $this->sys_core->operateDataById("cms_action", $setData, $id);

            if($res > 0){
                $this->jsonResponse(array('code' => 200, 'result' => '绑定成功！'));
            }else{
                $this->_errorResponse(self::OPERATE_EXCEPTION, '执行失败，稍后重试！');
            }

        }






    }

}